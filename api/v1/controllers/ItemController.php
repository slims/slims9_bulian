<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 09/11/20 22.35
 * @File name           : ItemController.php
 */

class ItemController extends Controller
{
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

    public function getTotalAll()
    {
        $query = $this->db->query("SELECT COUNT(item_id) FROM item");
        parent::withJson([
            'data' => ($query->fetch_row())[0]
        ]);
    }

    public function getTotalLent()
    {
        $query = $this->db->query("SELECT COUNT(loan_id) FROM loan WHERE is_lent=1 AND is_return=0");
        parent::withJson([
            'data' => ($query->fetch_row())[0]
        ]);
    }

    public function getTotalAvailable()
    {
        $query = $this->db->query("SELECT (SELECT COUNT(item_id) FROM item) - (SELECT COUNT(loan_id) FROM loan WHERE is_lent=1 AND is_return=0) AS 'available'");
        parent::withJson([
            'data' => ($query->fetch_row())[0]
        ]);
    }
}