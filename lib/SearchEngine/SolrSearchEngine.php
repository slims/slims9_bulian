<?php
/**
 * Apache Solr Search Engine Implementation for SLiMS
 *
 * Talks to Solr through its REST API (JSON Request API and Schema API,
 * Solr 7 and newer). The core or collection has to exist already, for
 * example: bin/solr create -c slims
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

use Throwable;

class SolrSearchEngine extends RemoteIndexEngine
{
    const CONFIG_KEY = 'solr_search_config';
    const LABEL = 'Solr';
    const DEFAULT_HOST = 'http://localhost:8983';

    /**
     * Fields this engine needs in the Solr schema: name => [type, multiValued].
     * The types are the ones of the default Solr configset.
     */
    public static function schemaFields(): array
    {
        return [
            'title' => ['text_general', false],
            'title_sort' => ['string', false],
            'author' => ['text_general', true],
            'topic' => ['text_general', true],
            'notes' => ['text_general', false],
            'publisher' => ['text_general', false],
            'isbn_issn' => ['string', false],
            'gmd' => ['string', false],
            'language' => ['string', false],
            'publish_year' => ['string', false],
            'classification' => ['string', false],
            'call_number' => ['string', false],
            'item_code' => ['string', true],
            'coll_type' => ['string', true],
            'location' => ['string', true],
            'item_status' => ['string', true],
            'opac_hide' => ['pint', false],
            // kept as text: they are only used for sorting, and "Y-m-d H:i:s" sorts correctly
            'input_date' => ['string', false],
            'last_update' => ['string', false]
        ];
    }

    protected function core(): string
    {
        return '/solr/' . $this->index;
    }

    /*
     |--------------------------------------------------------------------------
     | Index management
     |--------------------------------------------------------------------------
     */

    public function ping(): string
    {
        $this->call('GET', $this->core() . '/admin/ping?wt=json');

        // the version is nice to have; the info API may be closed for this login
        try {
            [$status, $info] = $this->request('GET', '/solr/admin/info/system?wt=json');
            return $status === 200 ? (string)($info['lucene']['solr-spec-version'] ?? '') : '';
        } catch (Throwable $e) {
            return '';
        }
    }

    /**
     * Schema fields that are not defined in the core yet
     */
    protected function missingFields(): array
    {
        $data = $this->call('GET', $this->core() . '/schema/fields?wt=json');
        $existing = array_column($data['fields'] ?? [], 'name');
        return array_diff_key(self::schemaFields(), array_flip($existing));
    }

    public function indexExists(): bool
    {
        return count($this->missingFields()) === 0;
    }

    /**
     * The core itself is made by the server administrator; here the fields
     * are added to its schema
     */
    public function createIndex(): void
    {
        $fields = [];
        foreach ($this->missingFields() as $name => [$type, $multi]) {
            $fields[] = ['name' => $name, 'type' => $type, 'indexed' => true, 'stored' => false, 'multiValued' => $multi];
        }
        if ($fields) $this->call('POST', $this->core() . '/schema', ['add-field' => $fields]);
    }

    public function deleteIndex(): void
    {
        $this->call('POST', $this->core() . '/update?commit=true', ['delete' => ['query' => '*:*']]);
    }

    public function countDocuments(): int
    {
        // make everything sent so far searchable
        $this->call('POST', $this->core() . '/update?commit=true', []);
        $data = $this->call('POST', $this->core() . '/query', ['query' => '*:*', 'limit' => 0]);
        return (int)($data['response']['numFound'] ?? 0);
    }

    /**
     * Turn the output of api::biblio_load() into a flat Solr document
     */
    public static function toSolrDocument(array $biblio): array
    {
        $biblio = self::prepareDocument($biblio);
        $list = function (array $rows, string $key) {
            $values = array_map('strval', array_column($rows, $key));
            return array_values(array_unique(array_filter($values, fn($value) => trim($value) !== '')));
        };

        $document = [
            'id' => (string)$biblio['biblio_id'],
            'title' => (string)($biblio['title'] ?? ''),
            'title_sort' => mb_strtolower(mb_substr((string)($biblio['title'] ?? ''), 0, 200)),
            'author' => $list($biblio['authors'], 'author_name'),
            'topic' => $list($biblio['subjects'], 'topic'),
            'notes' => (string)($biblio['notes'] ?? ''),
            'publisher' => (string)($biblio['publisher_name'] ?? ''),
            'isbn_issn' => (string)($biblio['isbn_issn'] ?? ''),
            'gmd' => (string)($biblio['gmd_name'] ?? ''),
            'language' => (string)($biblio['language_name'] ?? ''),
            'publish_year' => (string)($biblio['publish_year'] ?? ''),
            'classification' => (string)($biblio['classification'] ?? ''),
            'call_number' => (string)($biblio['call_number'] ?? ''),
            'item_code' => $list($biblio['items'], 'item_code'),
            'coll_type' => $list($biblio['items'], 'coll_type_name'),
            'location' => $list($biblio['items'], 'location_name'),
            'item_status' => $list($biblio['items'], 'item_status'),
            'input_date' => (string)($biblio['input_date'] ?? ''),
            'last_update' => (string)($biblio['last_update'] ?? '')
        ];

        // empty values are left out
        $document = array_filter($document, fn($value) => $value !== '' && $value !== []);
        $document['opac_hide'] = $biblio['opac_hide'];

        return $document;
    }

    /**
     * Solr takes the request as a whole: one bad document refuses all of
     * them, which is reported as an exception
     */
    public function indexDocuments(array $biblios): int
    {
        $documents = [];
        foreach ($biblios as $biblio) {
            $document = self::toSolrDocument($biblio);
            if ((int)$document['id'] > 0) $documents[] = $document;
        }
        if ($documents) $this->call('POST', $this->core() . '/update?commitWithin=1000', $documents);
        return 0;
    }

    public function deleteDocument(int $biblio_id): void
    {
        $this->call('POST', $this->core() . '/update?commitWithin=1000', ['delete' => ['id' => (string)$biblio_id]]);
    }

    /*
     |--------------------------------------------------------------------------
     | Searching
     |--------------------------------------------------------------------------
     */

    /**
     * Escape a value used as a single term (also keeps spaces in the term)
     */
    protected static function escape(string $value): string
    {
        return (string)preg_replace('/([+\-&|!(){}\[\]^"~*?:\\\\\/\s])/u', '\\\\$1', $value);
    }

    /**
     * A value between double quotes
     */
    protected static function quote(string $value): string
    {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
    }

    /**
     * Full text clause: every word must be there, "quoted words" as a phrase
     */
    protected function text(string $field, string $query, bool $phrase): ?string
    {
        $parts = [];
        if ($phrase) {
            $query = trim(str_replace('"', '', $query));
            if ($query !== '') $parts[] = self::quote($query);
        } else if (preg_match_all('/"([^"]+)"|(\S+)/u', $query, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (($match[1] ?? '') !== '') {
                    $parts[] = self::quote($match[1]);
                    continue;
                }
                $word = trim($match[2] ?? '', '"');
                if ($word === '') continue;
                // these would be read as operators
                $parts[] = in_array($word, ['AND', 'OR', 'NOT'], true) ? self::quote($word) : self::escape($word);
            }
        }

        return $parts ? $field . ':(' . implode(' AND ', $parts) . ')' : null;
    }

    /**
     * One search token into one Solr clause
     */
    protected function clause(array $token): ?string
    {
        $field = $token['f'];
        // boolean markers of the SQL engines have no meaning here
        $query = trim((string)preg_replace('/(^|\s)[+*\-]+/', '$1', (string)($token['q'] ?? '')));
        if ($query === '') return null;

        $phrase = isset($token['is_phrase']) || ($token['b'] ?? '') === '++';
        $terms = function (string $solr_field, ?array $names) use ($query) {
            $names = $names ?? [$query];
            // a list of IDs that matched nothing must find nothing
            if (!$names) return '(*:* -*:*)';
            return $solr_field . ':(' . implode(' OR ', array_map([self::class, 'quote'], array_map('strval', $names))) . ')';
        };

        switch ($field) {
            case 'title':
                return $this->text('title', $query, $phrase);
            case 'author':
                return $this->text('author', $query, $phrase);
            case 'subject':
                return $this->text('topic', $query, $phrase);
            case 'notes':
                return $this->text('notes', $query, $phrase);
            case 'publisher':
                return $this->text('publisher', $query, $phrase);
            case 'isbn':
                return 'isbn_issn:' . self::quote($query);
            case 'publishyear':
                return 'publish_year:' . self::quote($query);
            case 'itemcode':
                return 'item_code:' . self::quote($query);
            case 'callnumber':
                return 'call_number:' . self::escape($query) . '*';
            case 'class':
                return 'classification:' . self::escape($query) . '*';
            case 'years':
                $years = array_map('trim', explode(';', $query));
                if (count($years) !== 2 || !ctype_digit($years[0]) || !ctype_digit($years[1])) return null;
                return 'publish_year:[' . $years[0] . ' TO ' . $years[1] . ']';
            case 'gmd':
                return $terms('gmd', $this->namesFromIds($query, 'mst_gmd', 'gmd_id', 'gmd_name'));
            case 'lang':
                return $terms('language', $this->namesFromIds($query, 'mst_language', 'language_id', 'language_name'));
            case 'colltype':
                return $terms('coll_type', $this->namesFromIds($query, 'mst_coll_type', 'coll_type_id', 'coll_type_name'));
            case 'location':
                return $terms('location', $this->namesFromIds($query, 'mst_location', 'location_id', 'location_name'));
            case 'availability':
                return 'item_status:"Available"';
            // not searchable in this index
            case 'attachment':
            case 'sort':
            case 'opengroup':
            case 'closegroup':
                return null;
            default:
                $any = array_filter(array_map(fn($name) => $this->text($name, $query, $phrase), ['title', 'author', 'topic', 'notes']));
                return $any ? '(' . implode(' OR ', $any) . ')' : null;
        }
    }

    /**
     * Build the search request body from the criteria and the filter
     */
    protected function buildQuery(): array
    {
        $must = [];
        $must_not = [];
        $alternatives = [];

        // "a OR b": both sides are alternatives, so a term is optional when
        // the link before it or the link after it is an OR
        $terms = $this->linkedTerms($this->criteria, $this->stop_words, fn($token) => $this->clause($token));
        foreach ($terms as $i => [$clause, $link]) {
            if ($link === '-') {
                $must_not[] = '-(' . $clause . ')';
            } else if ($link === '*' || ($terms[$i + 1][1] ?? '') === '*') {
                $alternatives[] = '(' . $clause . ')';
            } else {
                $must[] = '+(' . $clause . ')';
            }
        }
        if ($alternatives) $must[] = '+(' . implode(' OR ', $alternatives) . ')';
        $has_query = !empty($terms);
        // a query made of exclusions only needs something to exclude from
        if (!$must) $must[] = '+*:*';

        // hidden records never show up in OPAC
        $filters = ['-opac_hide:1'];
        if (isset($this->filter)) {
            foreach ($this->linkedTerms($this->filter, [], fn($token) => $this->clause($token)) as [$clause, $link]) {
                $filters[] = ($link === '-' ? '-' : '') . '(' . $clause . ')';
            }
        }

        $sort_key = isset($this->filter) ? (string)$this->filter->sort : '';
        switch ($sort_key) {
            case 'recently-added':
                $sort = 'input_date desc';
                break;
            case 'publish-year-newest':
                $sort = 'publish_year desc';
                break;
            case 'publish-year-oldest':
                $sort = 'publish_year asc';
                break;
            case 'title-asc':
                $sort = 'title_sort asc';
                break;
            case 'title-desc':
                $sort = 'title_sort desc';
                break;
            default:
                // a real search is ordered by relevance first
                $sort = ($has_query ? 'score desc, ' : '') . 'last_update desc';
        }

        return [
            'query' => implode(' ', array_merge($must, $must_not)),
            'filter' => $filters,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'fields' => 'id',
            'sort' => $sort,
            'params' => ['defType' => 'lucene', 'q.op' => 'OR']
        ];
    }

    /**
     * Get documents: matching IDs from Solr, records from the database
     */
    public function getDocuments()
    {
        // start time for benchmarking
        $start = microtime(true);

        try {
            $body = $this->buildQuery();
            $this->dump($body);

            $result = $this->call('POST', $this->core() . '/query', $body);
            $this->num_rows = (int)($result['response']['numFound'] ?? 0);

            $biblio_ids = [];
            foreach ($result['response']['docs'] ?? [] as $doc) {
                $biblio_ids[] = (int)$doc['id'];
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
