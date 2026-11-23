<?php
/**
 * Base class for search engines that keep their index on another server
 * (Elasticsearch, Solr) and are reached through a REST API.
 *
 * The server is only asked for the matching biblio IDs; the records shown
 * to the user are always read from the database.
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

abstract class RemoteIndexEngine extends Contract
{
    /**
     * Name of the setting that holds the connection options
     */
    const CONFIG_KEY = '';

    /**
     * Name of the server software, for messages
     */
    const LABEL = 'Search server';

    const DEFAULT_HOST = 'http://localhost';

    protected string $host = '';
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

        $config = config(static::CONFIG_KEY);
        $config = is_array($config) ? $config : [];

        $this->host = static::normalizeHost((string)($config['host'] ?? ''));
        $this->index = static::normalizeIndexName((string)($config['index_name'] ?? $this->index));
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
        if ($host === '') $host = static::DEFAULT_HOST;
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

    /*
     |--------------------------------------------------------------------------
     | REST client
     |--------------------------------------------------------------------------
     */

    /**
     * Send a request to the search server
     *
     * @param string $method HTTP method
     * @param string $path path after the host, starting with a slash
     * @param array|string|null $body array is sent as JSON, string as NDJSON (bulk)
     * @return array{0: int, 1: array} HTTP status and decoded response
     */
    protected function request(string $method, string $path, $body = null): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required to connect to ' . static::LABEL . '.');
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
            throw new RuntimeException('Can not connect to ' . static::LABEL . ' at ' . $this->host . ': ' . $message);
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
            $reason = $data['error']['reason'] ?? $data['error']['msg'] ?? ($data['error']['type'] ?? (is_string($data['error'] ?? null) ? $data['error'] : ''));
            throw new RuntimeException(static::LABEL . ' error (HTTP ' . $status . ')' . ($reason !== '' ? ': ' . $reason : ''));
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
     * @return string version number of the server, when it tells
     */
    abstract public function ping(): string;

    /**
     * Is the index there and ready to receive documents?
     */
    abstract public function indexExists(): bool;

    abstract public function createIndex(): void;

    /**
     * Remove everything that has been indexed
     */
    abstract public function deleteIndex(): void;

    /**
     * Number of documents currently in the index
     */
    abstract public function countDocuments(): int;

    /**
     * Add or replace many bibliographic records in one request
     *
     * @param array $biblios list of api::biblio_load() results
     * @return int number of documents the server refused
     */
    abstract public function indexDocuments(array $biblios): int;

    abstract public function deleteDocument(int $biblio_id): void;

    /**
     * Add or replace one bibliographic record
     */
    public function indexDocument(array $biblio): void
    {
        if ($this->indexDocuments([$biblio]) > 0) {
            throw new RuntimeException(static::LABEL . ' refused the document.');
        }
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
     * The active search engine, when it is one that keeps a remote index
     */
    public static function activeInstance(): ?RemoteIndexEngine
    {
        $class = Engine::active();
        if (!is_string($class) || !in_array($class, Engine::init()->get(), true)) return null;
        return is_subclass_of($class, self::class) ? new $class : null;
    }

    /**
     * Keep the index in step with the database after a record is saved.
     * Does nothing unless the active engine keeps a remote index, and never
     * lets a server problem stop the save: the failure is logged instead.
     *
     * @param object $obj_db mysqli connection, as used by api::biblio_load()
     */
    public static function sync($obj_db, int $biblio_id): void
    {
        if ($biblio_id < 1) return;
        try {
            if (!($engine = self::activeInstance())) return;
            if (!class_exists('api', false)) require_once LIB . 'api.inc.php';
            $biblio = \api::biblio_load($obj_db, $biblio_id);
            if (is_array($biblio) && $biblio) $engine->indexDocument($biblio);
        } catch (Throwable $e) {
            error_log('Search index update failed for biblio ' . $biblio_id . ': ' . $e->getMessage());
        }
    }

    /**
     * Drop a record from the index after it is deleted from the database.
     * Same rules as sync().
     */
    public static function forget(int $biblio_id): void
    {
        if ($biblio_id < 1) return;
        try {
            if ($engine = self::activeInstance()) $engine->deleteDocument($biblio_id);
        } catch (Throwable $e) {
            error_log('Search index delete failed for biblio ' . $biblio_id . ': ' . $e->getMessage());
        }
    }

    /*
     |--------------------------------------------------------------------------
     | Searching
     |--------------------------------------------------------------------------
     */

    /**
     * Search terms with the boolean that links each one to the term before it
     *
     * @param callable $clause turns one token into a clause, null to skip it
     * @return array list of [clause, link]
     */
    protected function linkedTerms(Criteria $criteria, array $stop_words, callable $clause): array
    {
        $terms = [];
        foreach ($criteria->toCQLToken($stop_words) as $token) {
            if ($token['f'] === 'cql_end') break;
            if ($token['f'] === 'boolean') continue;
            if (($built = $clause($token)) === null) continue;
            $terms[] = [$built, $token['b'] ?? '+'];
        }
        return $terms;
    }

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

        // keep the order the search server returned
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
            error_log(static::LABEL . ' query: ' . json_encode($query));
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
