<?php
/**
 * APA Style citation
 * Copyright (C) 2015  Arie Nugraha (dicarve@gmail.com)
 *
 * Available data to use:
 * $author_list    : Array of authors <-- you must pre-proccess this to string first
 * $authors_string : String of authors name separated by comma if there is more than one
 * $title          : String of title
 * $publish_year   : String of publication year
 * $edition        : String of edition statement
 * $publish_place  : String of place of publication
 * $publisher_name : String of name of publisher
 * $gmd_name       : String of name of GMD/Document format
 *
 */

?>
<p class="citation">
  <h3><?php echo __('Chicago Style'); ?></h3>
  <?php if ($authors_string) : ?>
    <span class="authors"><?php print $authors_string ?>.</span>
    <span class="title"><em><?php print $title ?></em>.</span>
    <span class="edition"><?php print $edition ?></span>
  <?php else : ?>
    <span class="title"><em><?php print $title ?></em>.</span>
    <span class="edition"><?php print $edition ?>.</span>
  <?php endif; ?>
  <span class="publish_place"><?php print $publish_place ?>:</span>
  <span class="publisher"><?php print $publisher_name ?>,</span>
  <span class="year"><?php print $publish_year ?>.</span>
  <span class="gmd_name"><?php print $gmd_name ?>.</span>
</p>
