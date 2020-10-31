<?php
/**
 * @Created by          : Heru Subekti (heroe.soebekti@gmail.com)
 * @Date                : 2020-02-29
 * @File name           : procurement_report.php
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
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

$page_title = 'Procurement Report';
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <div class="per_title">
        <h2><?php echo __('Procurement Report'); ?></h2>
    </div>
    <div class="infoBox"><?= __('Displays a collection procurement report')?></div>
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();

    // class array
    for ($i=0; $i < 10 ; $i++) { 
        $class[$i.'00'] = $i;
    }
    $class[__('Others')] = __('Others');
    
    // table start
    $first_header = '';
    $second_header = '';
    $dataset = array();
    $detail_class_coll = '<th class="text-center small" title="'.__('Title').'">'.__('t').'</th><th class="text-center small" title="'.__('Item').'">'.__('i').'</th>';
    // table header
    $output = '<table class="s-table table table-sm table-bordered mb-0">';
    $output .= '<tr><th rowspan="2">'.__('Receiving Year').' / '.__('Classification').'</th>';
    foreach ($class as $class_num => $month) {
        $first_header .= '<th colspan="2" class="text-center">'.$class_num.'</th>';
        $second_header .= $detail_class_coll;
        $xAxis[$class_num] = $class_num;
    }
    $output .= $first_header;
    $output .= '<th colspan="2" class="text-center">'.__('ALL').'</th><th rowspan="2" class="text-center">'.__('Options').'</th></tr>';
    $output .= '<tr>'.$second_header.$detail_class_coll.'</tr>';

    // get year data from databse
    $_q = $dbs->query("SELECT YEAR(input_date) AS YEAR FROM item GROUP BY YEAR(input_date)");

    if($_q->num_rows >0){
        while ($_d = $_q->fetch_row()) {
            $years[$_d[0]] = $_d[0];
        }
        $years[__('ALL')] = __('ALL');

        foreach ($years as $year_num => $year) {

            $total_y_title = 0;
            $total_y_item = 0;
            $output .= '<tr><td><strong>'.$year.'</strong></td>';

            foreach ($class as $class_name => $class_value) {

                //filter by classification
                $classes = preg_match('/^([^0-9]+)$/', $class_value) ? " (trim(b.classification) REGEXP '^[^0-9]' OR trim(b.classification)='' OR trim(b.classification) IS NULL) " : " trim(b.classification) LIKE '".$class_value."%' ";

                $_q = $dbs->query("SELECT i.item_code,i.biblio_id 
                    FROM item i LEFT JOIN biblio b ON b.biblio_id=i.biblio_id 
                    WHERE  b.title IS NOT NULL 
                    AND ".$classes.($year == __('ALL')? '': ' AND YEAR(i.input_date)  = '.$year));

                $dataset[$year]['title'][$class_name] = 0;
                $dataset[$year]['item'][$class_name] = 0;

                if ($_q && $_q->num_rows>0) {
                    while ($_d = $_q->fetch_row()) {
                      $data['item'][$class_name][$_d[0]] = $_d[0];
                      $data['title'][$class_name][$_d[1]] = $_d[1];
                    }
                    $dataset[$year]['title'][$class_name] =count($data['title'][$class_name]);
                    $dataset[$year]['item'][$class_name] = count($data['item'][$class_name]);
                }

                // table content
                $count_title = (isset($data['title'][$class_name]) && is_array($data['title'][$class_name]))?count($data['title'][$class_name]):0;
                $count_item   = (isset($data['item'][$class_name]) && is_array($data['item'][$class_name]))?count($data['item'][$class_name]):0;
                $output .= '<td class="text-right text-center">'.$count_title.'</td>';
                $output .= '<td  class="text-right text-center"><strong>'.$count_item.'</strong></td>';
                $total_y_title += $count_title;
                $total_y_item += $count_item;
                unset($data);
            }
            $output .= '<td  class="text-right text-center">'.$total_y_title.'</td><td class="text-right text-center"><strong>'.$total_y_item.'</strong></td>';
            $output .= '<td><div class="btn-group">';
            $output .= '<a class="btn-sm btn btn-info notAJAX openPopUp" href="'.MWB.'reporting/customs/pop_procurement_list.php?filter='.$year_num.'" width="900" height="630" title="'.__('Procurement List').'"><i class="fa fa-list" aria-hidden="true"></i></a>';
            $output .= '<a class="btn-sm btn btn-primary notAJAX openPopUp" href="'.MWB.'reporting/pop_chart.php?filter='.$year.'" width="700" height="530" title="'.__('Procurement Bar Chart').'"><i class="fa fa-bar-chart" aria-hidden="true"></i></a>';           
            $output .= '</div></td></tr>';
        }
    }   
    $output .= '</table>';
    // set abbreviation details about "t" and "i" below year column
    $output .= '<div class="d-block mt-2"><strong>'.__('Description').'</strong><br><b class="text-bold">'.__('t').'</b> : <label>'.__('Title').'</label><br><b class="text-bold">'.__('i').'</b> : <label>'.__('Item').'</label></div>';

    $chart['xAxis'] = $xAxis;
    $chart['data'] = $dataset;
    $chart['chart_type'] = 'Bar';
    $chart['title'] =  __('Procurement Report Year : ');
    $_SESSION['chart'] = $chart;

    echo $output;

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/pop_iframe_tpl.php';

}
