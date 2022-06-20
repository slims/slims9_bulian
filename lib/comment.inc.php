<?php
/**
 * Comment class
 * Class for record comment
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Some security patches by Hendro Wicaksono (hendrowicaksono@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

function showComment($_detail_id)
    {
		global $dbs;
        require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
		$_list_comment = '';
		if (!is_null(config('3rd_party_comment')))
		{
			// execute registered hook for 3rd party comment management
			\SLiMS\Plugins::getInstance()->execute('comment_init', [&$_list_comment]);
			return $_list_comment;
		}
		$_recs_each_page = 3;
		$_pages_each_set = 10;
		$_all_recs = 0;
		$_detail_id = (int)$_detail_id;
		
		if (ISSET($_GET['page']) && $_GET['page']>1) {
			$page = (int)$_GET['page'];
		} else {
			$page  = 1;
		}
		$_sql = "Select c.comment, m.member_name, c.input_date from comment AS c
		 LEFT JOIN biblio AS b ON b.biblio_id = c.biblio_id
		 LEFT JOIN member AS m ON m.member_id = c.member_id
		 WHERE b.biblio_id =".$_detail_id.
		 " ORDER BY c.last_update DESC";
		$commlist = $dbs->query($_sql);
		if ($commlist) {
			$_all_recs = $commlist->num_rows;
		}
		if ($_all_recs >0) {
			$_page = ($page -1) * $_recs_each_page;
			$_sql .= " Limit " . $_page. ", " . $_recs_each_page;
			$commlist = $dbs->query($_sql);
			$_list_comment .= '<div class="comment-found">'. $_all_recs . __(' comments available'). '</div>'; 
			while ($_data = $commlist->fetch_assoc()) {
				$_list_comment .= '<div class="comments">';
				$_list_comment .= '<div class="comment-member">'.$_data['member_name']. __(' at ') . $_data['input_date']. __(' write'). '</div>';
				$_list_comment .= '<div class="comment-content mt-2">'. $_data['comment'] . '</div>';
				$_list_comment .= '</div>';		
			}
			$_list_comment .= '<div class="comment-found">'.simbio_paging::paging($_all_recs, $_recs_each_page, $int_pages_each_set = 10, '', '_self').'</div>';
		}

		if (ISSET($_SESSION['mid'])) {
		// Comment form
			$_forms  = '<form method="post" id="mainForm" action="index.php?p=show_detail&id='.$_detail_id.'" class="comment-form">';
			//$_forms .=  simbio_form_element::textField('textarea','comment','','placeholder="Add your comment" class="comment-input form-control ckeditor"'). '<br />';
			$_forms .= '<div id="container0" class="mb-3" data-field="comment"><div id="ckeditor-toolbar0"></div><div id="ckeditor-content0" style="min-height: 150px"></div></div>';
			$_forms .= '<input type="submit" name="SaveComment" value="Save comment" class="s-btn btn btn-primary">';
			$_forms .= \Volnix\CSRF\CSRF::getHiddenInputString();
			$_forms .= '</form>';
			// ckeditor for rich feature
			$js = '<script type="text/javascript" src="'.JWB.'/ckeditor5/ckeditor.js"></script>';
			$js .= '<script type="text/javascript" src="'.JWB.'/ckeditor5/ckeditor.tinfo.js"></script>';
			$js .= "<script>createMultiEditor('1', '#mainForm', ['bold','italic','bulletedList','numberedList'])</script>";
			// $js .= "<script type=\"text/javascript\">CKEDITOR.config.toolbar = [['Bold','Italic','Underline','StrikeThrough','NumberedList','BulletedList','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']] ;</script>";

			return $_list_comment.$_forms.$js;
		} else  {
			return $_list_comment;
		}
    }
