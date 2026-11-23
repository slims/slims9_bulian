<?php
/**
 * Elasticsearch Search Engine Implementation for SLiMS
 *
 * Talks to Elasticsearch 7.x / 8.x through its REST API, so no client
 * library is needed. Elasticsearch is only asked for the matching biblio
 * IDs; the records shown to the user are always read from the database.
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

use RuntimeException;
use SLiMS\DB;
use Throwable;

class ElasticSearchEngine extends Contract
{
    /**
     * Name of the setting that holds the connection options
     */
    const CONFIG_KEY = 'elasticsearch_config';

    protected string $host = 'http://localhost:9200';
    protected string $index = 'slims';
    protected string $username = '';
    protected string $password = '';
    protected bool $verify_ssl = true;
    protected int $timeout = 10;
    protected array $custom_fields = [];

    public array $searchable_fields = [
        'title', 'author', 'subject', 'isbn',
        'publisher', 'gmd', 'notes', 'colltype', 'publishyear',
        'location', 'itemcode', 'callnumber', 'class'
    ];

    public function __construct()
    {
        parent::__construct();

        $config = config(self::CONFIG_KEY);
        $config = is_array($config) ? $config : [];

        $this->host = self::normalizeHost((string)($config['host'] ?? $this->host));
        $this->index = self::normalizeIndexName((string)($config['index_name'] ?? $this->index));
        $this->username = (string)($config['username'] ?? '');
        $this->password = (string)($config['password'] ?? '');
        $this->verify_ssl = (bool)($config['verify_ssl'] ?? true);
    }

    /*
     |--------------------------------------------------------------------------
     | Configuration helpers
     |--------------------------------------------------------------------------
     */

    /**
     * Make sure the host is a base URL without trailing slash
     */
    public static function normalizeHost(string $host): string
    {
        $host = trim($host);
        if ($host === '') $host = 'http://localhost:9200';
        if (!preg_match('@^https?://@i', $host)) $host = 'http://' . $host;
        return rtrim($host, '/');
    }

    /**
     * Index names are part of the request URL: lowercase letters, digits,
     * dash and underscore only
     */
    public static function normalizeIndexName(string $name): string
    {
        $name = preg_replace('/[^a-z0-9_\-]/', '', strtolower(trim($name)));
        $name = ltrim((string)$name, '_-');
        return $name === '' ? 'slims' : $name;
    }

    public function getIndexName(): string
    {
        return $this->index;
    }

    /**
     * Index settings and field mapping (Elasticsearch 7.x / 8.x, no mapping type)
     */
    public static function indexDefinition(): array
    {
        $text = ['type' => 'text', 'fields' => ['raw' => ['type' => 'keyword', 'ignore_above' => 256]]];
        $keyword = ['type' => 'keyword', 'ignore_above' => 256];
        $date = ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd', 'ignore_malformed' => true];

        return [
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0
            ],
            'mappings' => [
                // values such as 0000-00-00 must not be guessed as dates
                'date_detection' => false,
                'properties' => [
                    'biblio_id' => ['type' => 'integer'],
                    'title' => $text,
                    'sor' => ['type' => 'text'],
                    'edition' => $keyword,
                    'isbn_issn' => $keyword,
                    'gmd_name' => $keyword,
                    'publisher_name' => $text,
                    'publish_year' => $keyword,
                    'place' => $keyword,
                    'language_name' => $keyword,
                    'classification' => $keyword,
                    'call_number' => $keyword,
                    'series_title' => ['type' => 'text'],
                    'notes' => ['type' => 'text'],
                    'opac_hide' => ['type' => 'integer'],
                    'promoted' => ['type' => 'integer'],
                    'authors' => ['properties' => ['author_name' => $text]],
                    'subjects' => ['properties' => ['topic' => $text]],
                    'items' => ['properties' => [
                        'item_code' => $keyword,
                        'call_number' => $keyword,
                        'coll_type_name' => $keyword,
                        'location_name' => $keyword,
                        'item_status' => $keyword
                    ]],
                    'input_date' => $date,
                    'last_update' => $date
                ]
            ]
        ];
    }

    /*
     |--------------------------------------------------------------------------
     | REST client
     |--------------------------------------------------------------------------
     */

    /**
     * Send a request to Elasticsearch
     *
     * @param string $method HTTP method
     * @param string $path path after the host, starting with a slash
     * @param array|string|null $body array is sent as JSON, string as NDJSON (bulk)
     * @return array{0: int, 1: array} HTTP status and decoded response
     */
    protected function request(string $method, string $path, $body = null): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required to connect to Elasticsearch.');
        }

        $headers = ['Accept: application/json'];
        $payload = null;
        if (is_array($body)) {
            $payload = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            $headers[] = 'Content-Type: application/json';
        } else if (is_string($body)) {
            $payload = $body;
            $headers[] = 'Content-Type: application/x-ndjson';
        }

        $ch = curl_init($this->host . $path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($payload !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        if ($this->username !== '') curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        if (!$this->verify_ssl) {
            // only for a server with a self signed certificate; chosen by the administrator
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // safe-anything:ignore
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Can not connect to Elasticsearch at ' . $this->host . ': ' . $message);
        }
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $data = json_decode((string)$raw, true);
        return [$status, is_array($data) ? $data : []];
    }

    /**
     * Same as request(), but a failed response becomes an exception
     */
    protected function call(string $method, string $path, $body = null): array
    {
        [$status, $data] = $this->request($method, $path, $body);
        if ($status < 200 || $status >= 300) {
            $reason = $data['error']['reason'] ?? ($data['error']['type'] ?? (is_string($data['error'] ?? null) ? $data['error'] : ''));
            throw new RuntimeException('Elasticsearch error (HTTP ' . $status . ')' . ($reason !== '' ? ': ' . $reason : ''));
        }
        return $data;
    }

    /*
     |--------------------------------------------------------------------------
     | Index management
     |--------------------------------------------------------------------------
     */

    /**
     * Check the connection
     *
     * @return string Elasticsearch version number
     */
    public function ping(): string
    {
        $info = $this->call('GET', '/');
        return (string)($info['version']['number'] ?? '');
    }

    public function indexExists(): bool
    {
        [$status] = $this->request('HEAD', '/' . $this->index);
        return $status === 200;
    }

    public function createIndex(): void
    {
        $this->call('PUT', '/' . $this->index, self::indexDefinition());
    }

    public function deleteIndex(): void
    {
        [$status, $data] = $this->request('DELETE', '/' . $this->index);
        // nothing to delete is fine
        if ($status === 404) return;
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('Elasticsearch error (HTTP ' . $status . '): ' . ($data['error']['reason'] ?? 'can not delete index'));
        }
    }

    /**
     * Number of documents currently in the index
     */
    public function countDocuments(): int
    {
        $this->call('POST', '/' . $this->index . '/_refresh');
        $data = $this->call('GET', '/' . $this->index . '/_count');
        return (int)($data['count'] ?? 0);
    }

    /**
     * Turn the output of api::biblio_load() into an index document
     */
    public static function prepareDocument(array $biblio): array
    {
        // "_id" is a reserved metadata field
        unset($biblio['_id'], $biblio['id']);

        $biblio['biblio_id'] = (int)($biblio['biblio_id'] ?? 0);
        $biblio['opac_hide'] = (int)($biblio['opac_hide'] ?? 0);
        $biblio['promoted'] = (int)($biblio['promoted'] ?? 0);

        foreach (['input_date', 'last_update'] as $field) {
            if (empty($biblio[$field]) || strpos((string)$biblio[$field], '0000-00-00') === 0) $biblio[$field] = null;
        }
        foreach (['authors', 'subjects', 'items'] as $field) {
            if (!is_array($biblio[$field] ?? null)) $biblio[$field] = [];
        }

        return $biblio;
    }

    /**
     * Add or replace one bibliographic record
     */
    public function indexDocument(array $biblio): void
    {
        $document = self::prepareDocument($biblio);
        if ($document['biblio_id'] < 1) return;
        $this->call('PUT', '/' . $this->index . '/_doc/' . $document['biblio_id'], $document);
    }

    /**
     * Add or replace many bibliographic records in one request
     *
     * @return int number of documents Elasticsearch refused
     */
    public function indexDocuments(array $biblios): int
    {
        $lines = '';
        foreach ($biblios as $biblio) {
            $document = self::prepareDocument($biblio);
            if ($document['biblio_id'] < 1) continue;
            $lines .= json_encode(['index' => ['_id' => (string)$document['biblio_id']]]) . "\n";
            $lines .= json_encode($document, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . "\n";
        }
        if ($lines === '') return 0;

        $data = $this->call('POST', '/' . $this->index . '/_bulk', $lines);
        if (empty($data['errors'])) return 0;

        $failed = 0;
        foreach ($data['items'] ?? [] as $item) {
            if (isset($item['index']['error'])) $failed++;
        }
        return $failed;
    }

    public function deleteDocument(int $biblio_id): void
    {
        [$status, $data] = $this->request('DELETE', '/' . $this->index . '/_doc/' . $biblio_id);
        if ($status === 404) return;
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('Elasticsearch error (HTTP ' . $status . '): ' . ($data['error']['reason'] ?? 'can not delete document'));
        }
    }

    /**
     * Keep the index in step with the database after a record is saved.
     * Does nothing unless this engine is the active one, and never lets an
     * Elasticsearch problem stop the save: the failure is logged instead.
     *
     * @param object $obj_db mysqli connection, as used by api::biblio_load()
     */
    public static function sync($obj_db, int $biblio_id): void
    {
        if (Engine::active() !== self::class || $biblio_id < 1) return;
        try {
            if (!class_exists('api', false)) require_once LIB . 'api.inc.php';
            $biblio = \api::biblio_load($obj_db, $biblio_id);
            if (is_array($biblio) && $biblio) (new self)->indexDocument($biblio);
        } catch (Throwable $e) {
            error_log('Elasticsearch index update failed for biblio ' . $biblio_id . ': ' . $e->getMessage());
        }
    }

    /**
     * Drop a record from the index after it is deleted from the database.
     * Same rules as sync().
     */
    public static function forget(int $biblio_id): void
    {
        if (Engine::active() !== self::class || $biblio_id < 1) return;
        try {
            (new self)->deleteDocument($biblio_id);
        } catch (Throwable $e) {
            error_log('Elasticsearch index delete failed for biblio ' . $biblio_id . ': ' . $e->getMessage());
        }
    }

    /*
     |--------------------------------------------------------------------------
     | Searching
     |--------------------------------------------------------------------------
     */

    /**
     * Look up names for a JSON list of master file IDs, as sent by the OPAC filter
     */
    protected function namesFromIds(string $query, string $table, string $id_column, string $name_column): ?array
    {
        $ids = json_decode($query, true);
        if (!is_array($ids)) return null;
        $ids = array_values(array_filter($ids, 'is_scalar'));
        if (!$ids) return [];

        $statement = DB::getInstance()->prepare("select `$name_column` from `$table` where `$id_column` in (" . trim(str_repeat('?,', count($ids)), ',') . ')');
        $statement->execute($ids);
        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * One search token into one Elasticsearch clause
     */
    protected function clause(array $token): ?array
    {
        $field = $token['f'];
        // boolean markers of the SQL engines have no meaning here
        $query = trim((string)preg_replace('/(^|\s)[+*\-]+/', '$1', (string)($token['q'] ?? '')));
        if ($query === '') return null;

        // every word must be there; "quoted words" are searched as a phrase
        $phrase = isset($token['is_phrase']) || ($token['b'] ?? '') === '++';
        $text = function (string $es_field) use ($query, $phrase) {
            if ($phrase) return ['match_phrase' => [$es_field => str_replace('"', '', $query)]];
            return ['simple_query_string' => [
                'query' => $query,
                'fields' => [$es_field],
                'default_operator' => 'and',
                'flags' => 'PHRASE|WHITESPACE'
            ]];
        };
        $terms = function (string $es_field, ?array $names) use ($query) {
            return ['terms' => [$es_field => $names ?? [$query]]];
        };

        switch ($field) {
            case 'title':
                return $text('title');
            case 'author':
                return $text('authors.author_name');
            case 'subject':
                return $text('subjects.topic');
            case 'notes':
                return $text('notes');
            case 'publisher':
                return $text('publisher_name');
            case 'isbn':
                return ['term' => ['isbn_issn' => $query]];
            case 'publishyear':
                return ['term' => ['publish_year' => $query]];
            case 'itemcode':
                return ['term' => ['items.item_code' => $query]];
            case 'callnumber':
                return ['prefix' => ['call_number' => $query]];
            case 'class':
                return ['prefix' => ['classification' => $query]];
            case 'years':
                $years = array_map('trim', explode(';', $query));
                if (count($years) !== 2) return null;
                return ['range' => ['publish_year' => ['gte' => $years[0], 'lte' => $years[1]]]];
            case 'gmd':
                return $terms('gmd_name', $this->namesFromIds($query, 'mst_gmd', 'gmd_id', 'gmd_name'));
            case 'lang':
                return $terms('language_name', $this->namesFromIds($query, 'mst_language', 'language_id', 'language_name'));
            case 'colltype':
                return $terms('items.coll_type_name', $this->namesFromIds($query, 'mst_coll_type', 'coll_type_id', 'coll_type_name'));
            case 'location':
                return $terms('items.location_name', $this->namesFromIds($query, 'mst_location', 'location_id', 'location_name'));
            case 'availability':
                return ['term' => ['items.item_status' => 'Available']];
            // not searchable in this index
            case 'attachment':
            case 'sort':
            case 'opengroup':
            case 'closegroup':
                return null;
            default:
                return ['multi_match' => [
                    'query' => $query,
                    'fields' => ['title^3', 'authors.author_name^2', 'subjects.topic^2', 'notes']
                ]];
        }
    }

    /**
     * Build the search request body from the criteria and the filter
     */
    protected function buildQuery(): array
    {
        $bool = ['must' => [], 'must_not' => [], 'filter' => []];
        $has_query = false;

        // each term carries the boolean that links it to the term before it
        $terms = [];
        foreach ($this->criteria->toCQLToken($this->stop_words) as $token) {
            if ($token['f'] === 'cql_end') break;
            if ($token['f'] === 'boolean') continue;
            if (($clause = $this->clause($token)) === null) continue;
            $terms[] = [$clause, $token['b'] ?? '+'];
        }

        // "a OR b": both sides are alternatives, so a term is optional when
        // the link before it or the link after it is an OR
        $alternatives = [];
        foreach ($terms as $i => [$clause, $link]) {
            if ($link === '-') {
                $bool['must_not'][] = $clause;
            } else if ($link === '*' || ($terms[$i + 1][1] ?? '') === '*') {
                $alternatives[] = $clause;
            } else {
                $bool['must'][] = $clause;
            }
            $has_query = true;
        }
        if ($alternatives) {
            $bool['must'][] = ['bool' => ['should' => $alternatives, 'minimum_should_match' => 1]];
        }

        if (isset($this->filter)) {
            foreach ($this->filter->toCQLToken() as $token) {
                if ($token['f'] === 'cql_end') break;
                if ($token['f'] === 'boolean') continue;
                if (($clause = $this->clause($token)) === null) continue;
                $bool[($token['b'] ?? '+') === '-' ? 'must_not' : 'filter'][] = $clause;
            }
        }

        // hidden records never show up in OPAC
        $bool['must_not'][] = ['term' => ['opac_hide' => 1]];

        $sort_key = isset($this->filter) ? (string)$this->filter->sort : '';
        switch ($sort_key) {
            case 'recently-added':
                $sort = [['input_date' => ['order' => 'desc', 'missing' => '_last']]];
                break;
            case 'publish-year-newest':
                $sort = [['publish_year' => 'desc']];
                break;
            case 'publish-year-oldest':
                $sort = [['publish_year' => 'asc']];
                break;
            case 'title-asc':
                $sort = [['title.raw' => 'asc']];
                break;
            case 'title-desc':
                $sort = [['title.raw' => 'desc']];
                break;
            default:
                $sort = [['last_update' => ['order' => 'desc', 'missing' => '_last']]];
                // a real search is ordered by relevance first
                if ($has_query) array_unshift($sort, '_score');
        }

        return [
            'from' => $this->offset,
            'size' => $this->limit,
            'track_total_hits' => true,
            '_source' => false,
            'query' => ['bool' => array_filter($bool)],
            'sort' => $sort
        ];
    }

    /**
     * Get documents: matching IDs from Elasticsearch, records from the database
     */
    public function getDocuments()
    {
        // start time for benchmarking
        $start = microtime(true);

        try {
            $body = $this->buildQuery();
            $this->dump($body);

            $result = $this->call('POST', '/' . $this->index . '/_search', $body);

            $total = $result['hits']['total'] ?? 0;
            $this->num_rows = (int)(is_array($total) ? ($total['value'] ?? 0) : $total);

            $biblio_ids = [];
            foreach ($result['hits']['hits'] ?? [] as $hit) {
                $biblio_ids[] = (int)$hit['_id'];
            }
            $this->documents = $this->getBiblioDetails($biblio_ids);
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            $this->documents = [];
            $this->num_rows = 0;
        }

        // end time
        $this->query_time = round(microtime(true) - $start, 5);
    }

    /**
     * Get bibliographic details from the database, in the order given
     */
    protected function getBiblioDetails(array $biblio_ids): array
    {
        if (empty($biblio_ids)) return [];

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

        // check for custom front page fields
        $file_path = SB . config('template.dir', 'template') . DS .
                     config('template.theme', 'default') . DS .
                     'custom_frontpage_record.inc.php';

        if (file_exists($file_path)) {
            include $file_path;
            $this->custom_fields = $custom_fields ?? [];
        }

        $statement = DB::getInstance()->prepare($sql);
        $statement->execute($biblio_ids);

        // keep the order Elasticsearch returned
        $rows = [];
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $rows[(int)$row['biblio_id']] = $row;
        }
        $documents = [];
        foreach ($biblio_ids as $biblio_id) {
            if (isset($rows[$biblio_id])) $documents[] = $rows[$biblio_id];
        }
        return $documents;
    }

    public function dump(array $query)
    {
        if (defined('DEBUG') && DEBUG) {
            error_log('Elasticsearch Query: ' . json_encode($query));
        }
    }

    /*
     |--------------------------------------------------------------------------
     | Output
     |--------------------------------------------------------------------------
     */

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

        foreach ($this->documents as $document) {
            $record = [];
            $record['@id'] = 'http://' . $_SERVER['SERVER_NAME'] . SWB . 'index.php?p=show_detail&id=' . $document['biblio_id'];
            $record['name'] = trim((string)$document['title']);
            $record['author'] = [];
            foreach (array_filter(array_map('trim', explode(' - ', (string)$document['author']))) as $author) {
                $record['author']['name'][] = $author;
            }
            $record['isbn'] = $document['isbn_issn'];
            $record['publisher'] = $document['publisher'];
            $record['dateCreated'] = $document['publish_year'];
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

        // include biblio list html template callback
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
        foreach (['modsResultNum' => $this->num_rows, 'modsResultPage' => $this->page, 'modsResultShowed' => $this->limit] as $name => $value) {
            $xml->startElementNS('slims', $name, null);
            $xml->text((string)$value);
            $xml->endElement();
        }
        $xml->endElement();

        foreach ($this->documents as $document) {
            $xml->startElement('mods');
            $xml->writeAttribute('version', '3.3');
            $xml->writeAttribute('ID', (string)$document['biblio_id']);

            $xml->startElement('titleInfo');
            $xml->writeElement('title', (string)$document['title']);
            $xml->endElement();

            foreach (array_filter(array_map('trim', explode(' - ', (string)$document['author']))) as $author) {
                $xml->startElement('name');
                $xml->writeAttribute('type', 'personal');
                $xml->writeElement('namePart', $author);
                $xml->startElement('role');
                $xml->startElement('roleTerm');
                $xml->writeAttribute('type', 'text');
                $xml->text('creator');
                $xml->endElement();
                $xml->endElement();
                $xml->endElement();
            }

            if (!empty($document['publisher']) || !empty($document['publish_year'])) {
                $xml->startElement('originInfo');
                if (!empty($document['publisher'])) $xml->writeElement('publisher', (string)$document['publisher']);
                if (!empty($document['publish_year'])) $xml->writeElement('dateIssued', (string)$document['publish_year']);
                $xml->endElement();
            }

            if (!empty($document['isbn_issn'])) {
                $xml->startElement('identifier');
                $xml->writeAttribute('type', 'isbn');
                $xml->text((string)$document['isbn_issn']);
                $xml->endElement();
            }

            foreach (array_filter(array_map('trim', explode(', ', (string)$document['topic']))) as $topic) {
                $xml->startElement('subject');
                $xml->writeElement('topic', $topic);
                $xml->endElement();
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
        $xml->writeElement('title', 'SLiMS Search Results');
        $xml->writeElement('description', 'Search results from SLiMS library catalog');
        $xml->writeElement('link', 'http://' . $_SERVER['SERVER_NAME'] . SWB);

        foreach ($this->documents as $document) {
            $description = '';
            if (!empty($document['author'])) $description .= 'Author: ' . $document['author'] . '<br/>';
            if (!empty($document['publisher'])) $description .= 'Publisher: ' . $document['publisher'] . '<br/>';
            if (!empty($document['publish_year'])) $description .= 'Year: ' . $document['publish_year'] . '<br/>';

            $xml->startElement('item');
            $xml->writeElement('title', (string)$document['title']);
            $xml->writeElement('link', 'http://' . $_SERVER['SERVER_NAME'] . SWB . 'index.php?p=show_detail&id=' . $document['biblio_id']);
            $xml->writeElement('description', $description);
            if (!empty($document['isbn_issn'])) $xml->writeElement('isbn', (string)$document['isbn_issn']);
            $xml->endElement(); // item
        }

        $xml->endElement(); // channel
        $xml->endElement(); // rss

        return $xml->outputMemory();
    }
}
