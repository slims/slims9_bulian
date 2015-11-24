<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-12 18:46:55
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-23 20:28:39
 */

if (!(isset($_GET['p']) && ($_GET['p'] == 'login' || $_GET['p'] == 'visitor'))) {
?>

<footer class="slims-row">
    <div class="slims-12">
        <div class="slims-card slims-card--default">Template by "Ido Alit" &copy; 2015</div>
    </div>
</footer>

<?php } ?>

</div> <!-- // wraper end -->

<script>

	<?php if(isset($_GET['search']) && ($_GET['keywords']) != '') : ?>
  	$('.biblioRecord .detail-list, .biblioRecord .title, .biblioRecord .abstract, .biblioRecord .controls').highlight(<?php echo $searched_words_js_array; ?>);
  	<?php endif; ?>

    <?php if(isset($_GET['p']) && ($_GET['p']) == 'librarian') : ?>
    var noLibrarian = $('.librarian-list p:first').text();
    if ( noLibrarian === 'No librarian data yet') {
        $('.librarian-list p:first').addClass('slims-card slims-card--warning');
    }
    <?php endif; ?>

    $(document).ready(function () {
        var eHeight = $('.slims-vertical').height();
        var docHeight = $(document).height();
        var eMarginTop = ( docHeight / 2 ) - ( eHeight / 2 );

        // vertical element
        $('.slims-vertical').css('margin-top', eMarginTop);

    });

</script>
</body>
</html>