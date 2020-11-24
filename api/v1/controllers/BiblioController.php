<?php

/**
 * @author              : Waris Agung Widodo
 * @Date                : 2017-07-05 12:15:12
 * @Last Modified by    : ido
 * @Last Modified time  : 2017-07-05 15:08:08
 *
 * Copyright (C) 2017  Waris Agung Widodo (ido.alit@gmail.com)
 */

require_once 'Controller.php';
require_once __DIR__ . '/../helpers/Image.php';
require_once __DIR__ . '/../helpers/Cache.php';

class BiblioController extends Controller
{

    use Image;

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

    public function getPopular()
    {
        $cache_name = 'biblio_popular';
        if (!is_null($json = Cache::get($cache_name))) return parent::withJson($json);

        $limit = $this->sysconf['template']['classic_popular_collection_item'];
        $sql = "SELECT b.biblio_id, b.title, b.image, COUNT(*) AS total
          FROM loan AS l
          LEFT JOIN item AS i ON l.item_code=i.item_code
          LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
          WHERE b.title IS NOT NULL
          GROUP BY b.biblio_id
          ORDER BY total DESC
          LIMIT {$limit}";

        $query = $this->db->query($sql);
        $return = array();
        while ($data = $query->fetch_assoc()) {
            $data['image'] = $this->getImagePath($data['image']);
            $return[] = $data;
        }
        if ($query->num_rows < $limit) {
            $need = $limit - $query->num_rows;
            if ($need < 0) {
                $need = $limit;
            }

            $sql = "SELECT biblio_id, title, image FROM biblio ORDER BY last_update DESC LIMIT {$need}";
            $query = $this->db->query($sql);
            while ($data = $query->fetch_assoc()) {
                $data['image'] = $this->getImagePath($data['image']);
                $return[] = $data;
            }
        }

        Cache::set($cache_name, json_encode($return));
        parent::withJson($return);
    }

    public function getLatest() {
        $limit = 6;

        $sql = "SELECT biblio_id, title, image
          FROM biblio
          ORDER BY last_update DESC
          LIMIT {$limit}";

        $query = $this->db->query($sql);
        $return = array();
        while ($data = $query->fetch_assoc()) {
            $data['image'] = $this->getImagePath($data['image']);
            $return[] = $data;
        }

        parent::withJson($return);
    }

    public function getTotalAll()
    {
        $query = $this->db->query("SELECT COUNT(biblio_id) FROM biblio");
        parent::withJson([
            'data' => ($query->fetch_row())[0]
        ]);
    }
}