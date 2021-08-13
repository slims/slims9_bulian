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
          WHERE b.title IS NOT NULL AND b.opac_hide < 1
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

            $sql = "SELECT biblio_id, title, image FROM biblio WHERE opac_hide < 1 ORDER BY last_update DESC LIMIT {$need}";
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
          FROM biblio WHERE opac_hide < 1
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
        $query = $this->db->query("SELECT COUNT(biblio_id) FROM biblio WHERE opac_hide < 1");
        parent::withJson([
            'data' => ($query->fetch_row())[0]
        ]);
    }

    public function getByGmd($gmd) {
        $limit = 3;
        $sql = "SELECT b.biblio_id, b.title, b.image, b.notes
          FROM biblio AS b, mst_gmd AS g
          WHERE b.gmd_id=g.gmd_id AND g.gmd_name='$gmd' AND b.opac_hide < 1
          ORDER BY b.last_update DESC
          LIMIT {$limit}";
        $query = $this->db->query($sql);
        $return = array();
        while ($data = $query->fetch_assoc()) {
            $data['image'] = $this->getImagePath($data['image']);
            $return[] = $data;
        }
    
        parent::withJson($return);
    }

    public function getByCollType($coll_type) {
        $limit = 3;
        $sql = "SELECT b.biblio_id, b.title, b.image, b.notes
          FROM biblio AS b, item AS i, mst_coll_type AS c
          WHERE b.biblio_id=i.biblio_id AND i.coll_type_id=c.coll_type_id AND c.coll_type_name='$coll_type' AND b.opac_hide < 1
          ORDER BY b.last_update DESC
          LIMIT {$limit}";
        $query = $this->db->query($sql);
        $return = array();
        while ($data = $query->fetch_assoc()) {
            $data['image'] = $this->getImagePath($data['image']);
            $return[] = $data;
        }
    
        parent::withJson($return);
    }


}