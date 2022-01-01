<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 01/01/2022 9:42
 * @File name           : DefaultEngine.php
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

namespace SLiMS\SearchEngine;


use SLiMS\DB;

class DefaultEngine extends Contract
{
    public array $searchable_fields = ['title', 'author', 'subject', 'isbn',
        'publisher', 'gmd', 'notes', 'colltype', 'publishyear',
        'location', 'itemcode', 'callnumber', 'itemcallnumber', 'notes'];

    function getDocuments(Criteria $criteria)
    {
        $this->criteria = $criteria;

        // start time to benchmarking
        $start = microtime(true);

        // build sql command
        $sql = $this->buildSQL();

        // execute query
        $db = DB::getInstance();
        $count = $db->query($sql['count']);
        $query = $db->query($sql['query']);

        // get results
        $this->num_rows = ($count->fetch(\PDO::FETCH_NUM))[0] ?? 0;
        $this->documents = $query->fetchAll(\PDO::FETCH_ASSOC);

        // end time
        $end = microtime(true);
        $this->query_time = round($end-$start, 5);
    }

    function buildSQL(): array
    {
        $sql_select = 'select b.biblio_id, b.title, b.image, b.isbn_issn, b.publish_year, mp.publisher_name as `publisher`, mpl.place_name as `publish_place`, b.labels, b.input_date';

        // checking custom front page fields file
        $file_path = SB;
        $file_path .= config('template.dir', 'template') . DS;
        $file_path .= config('template.theme', 'default') . DS;
        $file_path .= 'custom_frontpage_record.inc.php';
        if (file_exists($file_path)) {
            include $file_path;
            $this->custom_fields = $custom_fields ?? [];
            foreach ($this->custom_fields as $field => $field_opts) {
                if ($field_opts[0] == 1 && !in_array($field, array('availability', 'isbn_issn'))) {
                    $sql_select .= ", b.$field";
                }
            }
        }

        $sql_join = 'left join mst_publisher as mp on b.publisher_id=mp.publisher_id ';
        $sql_join .= 'left join mst_place as mpl on b.publish_place_id=mpl.place_id ';

        // location
        $sql_group = '';
        if (!is_null($this->criteria->location) || !is_null($this->criteria->colltype)) {
            $sql_join .= 'left join item as i on b.biblio_id=i.biblio_id ';
            $sql_group = 'group by b.biblio_id';
        }

        $sql_criteria = 'where b.opac_hide=0 ';
        $sql_criteria .= ($c = $this->buildCriteria($this->criteria)) !== '' ? 'and (' . $c . ') ' : '';

        $sql_query = $sql_select . ' from biblio as b ' . $sql_join . $sql_criteria . $sql_group . ' order by b.last_update desc limit ' . $this->limit . ' offset ' . $this->offset;
        $sql_count = 'select count(b.biblio_id)' . ' from biblio as b ' . $sql_join . $sql_criteria . $sql_group;

        return [
            'count' => $sql_count,
            'query' => $sql_query
        ];
    }

