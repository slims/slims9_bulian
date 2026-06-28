<?php
/**
 * Sphinx Search Engine Implementation for SLiMS
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

namespace SLiMS\SearchEngine;

use SLiMS\DB;
use SLiMS\SearchEngine\Contract;

class SphinxSearchEngine extends Contract {
    
    protected $sphinx_host = 'localhost';
    protected $sphinx_port = 9312;
    protected $sphinx_index = 'slims';
    protected $sphinx_connection = null;
    protected array $custom_fields = [];
    
    public array $searchable_fields = [
        'title', 'author', 'subject', 'isbn',
        'publisher', 'gmd', 'notes', 'colltype', 'publishyear',
        'location', 'itemcode', 'callnumber'
    ];

    public function __construct()
    {
        parent::__construct();

        // Load configuration from database settings first
        $sphinx_config = config('sphinx_search_config') ?? [];
        
        // Get Sphinx configuration from sysconf as fallback
        global $sysconf;
        
        // Priority: database config -> sysconf -> default values
        $this->sphinx_host = $sphinx_config['host'] ?? ($sysconf['sphinx']['host'] ?? $this->sphinx_host);
        $this->sphinx_port = $sphinx_config['port'] ?? ($sysconf['sphinx']['port'] ?? $this->sphinx_port);
        $this->sphinx_index = $sphinx_config['index_name'] ?? ($sysconf['sphinx']['index'] ?? $this->sphinx_index);
    }

    /**
     * Get Sphinx connection via MySQL protocol
     */
    protected function getSphinxConnection()
    {
        if ($this->sphinx_connection === null) {
            try {
                $dsn = "mysql:host={$this->sphinx_host};port={$this->sphinx_port}";
                $this->sphinx_connection = new \PDO($dsn, '', '', [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]);
            } catch (\PDOException $e) {
                $this->error = "Sphinx connection failed: " . $e->getMessage();
                throw new \Exception($this->error);
            }
        }
        return $this->sphinx_connection;
    }

    /**
     * Build Sphinx query from criteria
     */
    protected function buildSphinxQuery(): array
    {
        $search_text = '';
        $filters = [];
        $sort_order = '@relevance DESC';
        
        // Process search criteria
        foreach ($this->criteria->toCQLToken($this->stop_words) as $token) {
            $field = $token['f'];
            
            if ($field === 'cql_end') break;
            
            $query = $token['q'] ?? null;
            $bool = $token['b'] ?? '*';
            
            // Build full-text search query
            if (in_array($field, ['title', 'author', 'subject', 'notes'])) {
                $operator = ($bool === '-') ? '-' : ($bool === '*' ? '|' : '');
                $search_text .= ' ' . $operator . $query;
            }
            
            // Build attribute filters
            switch ($field) {
                case 'isbn':
                    $filters[] = "isbn_issn = '" . $this->escapeString($query) . "'";
                    break;
                case 'publisher':
                    $filters[] = "MATCH('@publisher " . $this->escapeString($query) . "')";
                    break;
                case 'publishyear':
                    $filters[] = "publish_year = '" . $this->escapeString($query) . "'";
                    break;
                case 'gmd':
                    $filters[] = "gmd = '" . $this->escapeString($query) . "'";
                    break;
                case 'callnumber':
                    $filters[] = "call_number LIKE '" . $this->escapeString($query) . "%'";
                    break;
            }
        }
        
        // Add filter criteria
        if (isset($this->filter)) {
            if (!is_null($this->filter->gmd)) {
                $gmd_list = json_decode($this->filter->gmd);
                if (is_array($gmd_list)) {
                    $escaped = array_map([$this, 'escapeString'], $gmd_list);
                    $filters[] = "gmd IN ('" . implode("','", $escaped) . "')";
                }
            }
            
            if (!is_null($this->filter->lang)) {
                $lang_list = json_decode($this->filter->lang);
                if (is_array($lang_list)) {
                    $escaped = array_map([$this, 'escapeString'], $lang_list);
                    $filters[] = "language IN ('" . implode("','", $escaped) . "')";
                }
            }
            
            // Sort order
            switch ($this->filter->sort) {
                case 'recently-added':
                    $sort_order = 'input_date DESC';
                    break;
                case 'publish-year-newest':
                    $sort_order = 'publish_year DESC';
                    break;
                case 'publish-year-oldest':
                    $sort_order = 'publish_year ASC';
                    break;
                case 'title-asc':
                    $sort_order = 'WEIGHT() DESC, title ASC';
                    break;
                case 'title-desc':
                    $sort_order = 'WEIGHT() DESC, title DESC';
                    break;
                default:
                    $sort_order = 'WEIGHT() DESC, last_update DESC';
            }
        }
        
        // Always filter out hidden items
        $filters[] = "opac_hide = 0";
        
        // Promoted items
        if ($this->filter->promoted ?? false) {
            $filters[] = "promoted = 1";
        }
        
        return [
            'search' => trim($search_text),
            'filters' => $filters,
            'sort' => $sort_order
        ];
    }

    /**
     * Escape string for Sphinx
     */
    protected function escapeString($string)
    {
        return str_replace(["'", "\\", "\x00"], ["''", "\\\\", ""], $string);
    }

    /**
     * Get documents from Sphinx
     */
    public function getDocuments()
    {
        // Start time for benchmarking
        $start = microtime(true);

        try {
            $sphinx = $this->getSphinxConnection();
            $query_parts = $this->buildSphinxQuery();
            
            // Build SphinxQL query
            $match_clause = !empty($query_parts['search']) 
                ? "MATCH('" . $this->escapeString($query_parts['search']) . "')" 
                : "1=1";
            
            $where_clause = $match_clause;
            if (!empty($query_parts['filters'])) {
                $where_clause .= " AND " . implode(" AND ", $query_parts['filters']);
            }
            
            // Count query
            $count_sql = "SELECT COUNT(*) as total FROM {$this->sphinx_index} WHERE {$where_clause}";
            
            // Main query
            $sql = "SELECT biblio_id_attr as biblio_id FROM {$this->sphinx_index} 
                    WHERE {$where_clause}
                    ORDER BY {$query_parts['sort']}
                    LIMIT {$this->offset}, {$this->limit}
                    OPTION ranker=sph04";
            
            // Dump query for debugging
            $this->dump(['count' => $count_sql, 'query' => $sql]);
            
            // Execute count
            $count_result = $sphinx->query($count_sql);
            $this->num_rows = $count_result->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Execute main query
            $result = $sphinx->query($sql);
            $sphinx_results = $result->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get full biblio data from MySQL
            if (!empty($sphinx_results)) {
                $biblio_ids = array_column($sphinx_results, 'biblio_id');
                $this->documents = $this->getBiblioDetails($biblio_ids);
            } else {
                $this->documents = [];
            }
            
        } catch (\PDOException | \Exception $e) {
            $this->error = $e->getMessage();
            $this->documents = [];
            $this->num_rows = 0;
        }

        // End time
        $end = microtime(true);
        $this->query_time = round($end - $start, 5);
    }

    /**
     * Get full bibliographic details from MySQL database
     */
    protected function getBiblioDetails(array $biblio_ids): array
    {
        if (empty($biblio_ids)) return [];
        
        $db = DB::getInstance();
        $placeholders = implode(',', array_fill(0, count($biblio_ids), '?'));
        
        $sql = "SELECT b.biblio_id, b.title, b.image, b.isbn_issn, b.publish_year,
                b.edition, b.collation, b.series_title, b.call_number,
                mp.publisher_name as publisher, 
                mpl.place_name as publish_place, 
                b.labels, b.input_date,
                mg.gmd_name as gmd,
                GROUP_CONCAT(DISTINCT ma.author_name SEPARATOR ' - ') AS author,
                GROUP_CONCAT(DISTINCT mt.topic SEPARATOR ', ') AS topic
                FROM biblio as b
                LEFT JOIN mst_publisher as mp ON b.publisher_id = mp.publisher_id
                LEFT JOIN mst_place as mpl ON b.publish_place_id = mpl.place_id
                LEFT JOIN mst_gmd as mg ON b.gmd_id = mg.gmd_id
                LEFT JOIN biblio_author as ba ON ba.biblio_id = b.biblio_id
                LEFT JOIN mst_author as ma ON ba.author_id = ma.author_id
                LEFT JOIN biblio_topic as bt ON bt.biblio_id = b.biblio_id
                LEFT JOIN mst_topic as mt ON bt.topic_id = mt.topic_id
                WHERE b.biblio_id IN ($placeholders)
                GROUP BY b.biblio_id";
        
        // Check for custom fields
        $file_path = SB . config('template.dir', 'template') . DS . 
                     config('template.theme', 'default') . DS . 
                     'custom_frontpage_record.inc.php';
        
        if (file_exists($file_path)) {
            include $file_path;
            $this->custom_fields = $custom_fields ?? [];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($biblio_ids);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function dump(array $query)
    {
        if (defined('DEBUG') && DEBUG) {
            error_log("Sphinx Query: " . print_r($query, true));
        }
    }

    public function toArray()
    {
        return [
            'total_rows' => $this->num_rows,
            'page' => $this->page,
            'limit' => $this->limit,
            'query_time' => $this->query_time,
            'documents' => $this->documents,
            'error' => $this->error
        ];
    }

    public function toJSON()
    {
        $jsonld = [
            '@context' => 'http://schema.org',
            '@type' => 'Book',
            'total_rows' => $this->num_rows,
            'page' => $this->page,
            'records_each_page' => $this->limit,
            'query_time' => $this->query_time,
            '@graph' => [],
        ];

        $db = DB::getInstance();

        foreach ($this->documents as $document) {
            $record = [];
            $record['@id'] = 'http://' . $_SERVER['SERVER_NAME'] . SWB . 'index.php?p=show_detail&id=' . $document['biblio_id'];
            $record['name'] = trim($document['title']);

            // Get the authors data
            $_biblio_authors_q = $db->prepare('SELECT a.*, ba.level FROM mst_author AS a'
                . ' LEFT JOIN biblio_author AS ba ON a.author_id = ba.author_id WHERE ba.biblio_id = ?');
            $_biblio_authors_q->execute([$document['biblio_id']]);

            $record['author'] = [];

            while ($_auth_d = $_biblio_authors_q->fetch(\PDO::FETCH_ASSOC)) {
                $record['author']['name'][] = trim($_auth_d['author_name']);
            }

            // ISBN
            $record['isbn'] = $document['isbn_issn'];

            // Publisher
            $record['publisher'] = $document['publisher'];

            // Publish date
            $record['dateCreated'] = $document['publish_year'];

            // Document images
            if (!empty($document['image'])) {
                $record['image'] = urlencode($document['image']);
            }

            $jsonld['@graph'][] = $record;
        }

        return json_encode($jsonld);
    }

    public function toHTML()
    {
        global $sysconf;
        $buffer = '';
        
        // Include biblio list html template callback
        $path = SB . config('template.dir', $sysconf['template']['dir']) . DS;
        $path .= config('template.theme', $sysconf['template']['theme']) . DS . 'biblio_list_template.php';
        
        if (file_exists($path)) {
            include $path;
            
            foreach ($this->documents as $i => $document) {
                $buffer .= \biblio_list_format(DB::getInstance('mysqli'), $document, $i, [
                    'keywords' => $this->criteria->keywords,
                    'enable_custom_frontpage' => true,
                    'custom_fields' => $this->custom_fields
                ]);
            }
        }
        
        return $buffer;
    }

    public function toXML()
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
        $xml->endElement();
        $xml->startElementNS('slims', 'modsResultPage', null);
        $xml->text($this->page);
        $xml->endElement();
        $xml->startElementNS('slims', 'modsResultShowed', null);
        $xml->text($this->limit);
        $xml->endElement();
        $xml->endElement();

        foreach ($this->documents as $document) {
            $xml->startElement('mods');
            $xml->writeAttribute('version', '3.3');
            $xml->writeAttribute('ID', $document['biblio_id']);

            // Title
            $xml->startElement('titleInfo');
            $xml->startElement('title');
            $xml->text($document['title']);
            $xml->endElement();
            $xml->endElement();

            // Authors
            if (!empty($document['author'])) {
                $authors = explode(' - ', $document['author']);
                foreach ($authors as $author) {
                    $xml->startElement('name');
                    $xml->writeAttribute('type', 'personal');
                    $xml->startElement('namePart');
                    $xml->text(trim($author));
                    $xml->endElement();
                    $xml->startElement('role');
                    $xml->startElement('roleTerm');
                    $xml->writeAttribute('type', 'text');
                    $xml->text('creator');
                    $xml->endElement();
                    $xml->endElement();
                    $xml->endElement();
                }
            }

            // Publisher info
            if (!empty($document['publisher']) || !empty($document['publish_year'])) {
                $xml->startElement('originInfo');
                if (!empty($document['publisher'])) {
                    $xml->startElement('publisher');
                    $xml->text($document['publisher']);
                    $xml->endElement();
                }
                if (!empty($document['publish_year'])) {
                    $xml->startElement('dateIssued');
                    $xml->text($document['publish_year']);
                    $xml->endElement();
                }
                $xml->endElement();
            }

            // ISBN
            if (!empty($document['isbn_issn'])) {
                $xml->startElement('identifier');
                $xml->writeAttribute('type', 'isbn');
                $xml->text($document['isbn_issn']);
                $xml->endElement();
            }

            // Topics
            if (!empty($document['topic'])) {
                $topics = explode(', ', $document['topic']);
                foreach ($topics as $topic) {
                    $xml->startElement('subject');
                    $xml->startElement('topic');
                    $xml->text(trim($topic));
                    $xml->endElement();
                    $xml->endElement();
                }
            }

            $xml->endElement(); // mods
        }

        $xml->endElement(); // modsCollection
        return $xml->outputMemory();
    }

    public function toRSS()
    {
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        
        $xml->startElement('channel');
        $xml->startElement('title');
        $xml->text('SLiMS Search Results');
        $xml->endElement();
        $xml->startElement('description');
        $xml->text('Search results from SLiMS library catalog (Sphinx Search)');
        $xml->endElement();
        $xml->startElement('link');
        $xml->text('http://' . $_SERVER['SERVER_NAME'] . SWB);
        $xml->endElement();

        foreach ($this->documents as $document) {
            $xml->startElement('item');
            
            $xml->startElement('title');
            $xml->text($document['title']);
            $xml->endElement();
            
            $xml->startElement('link');
            $xml->text('http://' . $_SERVER['SERVER_NAME'] . SWB . 'index.php?p=show_detail&id=' . $document['biblio_id']);
            $xml->endElement();
            
            $description = '';
            if (!empty($document['author'])) {
                $description .= 'Author: ' . $document['author'] . '<br/>';
            }
            if (!empty($document['publisher'])) {
                $description .= 'Publisher: ' . $document['publisher'] . '<br/>';
            }
            if (!empty($document['publish_year'])) {
                $description .= 'Year: ' . $document['publish_year'] . '<br/>';
            }
            
            $xml->startElement('description');
            $xml->text($description);
            $xml->endElement();
            
            if (!empty($document['isbn_issn'])) {
                $xml->startElement('isbn');
                $xml->text($document['isbn_issn']);
                $xml->endElement();
            }
            
            $xml->endElement(); // item
        }

        $xml->endElement(); // channel
        $xml->endElement(); // rss
        
        return $xml->outputMemory();
    }
}
