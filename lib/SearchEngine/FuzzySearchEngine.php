<?php

/**
 * Fuzzy Search Engine for SLiMS
 * 
 * This search engine implements fuzzy matching algorithms to provide
 * typo-tolerant search capabilities using the index_words and index_documents tables.
 * 
 * Features:
 * - Levenshtein distance for typo tolerance
 * - Phonetic matching (Soundex/Metaphone)
 * - Relevance scoring based on word frequency and hit counts
 * - Support for multiple search terms with boolean operators
 * 
 * @author SLiMS Development Team
 */

namespace SLiMS\SearchEngine;

use SLiMS\DB;
use PDO;

class FuzzySearchEngine extends Contract
{
    /**
     * Maximum Levenshtein distance for fuzzy matching
     * Lower values = stricter matching
     */
    protected int $maxDistance = 2;

    /**
     * Minimum word length to apply fuzzy matching
     */
    protected int $minWordLength = 3;

    /**
     * Enable phonetic matching (Soundex/Metaphone)
     */
    protected bool $usePhonetic = true;

    /**
     * Searchable locations in documents
     */
    protected array $searchLocations = ['title', 'author', 'subject', 'isbn', 'publisher', 'notes'];

    /**
     * Search results with relevance scores
     */
    protected array $scoredResults = [];

    /**
     * Set maximum Levenshtein distance
     */
    public function setMaxDistance(int $distance): self
    {
        $this->maxDistance = $distance;
        return $this;
    }

    /**
     * Enable or disable phonetic matching
     */
    public function setPhoneticMatching(bool $enabled): self
    {
        $this->usePhonetic = $enabled;
        return $this;
    }

    /**
     * Main method to retrieve documents using fuzzy search
     */
    public function getDocuments()
    {
        $start = microtime(true);

        try {
            // Extract search terms from criteria
            $searchTerms = $this->extractSearchTerms();

            if (empty($searchTerms)) {
                $this->num_rows = 0;
                $this->documents = [];
                return;
            }

            // Find matching words using fuzzy algorithms
            $matchedWords = $this->findFuzzyMatches($searchTerms);

            if (empty($matchedWords)) {
                $this->num_rows = 0;
                $this->documents = [];
                return;
            }

            // Get documents containing the matched words
            $documentScores = $this->getDocumentsByWords($matchedWords);

            // Retrieve full document details
            $this->retrieveDocumentDetails($documentScores);
        } catch (\PDOException | \Exception $e) {
            $this->error = $e->getMessage();
            $this->num_rows = 0;
            $this->documents = [];
        }

        $end = microtime(true);
        $this->query_time = round($end - $start, 5);
    }

    /**
     * Extract search terms from criteria
     */
    protected function extractSearchTerms(): array
    {
        $terms = [];

        foreach ($this->criteria->toCQLToken($this->stop_words) as $token) {
            $field = $token['f'];

            if ($field === 'cql_end') break;
            if ($field === 'boolean') continue;

            $query = $token['q'] ?? null;
            if (empty($query)) continue;

            // Split query into individual words
            $words = preg_split('/\s+/', trim($query));
            foreach ($words as $word) {
                $word = strtolower(trim($word));
                if (strlen($word) >= $this->minWordLength && !in_array($word, $this->stop_words)) {
                    $terms[] = [
                        'word' => $word,
                        'field' => $field,
                        'boolean' => $token['b'] ?? '+'
                    ];
                }
            }
        }

        return $terms;
    }

