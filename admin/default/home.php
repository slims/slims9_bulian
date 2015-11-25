<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * some patches by hendro
 */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    include_once '../../sysconfig.inc.php';
}
?>
<fieldset class="menuBox adminHome">
<div class="menuBoxInner">
    <div class="per_title">
        <h2><?php echo __('Library Administration'); ?></h2>
    </div>
</div>
</fieldset>
<?php

// generate warning messages
$warnings = array();
// check GD extension
if (!extension_loaded('gd')) {
    $warnings[] = __('<strong>PHP GD</strong> extension is not installed. Please install it or application won\'t be able to create image thumbnail and barcode.');
} else {
    // check GD Freetype
    if (!function_exists('imagettftext')) {
        $warnings[] = __('<strong>Freetype</strong> support is not enabled in PHP GD extension. Rebuild PHP GD extension with Freetype support or application won\'t be able to create barcode.');
    }
}
// check for overdue
$overdue_q = $dbs->query('SELECT COUNT(loan_id) FROM loan AS l WHERE (l.is_lent=1 AND l.is_return=0 AND TO_DAYS(due_date) < TO_DAYS(\''.date('Y-m-d').'\')) GROUP BY member_id');
$num_overdue = $overdue_q->num_rows;
if ($num_overdue > 0) {
    $warnings[] = str_replace('{num_overdue}', $num_overdue, __('There is currently <strong>{num_overdue}</strong> library members having overdue. Please check at <b>Circulation</b> module at <b>Overdues</b> section for more detail')); //mfc
    $overdue_q->free_result();
}
// check if images dir is writable or not
if (!is_writable(IMGBS) OR !is_writable(IMGBS.'barcodes') OR !is_writable(IMGBS.'persons') OR !is_writable(IMGBS.'docs')) {
    $warnings[] = __('<strong>Images</strong> directory and directories under it is not writable. Make sure it is writable by changing its permission or you won\'t be able to upload any images and create barcodes');
}
// check if file repository dir is writable or not
if (!is_writable(REPOBS)) {
    $warnings[] = __('<strong>Repository</strong> directory is not writable. Make sure it is writable (and all directories under it) by changing its permission or you won\'t be able to upload any bibliographic attachments.');
}
// check if file upload dir is writable or not
if (!is_writable(UPLOAD)) {
    $warnings[] = __('<strong>File upload</strong> directory is not writable. Make sure it is writable (and all directories under it) by changing its permission or you won\'t be able to upload any file, create report files and create database backups.');
}
// check mysqldump
if (!file_exists($sysconf['mysqldump'])) {
    $warnings[] = __('The PATH for <strong>mysqldump</strong> program is not right! Please check configuration file or you won\'t be able to do any database backups.');
}
// check installer directory
if (is_dir('../install/')) {
    $warnings[] = __('Installer folder is still exist inside your server. Please remove it or rename to another name for security reason.');
}


// check need to be repaired mysql database
$query_of_tables    = $dbs->query('SHOW TABLES');
$num_of_tables      = $query_of_tables->num_rows;
$prevtable          = '';
$repair             = '';
$is_repaired        = false;

if (isset ($_POST['do_repair'])) {
    if ($_POST['do_repair'] == 1) {
        while ($row = $query_of_tables->fetch_row()) {
            $sql_of_repair = 'REPAIR TABLE '.$row[0];
            $query_of_repair = $dbs->query ($sql_of_repair);
        }
    }
}

