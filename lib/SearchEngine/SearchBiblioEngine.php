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

        $sql_query = $sql_select . ' from search_biblio as sb ' . $sql_criteria . ' order by sb.last_update desc limit ' . $this->limit . ' offset ' . $this->offset;
        $sql_count = 'select count(sb.biblio_id)' . ' from search_biblio as sb ' . $sql_criteria;

        return [
            'count' => $sql_count,
            'query' => $sql_query
        ];
    }

    function buildCriteria()
    {
        $boolean = '';
        $sql_criteria = '';
        foreach ($this->criteria->toCQLToken($this->stop_words) as $token) {
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
                        if ($bool == '-') {
                            $sql_criteria .= " not (match (sb.location) against ('$query' in boolean mode))";
                        } else {
                            $sql_criteria .= " (match (sb.location) against ('$query' in boolean mode))";
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
                    if (!$this->disable_item_data) {
                        if ($bool == '-') {
                            $sql_criteria .= " not (match (sb.collection_types) against ('$query' in boolean mode))";
                        } else {
                            $sql_criteria .= " match (sb.collection_types) against ('$query' in boolean mode)";
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
                case 'gmd':
                    if ($bool == '-') {
                        $sql_criteria .= " sb.gmd!='$query'";
                    } else {
                        $sql_criteria .= " sb.gmd='$query'";
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
        // TODO: Implement toJSON() method.
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
            $xml->writeAttribute('authority', $author['auth_list']);
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
