<?php
/**
 * @Created by          : Heru Subekti (heroe.soebekti@yahoo.co.id)
 * @Date                : 2020-01-23 08:01
 * @File name           : pop_chart.php
 */


// key to authenticate
define('INDEX_AUTH', '1');

// load SLiMS main system configuration
require '../../../sysconfig.inc.php';

// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

$array_color = array('#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080', '#ffffff', '#000000');

ob_start();

$label = array();
$data = array();

$filter = simbio_security::xssFree($_GET['filter']??'');
$chart = $_SESSION['chart']??[];
$page_title = isset($chart['title']) ? $chart['title'].$filter : 'Chart ' . $filter;
$chart_type = isset($chart['chart_type']) ? $chart['chart_type'] : 'Line';

foreach ($chart['xAxis']??[] as $key => $value) {
	array_push($label,$value);
}

$num_color = 1;
$legend = [];

$dataset = !empty($filter) && isset($chart['data']) ?$chart['data'][$filter]:($chart['data']??[]);

foreach ($dataset as $key => $value) {
	$color = $array_color[$num_color];
    $legend[$key] = $color;
	$data[] = array(
		'fillColor'=> (!isset($chart['chart_type'])?'rgba(220,220,220,0)':$color),
		'strokeColor'=> $color,
		'pointColor' => $color,
		'data' => array_values($value));
    $num_color++;
}

?>
<script src="<?php echo JWB?>chartjs/Chart.min.js"></script>
<h5 style="text-align: center;"><?= $page_title; ?></h5>
<hr/><br/>
<div class="s-chart">                        
    <canvas id="chartjs"></canvas>   
    <div class="s-dashboard-legend">
    <?php
    foreach ($legend as $labels => $color) {
        echo '<i class="fa fa-square" style="color:'.$color.';"></i>&nbsp;'.__(ucfirst(str_replace('_', ' ',$labels))).'&nbsp;';
    }
    ?>
    </div>           
</div>

<script>
$(function(){  
	var ct = $('#chartjs').get(0).getContext("2d");
    var lineChartData = {
      labels : <?php echo json_encode($label)?>,
      datasets : <?php echo json_encode($data)?>
    }
    $(window).resize( respondCanvas );
    function respondCanvas(){ 
        var myChart = new Chart(ct).<?= $chart_type?>(lineChartData,{
        responsive:true,
        maintainAspectRatio: true,
        bezierCurve : false,
        });
    }
    respondCanvas();
});    

</script>

<?php
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';



