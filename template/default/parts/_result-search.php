<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2018-01-23T11:32:46+07:00
# @Email:  ido.alit@gmail.com
# @Filename: _result-search.php
# @Last modified by:   user
# @Last modified time: 2018-01-26T16:53:58+07:00

?>

<div class="result-search">
    <section id="section1 container-fluid">
        <header class="c-header">
            <div class="mask"></div>
            <?php
            // ----------------------------------------------------------------------
            // include navbar part
            // ----------------------------------------------------------------------
            include '_navbar.php'; ?>
        </header>
        <?php
        // ------------------------------------------------------------------------
        // include search form part
        // ------------------------------------------------------------------------
        include '_search-form.php'; ?>
    </section>

    <section class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <h4>Filter</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-transparent pl-0 border-top-0">
                        <strong>Publish Year</strong>
                        <div>
                            <input type="text" class="year-slider" name="publish_year" value=""
                                   data-type="double"
                                   data-min="2000"
                                   data-max="2022"
                                   data-from="2010"
                                   data-to="2020"
                                   data-grid="true"
                            />
                        </div>
                    </li>
                    <li class="list-group-item bg-transparent pl-0">
                        <div class="d-flex justify-content-between align-items-center cursor-pointer" data-toggle="collapse" data-target="#collapseAvailability">
                            <strong>Availability</strong>
                            <i class="dropdown-toggle"></i>
                        </div>
                        <div class="collapse show text-sm" id="collapseAvailability">
                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1">
                                    <label class="form-check-label" for="exampleRadios1">
                                        Tersedia
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
                                    <label class="form-check-label" for="exampleRadios2">
                                        Sedang Dipinjam
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item bg-transparent pl-0">
                        <div class="d-flex justify-content-between align-items-center cursor-pointer" data-toggle="collapse" data-target="#collapseAttachment">
                            <strong>Attachment</strong>
                            <i class="dropdown-toggle"></i>
                        </div>
                        <div class="collapse show text-sm" id="collapseAttachment">
                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        PDF
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Audio
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Video
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item bg-transparent pl-0">
                        <div class="d-flex justify-content-between align-items-center cursor-pointer" data-toggle="collapse" data-target="#collapseSubject">
                            <strong>Subject</strong>
                            <i class="dropdown-toggle"></i>
                        </div>
                        <div class="collapse show text-sm" id="collapseSubject">
                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Komputer
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Indonesia
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Perpustakaan
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Web Service
                                    </label>
                                </div>
                                <div class="mt-2">
                                    <a href="#">Lihat Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item bg-transparent pl-0">
                        <div class="d-flex justify-content-between align-items-center cursor-pointer" data-toggle="collapse" data-target="#collapseCollectionType">
                            <strong>Collection Type</strong>
                            <i class="dropdown-toggle"></i>
                        </div>
                        <div class="collapse show text-sm" id="collapseCollectionType">
                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Textbook
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Reference
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Tandon
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item bg-transparent pl-0">
                        <div class="d-flex justify-content-between align-items-center cursor-pointer" data-toggle="collapse" data-target="#collapseGMD">
                            <strong>GMD</strong>
                            <i class="dropdown-toggle"></i>
                        </div>
                        <div class="collapse show text-sm" id="collapseGMD">
                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Text
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        CD-ROM
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Manuscript
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item bg-transparent pl-0">
                        <div class="d-flex justify-content-between align-items-center cursor-pointer" data-toggle="collapse" data-target="#collapseAuthor">
                            <strong>Author</strong>
                            <i class="dropdown-toggle"></i>
                        </div>
                        <div class="collapse show text-sm" id="collapseAuthor">
                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Waris Agung
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Ido Alit
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Ekky Maria
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Khaila Praditya
                                    </label>
                                </div>
                                <div class="mt-2">
                                    <a href="#">Lihat Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item bg-transparent pl-0">
                        <div class="d-flex justify-content-between align-items-center cursor-pointer" data-toggle="collapse" data-target="#collapseLocation">
                            <strong>Location</strong>
                            <i class="dropdown-toggle"></i>
                        </div>
                        <div class="collapse show text-sm" id="collapseLocation">
                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Perpustakaan Kampus 1
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Perpustakaan Kampus 2
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Ruang Reference
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        BI Corner
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item bg-transparent pl-0">
                        <div class="d-flex justify-content-between align-items-center cursor-pointer" data-toggle="collapse" data-target="#collapseColor">
                            <strong>Color</strong>
                            <i class="dropdown-toggle"></i>
                        </div>
                        <div class="collapse show text-sm" id="collapseColor">
                            <div class="mt-2 d-flex flex-wrap">
                                <a href="#" class="p-3 rounded-circle border mr-3 mb-3"></a>
                                <a href="#" class="p-3 rounded-circle border mr-3 mb-3"></a>
                                <a href="#" class="p-3 rounded-circle border mr-3 mb-3"></a>
                                <a href="#" class="p-3 rounded-circle border mr-3 mb-3"></a>
                                <a href="#" class="p-3 rounded-circle border mr-3 mb-3"></a>
                                <a href="#" class="p-3 rounded-circle border mr-3 mb-3"></a>
                                <a href="#" class="p-3 rounded-circle border mr-3 mb-3"></a>
                                <a href="#" class="p-3 rounded-circle border mr-3 mb-3"></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item bg-transparent pl-0">
                        <div class="d-flex justify-content-between align-items-center cursor-pointer" data-toggle="collapse" data-target="#collapseLanguage">
                            <strong>Language</strong>
                            <i class="dropdown-toggle"></i>
                        </div>
                        <div class="collapse show text-sm" id="collapseLanguage">
                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Indonesia
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        English
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mt-1 mb-2 text-sm">
                    <div>
                        <?php
                        $keywords_info = '<span class="search-keyword-info" title="' . htmlentities($keywords) . '">' . ((strlen($keywords) > 30) ? substr($keywords, 0, 30) . '...' : $keywords) . '</span>';
                        $search_result_info = '<div class="search-found-info">';
                        $search_result_info .= __('Found <strong>{biblio_list->num_rows}</strong> from your keywords') . ': <strong class="search-found-info-keywords">' . $keywords_info . '</strong>';
                        $search_result_info .= '</div>';
                        echo str_replace('{biblio_list->num_rows}', $engine->getNumRows(), $search_result_info);
                        ?>
                    </div>
                    <form class="form-inline pl-3">
                        <label class="mr-2 font-weight-bold" for="result-sort">Sort by</label>
                        <select class="custom-select custom-select-sm" id="result-sort">
                            <option selected>Most relevant</option>
                            <option value="1">Last Update</option>
                            <option value="2">Publish Year</option>
                            <option value="3">Title Ascending</option>
                            <option value="3">Title Descending</option>
                        </select>
                    </form>
                </div>
                <div class="wrapper">
                    <?php
                    // catch empty list
                    if (trim(strip_tags($main_content)) === '') {
                        echo '<h2 class="text-danger">' . __('No Result') . '</h2><hr/><p class="text-danger">' . __('Please try again') . '</p>';
                    } else {
                        echo $main_content;
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</div>
