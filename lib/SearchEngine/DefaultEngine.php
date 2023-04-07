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
    public array $searchable_fields = [
        'title', 'author', 'subject', 'isbn',
        'publisher', 'gmd', 'notes', 'colltype', 'publishyear',
        'location', 'itemcode', 'callnumber', 'itemcallnumber', 'notes',
    ];

    function getDocuments()
    {
        // start time to benchmarking
        $start = microtime(true);

        try {
            // build sql command
            $sql = $this->buildSQL();

            // dump SQL
            $this->dump($sql);

            $db = DB::getInstance();
            $count = $db->prepare($sql['count']);
            $count->execute($this->execute);
            $query = $db->prepare($sql['query']);
            $query->execute($this->execute);

            // get results
            $this->num_rows = ($count->fetch(\PDO::FETCH_NUM))[0] ?? 0;
            $this->documents = $query->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException | \Exception $e) {
            $this->error = $e->getMessage();
        }

        // end time
        $end = microtime(true);
        $this->query_time = round($end - $start, 5);
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
        if (!is_null($this->criteria->location) || !is_null($this->criteria->colltype)
            || !is_null($this->filter->location) || !is_null($this->filter->colltype)
            || !is_null($this->filter->availability)) {
            $sql_join .= 'left join item as i on b.biblio_id=i.biblio_id ';
            $sql_group = 'group by b.biblio_id';
        }

        $sql_criteria = 'where b.opac_hide=0 ';
        $sql_criteria .= ($c = $this->buildCriteria($this->criteria)) !== '' ? 'and (' . $c . ') ' : '';
        $sql_criteria .= ($c = $this->buildCriteria($this->filter)) !== '' ? 'and (' . $c . ') ' : '';

        switch ($this->filter->sort) {
            case 'recently-added':
                $sql_order = 'b.input_date desc';
                break;
            case 'publish-year-newest':
                $sql_order = 'b.publish_year desc';
                break;
            case 'publish-year-oldest':
                $sql_order = 'b.publish_year asc';
                break;
            case 'title-asc':
                $sql_order = 'b.title asc';
                break;
            case 'title-desc':
                $sql_order = 'b.title desc';
                break;
            case 'most-relevant':
            case 'most-loaned':
            default:
                $sql_order = 'b.last_update desc';
        }

        $sql_query = $sql_select . ' from biblio as b ' . $sql_join . $sql_criteria . $sql_group . ' order by '.$sql_order.' limit ' . $this->limit . ' offset ' . $this->offset;
        $sql_count = 'select count(distinct b.biblio_id)' . ' from biblio as b ' . $sql_join . $sql_criteria;

        return [
            'count' => preg_replace('/\s+/', ' ', trim($sql_count)),
            'query' => preg_replace('/\s+/', ' ', trim($sql_query))
        ];
    }

    function buildCriteria($criteria): string
    {
        $title_buffer = '';
        $boolean = '';
        $sql_criteria = '';
        foreach ($criteria->toCQLToken($this->stop_words) as $token) {
            $field = $token['f'];

            // break the loop if we meet cql_end field
            if ($field === 'cql_end') break;

            if ($title_buffer === '' && $field !== 'boolean')
                $sql_criteria .= ' ' . $boolean . ' ';

            // flush title string concatenation
            if ($field !== 'title' && $title_buffer !== '') {
                $title_buffer = trim($title_buffer);
                $sql_criteria .= " b.biblio_id in(select distinct biblio_id from biblio where match (title, series_title) against (" . $title_buffer . ")) ";
                // reset title buffer
                $title_buffer = '';
            }
            // boolean mode
            $bool = $token['b'] ?? $token;
            $boolean = ($bool === '*') ? 'or' : 'and';

            // search value
            $query = $token['q'] ?? null;
            $query = !in_array($field, ['location','colltype','gmd','attachment','lang']) ? str_replace(['\\','"','\''], '', $query) : $query;
            switch ($field) {
                case 'title':
                    $this->execute[] = "%" . $query . "%";
                    $sql_criteria .= " b.title like ? ";
                    $title_buffer = '';
                    break;

                case 'author':
                    $this->execute[] = "%" . $query . "%";
                    $sub_query = "select ba.biblio_id from biblio_author as ba left join mst_author as ma on ba.author_id=ma.author_id where ma.author_name like ?";
                    if ($bool === '-') {
                        $sql_criteria .= ' b.biblio_id not in(' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' b.biblio_id in(' . $sub_query . ')';
                    }
                    break;

                case 'subject':
                    $this->execute[] = "%" . $query . "%";
                    $sub_query = "select bt.biblio_id from biblio_topic as bt left join mst_topic as mt on bt.topic_id=mt.topic_id where mt.topic like ?";
                    if ($bool === '-') {
                        $sql_criteria .= ' b.biblio_id not in(' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' b.biblio_id in(' . $sub_query . ')';
                    }
                    // reset title buffer
                    $title_buffer = '';
                    break;

                case 'location':
                    $idx = json_decode($query);
                    if (is_null($idx))
                    {
                        $this->execute[] = $query;
                        $sub_query = "select location_id from mst_location where location_name = ?";
                    }
                    else
                    {
                        $this->execute = array_merge($this->execute, $idx);
                        $sub_query = trim(str_repeat('?,', count($idx??1)), ',');
                    }

                    if ($bool === '-') {
                        $sql_criteria .= ' i.location_id not in(' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' i.location_id in(' . $sub_query . ')';
                    }
                    break;

                case 'colltype':
                    $idx = json_decode($query);
                    
                    if (is_null($idx))
                    {
                        $this->execute[] = $query;
                        $sub_query = "select coll_type_id from mst_coll_type where coll_type_name = ?";
                    }
                    else
                    {
                        $this->execute = array_merge($this->execute, $idx);
                        $sub_query = trim(str_repeat('?,', count($idx??1)), ',');
                    }
                    
                    if ($bool === '-') {
                        $sql_criteria .= ' i.coll_type_id not in(' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' i.coll_type_id in(' . $sub_query . ')';
                    }
                    break;

                case 'itemcode':
                    $this->execute[] = $query;
                    if ($bool === '-') {
                        $sql_criteria .= " i.item_code != ?";
                    } else {
                        $sql_criteria .= " i.item_code = ?";
                    }
                    break;

                case 'callnumber':
                    $this->execute[] = $query . '%';
                    if ($bool === '-') {
                        $sql_criteria .= " b.call_number not like ?";
                    } else {
                        $sql_criteria .= " b.call_number like ?";
                    }
                    break;

                case 'itemcallnumber':
                    $this->execute[] = $query . '%';
                    if ($bool === '-') {
                        $sql_criteria .= " i.call_number not like ?";
                    } else {
                        $sql_criteria .= " i.call_number like ?";
                    }
                    break;

                case 'class':
                    $this->execute[] = $query . '%';
                    if ($bool === '-') {
                        $sql_criteria .= " b.classification not like ?";
                    } else {
                        $sql_criteria .= " b.classification like ?";
                    }
                    break;

                case 'isbn':
                    $this->execute[] = $query;
                    if ($bool === '-') {
                        $sql_criteria .= " b.isbn_issn != ?";
                    } else {
                        $sql_criteria .= " b.isbn_issn = ?";
                    }
                    break;

                case 'publisher':
                    $this->execute[] = '%' . $query . '%';
                    $sub_query = "select publisher_id from mst_publisher where publisher_name like ?";
                    if ($bool === '-') {
                        $sql_criteria .= ' b.publisher_id not in (' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' b.publisher_id in (' . $sub_query . ')';
                    }
                    break;

                case 'publishyear':
                    $this->execute[] = '%' . $query . '%';
                    if ($bool === '-') {
                        $sql_criteria .= " b.publish_year NOT LIKE ?";
                    } else {
                        $sql_criteria .= " b.publish_year LIKE ?";
                    }
                    break;

                case 'years':
                    list($from, $to) = explode(';', $query);
                    $this->execute[] = $from;
                    $this->execute[] = $to;
                    $sql_criteria .= " (b.publish_year between ? and ?) ";
                    break;

                case 'gmd':
                    $idx = json_decode($query);
                    
                    if (is_null($idx))
                    {
                        $this->execute[] = '%' . $query . '%';
                        $sub_query = "select gmd_id from mst_gmd where gmd_name like ?";
                    }
                    else
                    {
                        $this->execute = array_merge($this->execute, $idx);
                        $sub_query = trim(str_repeat('?,', count($idx??1)), ',');
                    }

                    if ($bool === '-') {
                        $sql_criteria .= ' b.gmd_id not in (' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' b.gmd_id in (' . $sub_query . ')';
                    }
                    break;

                case 'notes':
                    $query = isset($query['is_phrase']) ? '"' . $query . '"' : $query;
                    $this->execute[] = "'" . $query . "' in boolean mode";
                    if ($bool === '-') {
                        $sql_criteria .= " not (match (b.notes) against (?))";
                    } else {
                        $sql_criteria .= " (match (b.notes) against ('" . $query . "' in boolean mode))";
                    }
                    break;

                case 'availability':
                    $sql_criteria .= ' i.item_id is not null ';

                    $sub_query = "select distinct item.biblio_id from item 
                                    left join loan on item.item_code = loan.item_code 
                                    where loan.is_return = 0";
                    $sql_criteria .= ' and b.biblio_id not in('.$sub_query.')';
                    break;

                case 'attachment':
                    $mime_types = [];
                    $queryArr = json_decode($query, true);
                    foreach ($queryArr??[] as $q) {
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

                    $this->execute = array_merge($this->execute, $mime_types);
                    $sub_query_criteria = trim(str_repeat('?,', count($mime_types??1)), ',');

                    $sub_query = "select bat.biblio_id from biblio_attachment as bat left join files as f on bat.file_id=f.file_id where f.mime_type in(" . $sub_query_criteria . ")";
                    if ($bool === '-') {
                        $sql_criteria .= ' b.biblio_id not in(' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' b.biblio_id in(' . $sub_query . ')';
                    }
                    break;

                case 'lang':
                    $idx = json_decode($query);
                    $this->execute = array_merge($this->execute, $idx);
                    $sub_query = trim(str_repeat('?,', count($idx??1)), ',');
                    if ($bool === '-') {
                        $sql_criteria .= ' b.language_id not in (' . $sub_query . ')';
                    } else {
                        $sql_criteria .= ' b.language_id in (' . $sub_query . ')';
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

            // get the authors data
            $_biblio_authors_q = $db->prepare('SELECT a.*,ba.level FROM mst_author AS a'
                . ' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id=?');
            $_biblio_authors_q->execute([$document['biblio_id']]);

            $record['author'] = [];

            while ($_auth_d = $_biblio_authors_q->fetch(\PDO::FETCH_ASSOC)) {
                $record['author']['name'][] = trim($_auth_d['author_name']);
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
            $xml->text($document['publish_place'] ?? '');
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

    function dump(array $sql)
    {
        if (!isset($_GET['resultXML'])) debug('Engine ⚙️ : ' . get_class($this), "SQL ⚒️", $sql, "Bind Value ⚒️", $this->execute);
    }
}