    /**
     * Find fuzzy matches for search terms in index_words table
     */
    protected function findFuzzyMatches(array $searchTerms): array
    {
        $db = DB::getInstance();
        $matchedWords = [];

        foreach ($searchTerms as $term) {
            $word = $term['word'];
            $field = $term['field'];

            // Strategy 1: Exact match (highest priority)
            $exact = $this->findExactMatch($db, $word);
            if (!empty($exact)) {
                $matchedWords[] = array_merge($exact, [
                    'search_term' => $word,
                    'match_type' => 'exact',
                    'score' => 100,
                    'field' => $field,
                    'boolean' => $term['boolean']
                ]);
                continue;
            }

            // Strategy 2: Prefix match (e.g., "comp" matches "computer")
            $prefix = $this->findPrefixMatch($db, $word);
            if (!empty($prefix)) {
                foreach ($prefix as $match) {
                    $matchedWords[] = array_merge($match, [
                        'search_term' => $word,
                        'match_type' => 'prefix',
                        'score' => 80,
                        'field' => $field,
                        'boolean' => $term['boolean']
                    ]);
                }
                continue;
            }

            // Strategy 3: Levenshtein distance (typo tolerance)
            $fuzzy = $this->findLevenshteinMatches($db, $word);
            if (!empty($fuzzy)) {
                foreach ($fuzzy as $match) {
                    $matchedWords[] = array_merge($match, [
                        'search_term' => $word,
                        'match_type' => 'fuzzy',
                        'field' => $field,
                        'boolean' => $term['boolean']
                    ]);
                }
                continue;
            }

            // Strategy 4: Phonetic matching (sounds like)
            if ($this->usePhonetic) {
                $phonetic = $this->findPhoneticMatches($db, $word);
                if (!empty($phonetic)) {
                    foreach ($phonetic as $match) {
                        $matchedWords[] = array_merge($match, [
                            'search_term' => $word,
                            'match_type' => 'phonetic',
                            'score' => 60,
                            'field' => $field,
                            'boolean' => $term['boolean']
                        ]);
                    }
                }
            }
        }

        return $matchedWords;
    }

