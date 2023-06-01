<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 01/01/2022 12:14
 * @File name           : default.inc.php
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

use SLiMS\SearchEngine\Contract;
use SLiMS\SearchEngine\Criteria;
use SLiMS\SearchEngine\DefaultEngine;

// if we are in searching mode
if (isset($_GET['search'])) {
    // required library
    require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
    require LIB . 'biblio_list_model.inc.php';

    // initialize variable
    $keywords = '';
    $search_result_info = '';

    // get engine name from setting
    $search_engine = config('search_engine', DefaultEngine::class);
    // data setting exists but class not exists
    if (!class_exists($search_engine)) $search_engine = DefaultEngine::class;

    // starting engine
    $engine = new $search_engine;

    // make sure the engine running is the correct engine
    if ($engine instanceof Contract) {
        // opac limit result
        $engine->setLimit(config('opac_result_num', 10));

        // initialize criteria
        $criteria = new Criteria;
        $searched_fields = array_intersect($engine->searchable_fields, array_keys($_GET));
        if (!empty($searched_fields) && !isset($_GET['filter'])) {
            // get criteria from advanced search
            foreach ($searched_fields as $field) {
                $value = !is_array($_GET[$field]) ? trim(strip_tags(urldecode($_GET[$field]))) : '';
                if ($value !== '' && $value !== '0') $criteria->and($field, $value);
            }
            $keywords = $criteria->getQueries();
        } else {
            // get criteria from simple search
            $keywords = trim(strip_tags(urldecode($_GET['keywords'] ?? '')));
            if ($keywords !== '') {
                $criteria->keywords = $keywords;
                foreach (['title', 'author', 'subject'] as $item) $criteria->or($item, $keywords);
            }
        }

        // filter
        $filterCriteria = new Criteria;
        if (isset($_GET['filter'])) {
            $filter = utility::filterData('filter', 'get', false, true, true);
            $filterArr = json_decode($filter, true);
            unset($filterArr['csrf_token']);
            $filters = [];
            foreach ($filterArr??[] as $idx => $x) {
                if (strpos($idx, '[') !== false) {
                    $arr = explode('[', $idx);
                    $filters[$arr[0]][] = $x;
                } else {
                    $filters[$idx] = $x;
                }
            }

            foreach ($filters as $key => $value) {
                if (is_array($value)) {
                    $filterCriteria->and($key, json_encode($value));
                } else {
                    $filterCriteria->and($key, $value);
                }
            }
        }

        // get records base on criteria
        $engine->setCriteria($criteria);
        $engine->setFilter($filterCriteria);
        $engine->getDocuments();

        // create output
        // check if we are on json-ld result set mode
        if (isset($_GET['JSONLD']) && config('jsonld_result')) {
            // send http header
            header('Content-Type: application/ld+json');
            header('Content-disposition: attachment; filename=biblio-opac.json');
            echo $engine->toJSON();
            exit();
        } else
            // check if we are on xml result set mode
            if ((isset($_GET['rss']) || isset($_GET['resultXML'])) && config('enable_xml_result')) {
                // send http header
                header('Content-Type: text/xml');
                echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
                if (isset($_GET['rss'])) {
                    echo $engine->toRSS();
                } else {
                    echo $engine->toXML();
                }
                exit();
            } else {
                // default to HTML mode
                // generate search result info
                $keywords = str_replace(['\\'], '', $keywords);
                $keywords_info = '<span class="search-keyword-info" title="' . htmlentities($keywords) . '">' . ((strlen($keywords) > 30) ? substr($keywords, 0, 30) . '...' : $keywords) . '</span>';
                $search_result_info .= '<div class="search-found-info">';
                $search_result_info .= __('Found <strong>{biblio_list->num_rows}</strong> from your keywords') . ': <strong class="search-found-info-keywords">' . $keywords_info . '</strong>';
                $search_result_info .= '</div>';
                $search_result_info = str_replace('{biblio_list->num_rows}', $engine->getNumRows(), $search_result_info);
                $search_result_info .= '<div class="search-query-time">' . __('Query took') . ' <b>' . $engine->query_time . '</b> ' . __('second(s) to complete') . '</div>';
                $search_result_info .= '<div>';
                $search_result_info .= '<a href="index.php?resultXML=true&'.$_SERVER['QUERY_STRING'].'" class="xmlResultLink" target="_blank" title="View Result in XML Format" style="clear: both;">XML Result</a>';
                $search_result_info .= '<a href="index.php?JSONLD=true&'.$_SERVER['QUERY_STRING'].'" class="jsonResultLink" target="_blank" title="View Result in JSON Format" style="clear: both;">JSON Result</a>';
                $search_result_info .= '</div>';

                // pagination
                $paging = simbio_paging::paging($engine->getNumRows(), $engine->getLimit(), '5');
                if ($paging) echo '<div class="biblioPaging biblioPagingTop">' . $paging . '</div>';
                echo '<div class="biblioResult">' . $engine->toHTML() . '</div>';
                if ($paging) echo '<div class="biblioPaging biblioPagingBottom mb-4">' . $paging . '</div>';
            }
    }
}