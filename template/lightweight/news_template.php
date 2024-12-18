<?php
function news_list_tpl($title, $path, $date, $summary) {
?>

<div class="panel panel-default">
<div class="panel-heading"><h3 class="content-title"><?php echo $title ?></h3></div>
<div class="panel-body">
<div class="content-date"><?php echo $date ?></div>
<p class="content-summary"><?php echo $summary ?>...</p>
<div class="content-readmore"><a class="btn btn-info btn-small" href="<?php echo SWB.'index.php?p='.$path ?>"><?php echo __('Read More') ?></a></div>
</div>
</div>

<?php
}