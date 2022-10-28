<?php
/**
 * Template for Biblio List
 * name of memberID text field must be: memberID
 * name of institution text field must be: institution
 *
 * Copyright (C) 2015 Arie Nugraha (dicarve@gmail.com)
 * Create by Eddy Subratha (eddy.subratha@slims.web.id)
 *
 * Slims 8 (Akasia)
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
 */

$label_cache = array();
/**
 *
 * Format bibliographic item list for OPAC display
 *
 * @param   object $dbs
 * @param   array $biblio_detail
 * @param   int $n
 * @param   array $settings
 * @param   array $return_back
 *
 * @return string
 */
function biblio_list_format($dbs, $biblio_detail, $n, $settings = array(), &$return_back = array()) {
    global $label_cache, $sysconf;
    // init output var
    $output     = '';

    $title      = $biblio_detail['title'];
    $biblio_id  = $biblio_detail['biblio_id'];
    $detail_url = SWB.'index.php?p=show_detail&id='.$biblio_id.'&keywords='.$settings['keywords'];
    $cite_url   = SWB.'index.php?p=cite&id='.$biblio_id.'&keywords='.$settings['keywords'];
    // $title_link = '<a href="'.$detail_url.'" class="titleField" itemprop="name" property="name" title="'.__('View record detail description for this title').'">'.$title.'</a>';

    // image thumbnail
    $images_loc = 'images/docs/'.$biblio_detail['image'];
    if($biblio_detail['image'] == '' || $biblio_detail['image'] == NULL){
        $images_loc = 'images/default/image.png';
    }
    $thumb_url = './lib/minigalnano/createthumb.php?filename='.urlencode($images_loc).'&width=240';

    // notes
    $notes = getNotes($dbs, $biblio_id);
    $custom_field = '';
    $grid_item_content = '';
    $i = 0;
    $expand = true;
    if ($settings['enable_custom_frontpage'] AND $settings['custom_fields']) {
        $custom_field = '<dl class="row text-sm">';
        foreach ($settings['custom_fields'] as $field => $field_opts) {
            if ($field_opts[0] == 1) {
                $field_value = (trim($biblio_detail[$field]??'') !== '' ? $biblio_detail[$field] : '-');
                $custom_field .= '<dt class="col-sm-3">'.$field_opts[1].'</dt><dd class="col-sm-9">'.$field_value.'</dd>';
                $grid_item_content .= '<li class="list-group-item"><label>'.$field_opts[1].'</label><span class="text-right">'.$field_value.'</span></li>';
                $i++;
            }
        }
        $custom_field .= '</dl>';
    }
    if (empty($notes)) {
        $notes = $custom_field;
        $expand = false;
    }

    // availability
    $availability = getAvailability($dbs, $biblio_id);
    $class_avail = ($availability > 0) ? '' : 'text-danger';

    // authors
    $_authors = isset($biblio_detail['author'])?$biblio_detail['author']:biblio_list_model::getAuthors($dbs, $biblio_id, true);
    $_authors_string = '';
    if ($_authors) {
        if (!is_array($_authors)) {
            $_authors = explode('-', $_authors);
        }
        foreach ($_authors as $a) {
            $a = trim($a);
            $_authors_string .= '<a href="index.php?author='.urlencode($a).'&search=Search" itemprop="name" property="name" class="btn btn-outline-secondary btn-rounded">'.$a.'</a>';
        }
    }

    if (($_POST['view'] ?? $_SESSION['LIST_VIEW'] ?? 'list') === 'list'):

        $output .= '<div class="card item border-0 elevation-1 mb-6">';
        $output .= '<div class="card-body">';
        $output .= '<div class="row">';
        $output .= '<div class="col-12 col-md-2">';
        $output .= '<img loading="lazy" src="'.$thumb_url.'" alt="cover" class="img-fluid rounded '.($availability > 0 ?: 'not-available').'" />';
        $output .= '</div>'; // -- close col-2
        $output .= '<div class="col-8">';
        $output .= '<h5><a title="'.__('View record detail description for this title').'" class="card-link text-dark" href="'.$detail_url.'">'.addEllipsis($title, 80).'</a></h5>';
        $output .= '<div class="d-flex authors flex-wrap py-2">';
        $output .= $_authors_string;
        $output .= '</div>'; // -- close d-flex authors flex-wrap
        $output .= '<p>'.$notes.'</p>';
        $output .= '<div id="expand-'.$biblio_id.'" class="collapse py-2 collapse-detail">'.$custom_field.'</div>';
        $output .= '</div>'; // -- close col-8
        $output .= '<div class="col-2 hidden md:block">';
        $output .= '<div class="card availability cursor-pointer">';
        $output .= '<div class="card-body pt-3 pb-2 px-1">';
        $output .= '<div class="d-flex availability-content flex-column">';
        $output .= '<span class="label">'.__('Availability').'</span>';
        $output .= '<span class="value '.$class_avail.'">'.$availability.'</span>';
        $output .= '</div>'; // -- close d-flex flex-column
        $output .= '<div class="add-to-chart ' . ($availability < 1 ?: 'add-to-chart-button') . ' align-items-center justify-content-center flex-column" data-biblio="'.$biblio_id.'">';
        $output .= '<span class="label">'. ($availability > 0 ? __('Add to basket') : __('Items not available') ) .'</span>';
        $output .= '<span class="value"><i class="fas ' . ($availability > 0 ? 'fa-plus' : 'fa-ban') . '"></i></span>';
        $output .= '</div>'; // -- close d-flex flex-column
        $output .= '</div>'; // -- close card-body pt-3 pb-2 px-1
        $output .= '</div>'; // -- close card availability
        //  $output .= '<a class="btn btn-outline-primary btn-block mt-2 btn-sm" href="'.$detail_url.'">'.__('View Detail').'</a>';
        $output .= '<a class="btn btn-outline-secondary btn-block mt-2 btn-sm" href="'.$detail_url.'&MARC=true" title="Download detail data in MARC" target="_blank">'.__('MARC Download').'</a>';
        $output .= '<a class="btn btn-outline-secondary btn-block mt-2 btn-sm openPopUp citationLink" href="'.$cite_url.'" title="'.str_replace('{title}', substr($title, 0, 50) , __('Citation for: {title}')).'" target="_blank">'.__('Cite').'</a>';
        $output .= '</div>'; // -- close col-2
        $output .= '</div>'; // -- close row
        if ($i > 0 && $expand) {
            $output .= '<div class="expand"><a id="btn-expand-'.$biblio_id.'" class="flex justify-center text-decoration-none py-2" data-toggle="collapse" href="#expand-'.$biblio_id.'" role="button" aria-expanded="false" aria-controls="expand-'.$biblio_id.'"><i class="fas fa-angle-double-down"></i></a></div>';
        }
        $output .= '</div>';
        $output .= '</div>';

    else:

        $output .= '<div class="col-md-3 px-2 grid-item">';
        $output .= '<div class="card p-0 mb-3">';
        $__ = '__';
        $title_cite = str_replace('{title}', substr($title, 0, 50) , __('Citation for: {title}'));
        $output .= <<<HTML
<div class="grid-item--menu dropdown">
    <a class="dropdown-toggle" role="button" data-toggle="dropdown" aria-expanded="false" data-display="static">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
            <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
        </svg>
    </a>
    <div class="dropdown-menu dropdown-menu-right text-sm">
        <a class="dropdown-item text-left px-3" href="{$detail_url}&MARC=true">{$__('MARC Download')}</a>
        <a class="dropdown-item text-left px-3 openPopUp citationLink" href="{$cite_url}" title="{$title_cite}">{$__('Cite')}</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-left px-3 add-to-chart-button" data-biblio="{$biblio_id}" href="#">{$__('Add to basket')}</a>
    </div>
</div>
HTML;
        $output .= '<div class="p-5 flex justify-center items-center bg-grey-light">';
        $output .= '<img loading="lazy" src="'.$thumb_url.'" class="img-fluid img-thumbnail shadow '.($availability > 0 ?: 'not-available').'"/>';
        $output .= '</div>';
        $output .= '<div class="card-body p-2">';
        $output .= '<a href="'.$detail_url.'" class="text-sm text-decoration-none grid-item--title m-0">'.$title.'</a>';
        $output .= '</div>';
        $output .= '<ul class="list-group list-group-flush">';
        $output .= $grid_item_content;
        if ($availability < 1) {
            $output .= '<li class="list-group-item text-danger"><span></span><span class="text-center">'.__('Item Not Available').'</span></li>';
        }
        $output .= '</ul>';
        $output .= '</div>';
        $output .= '</div>';

    endif;

    // debug
    // $output .= '<code>'.json_encode($biblio_detail).'</code>';

    return $output;
}

function getNotes($dbs, $biblio_id)
{
    $query = $dbs->query('SELECT notes FROM biblio WHERE biblio_id = ' . $biblio_id);
    $data = $query->fetch_row();
    return addEllipsis($data[0], 400);
}

function addEllipsis($string, $length, $end='…')
{
    if (strlen($string??'') > $length)
    {
        $length -= strlen($end);
        $string  = substr($string, 0, $length);
        $string .= $end;
    }

    return $string;
}

function getAvailability($dbs, $biblio_id)
{
    // get total number of this biblio items/copies
    $_item_q = $dbs->query('SELECT COUNT(*) FROM item WHERE biblio_id='.$biblio_id);
    $_item_c = $_item_q->fetch_row();
    // get total number of currently borrowed copies
    $_borrowed_q = $dbs->query('SELECT COUNT(*) FROM loan AS l INNER JOIN item AS i'
        .' ON l.item_code=i.item_code WHERE l.is_lent=1 AND l.is_return=0 AND i.biblio_id='.$biblio_id);
    $_borrowed_c = $_borrowed_q->fetch_row();
    // total available
    $_total_avail = $_item_c[0]-$_borrowed_c[0];

    return $_total_avail;
}
