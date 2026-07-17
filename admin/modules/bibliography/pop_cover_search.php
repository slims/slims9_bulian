<?php
/**
 * Copyright (C) 2026 SLiMS Cover Search Popup
 *
 */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

// privileges checking
$can_write = utility::havePrivilege('bibliography', 'w');
if (!$can_write) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}
# CHECK ACCESS
if ($_SESSION['uid'] != 1) {
    if (!utility::haveAccess('bibliography.bibliography-add') && !utility::haveAccess('bibliography.bibliography-edit')) {
        die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
    }
}

// Server-side proxy to bypass CORS restrictions
if (isset($_GET['ajax_search'])) {
    header('Content-Type: application/json');
    $query = trim($_GET['ajax_search']);
    if (empty($query)) {
        echo json_encode(['docs' => []]);
        exit;
    }
    
    $url = 'https://openlibrary.org/search.json?q=' . urlencode($query) . '&limit=30&fields=title,author_name,isbn,cover_i';
    
    try {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new Exception('Curl error: ' . curl_error($ch));
            }
            curl_close($ch);
            echo $response;
        } else {
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: " . $userAgent . "\r\n",
                    'timeout' => 15
                ]
            ];
            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                throw new Exception('file_get_contents failed to fetch URL');
            }
            echo $response;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['docs' => [], 'error' => $e->getMessage()]);
    }
    exit;
}

// page title
$page_title = __('Search Book Cover');

// start the output buffer
ob_start();
?>

<div class="popUpForm" style="padding: 20px;">
    <h4><i class="fa fa-search"></i> <?php echo __('Search Book Cover (Open Library)'); ?></h4>
    <hr />
    
    <div class="form-group">
        <div class="input-group">
            <input type="text" id="searchQuery" class="form-control" placeholder="<?php echo __('Type book title, author, or ISBN...'); ?>" value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>" style="font-size: 14px;">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" id="btnSearch">
                    <i class="fa fa-search"></i> <?php echo __('Search'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="searchLoader" class="text-center my-4 d-none">
        <i class="fa fa-spin fa-spinner fa-3x text-primary"></i>
        <p class="mt-2 text-muted"><?php echo __('Searching Open Library database...'); ?></p>
    </div>

    <!-- Error/Alert Box -->
    <div id="searchAlert" class="alert alert-warning d-none" role="alert"></div>

    <!-- Results Grid -->
    <div class="row" id="searchResults" style="margin-top: 20px; max-height: 400px; overflow-y: auto;">
        <!-- Results will be appended here dynamically -->
    </div>
</div>

<style>
.cover-item {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    padding: 10px;
    margin-bottom: 20px;
    text-align: center;
}
.cover-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    border-color: #007bff;
}
.cover-img-wrapper {
    height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}
.cover-img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}
.cover-title {
    font-size: 11px;
    font-weight: bold;
    height: 32px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 16px;
    color: #333;
}
.cover-author {
    font-size: 10px;
    color: #666;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    function performSearch() {
        var query = $('#searchQuery').val().trim();
        if (query === '') {
            $('#searchAlert').text('<?php echo __('Please enter a search query.'); ?>').removeClass('d-none');
            return;
        }

        $('#searchLoader').removeClass('d-none');
        $('#searchAlert').addClass('d-none');
        $('#searchResults').empty();

        // Call Local Proxy instead of direct API to bypass CORS
        var apiUrl = 'pop_cover_search.php?ajax_search=' + encodeURIComponent(query);
        
        $.ajax({
            url: apiUrl,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#searchLoader').addClass('d-none');
                
                if (!data.docs || data.docs.length === 0) {
                    $('#searchAlert').text('<?php echo __('No books found. Try different keywords.'); ?>').removeClass('d-none');
                    return;
                }

                var count = 0;
                data.docs.forEach(function(book) {
                    var coverId = book.cover_i;
                    var isbnList = book.isbn;
                    var coverUrl = '';

                    if (coverId) {
                        coverUrl = 'https://covers.openlibrary.org/b/id/' + coverId + '-L.jpg';
                    } else if (isbnList && isbnList.length > 0) {
                        coverUrl = 'https://covers.openlibrary.org/b/isbn/' + isbnList[0] + '-L.jpg';
                    }

                    // Skip books that don't have any covers
                    if (!coverUrl) return;

                    count++;
                    var title = book.title || 'Untitled';
                    var author = (book.author_name && book.author_name.length > 0) ? book.author_name.join(', ') : 'Unknown';

                    var html = `
                        <div class="col-md-3 col-sm-4 col-6">
                            <div class="cover-item" data-url="${coverUrl}">
                                <div class="cover-img-wrapper">
                                    <img src="${coverUrl.replace('-L.jpg', '-M.jpg')}" class="cover-img" alt="Cover" onerror="this.src='../../images/default/image.png'">
                                </div>
                                <div class="cover-title" title="${title}">${title}</div>
                                <div class="cover-author" title="${author}">${author}</div>
                            </div>
                        </div>
                    `;
                    $('#searchResults').append(html);
                });

                if (count === 0) {
                    $('#searchAlert').text('<?php echo __('No covers found for these books. Try searching by title/ISBN.'); ?>').removeClass('d-none');
                }
            },
            error: function(xhr, status, error) {
                $('#searchLoader').addClass('d-none');
                $('#searchAlert').text('<?php echo __('Error contacting Open Library API. Please check your internet connection.'); ?>').removeClass('d-none');
                console.error('API Error:', error);
            }
        });
    }

    // Bind Search Click
    $('#btnSearch').click(performSearch);

    // Bind Enter Key
    $('#searchQuery').keypress(function(e) {
        if (e.which === 13) {
            performSearch();
        }
    });

    // Auto trigger search if query parameter exists
    if ($('#searchQuery').val().trim() !== '') {
        performSearch();
    }

    // Handle cover selection
    $(document).on('click', '.cover-item', function() {
        var imageUrl = $(this).data('url');
        if (window.opener && !window.opener.closed) {
            var openerJq = window.opener.jQuery || window.opener.$;
            if (openerJq) {
                openerJq('#getUrl').val(imageUrl);
                openerJq('#getImage').click();
                window.close();
            } else {
                // Fallback using direct DOM manipulation if jQuery is not initialized yet
                var getUrlInput = window.opener.document.getElementById('getUrl');
                var getImageBtn = window.opener.document.getElementById('getImage');
                if (getUrlInput && getImageBtn) {
                    getUrlInput.value = imageUrl;
                    getImageBtn.click();
                    window.close();
                } else {
                    alert('Form elements not found in parent window.');
                }
            }
        } else {
            alert('Parent window closed.');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