while ($row = $query_of_tables->fetch_row()) {
    $query_of_check = $dbs->query('CHECK TABLE '.$row[0]);
    while ($rowcheck = $query_of_check->fetch_assoc()) {
        if (!(($rowcheck['Msg_type'] == "status") && ($rowcheck['Msg_text'] == "OK"))) {
            if ($row[0] != $prevtable) {
                $repair .= '<li>Table '.$row[0].' might need to be repaired.</li>';
            }
            $prevtable = $row[0];
            $is_repaired = true;
        }
    }
}
if (($is_repaired) && !isset($_POST['do_repair'])) {
    echo '<div class="message">';
    echo '<ul>';
    echo $repair;
    echo '</ul>';
    echo '</div>';
    echo ' <form method="POST" style="margin:0 10px;">
        <input type="hidden" name="do_repair" value="1">
        <input type="submit" value="'.__('Click Here To Repaire The Tables').'" class="button btn btn-block btn-default">
        </form>';
}

// if there any warnings
if ($warnings) {
echo '<div class="message">';
echo '<ul>';
    foreach ($warnings as $warning_msg) {
        echo '<li>'.$warning_msg.'</li>';
    }
echo '</ul>';
echo '</div>';
}

// admin page content
if($sysconf['admin_home']['mode'] == 'default') {
    require LIB.'content.inc.php';
    $content = new content();
    $content_data = $content->get($dbs, 'adminhome');
    if ($content_data) {
        echo '<div class="contentDesc">'.$content_data['Content'].'</div>';
        unset($content_data);
    }
} else {
    // generate dashboard content
    $get_date       = '';
    $get_loan       = '';
    $get_return     = '';
    $get_extends    = '';
    $start_date     = date('Y-m-d'); // set date from TODAY

    // get date transaction
    $sql_date = 
            "SELECT 
                DATE_FORMAT(loan_date,'%d/%m') AS loandate,
                loan_date
            FROM 
                loan
            WHERE 
                loan_date BETWEEN DATE_SUB('".$start_date."', INTERVAL 8 DAY) AND '".$start_date."' 
            GROUP BY 
                loan_date
            ORDER BY 
                loan_date";

    // echo $sql_loan; //for debug purpose only
    $set_date = $dbs->query($sql_date);
    while ($transc_date = $set_date->fetch_object()) {
        // set transaction date
        $get_date .= '"'.$transc_date->loandate.'",';

        // get latest loan
        $sql_loan = 
                "SELECT 
                    COUNT(loan_date) AS countloan
                FROM 
                    loan
                WHERE 
                    loan_date = '".$transc_date->loan_date."' 
                    AND is_lent = 1 
                    AND renewed = 0
                    AND is_return = 0
                GROUP BY 
                    loan_date";

        $set_loan       = $dbs->query($sql_loan);
        if($set_loan->num_rows > 0) {
            $transc_loan    = $set_loan->fetch_object();
            $get_loan      .= $transc_loan->countloan.',';            
        } else {
            $get_loan       = 0;
        }

        // get latest return
        $sql_return = 
                "SELECT 
                    COUNT(loan_date) AS countloan
                FROM 
                    loan
                WHERE 
                    loan_date = '".$transc_date->loan_date."' 
                    AND is_lent = 1 
                    AND renewed = 0
                    AND is_return = 1
                GROUP BY 
                    loan_date";

        $set_return       = $dbs->query($sql_return);                     
        if($set_return->num_rows > 0) {
            $transc_return    = $set_return->fetch_object();
            $get_return      .= $transc_return->countloan.',';
        } else {
            $get_return       = 0;
        }

        // get latest extends
        $sql_extends = 
                "SELECT 
                    COUNT(loan_date) AS countloan
                FROM 
                    loan
                WHERE 
                    loan_date = '".$transc_date->loan_date."' 
                    AND is_lent     = 1 
                    AND renewed     = 1
                GROUP BY 
                    loan_date";
        $set_extends       = $dbs->query($sql_extends);   
        if($set_extends->num_rows > 0) {              
            $transc_extends    = $set_extends->fetch_object();
            $get_extends      .= $transc_extends->countloan.',';
        } else {
            $get_extends       = 0;
        }
    }
    // return transaction date
    $get_date       = substr($get_date,0,-1);
    $get_loan       = substr($get_loan,0,-1);
    $get_return     = substr($get_return,0,-1);
    $get_extends    = substr($get_extends,0,-1);

    // get total summary
    $sql_total_coll = ' SELECT 
                            COUNT(loan_id) AS total
                        FROM 
                            loan';
    $total_coll = $dbs->query($sql_total_coll);
    $total      = $total_coll->fetch_object();
    $get_total  = $total->total;

    // get loan summary
    $sql_loan_coll = ' SELECT 
                            COUNT(loan_id) AS total
                        FROM 
                            loan
                        WHERE
                            is_lent = 1
                            AND is_return = 0';
    $total_loan         = $dbs->query($sql_loan_coll);
    $loan               = $total_loan->fetch_object();
    $get_total_loan     = $loan->total;

    // get return summary
    $sql_return_coll = ' SELECT 
                            COUNT(loan_id) AS total
                        FROM 
                            loan
                        WHERE
                            is_lent = 1
                            AND is_return = 1';
    $total_return         = $dbs->query($sql_return_coll);
    $return               = $total_return->fetch_object();
    $get_total_return     = $return->total;

    // get extends summary
    $sql_extends_coll = ' SELECT 
                            COUNT(loan_id) AS total
                        FROM 
                            loan
                        WHERE
                            is_lent = 1
                            AND renewed = 1
                            AND is_return = 0';
    $total_extends         = $dbs->query($sql_extends_coll);
    $renew                 = $total_extends->fetch_object();
    $get_total_extends     = $renew->total;

    // get overdue
    $sql_overdue_coll = ' SELECT 
                            COUNT(fines_id) AS total
                        FROM 
                            fines';
    $total_overdue         = $dbs->query($sql_overdue_coll);
    $overdue               = $total_overdue->fetch_object();
    $get_total_overdue     = $overdue->total;

    // get titles
    $sql_title_coll = ' SELECT 
                            COUNT(biblio_id) AS total
                        FROM 
                            biblio';
    $total_title         = $dbs->query($sql_title_coll);
    $title               = $total_title->fetch_object();
    $get_total_title     = number_format($title->total,0,'.',',');

    // get item
    $sql_item_coll = ' SELECT 
                            COUNT(item_id) AS total
                        FROM 
                            item';
    $total_item          = $dbs->query($sql_item_coll);
    $item                = $total_item->fetch_object();
    $get_total_item      = number_format($item->total,0,'.',',');
    $get_total_available = $item->total - $get_total_loan;
    $get_total_available = number_format($get_total_available,0,'.',',');
?>
<div class="contentDesc">    
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 s-dashboard">
              <div class="panel panel-info">
                <div class="panel-heading">
                  <h2 class="panel-title">Latest Transactions</h2>
                </div>
                <div class="panel-body">
                    <canvas id="line-chartjs" height="319"></canvas>            
                </div>
                <div class="panel-footer">
                    <div class="s-dashboard-legend">
                        <div><i class="fa fa-square" style="color:#f2f2f2;"></i> New</div>
                        <div><i class="fa fa-square" style="color:#459CBD;"></i> Return</div>
                        <div><i class="fa fa-square" style="color:#5D45BD;"></i> Extend</div>
                    </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4 s-dashboard">
              <div class="panel panel-default s-dashboard">
                <div class="panel-heading">
                  <h2 class="panel-title">Summary</h2>
                </div>
                <div class="panel-body">
                    <div class="s-chart">                        
                        <canvas id="radar-chartjs" width="175" height="175"></canvas>              
                    </div>
                </div>
                <div class="panel-footer">
                    <table class="table">
                        <tr>
                            <td class="text-left"><i class="fa fa-square" style="color:#f2f2f2;"></i>&nbsp;&nbsp;Total</td>
                            <td class="text-right"><?php echo $get_total?></td>
                        </tr>
                        <tr>
                            <td class="text-left"><i class="fa fa-square" style="color:#337AB7;"></i>&nbsp;&nbsp;New</td>
                            <td class="text-right"><?php echo $get_total_loan?></td>
                        </tr>
                        <tr>
                            <td class="text-left"><i class="fa fa-square" style="color:#06B1CD;"></i>&nbsp;&nbsp;Return</td>
                            <td class="text-right"><?php echo $get_total_return?></td>
                        </tr>
                        <tr>
                            <td class="text-left"><i class="fa fa-square" style="color:#4AC49B;"></i>&nbsp;&nbsp;Extends</td>
                            <td class="text-right"><?php echo $get_total_extends?></td>
                        </tr>
                        <tr>
                            <td class="text-left"><i class="fa fa-square" style="color:#F4CC17;"></i>&nbsp;&nbsp;Overdue</dd>
                            <td class="text-right"><?php echo $get_total_overdue?></td>
                        </tr>
                    </table>                                      
                </div>
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="s-widget-icon"><i class="fa fa-bookmark"></i></div>
                        <div class="s-widget-value"><?php echo $get_total_title?></div>
                        <div class="s-widget-title">Total of Collections</div>                  
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="s-widget-icon"><i class="fa fa-barcode"></i></div>
                        <div class="s-widget-value"><?php echo $get_total_item?></div>
                        <div class="s-widget-title">Total of Items</div>                  
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="s-widget-icon"><i class="fa fa-archive"></i></div>
                        <div class="s-widget-value"><?php echo $get_total_loan?></div>
                        <div class="s-widget-title">Lent</div>                  
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="s-widget-icon"><i class="fa fa-check"></i></div>
                        <div class="s-widget-value"><?php echo $get_total_available?></div>
                        <div class="s-widget-title">Available</div>                  
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>

    </div>
</div>
<script src="<?php echo JWB?>chartjs/Chart.min.js"></script>
<script>
$(function(){  
    var lineChartData = {
      labels : [<?php echo $get_date?>],
      datasets : 
        [
            {
              fillColor : "#f2f2f2",
              data : [<?php echo $get_loan?>]
            },{
              fillColor : "#459CBD",
              data : [<?php echo $get_return?>]
            },{
                fillColor : "#5D45BD",
                data : [<?php echo $get_extends?>]
            }
        ]
    }

    var c = $('#line-chartjs');
    var container = $(c).parent();
    var ct = c.get(0).getContext("2d");
    $(window).resize( respondCanvas );
    function respondCanvas(){ 
        c.attr('width', $(container).width() ); //max width
        c.attr('height', $(container).height() ); //max height
        //Call a function to redraw other content (texts, images etc)
        var myChart = new Chart(ct).Bar(lineChartData,{
            barShowStroke: false,
            barDatasetSpacing : 4,
            animation: false
        });
    }
    respondCanvas();

    var data = [
        {
            value       : <?php echo $get_total?>,
            color       : "#f2f2f2",
            label       : "Total"
        },
        {
            value       : <?php echo $get_total_loan?>,
            color       : "#337AB7",
            label       : "Loan"
        },
        {
            value       : <?php echo $get_total_return?>,
            color       : "#06B1CD",
            label       : "Return"
        },
        {
            value       : <?php echo $get_total_extends?>,
            color       : "#4AC49B",
            label       : "Extend"
        },
        {
            value       : <?php echo $get_total_overdue?>,
            color       : "#F4CC17",
            label       : "Overdue"
        }

    ];

    var r = $('#radar-chartjs');
    var container = $(r).parent();
    var rt = r.get(0).getContext("2d");
    $(window).resize( respondCanvas );
    function respondCanvasRadar(){ 
        r.attr('width', $(container).width()); //max width
        r.attr('height', $(container).height()); //max height
        //Call a function to redraw other content (texts, images etc)
        var myChart = new Chart(rt).Doughnut(data,{
            animation: false,
            segmentStrokeWidth : 1
        });
    }
    respondCanvasRadar();


});    

</script>
<?php } ?>