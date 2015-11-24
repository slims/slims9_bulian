<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-15 13:10:58
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-15 18:50:37
 */

?>

<main class="slims-8">
	<?php 

	include 'content/simple-search.php';

	if(( isset($_GET['search']) || isset($_GET['title']) || isset($_GET['keywords']) || isset($_GET['p']))) {
		if (isset($_GET['search'])) {
			/*
			=========================
			result search
			=========================
			*/
			include 'content/search_result_info.php';
			
			echo '<div class="slims-container slims-card slims-card--default slims-biblio-list">';
			echo $main_content;
			echo '</div>';
		} else {
			if ($_GET['p'] === 'member') {
				// hack member layout
				echo '<div>';
			}

			include 'content/other-page.php';
		}
	} else {
		/*
		=========================
		home page
		=========================
		*/
		if ($sysconf['enable_promote_titles']) {
			include 'content/promoted-books.php';
		} else {
			include 'content/new-books.php';
		}
	}

	?>
</main>