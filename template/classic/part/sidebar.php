<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-12 20:58:00
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-22 16:10:32
 */

?>

<div class="slims-4">
	<?php

	echo $header_info;

	?>

      <?php
      if (($sysconf['enable_search_clustering'])) : ?>
          <h2><?php echo __('Search Cluster'); ?></h2>

          <hr/>

      <?php
      if ($sysconf['index']['engine']['enable']) {
        echo $biblio_list->getClustering();
      } else {
      if (isset($_GET['keywords']) && (!empty($_GET['keywords']))) :
      ?>

          <div id="search-cluster">
              <div class="cluster-loading"><?php echo __('Generating search cluster...'); ?></div>
          </div>

          <script type="text/javascript">
              $('document').ready(function () {
                  $.ajax({
                      url: 'index.php?p=clustering&q=<?php echo urlencode($criteria); ?>',
                      type: 'GET',
                      success: function (data, status, jqXHR) {
                          $('#search-cluster').html(data);
                      }
                  });
              });
          </script>

      <?php endif; ?>

      <?php } ?>

      <?php endif; ?>

	<?php
    include 'content/language.php';

    include 'content/advance-search.php';

    include 'content/chat.php';

    include 'content/license.php';

  ?>
</div>