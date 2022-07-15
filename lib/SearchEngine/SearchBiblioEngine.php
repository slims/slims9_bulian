<?php

/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 01/01/2022 9:44
 * @File name           : SearchBiblioEngine.php
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

class SearchBiblioEngine extends Contract
{
    protected $disable_item_data = false;

    function getDocuments()
    {
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
        $this->query_time = round($end - $start, 5);
    }

    function buildSQL()
    {
        $sql_select = 'select sb.biblio_id, sb.title, sb.author, sb.topic, sb.image, sb.isbn_issn, sb.publisher, sb.publish_place, sb.publish_year, sb.labels, sb.input_date';

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
                    $sql_select .= ", sb.$field";
                }
            }
        }

        $sql_criteria = 'where sb.opac_hide=0 ';
        $sql_criteria .= ($c = $this->buildCriteria($this->criteria)) !== '' ? 'and (' . $c . ') ' : '';
        $sql_criteria .= ($f = $this->buildCriteria($this->filter)) !== '' ? 'and (' . $f . ') ' : '';

        switch ($this->filter->sort) {
            case 'recently-added':
                $sql_order = 'sb.input_date desc';
                break;
            case 'publish-year-newest':
                $sql_order = 'sb.publish_year desc';
                break;
            case 'publish-year-oldest':
                $sql_order = 'sb.publish_year asc';
                break;
            case 'title-asc':
                $sql_order = 'sb.title asc';
                break;
            case 'title-desc':
                $sql_order = 'sb.title desc';
                break;
            case 'most-relevant':
            case 'most-loaned':
            default:
                $sql_order = 'sb.last_update desc';
        }

        $sql_query = $sql_select . ' from search_biblio as sb ' . $sql_criteria . ' order by '. $sql_order .' limit ' . $this->limit . ' offset ' . $this->offset;
        $sql_count = 'select count(sb.biblio_id)' . ' from search_biblio as sb ' . $sql_criteria;

        return [
            'count' => preg_replace('/\s+/', ' ', trim($sql_count)),
            'query' => preg_replace('/\s+/', ' ', trim($sql_query))
        ];
    }

    function buildCriteria(Criteria $criteria)
    {
        $boolean = '';
        $sql_criteria = '';
        foreach ($criteria->toCQLToken($this->stop_words) as $token) {
            $field = $token['f'];

            $is_phrase = isset($token['is_phrase']);

            // break the loop if we meet cql_end field
            if ($field === 'cql_end') break;

            // boolean mode
            if ($field == 'boolean') {
                if ($token['b'] == '*') {
                    $boolean = 'or';
                } else {
                    $boolean = 'and';
                }
                continue;
            } else {
                if ($boolean) {
                    $sql_criteria .= " $boolean ";
                } else {
                    if ($token['b'] == '*') {
                        $sql_criteria .= " or ";
                    } else {
                        $sql_criteria .= " and ";
                    }
                }
                $bool = $token['b'];
                $query = $token['q'];
                if (in_array($field, array('title', 'author', 'subject', 'notes'))) {
                    $query = '+' . ($is_phrase ? '"' . $query . '"' : $query);
                    if (!$is_phrase) {
                        $query = preg_replace('@\s+@i', ' +', $query);
                    }
                }
                $boolean = '';
            }

            // check fields
            switch ($field) {
                case 'author':
                    if ($bool == '-') {
                        $sql_criteria .= " not (match (sb.author) against ('$query' in boolean mode))";
                    } else {
                        $sql_criteria .= " (match (sb.author) against ('$query' in boolean mode))";
                    }
                    break;

                case 'subject':
                    if ($bool == '-') {
                        $sql_criteria .= " not (match (sb.topic) against ('$query' in boolean mode))";
                    } else {
                        $sql_criteria .= " (match (sb.topic) against ('$query' in boolean mode))";
                    }
                    break;

                case 'location':
                    if (!$this->disable_item_data) {
                        $idx = json_decode($query);
                        $sub_query = "'" . implode("', '", $idx) . "'";
                        if (!is_null($idx)) {
                            $sql_criteria_tmp = [];
                            $location_q = DB::getInstance()->query("select location_name from mst_location where location_id in (" . $sub_query . ")");
                            while ($location_d = $location_q->fetch()) {
                                if ($bool == '-') {
                                    $sql_criteria_tmp[] = " sb.location not like '%" . $location_d[0] . "%'";
                                } else {
                                    $sql_criteria_tmp[] = " sb.location like '%" . $location_d[0] . "%'";
                                }
                            }
                            $sql_criteria .= " (" . implode(' or ', $sql_criteria_tmp) . ") ";
                        }
                    } else {
                        if ($bool == '-') {
                            $sql_criteria .= " sb.node !='$query'";
                        } else {
                            $sql_criteria .= " sb.node = '$query'";
                        }
                    }
                    break;

                case 'colltype':
                    $idx = json_decode($query);
                    $sub_query = implode(", ", $idx);

                    if (!$this->disable_item_data) {
                        if (!is_null($idx)) {
                            $sql_criteria_tmp = [];
                            $coll_type_q = DB::getInstance()->query("select coll_type_name from mst_coll_type where coll_type_id in (" . $sub_query . ")");
                            while ($coll_type_d = $coll_type_q->fetch()) {
                                if ($bool == '-') {
                                    $sql_criteria_tmp[] = " sb.collection_types not like '%" . $coll_type_d[0] . "%'";
                                } else {
                                    $sql_criteria_tmp[] = " sb.collection_types like '%" . $coll_type_d[0] . "%'";
                                }
                            }
                            $sql_criteria .= " (" . implode(' or ', $sql_criteria_tmp) . ") ";
                        } else {
                            if ($bool == '-') {
                                $sql_criteria .= " not (match (sb.collection_types) against ('$query' in boolean mode))";
                            } else {
                                $sql_criteria .= " match (sb.collection_types) against ('$query' in boolean mode)";
                            }
                        }
                    }
                    break;

                case 'itemcode':
                    if (!$this->disable_item_data) {
                        if ($bool == '-') {
                            $sql_criteria .= " not (match (sb.items) against ('$query' in boolean mode))";
                        } else {
                            $sql_criteria .= " match (sb.items) against ('$query' in boolean mode)";
                        }
                    }
                    break;

                case 'callnumber':
                    if ($bool == '-') {
                        $sql_criteria .= ' biblio.call_number not LIKE \'' . $query . '%\'';
                    } else {
                        $sql_criteria .= ' sb.call_number LIKE \'' . $query . '%\'';
                    }
                    break;

                case 'itemcallnumber':
                    if (!$this->disable_item_data) {
                        if ($bool == '-') {
                            $sql_criteria .= ' item.call_number not LIKE \'' . $query . '%\'';
                        } else {
                            $sql_criteria .= ' item.call_number LIKE \'' . $query . '%\'';
                        }
                    }
                    break;

                case 'class':
                    if ($bool == '-') {
                        $sql_criteria .= ' sb.classification not LIKE \'' . $query . '%\'';
                    } else {
                        $sql_criteria .= ' sb.classification LIKE \'' . $query . '%\'';
                    }
                    break;

                case 'isbn':
                    if ($bool == '-') {
                        $sql_criteria .= ' sb.isbn_issn not LIKE \'' . $query . '%\'';
                    } else {
                        $sql_criteria .= ' sb.isbn_issn LIKE \'' . $query . '%\'';
                    }
                    break;

                case 'publisher':
                    if ($bool == '-') {
                        $sql_criteria .= " sb.publisher!='$query'";
                    } else {
                        $sql_criteria .= " sb.publisher LIKE '$query%'";
                    }
                    break;

                case 'publishyear':
                    if ($bool == '-') {
                        $sql_criteria .= ' sb.publish_year!=\'' . $query . '\'';
                    } else {
                        $sql_criteria .= ' sb.publish_year LIKE \'' . $query . '\'';
                    }
                    break;

                case 'years':
                    list($from, $to) = explode(';', $query);
                    $sql_criteria .= " (sb.publish_year between " . $from . " and " . $to . ") ";
                    break;

                case 'gmd':
                    $idx = json_decode($query);
                    $sub_query = implode(", ", $idx);
                    if (!is_null($idx)) {
                        $sql_criteria_tmp = [];
                        $gmd_q = DB::getInstance()->query("select gmd_name from mst_gmd where gmd_id in (" . $sub_query . ")");
                        while ($gmd_d = $gmd_q->fetch()) {
                            if ($bool == '-') {
                                $sql_criteria_tmp[] = " sb.gmd != '" . $gmd_d[0] . "'";
                            } else {
                                $sql_criteria_tmp[] = " sb.gmd = '" . $gmd_d[0] . "'";
                            }
                        }
                        $sql_criteria .= " (" . implode(' or ', $sql_criteria_tmp) . ") ";
                    } else {
                        if ($bool == '-') {
                            $sql_criteria .= " sb.gmd!='$query'";
                        } else {
                            $sql_criteria .= " sb.gmd='$query'";
                        }
                    }
                    break;

                case 'notes':
                    if ($bool == '-') {
                        $sql_criteria .= " not (match (sb.notes) against ('" . $query . "' in boolean mode))";
                    } else {
                        $sql_criteria .= " (match (sb.notes) against ('" . $query . "' in boolean mode))";
                    }
                    break;
                case 'opengroup':
                    $sql_criteria .= "(";
                    break;
                case 'closegroup':
                    $sql_criteria .= ")";
                    break;

                case 'availability':
                    $sql_criteria .= ' sb.items is not null ';

                    $sub_query = "select distinct biblio_id from loan_history where is_return = 0 and biblio_id is not null";
                    $sql_criteria .= ' and sb.biblio_id not in(' . $sub_query . ')';
                    break;

                case 'attachment':
                    $mime_types = [];
                    $queryArr = json_decode($query, true);
                    foreach ($queryArr as $q) {
                        switch ($q) {
                            case 'pdf':
                                $mime_types[] = 'application/pdf';
                                break;
                            case 'audio':
                                $mime_types[] = 'audio/mpeg';
                                break;
                            case 'video':
                                $mime_types[] = 'video/x-flv';
                                $mime_types[] = 'video/mp4';
                                break;
                        }
                    }

                    $sub_query_criteria = "'" . implode("', '", $mime_types) . "'";

                    $sub_query = "select distinct bat.biblio_id from biblio_attachment as bat left join files as f on bat.file_id=f.file_id where f.mime_type in(" . $sub_query_criteria . ")";
                    if ($bool === '-') {
                        $sql_criteria .= ' sb.biblio_id not in(' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' sb.biblio_id in(' . $sub_query . ')';
                    }
                    break;

                case 'lang':
                    $idx = json_decode($query);
                    $sub_query = "'" . implode("', '", $idx) . "'";

                    if (!is_null($idx)) {
                        $sql_criteria_tmp = [];
                        $lang_q = DB::getInstance()->query("select language_name from mst_language where language_id in (" . $sub_query . ")");
                        while ($lang_d = $lang_q->fetch()) {
                            if ($bool == '-') {
                                $sql_criteria_tmp[] = "sb.language != '" . $lang_d[0] . "'";
                            } else {
                                $sql_criteria_tmp[] = "sb.language = '" . $lang_d[0] . "'";
                            }
                        }
                        $sql_criteria .= " (" . implode(' or ', $sql_criteria_tmp) . ") ";
                    } else {
                        if ($bool === '-') {
                            $sql_criteria .= ' sb.language != \'' . $sub_query . '\'';
                        } else {
                            $sql_criteria .= ' sb.language = \'' . $sub_query . '\'';
                        }
                    }
                    break;

                case 'sort':
                    $sql_criteria . '';
                    break;

                default:
                    if ($bool == '-') {
                        $sql_criteria .= " not (match (sb.title, sb.series_title) against ('$query' in boolean mode))";
                    } else {
                        $sql_criteria .= " (match (sb.title, sb.series_title) against ('$query' in boolean mode))";
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
        $jsonld = [
            '@context' => 'http://schema.org',
            '@type' => 'Book',
            'total_rows' => $this->num_rows,
            'page' => $this->page,
            'records_each_page' => $this->limit,
            '@graph' => [],
        ];

        $db = DB::getInstance();

        foreach ($this->documents as $document) {
            $record = [];
            $record['@id'] = 'http://' . $_SERVER['SERVER_NAME'] . SWB . 'index.php?p=show_detail&id=' . $document['biblio_id'];
            $record['name'] = trim($document['title']);

            $record['author'] = [];

            foreach (explode('-', $document['author']) as $author) {
                $record['author']['name'][] = trim($author);
            }

            // ISBN
            $record['isbn'] = $document['isbn_issn'];

            // publisher
            $record['publisher'] = $document['publisher'];

            // publish date
            $record['dateCreated'] = $document['publish_year'];

            // doc images
            $_image = '';
            if (!empty($document['image'])) {
                $_image = urlencode($document['image']);
                $record['image'] = $_image;
            }

            $jsonld['@graph'][] = $record;
        }

        return json_encode($jsonld);
    }

    function toHTML()
    {
        $buffer = '';
        // include biblio list html template callback
        $path = SB . config('template.dir', 'template') . DS;
        $path .= config('template.theme', 'default') . DS . 'biblio_list_template.php';
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
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startElement('modsCollection');
        $xml->writeAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $xml->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->writeAttribute('xmlns', 'http://www.loc.gov/mods/v3');
        $xml->writeAttribute('xmlns:slims', 'http://slims.web.id');
        $xml->writeAttribute('xsi:schemaLocation', 'http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-3.xsd');

        $xml->startElementNS('slims', 'resultInfo', null);
        $xml->startElementNS('slims', 'modsResultNum', null);
        $xml->text($this->num_rows);
        $xml->endElement(); // -- modsResultNum
        $xml->startElementNS('slims', 'modsResultPage', null);
        $xml->text($this->page);
        $xml->endElement(); // -- modsResultPage
        $xml->startElementNS('slims', 'modsResultShowed', null);
        $xml->text($this->limit);
        $xml->endElement(); // -- modsResultShowed
        $xml->endElement(); // -- resultInfo

        foreach ($this->documents as $document) {
            $xml->startElement('mods');
            $xml->writeAttribute('version', '3.3');
            $xml->writeAttribute('ID', $document['biblio_id']);

            // parsing title
            $title_main = $document['title'];
            $title_sub = '';
            if (stripos($document['title'], '/') !== false) {
                $title_main = trim(substr_replace($document['title'], '', stripos($document['title'], '/') + 1));
            }
            if (stripos($document['title'], ':') !== false) {
                $title_main = trim(substr_replace($document['title'], '', stripos($document['title'], ':') + 1));
                $title_sub = trim(substr_replace($document['title'], '', 0, stripos($document['title'], ':') + 1));
            }
            if (stripos($title_sub, '/') !== false) {
                $title_sub = trim(substr_replace($title_sub, '', stripos($title_sub, '/') + 1));
            }

            $xml->startElement('titleInfo');
            $xml->startElement('title');
            $xml->text($title_main);
            $xml->endElement(); // -- title
            if ($title_sub !== '') {
                $xml->startElement('subTitle');
                $xml->text($title_sub);
                $xml->endElement(); // -- subTitle
            }
            $xml->endElement(); // -- titleInfo

            // authors
            $this->xmlAuthor($xml, $document['biblio_id']);

            $xml->startElement('typeOfResource');
            $xml->writeAttribute('collection', 'yes');
            $xml->text('mixed material');
            $xml->endElement(); // -- typeOfResource
            $xml->startElement('identifier');
            $xml->writeAttribute('type', 'isbn');
            $xml->text(str_replace(array('-', ' '), '', $document['isbn_issn']));
            $xml->endElement(); // -- identifier

            $xml->startElement('originInfo');
            $xml->startElement('place');
            $xml->startElement('placeTerm');
            $xml->writeAttribute('type', 'text');
            $xml->text($document['publish_place']);
            $xml->endElement(); // -- placeTerm
            $xml->startElement('publisher');
            $xml->text($document['publisher']);
            $xml->endElement(); // -- publisher
            $xml->startElement('dateIssued');
            $xml->text($document['publish_year']);
            $xml->endElement(); // -- dateIssued
            $xml->endElement(); // -- place
            $xml->endElement(); // -- originInfo

            // digital file
            $this->xmlAttachment($xml, $document['biblio_id']);

            // images
            if (!empty($document['image'])) {
                $xml->startElementNS('slims', 'image', null);
                $xml->text(urlencode($document['image']));
                $xml->endElement();
            }

            $xml->endElement(); // -- mods
        }

        $xml->endElement(); // -- modsCollection

        return $xml->flush();
    }

    function xmlAuthor(&$xml, $biblio_id)
    {
        $query = DB::getInstance()->query('SELECT a.*,ba.level FROM mst_author AS a'
            . ' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id=' . $biblio_id);
        while ($author = $query->fetch()) {
            $xml->startElement('name');
            $xml->writeAttribute('type', config('authority_type')[$author['authority_type']] ?? '');
            $xml->writeAttribute('authority', $author['auth_list'] ?? '');
            $xml->startElement('namePart');
            $xml->text($author['author_name']);
            $xml->endElement(); // -- namePart
            $xml->startElement('role');
            $xml->startElement('roleTerm');
            $xml->writeAttribute('type', 'text');
            $xml->text(config('authority_level')[$author['level']] ?? '');
            $xml->endElement(); // -- roleTerm
            $xml->endElement(); // -- role
            $xml->endElement(); // -- name
        }
    }

    function xmlAttachment(&$xml, $biblio_id)
    {
        $query = DB::getInstance()->query('SELECT att.*, f.* FROM biblio_attachment AS att
          LEFT JOIN files AS f ON att.file_id=f.file_id WHERE att.biblio_id=' . $biblio_id . ' AND att.access_type=\'public\' LIMIT 20');
        if ($query->rowCount() > 0) {
            $xml->startElementNS('slims', 'digitals', null);
            while ($attachment_d = $query->fetch()) {
                // check member type privileges
                if ($attachment_d['access_limit']) continue;
                $xml->startElementNS('slims', 'digital_item', null);
                $xml->writeAttribute('id', $attachment_d['file_id']);
                $xml->writeAttribute('url', trim($attachment_d['file_url']));
                $xml->writeAttribute('path', $attachment_d['file_dir'] . '/' . $attachment_d['file_name']);
                $xml->writeAttribute('mimetype', $attachment_d['mime_type']);
                $xml->text($attachment_d['file_title']);
                $xml->endElement(); // -- digital_item
            }
            $xml->endElement(); // -- digitals
        }
    }

    function toRSS()
    {
        // TODO: Implement toRSS() method.
    }
}
