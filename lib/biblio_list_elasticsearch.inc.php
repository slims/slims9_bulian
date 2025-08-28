<?php
/**
 * @author              : Waris Agung Widodo
 * @Date                : 25/12/18 15.00
 * @Last Modified by    : ido
 * @Last Modified time  : 25/12/18 15.00
 *
 * Copyright (C) 2017  Waris Agung Widodo (ido.alit@gmail.com)
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
  die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
  die("can not access this file directly");
}

class biblio_list extends biblio_list_model
{
  private $client;

  public function __construct($obj_db, int $int_num_show = 20)
  {
    global $sysconf;

    parent::__construct($obj_db, $int_num_show);
    $this->client = Elasticsearch\ClientBuilder::create()
      ->setHosts($sysconf['index']['engine']['es_opts']['hosts'])
      ->build();
  }

  public function setSQLcriteria($str_criteria)
  {
    global $sysconf;

    // get page number from http get var
    if (!isset($_GET['page']) OR $_GET['page'] < 1){ $_page = 1; } else {
      $_page = (integer)$_GET['page'];
    }
    $this->current_page = $_page;

    // count the row offset
    if ($_page <= 1) { $_offset = 0; } else {
      $_offset = ($_page*$this->num2show) - $this->num2show;
    }

    $params = [
      'index' => $sysconf['index']['engine']['es_opts']['index'],
      'type' => 'bibliography',
      'from' => $_offset,
      'size' => $this->num2show,
      'body' => [
        'query' => []
      ]
    ];

    if (!$str_criteria) {
      $params['body']['query'] = [
        'match_all' => new \stdClass()
      ];
    } else {
      // parse query
      $this->orig_query = $str_criteria;
      $queries = simbio_tokenizeCQL($str_criteria, $this->searchable_fields, $this->stop_words, $this->queries_word_num_allowed);
      // echo '<pre>'; print_r($queries); echo '</pre>';
      if (count($queries) < 1) {
        return null;
      }

      $params['body']['query'] = [
        'bool' => []
      ];

      $bool = isset($_GET['searchtype']) && $_GET['searchtype'] === 'advance' ? 'must' : 'should';

      foreach ($queries as $query) {
        // field
        $_field = $query['f'];
        $_q = isset($query['q']) ? trim($query['q']) : '';

        //  break the loop if we meet `cql_end` field
        if ($_field == 'cql_end') { continue; }

        // if field is boolean
        if ($_field == 'boolean') {
          if ($query['b'] == '*') {
            $bool = 'should';
          } else if ($query['b'] == '-') {
            $bool = 'must_not';
          } else {
            $bool = 'must';
          }
          continue;
        }

        if ($_field === 'author') {
          $params['body']['query']['bool'][$bool][] = ['nested' => [
            'path' => 'authors',
            'query' => [
              'bool' => [
                'should' => [
                  ['match' => ['authors.author_name' => $_q]]
                ]
              ]
            ]
          ]];
        } else if ($_field === 'subject') {
          $params['body']['query']['bool'][$bool][] = ['nested' => [
            'path' => 'subjects',
            'query' => [
              'bool' => [
                'should' => [
                  ['match' => ['subjects.topic' => $_q]]
                ]
              ]
            ]
          ]];
        } else if ($_field === 'colltype') {
          $params['body']['query']['bool'][$bool][] = ['nested' => [
            'path' => 'items',
            'query' => [
              'bool' => [
                'should' => [
                  ['match' => ['items.coll_type_name' => $_q]]
                ]
              ]
            ]
          ]];
        } else if ($_field === 'location') {
          $params['body']['query']['bool'][$bool][] = ['nested' => [
            'path' => 'items',
            'query' => [
              'bool' => [
                'should' => [
                  ['match' => ['items.location_name' => $_q]]
                ]
              ]
            ]
          ]];
        } else {
          switch ($_field) {
            case 'isbn':
              $_fieldTmp = 'isbn_issn';
              break;
            case 'gmd':
              $_fieldTmp = 'gmd_name';
              break;
            default:
              $_fieldTmp = $_field;
              break;
          }
          $params['body']['query']['bool'][$bool][] = ['match' => [$_fieldTmp => $_q]];
        }

      }
    }

    if ($sysconf['enable_search_clustering']) {
      $params['body']['aggs'] = [
        'authors' => [
          'nested' => [
            'path' => 'authors'
          ],
          'aggs' => [
            'authors' => ['terms' => ['field' => 'authors.author_name', 'size' => 10]]
          ]
        ],
        'topics' => [
          'nested' => [
            'path' => 'subjects'
          ],
          'aggs' => [
            'topics' => ['terms' => ['field' => 'subjects.topic', 'size' => 10]]
          ]
        ],
        'gmd' => ['terms' => ['field' => 'gmd_name', 'size' => 10]],
      ];
    }

    $this->criteria = $params;
    // echo '<pre>'; print_r($this->criteria); echo '</pre>';
    return $this->criteria;
  }

  public function getDocumentList($bool_return_output = true)
  {
    // start time
    $_start = function_exists('microtime')?microtime(true):time();

    // result
    $this->resultset = $this->client->search($this->criteria);
    $this->num_rows = $this->resultset['hits']['total'];

    // end time
    $_end = function_exists('microtime')?microtime(true):time();
    $this->query_time = round($_end-$_start, 5);

    // echo '<pre>'; print_r($this->resultset['aggregations']); echo '</pre>';
    if ($bool_return_output) {
      // return the html result
       return $this->makeOutput();
    }
  }

  public function getClustering() {
    global $sysconf;
    $aggs = $sysconf['enable_search_clustering'] ? $this->resultset['aggregations'] : [];
    $buffer = '';
    foreach ($aggs as $k => $agg) {
      $title = '';
      $field = '';
      $array = array();
      switch ($k) {
        case 'topics':
          $title = 'Subject(s)';
          $field = 'subject';
          $array = $agg['topics']['buckets'];
          break;
        case 'authors':
          $title = 'Author(s)';
          $field = 'author';
          $array = $agg['authors']['buckets'];
          break;
        case 'gmd':
          $title = 'GMD';
          $field = 'gmd';
          $array = $agg['buckets'];
          break;
      }
      $buffer .= '<h3 class="cluster-title">'.__($title).'</h3>'."\n";
      $buffer .= '<ul class="cluster-list">'."\n";
      foreach ($array as $item) {
        $buffer .= '<li class="cluster-item"><a href="index.php?'.$field.'='.$item['key'].'&searchtype=advance&search=search">'.$item['key'].' <span class="cluster-item-count">'.$item['doc_count'].'</span></a></li>';
      }
      $buffer .= '</ul>';
    }

    return $buffer;
  }

  protected function makeOutput()
  {
    global $sysconf;
    // init the result buffer
    $_buffer = '';
    // keywords from last search
    $_keywords = '';
    if (!$this->resultset) {
      return '<div class="errorBox">Query error : '.$this->query_error.'</div>';
    }

    if (isset($_GET['keywords'])) {
      $_keywords = urlencode(trim(urldecode($_GET['keywords'])));
    }

    // include biblio list HTML template callback
    include SB.$sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/biblio_list_template.php';
    $n = 0;
    $settings = get_object_vars($this);
    $settings['keywords'] = $_keywords;
    foreach ($this->resultset['hits']['hits'] as $item) {
      $_buffer .= biblio_list_format($this->obj_db, $item['_source'], $n, $settings);
      $n++;
    }

    // paging
    if (($this->num_rows > $this->num2show)) {
      $_paging = '<div class="biblioPaging">'.simbio_paging::paging($this->num_rows, $this->num2show, 5).'</div>';
    } else {
      $_paging = '';
    }

    $_biblio_list = '';
    $_is_member_logged_in = utility::isMemberLogin() && $this->enable_mark;
    if ($_paging) {
      $_biblio_list .= $_paging;
    }
    if ($_is_member_logged_in) {
      $_submit = '<div class="biblioMarkFormAction"><input type="submit" name="markBiblio" value="'.__('Put marked selection into basket').'" /></div>';
      $_biblio_list .= '<form class="biblioMarkForm" method="post" action="index.php?p=member#biblioBasket">';
      $_biblio_list .= $_submit;
    }
    $_biblio_list .= $_buffer;
    if ($_is_member_logged_in) {
      $_biblio_list .= $_submit;
      $_biblio_list .= '</form>';
    }
    if ($_paging) {
      $_biblio_list .= $_paging;
    }

    return $_biblio_list;
  }


}