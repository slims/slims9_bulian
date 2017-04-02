<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-15 19:31:26
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-22 19:37:25
 */

function getAuthors($biblio_id)
{
	global $dbs;

	$query = $dbs->query('SELECT ma.author_name FROM biblio_author AS ba 
		LEFT JOIN mst_author AS ma ON ba.author_id=ma.author_id
		WHERE ba.biblio_id='.$biblio_id);

	if ($query->num_rows > 0) {
		$author = '';
		while ($data = $query->fetch_row()) {
			$author .= $data[0];
			$author .= ' - ';
		}
		// remove last strip
		$author = substr_replace($author, '', -2);
		return $author;
	}
}

function biblioSimpleList($criteria, $limit, $header_title)
{
	global $sysconf;
	global $dbs;

	$book_q = $dbs->query('SELECT biblio_id, title, notes, image FROM biblio '.$criteria.' LIMIT '.$limit.'');

	$output = '';
	if ($book_q->num_rows > 0) {
		$output .= '<div class="slims-card slims-card--default slims-container slims-biblio-list">';
		$output .= '<div class="slims-card--header">';
	    $output .= '<h4>'.$header_title.'</h4>';
	    $output .= '</div>';
		while ($book = $book_q->fetch_assoc()) {
			// cover images var
			$image_cover = '';
			if ($sysconf['tg']['type'] == 'minigalnano') {
				
				if (!empty($book['image']) && !defined('LIGHTWEIGHT_MODE')) {
					$book_image = urlencode($book['image']);
					$image_loc = '../../images/docs/'.$book_image;
				} else {
					$image_loc = '../../images/default/image.png';
				}

				$thumb_url = './lib/minigalnano/createthumb.php?filename='.urlencode($image_loc).'&width=120';
				$image_cover = '<img src="'.$thumb_url.'" class="img-thumbnail" itemprop="image" alt="'.$book['title'].'" />';
			}

			$len = strlen($book['notes']);
			$lencut = 300;
			$notes = $book['notes'];

			if ($len > $lencut) {
				if (preg_match('/^.{1,300}\b/s', $notes, $match)) {
					$notes = $match[0].' ...';
				}
			}		

			$output .= '<div class="slims-row">';
			$output .= '<div class="slims-12 item">';
			$output .= '<div class="cover-list">'.$image_cover.'</div>';
			$output .= '<div class="detail-list"><h4><a href="index.php?p=show_detail&id='.$book['biblio_id'].'">'.$book['title'].'</a></h4>';
			$output .= '<div class="author">'.getAuthors($book['biblio_id']).'</div>';
			$output .= '<div class="notes">'.$notes.'</div>';
			$output .= '</div></div></div>';
		}
		$output .= '</div>';
	}

	return $output;
}