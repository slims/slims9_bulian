<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-08 15:41:21
 * @modify date 2022-11-08 21:51:20
 * @license GPLv3
 * @desc [description]
 */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

header("Content-Type: application/xls"); 
header('Content-Disposition: attachment; filename="member_fines_list_'.date('YmdHis').'.xls"');

if (isset($_GET['finesDateStart']) && isset($_GET['finesDateEnd']) && !empty($_GET['finesDateStart']) && !empty($_GET['finesDateEnd']))
{
    $date_criteria = ' AND (fines_date >= ? AND fines_date <= ?) ';
    $executeDate = [$_GET['finesDateStart'], $_GET['finesDateEnd']];
}
else
{
    $date_criteria = ' AND fines_date=?'; 
    $executeDate = [date('Y-m-d')];
}

$finesStatement = \SLiMS\DB::getInstance()
                        ->prepare('SELECT f.debet `' . __('Debet') . '`,f.credit `' . __('Credit') . '`, f.fines_date `' . __('Fines Date') . '`, f.description `' . __('Description') . '` 
                                    FROM fines AS f WHERE f.member_id= ? ' . $date_criteria);

$columns = '';
foreach ($_SESSION['csvData']??[] as $memberId => $value) {
    $finesStatement->execute(array_merge([$memberId], $executeDate));

    if (empty($columns))
    {
        $columns .= '"' . __('Member Name') . '";"' . __('Member ID') . '";';
        for ($i = 0; $i < $finesStatement->columnCount(); $i++) {
            $col = $finesStatement->getColumnMeta($i);
            $columns .= '"' . $col['name'] . '";';
        }
        echo $columns . "\n";
    }

    $no = 0;
    while ($data = $finesStatement->fetch(PDO::FETCH_NUM)) {
        if ($no == 0) echo '"' . $value['data'][0] . '";"'.$memberId.'";';
        if ($no > 0) echo '"";"";';
        echo '"' . implode('";"', $data) . '"'  . "\n";
        $no++;
    }
}