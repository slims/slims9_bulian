<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 10/09/20 21.27
 * @File name           : SubjectController.php
 */

require_once 'Controller.php';

class SubjectController extends Controller {

    protected $sysconf;

    /**
     * @var mysqli
     */
    protected $db;

    function __construct($sysconf, $obj_db)
    {
        $this->sysconf = $sysconf;
        $this->db = $obj_db;
    }

    function getPopular() {
        $limit = 5;
        $year = date('Y');
        $cache_name = 'subject_popular';
        $json = Cache::get($cache_name);
        if (!is_null($json) && $json !== 'null') return parent::withJson($json);

        $sql = "SELECT mt.topic, COUNT(*) AS total
          FROM loan AS l
          LEFT JOIN item AS i ON l.item_code=i.item_code
          LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
          LEFT JOIN biblio_topic AS bt ON i.biblio_id=bt.biblio_id
          LEFT JOIN mst_topic AS mt ON bt.topic_id=mt.topic_id
          WHERE mt.topic IS NOT NULL AND YEAR(l.loan_date) = '%s'
          GROUP BY bt.topic_id
          ORDER BY total DESC
          LIMIT %d";

        $query = $this->db->query(sprintf($sql, $year, $limit));
        $return = array();
        while ($data = $query->fetch_row()) {
            $return[] = $data[0];
        }
        if ($query->num_rows < $limit) {
            $need = $limit - $query->num_rows;
            if ($need > 0) {
                $return = array_merge($return, $this->getLatest($need));
            }
        }

        Cache::set($cache_name, json_encode($return));
    }

    function getLatest($limit = 5) {

        $sql = "SELECT topic FROM mst_topic mt
                INNER JOIN (SELECT topic_id FROM biblio_topic bt 
                INNER JOIN (SELECT biblio_id FROM biblio ORDER BY last_update DESC LIMIT 10) b ON bt.biblio_id=b.biblio_id 
                GROUP BY topic_id) tt ON tt.topic_id=mt.topic_id 
                LIMIT {$limit};";

        $query = $this->db->query($sql);
        $return = array();
        while ($data = $query->fetch_row()) {
            $return[] = $data[0];
        }

        parent::withJson($return);
        return $return;
    }
}