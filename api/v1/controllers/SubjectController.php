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

        $sql = "SELECT mt.topic, COUNT(*) AS total
          FROM loan AS l
          LEFT JOIN item AS i ON l.item_code=i.item_code
          LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
          LEFT JOIN biblio_topic AS bt ON i.biblio_id=bt.biblio_id
          LEFT JOIN mst_topic AS mt ON bt.topic_id=mt.topic_id
          WHERE mt.topic IS NOT NULL
          GROUP BY bt.topic_id
          ORDER BY total DESC
          LIMIT {$limit}";

        $query = $this->db->query($sql);
        $return = array();
        while ($data = $query->fetch_row()) {
            $return[] = $data[0];
        }
        if ($query->num_rows < $limit) {
            $need = $limit - $query->num_rows;
            if ($need < 0) {
                $need = $limit;
            }

            $sql = "SELECT mt.topic, COUNT(*) AS total
            FROM biblio_topic AS bt
            LEFT JOIN mst_topic AS mt ON bt.topic_id=mt.topic_id
            WHERE mt.topic IS NOT NULL
            GROUP BY bt.topic_id
            ORDER BY total DESC
            LIMIT {$need}";

            $query = $this->db->query($sql);
            while ($data = $query->fetch_row()) {
                $return[] = $data[0];
            }
        }

        parent::withJson($return);
    }

    function getLatest() {

        $limit = 5;

        $sql = "SELECT mt.topic
          FROM biblio_topic AS bt
          LEFT JOIN biblio AS b ON bt.biblio_id=b.biblio_id
          LEFT JOIN mst_topic AS mt ON mt.topic_id=bt.topic_id
          WHERE mt.topic IS NOT NULL
          GROUP BY bt.topic_id
          ORDER BY max(b.last_update) DESC
          LIMIT {$limit}";

        $query = $this->db->query($sql);
        $return = array();
        while ($data = $query->fetch_row()) {
            $return[] = $data[0];
        }

        parent::withJson($return);
    }
}