<?php
/**
 * @author              : Waris Agung Widodo
 * @Date                : 23/12/18 21.48
 * @Last Modified by    : ido
 * @Last Modified time  : 23/12/18 21.48
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

/* Biblio Index Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
require SIMBIO . 'simbio_DB/simbio_dbop.inc.php';
require MDLBS . 'system/biblio_indexer_es.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');
$error = array();
$info = array();

if (!$can_read) {
  die('<div class="errorBox">' . __('You don\'t have enough privileges to view this section') . '</div>');
}

$client = Elasticsearch\ClientBuilder::create()
  ->setHosts($sysconf['index']['engine']['es_opts']['hosts'])
  ->build();

/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
  if (!($can_read AND $can_write)) {
    die(json_encode(['status' => 'error', 'message' => __('You don\'t have enough privileges to view this section')]));
  }

  $params = ['index' => $sysconf['index']['engine']['es_opts']['index']];

  /* create index */
  if ($_GET['detail'] == 'create') {
    try {
      if (!$client->indices()->exists($params)) {
        include __DIR__ . './../../../indexing_engine/elasticsearch_config.conf.php';
        $client->indices()->create($params);
        $mapping = $client->indices()->getMapping(['index' => $sysconf['index']['engine']['es_opts']['index']]);
        echo json_encode(['status' => 'success', 'message' => 'Index created. READY.', 'mapping' => $mapping]);
      } else {
        echo json_encode(['status' => 'success', 'message' => 'READY.']);
      }
    } catch (\Elasticsearch\Common\Exceptions\NoNodesAvailableException $exception) {
      echo json_encode(['status' => 'danger', 'message' => $exception->getMessage() . '. Make sure ElasticSearch is running and accessible!']);
    } catch (Exception $e) {
      $message = json_decode($e->getMessage());
      echo json_encode(['status' => 'danger', 'message' => $message->error]);
    }
  }

  if ($_GET['detail'] == 'delete') {
    try {
      $response = $client->indices()->delete($params);
      if ($response['acknowledged']) {
        echo json_encode(['status' => 'success', 'message' => 'Index deleted']);
      } else {
        echo json_encode(['status' => 'danger', 'message' => 'Failed delete index']);
      }
    } catch (Exception $e) {
      $message = json_decode($e->getMessage());
      echo json_encode(['status' => 'danger', 'message' => $message->error]);
    }
  }

  /* biblios */
  if ($_GET['detail'] == 'biblios') {
    $rec_bib_q = $dbs->query('SELECT COUNT(*) FROM biblio');
    $rec_bib_d = $rec_bib_q->fetch_row();
    $bib_total = $rec_bib_d[0];
    echo json_encode(['status' => 'info', 'message' => 'Total data on biblio: ' . $bib_total . ' records.', 'biblios' => $bib_total]);
  }

  /* biblio indexed */
  if ($_GET['detail'] == 'indexed') {
    try {
      $info = $client->indices()->stats($params);
      echo json_encode(['status' => 'info', 'message' => 'Total indexed data: ' . $info['indices'][$params['index']]['primaries']['indexing']['index_total'] . ' records.', 'detail' => $info]);
    } catch (\Elasticsearch\Common\Exceptions\NoNodesAvailableException $exception) {
      echo json_encode(['status' => 'danger', 'message' => $exception->getMessage() . '. Make sure ElasticSearch is running and accessible!']);
    } catch (Exception $exception) {
      echo json_encode(['status' => 'danger', 'message' => $exception->getMessage()]);
    }
  }

  /* indexing data */
  if ($_GET['detail'] == 'indexing') {
      $start = (int)$_GET['start'];
      $end = (int)$_GET['end'];
      $query = $dbs->query('SELECT biblio_id FROM biblio LIMIT ' . $start . ', ' . ($end - $start));
      $r = array();
      if ($query) {
          while ($data = $query -> fetch_row()) {
              $b = api::biblio_load($dbs, $data[0]);
              unset($b['_id']);
              $p = [
                  'index' => $sysconf['index']['engine']['es_opts']['index'],
                  'type' => 'bibliography',
                  'id' => md5($data[0]),
                  'body' => $b
              ];
              $r[] = $client->index($p);
          }
      }
      echo json_encode(['status' => 'success', 'message' => __('Biblio from ' . $start . ' to ' . $end . ' indexed.'), 'detail' => $r]);
  }

  exit();
} else {
  ?>
    <div class="menuBox">
        <div class="menuBoxInner systemIcon">
            <div class="per_title">
                <h2><?php echo __('Bibliographic Index (ElasticSearch)'); ?></h2>
            </div>
            <div class="sub_section">.
                <div class="btn-group">
                    <button class="btn btn-default recreate-action"><i
                                class="glyphicon glyphicon-refresh"></i>&nbsp;<?php echo __('Re-create Index'); ?></button>
                    <button class="btn btn-danger empty-action"><i
                                class="glyphicon glyphicon-trash"></i>&nbsp;<?php echo __('Emptying Index'); ?></button>
                </div>
            </div>
            <div class="infoBox">Bibliographic Index will speed up catalog search</div>
        </div>
    </div>
  <?php
  echo '<div class="index-log-cat infoBox" style="background: #333333; color: #f0fafb; font-size: 10pt"></div>';
  ?>

    <script>
        const container = $('.index-log-cat')
        const uriCreate = '<?php echo MWB; ?>system/biblio_indexes_es.php?action=detail&detail=create'
        const uriDelete = '<?php echo MWB; ?>system/biblio_indexes_es.php?action=detail&detail=delete'
        const uriBiblios = '<?php echo MWB; ?>system/biblio_indexes_es.php?action=detail&detail=biblios'
        const uriIndexed = '<?php echo MWB; ?>system/biblio_indexes_es.php?action=detail&detail=indexed'
        const uriIndexing = '<?php echo MWB; ?>system/biblio_indexes_es.php?action=detail&detail=indexing'

        let doRequest = (url, callback) => {
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    console.log('DATA', data)
                    log(container, data.status, data.message)
                })
                .catch(err => log(container, 'danger', err.message))
                .then(() => {
                    if (typeof callback === "function") callback()
                })
        }

        $(document).ready(() => {
            log(container, 'info', 'initialize SLiMS ElasticSearch indexing engine')
            doRequest(uriCreate, () => doRequest(uriBiblios, doRequest(uriIndexed)))
        })

        let reCreateBtn = $('.recreate-action')
        reCreateBtn.click(e => {
            e.preventDefault()
            log(container, 'info', `${$(e.target).text()}`)
            log(container, 'info', `Trying delete index`)
            doRequest(uriDelete, () => doRequest(uriCreate, () => indexing()))
        })

        let indexing = () => {
            let total = 0, iterator = 0, interval = 100
            log(container, 'info', 'Preparing data')
            fetch(uriBiblios)
                .then(res => res.json())
                .then(data => {
                    total = data.biblios
                    log(container, data.status, data.message)
                })
                .catch(err => log(container, 'danger', err.message))
                .then(() => {
                    iterator = Math.ceil(total / interval)
                    let i = 0
                    let a = () => {
                        let start = i * interval
                        let end = () => {
                            let endTmp = (i + 1) * interval
                            if (endTmp > total) return total
                            return endTmp
                        }
                        log(container, 'info',  `Indexing biblio from ${start} to ${end()}.`)
                        doRequest(`${uriIndexing}&start=${start}&end=${end()}`, () => {
                            i++
                            if (i < iterator) a()
                            if (i >= iterator) doRequest(uriIndexed)
                        })
                    }
                    a()
                })
        }

        let emptyActionBtn = $('.empty-action')
        emptyActionBtn.click(e => {
            e.preventDefault()
            doRequest(uriDelete)
        })
    </script>

  <?php
}