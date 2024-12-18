<?php
namespace SLiMS\Log\Handler;

use utility;
use SLiMS\DB;

class Systemlog extends Contract
{
    private ?object $datagrid = null;

    public function write(string $type, string $value_id, string $location, string $message, string $submod='', string $action='')
    {
        utility::writeLogs(DB::getInstance('mysqli'), $type, $value_id, $location, $message, $submod, $action);
    }

    public function read(?Object $datagrid = null): Contract
    {
        $this->datagrid = $datagrid;

        $this->datagrid->setSQLColumn(
            'sl.log_date AS \''.__('Time').'\'',
            'sl.log_location AS \''.__('Location').'\'',
            'sl.log_msg AS \''.__('Message').'\'');
        $this->datagrid->setSQLorder('sl.log_date DESC');
        
        // is there any search
        if (isset($_GET['keywords']) AND $_GET['keywords']) {
            $keyword = utility::filterData('keywords', 'get', true, true, true);
            $words = explode(' ', $keyword);
            if (count($words) > 1) {
                $concat_sql = ' (';
                foreach ($words as $word) {
                    $concat_sql .= " (sl.log_date LIKE '%$word%' OR sl.log_msg LIKE '%$word%') AND";
                }
                // remove the last AND
                $concat_sql = substr_replace($concat_sql, '', -3);
                $concat_sql .= ') ';
                $this->datagrid->setSQLCriteria($concat_sql);
            } else {
                $this->datagrid->setSQLCriteria("sl.log_date LIKE '%$keyword%' OR sl.log_msg LIKE '%$keyword%'");
            }
        }
        
        // set table and table header attributes
        $this->datagrid->table_attr = 'id="dataList" class="s-table table"';
        $this->datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        // set delete proccess URL
        $this->datagrid->delete_URL = $_SERVER['PHP_SELF'];
        $this->datagrid->column_width = array('18%', '10%', '72%');
        $this->datagrid->disableSort('Message');
        
        return $this;
    }

    public function truncate()
    {
        DB::getInstance('mysqli')->query('TRUNCATE TABLE system_log');
    }

    public function download()
    {
        $logs = DB::getInstance('mysqli')->query('SELECT log_date, log_location, log_msg FROM system_log ORDER BY log_date DESC');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="system_logs_'.date('Ymd').'.log"');
        echo 'SENAYAN system logs record'."\n";
        while ($logs_d = $logs->fetch_row()) {
            echo '['.$logs_d[0].']---'.$logs_d[1].'---'.$logs_d[2]."\n";
        }
        exit();
    }

    public function __toString()
    {
        // put the result into variables
        if (isset($_GET['keywords']) AND $_GET['keywords']) {
            $msg = str_replace('{result->num_rows}', $this->datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
            echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</div>';
        }
        return $this->datagrid->createDataGrid(DB::getInstance('mysqli'), 'system_log AS sl', 50, false);
    }
}