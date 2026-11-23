<?php
/**
 * Elasticsearch Search Engine Implementation for SLiMS
 *
 * Talks to Elasticsearch 7.x / 8.x through its REST API, so no client
 * library is needed.
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
use Throwable;

class ElasticSearchEngine extends RemoteIndexEngine
{
    const CONFIG_KEY = 'elasticsearch_config';
    const LABEL = 'Elasticsearch';
    const DEFAULT_HOST = 'http://localhost:9200';

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
}