    function buildCriteria(): string
    {
        $title_buffer = '';
        $boolean = '';
        $sql_criteria = '';
        foreach ($this->criteria->toCQLToken($this->stop_words) as $token) {
            $field = $token['f'];

            if ($title_buffer === '' && $field !== 'boolean')
                $sql_criteria .= ' ' . $boolean . ' ';

            // flush title string concatenation
            if ($field !== 'title' && $title_buffer !== '') {
                $title_buffer = trim($title_buffer);
                $sql_criteria .= " b.biblio_id in(select distinct biblio_id from biblio where match (title, series_title) against ('" . $title_buffer . "' in boolean mode)) ";
                // reset title buffer
                $title_buffer = '';
            }

            // break the loop if we meet cql_end field
            if ($field === 'cql_end') break;

            // boolean mode
            $bool = $token['b'] ?? $token;
            $boolean = ($bool === '*') ? 'or' : 'and';

            // search value
            $query = $token['q'] ?? null;
            switch ($field) {
                case 'title':
                    if (strlen($query) < 4) {
                        $sql_criteria .= " b.title like '%" . $query . "%' ";
                        $title_buffer = '';
                    } else {
                        if ($token['is_phrase'] ?? false) {
                            $title_buffer .= ' ' . $bool . '"' . $query . '"';
                        } else {
                            $title_buffer .= ' ' . $bool . $query;
                        }
                    }
                    break;

                case 'author':
                    $sub_query = "select ba.biblio_id from biblio_author as ba left join mst_author as ma on ba.author_id=ma.author_id where ma.author_name like '%".$query."%'";
                    if ($bool === '-') {
                        $sql_criteria .= ' b.biblio_id not in(' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' b.biblio_id in(' . $sub_query . ')';
                    }
                    break;

                case 'subject':
                    $sub_query = "select bt.biblio_id from biblio_topic as bt left join mst_topic as mt on bt.topic_id=mt.topic_id where mt.topic like '%".$query."%'";
                    if ($bool === '-') {
                        $sql_criteria .= ' b.biblio_id not in(' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' b.biblio_id in(' . $sub_query . ')';
                    }
                    // reset title buffer
                    $title_buffer = '';
                    break;

                case 'location':
                    $sub_query = "select location_id from mst_location where location_name = '".$query."'";
                    if ($bool === '-') {
                        $sql_criteria .= ' i.location_id not in(' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' i.location_id in(' . $sub_query . ')';
                    }
                    break;

                case 'colltype':
                    $sub_query = "select coll_type_id from mst_coll_type where coll_type_name = '".$query."'";
                    if ($bool === '-') {
                        $sql_criteria .= ' i.coll_type_id not in(' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' i.coll_type_id in(' . $sub_query . ')';
                    }
                    break;

                case 'itemcode':
                    if ($bool === '-') {
                        $sql_criteria .= " i.item_code != '".$query."'";
                    } else {
                        $sql_criteria .= " i.item_code = '".$query."'";
                    }
                    break;

                case 'callnumber':
                    if ($bool === '-') {
                        $sql_criteria .= " b.call_number not like '".$query."%'";
                    } else {
                        $sql_criteria .= " b.call_number like '".$query."%'";
                    }
                    break;

                case 'itemcallnumber':
                    if ($bool === '-') {
                        $sql_criteria .= " i.call_number not like '".$query."%'";
                    } else {
                        $sql_criteria .= " i.call_number like '".$query."%'";
                    }
                    break;

                case 'class':
                    if ($bool === '-') {
                        $sql_criteria .= " b.classification not like '".$query."%'";
                    } else {
                        $sql_criteria .= " b.classification like '".$query."%'";
                    }
                    break;

                case 'isbn':
                    if ($bool === '-') {
                        $sql_criteria .= " b.isbn_issn != '".$query."'";
                    } else {
                        $sql_criteria .= " b.isbn_issn = '".$query."'";
                    }
                    break;

                case 'publisher':
                    $sub_query = "select publisher_id from mst_publisher where publisher_name like '%".$query."%'";
                    if ($bool === '-') {
                        $sql_criteria .= ' b.publisher_id not in (' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' b.publisher_id in (' . $sub_query . ')';
                    }
                    break;

                case 'publishyear':
                    if ($bool === '-') {
                        $sql_criteria .= " b.publish_year != '".$query."'";
                    } else {
                        $sql_criteria .= " b.publish_year = '".$query."'";
                    }
                    break;

                case 'gmd':
                    $sub_query = "select gmd_id from mst_gmd where gmd_name like '%".$query."%'";
                    if ($bool === '-') {
                        $sql_criteria .= ' b.gmd_id not in (' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' b.gmd_id in (' . $sub_query . ')';
                    }
                    break;

                case 'notes':
                    $query = isset($query['is_phrase']) ? '"' . $query . '"' : $query;
                    if ($bool === '-') {
                        $sql_criteria .= " not (match (b.notes) against ('".$query."' in boolean mode))";
                    } else {
                        $sql_criteria .= " (match (b.notes) against ('".$query."' in boolean mode))";
                    }
                    break;
            }
        }
        return preg_replace('@^(AND|OR|NOT)\s*|\s+(AND|OR|NOT)$@i', '', trim($sql_criteria));
    }

    function toArray()
    {
        // TODO: Implement toArray() method.
    }

    function toJSON()
    {
        // TODO: Implement toJSON() method.
    }

    function toHTML()
    {
        $buffer = '';
        // include biblio list html template callback
        $path = SB . config('template.dir', 'template') . DS;
        $path .= config('template.theme', 'default') .DS . 'biblio_list_template.php';
        include $path;

        foreach ($this->documents as $i => $document) {
            $buffer .= \biblio_list_format(DB::getInstance('mysqli'), $document, $i, [
                'keywords' => $this->criteria->keywords,
                'enable_custom_frontpage' => true,
                'custom_fields' => $this->custom_fields
            ]);
        }
        return $buffer;
    }

    function toXML()
    {
        // TODO: Implement toXML() method.
    }

    function toRSS()
    {
        // TODO: Implement toRSS() method.
    }
}