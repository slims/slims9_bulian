<?php
/**
 * APA Style citation
 * Copyright (C) 2015  Arie Nugraha (dicarve@gmail.com)
 * Modification by Drajat Hasan 2023 (drajathasan20@gmail.com)
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

//  set pre-processor variable
$author_list = [];
$authors_string = '';

// iterate some author data
foreach ($authors as $order => $data) {
  // chunk author name as an array based on space
  $chunk_name = explode(' ', $data['author_name']);
  // get last key order
  $last_chunkname_order = array_key_last($chunk_name);
  // set lastname
  $last_name = $chunk_name[$last_chunkname_order];
  // set first name
  $first_name = $chunk_name[0]??'';

  // Check everthing first name ended with comma or not
  if (!str_ends_with(trim($first_name), ',')) {
    unset($chunk_name[$last_chunkname_order]); // remote last chunkname
    if ($order > 0 & count($authors) > 2) continue; // don't make it pain, just say it et al if author > 2
    // Process for inverting name
    $author_list[] = $last_name . ', ' . implode(', ', array_map(fn($name) => ucfirst(substr($name, 0,1)) . '', $chunk_name)) . '.';
  } else {
    // Same as above
    if ($order > 0 & count($authors) > 2) continue;
    unset($chunk_name[0]);
    // if author have comma/before it already inverted
    $author_list[] = $first_name . ' ' . implode(', ', array_map(fn($name) => ucfirst(substr($name, 0,1)) . '', $chunk_name)) . '.';
  }
}

// glue all author data into one string
$authors_string = implode(', ', $author_list) . (count($authors) > 2 ? ' et al' : '');

?>
<p class="citation text-justify">
  <h3><?php echo __('APA Style'); ?></h3>
  <?php if ($authors_string) : ?>
    <span class="authors"><?php print $authors_string ?></span> <span class="year">(<?php print $publish_year ?>).</span>
    <span class="title"><em><?php print $title ?></em> <?php if ($edition) : ?>(<span class="edition"><?php print $edition ?>)</span><?php endif; ?>.</span>
  <?php else : ?>
    <span class="title"><em><?php print $title ?></em>.</span> <span class="year">(<?php print $publish_year ?>).</span>
  <?php endif; ?>
  <span class="publish_place"><?php print $publish_place ?>:</span>
  <span class="publisher"><?php print $publisher_name ?>.</span>
</p>