    /**
     * Find exact word match
     */
    protected function findExactMatch(PDO $db, string $word): ?array
    {
        $stmt = $db->prepare("SELECT id as word_id, word, num_hits, doc_hits FROM index_words WHERE word = ? LIMIT 1");
        $stmt->execute([$word]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find prefix matches
     */
    protected function findPrefixMatch(PDO $db, string $word): array
    {
        $stmt = $db->prepare("SELECT id as word_id, word, num_hits, doc_hits FROM index_words WHERE word LIKE ? ORDER BY num_hits DESC LIMIT 10");
        $stmt->execute([$word . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find matches using Levenshtein distance
     */
    protected function findLevenshteinMatches(PDO $db, string $word): array
    {
        // Get candidate words (similar length)
        $minLen = strlen($word) - $this->maxDistance;
        $maxLen = strlen($word) + $this->maxDistance;

        $stmt = $db->prepare("
            SELECT id as word_id, word, num_hits, doc_hits 
            FROM index_words 
            WHERE CHAR_LENGTH(word) BETWEEN ? AND ?
            AND word LIKE ?
            ORDER BY num_hits DESC 
            LIMIT 100
        ");
        $stmt->execute([$minLen, $maxLen, $word[0] . '%']); // First char optimization

        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $matches = [];

        foreach ($candidates as $candidate) {
            $distance = levenshtein($word, $candidate['word']);
            if ($distance <= $this->maxDistance) {
                // Calculate score based on distance (closer = higher score)
                $score = max(0, 100 - ($distance * 20));
                $matches[] = array_merge($candidate, ['score' => $score, 'distance' => $distance]);
            }
        }

        // Sort by score descending
        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, 10);
    }

    /**
     * Find phonetic matches using Soundex and Metaphone
     */
    protected function findPhoneticMatches(PDO $db, string $word): array
    {
        $soundex = soundex($word);
        $metaphone = metaphone($word);

        // Get all words and filter by phonetic similarity
        $stmt = $db->prepare("
            SELECT id as word_id, word, num_hits, doc_hits 
            FROM index_words 
            WHERE CHAR_LENGTH(word) BETWEEN ? AND ?
            ORDER BY num_hits DESC 
            LIMIT 200
        ");
        $minLen = max(3, strlen($word) - 3);
        $maxLen = strlen($word) + 3;
        $stmt->execute([$minLen, $maxLen]);

        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $matches = [];

        foreach ($candidates as $candidate) {
            $candSoundex = soundex($candidate['word']);
            $candMetaphone = metaphone($candidate['word']);

            if ($candSoundex === $soundex || $candMetaphone === $metaphone) {
                $matches[] = $candidate;
            }
        }

        return array_slice($matches, 0, 10);
    }

    /**
     * Get documents by matched words
     */
    protected function getDocumentsByWords(array $matchedWords): array
    {
        if (empty($matchedWords)) return [];

        $db = DB::getInstance();
        $documentScores = [];

        // Group words by field for location filtering
        foreach ($matchedWords as $match) {
            $wordId = $match['word_id'];
            $field = $match['field'];
            $location = $this->mapFieldToLocation($field);

            // Get documents containing this word at the specified location
            $stmt = $db->prepare("
                SELECT document_id, location, hit_count 
                FROM index_documents 
                WHERE word_id = ? AND location = ?
            ");
            $stmt->execute([$wordId, $location]);

            $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($docs as $doc) {
                $docId = $doc['document_id'];

                // Calculate relevance score
                $relevance = $this->calculateRelevance($match, $doc);

                if (!isset($documentScores[$docId])) {
                    $documentScores[$docId] = 0;
                }

                // Accumulate scores
                $documentScores[$docId] += $relevance;
            }
        }

        // Sort by relevance score
        arsort($documentScores);

        return $documentScores;
    }

    /**
     * Map search field to document location
     */
    protected function mapFieldToLocation(string $field): string
    {
        $mapping = [
            'title' => 'title',
            'author' => 'author',
            'subject' => 'subject',
            'isbn' => 'isbn',
            'publisher' => 'publisher',
            'notes' => 'notes'
        ];

        return $mapping[$field] ?? 'title';
    }

    /**
     * Calculate relevance score for a document
     */
    protected function calculateRelevance(array $match, array $doc): float
    {
        $score = 0;

        // Base score from match quality
        $score += $match['score'];

        // Boost by hit count in document
        $score += $doc['hit_count'] * 10;

        // Boost by global word frequency
        $score += log($match['num_hits'] + 1) * 5;

        // Boost by document frequency
        $score += log($match['doc_hits'] + 1) * 5;

        return $score;
    }

    /**
     * Retrieve full document details from biblio table
     */
    protected function retrieveDocumentDetails(array $documentScores): void
    {
        if (empty($documentScores)) {
            $this->num_rows = 0;
            $this->documents = [];
            return;
        }

        $this->num_rows = count($documentScores);

        // Apply pagination
        $documentIds = array_keys($documentScores);
        $paginatedIds = array_slice($documentIds, $this->offset, $this->limit);

        if (empty($paginatedIds)) {
            $this->documents = [];
            return;
        }

        $db = DB::getInstance();
        $placeholders = implode(',', array_fill(0, count($paginatedIds), '?'));

        // Build query similar to DefaultEngine
        $sql = "
            SELECT 
                b.biblio_id, b.title, b.image, b.isbn_issn, b.publish_year,
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
            LEFT JOIN biblio_author AS ba ON ba.biblio_id = b.biblio_id
            LEFT JOIN mst_author AS ma ON ba.author_id = ma.author_id
            LEFT JOIN biblio_topic AS bt ON bt.biblio_id = b.biblio_id
            LEFT JOIN mst_topic AS mt ON bt.topic_id = mt.topic_id
            WHERE b.biblio_id IN ($placeholders) AND b.opac_hide = 0
            GROUP BY b.biblio_id
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($paginatedIds);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sort results by relevance score
        $sortedResults = [];
        foreach ($paginatedIds as $id) {
            foreach ($results as $result) {
                if ($result['biblio_id'] == $id) {
                    $result['relevance_score'] = $documentScores[$id];
                    $sortedResults[] = $result;
                    break;
                }
            }
        }

        $this->documents = $sortedResults;
    }

    /**
     * Dump debug information
     */
    public function dump(array $query)
    {
        if (!isset($_GET['resultXML']) && !isset($_GET['rss'])) {
            debugBox(content: function () use ($query) {
                debug('Fuzzy Search Engine Debug 🔎 🪲', [
                    'Engine Type ⚙️:' => get_class($this),
                    'Max Distance:' => $this->maxDistance,
                    'Phonetic Matching:' => $this->usePhonetic ? 'Enabled' : 'Disabled'
                ], [
                    'Search Info ⚙️:' => $query
                ]);
            });
        }
    }

    /**
     * Convert results to array format
     */
    public function toArray()
    {
        return [
            'total_rows' => $this->num_rows,
            'page' => $this->page,
            'limit' => $this->limit,
            'query_time' => $this->query_time,
            'documents' => $this->documents
        ];
    }

    /**
     * Convert results to JSON format
     */
    public function toJSON()
    {
        $jsonld = [
            '@context' => 'http://schema.org',
            '@type' => 'SearchResultsPage',
            'total_rows' => $this->num_rows,
            'page' => $this->page,
            'records_each_page' => $this->limit,
            'query_time' => $this->query_time,
            '@graph' => [],
        ];

        foreach ($this->documents as $document) {
            $record = [
                '@type' => 'Book',
                '@id' => 'http://' . $_SERVER['SERVER_NAME'] . SWB . 'index.php?p=show_detail&id=' . $document['biblio_id'],
                'name' => trim($document['title']),
                'author' => $document['author'],
                'isbn' => $document['isbn_issn'],
                'publisher' => $document['publisher'],
                'datePublished' => $document['publish_year'],
                'relevanceScore' => $document['relevance_score'] ?? 0
            ];

            if (!empty($document['image'])) {
                $record['image'] = urlencode($document['image']);
            }

            $jsonld['@graph'][] = $record;
        }

        return json_encode($jsonld);
    }

    /**
     * Convert results to HTML format
     */
    public function toHTML()
    {
        $buffer = '';
        $path = SB . config('template.dir', 'template') . DS;
        $path .= config('template.theme', 'default') . DS . 'biblio_list_template.php';

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

    /**
     * Convert results to XML format (MODS)
     */
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

        $xml->startElementNS('slims', 'resultInfo', null);
        $xml->startElementNS('slims', 'searchEngine', null);
        $xml->text('FuzzySearch');
        $xml->endElement();
        $xml->startElementNS('slims', 'modsResultNum', null);
        $xml->text($this->num_rows);
        $xml->endElement();
        $xml->startElementNS('slims', 'modsResultPage', null);
        $xml->text($this->page);
        $xml->endElement();
        $xml->startElementNS('slims', 'queryTime', null);
        $xml->text($this->query_time);
        $xml->endElement();
        $xml->endElement(); // resultInfo

        foreach ($this->documents as $document) {
            $xml->startElement('mods');
            $xml->writeAttribute('version', '3.3');
            $xml->writeAttribute('ID', $document['biblio_id']);

            $xml->startElement('titleInfo');
            $xml->startElement('title');
            $xml->text($document['title']);
            $xml->endElement();
            $xml->endElement();

            if (!empty($document['relevance_score'])) {
                $xml->startElementNS('slims', 'relevanceScore', null);
                $xml->text(round($document['relevance_score'], 2));
                $xml->endElement();
            }

            $xml->endElement(); // mods
        }

        $xml->endElement(); // modsCollection
        return $xml->flush();
    }

    /**
     * Convert results to RSS format
     */
    public function toRSS()
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        header('Content-Type: text/xml; charset=utf-8');

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);

        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');

        $xml->startElement('channel');
        $xml->startElement('title');
        $xml->writeCData('Fuzzy Search Results');
        $xml->endElement();

        foreach ($this->documents as $document) {
            $xml->startElement('item');

            $xml->startElement('title');
            $xml->writeCData($document['title']);
            $xml->endElement();

            $xml->startElement('link');
            $link = 'http://' . $_SERVER['SERVER_NAME'] . SWB . '/index.php?p=show_detail&id=' . $document['biblio_id'];
            $xml->writeCData($link);
            $xml->endElement();

            $xml->startElement('description');
            $desc = 'Author: ' . $document['author'] . ' | ISBN: ' . $document['isbn_issn'];
            $xml->writeCData($desc);
            $xml->endElement();

            $xml->endElement(); // item
        }

        $xml->endElement(); // channel
        $xml->endElement(); // rss

        echo $xml->flush();
        exit;
    }
}
