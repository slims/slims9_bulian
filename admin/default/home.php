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

use SLiMS\DB;

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    include_once '../../sysconfig.inc.php';
}
?>
<div class="menuBox adminHome">
    <div class="menuBoxInner">
        <div class="per_title">
            <h2><?php echo __('Library Administration'); ?></h2>
        </div>
    </div>
</div>
<div id="backupProccess" style="display: none">
    <div class="alert alert-info">
        <strong><?= __('Database backup process is running, please wait') ?></strong>
    </div>
</div>
<div class="contentDesc">
    <div class="container-fluid">

        <div id="alert-new-version" class="alert alert-info border-0 mt-3 hidden">
            <strong>News!</strong> New version of SLiMS (<code id="new_version"></code>) available to <a class="notAJAX"
                                                                                                         target="_blank"
                                                                                                         href="https://github.com/slims/slims9_bulian/releases/latest">download</a>.
        </div>

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
        $overdue_q = $dbs->query('SELECT COUNT(loan_id) FROM loan AS l WHERE (l.is_lent=1 AND l.is_return=0 AND TO_DAYS(due_date) < TO_DAYS(\'' . date('Y-m-d') . '\')) GROUP BY member_id');
        $num_overdue = $overdue_q->num_rows;
        if ($num_overdue > 0) {
            $warnings[] = str_replace('{num_overdue}', $num_overdue, __('There are currently <strong>{num_overdue}</strong> library members having overdue. Please check at <b>Circulation</b> module at <b>Overdues</b> section for more detail')); //mfc
            $overdue_q->free_result();
        }
        // check if images dir is writable or not
        if (!is_writable(IMGBS) OR !is_writable(IMGBS . 'barcodes') OR !is_writable(IMGBS . 'persons') OR !is_writable(IMGBS . 'docs')) {
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
        //if (!file_exists($sysconf['mysqldump'])) {
        //    $warnings[] = __('The PATH for <strong>mysqldump</strong> program is not right! Please check configuration file or you won\'t be able to do any database backups.');
        //}
        // check installer directory
        if (is_dir('../install/')) {
            $warnings[] = __('Installer folder is still exist inside your server. Please remove it or rename to another name for security reason.');
        }


        // check need to be repaired mysql database
        $query_of_tables = $dbs->query('SHOW TABLES');
        $num_of_tables = $query_of_tables->num_rows;
        $prevtable = '';
        $repair = '';
        $is_repaired = false;

        if ($_SESSION['uid'] === '1') {
            $warnings[] = __('<strong><i>You are logged in as Super User. With great power comes great responsibility.</i></strong>');
            if (isset ($_POST['do_repair'])) {
                if ($_POST['do_repair'] == 1) {
                    while ($row = $query_of_tables->fetch_row()) {
                        $sql_of_repair = 'REPAIR TABLE ' . $row[0];
                        $query_of_repair = $dbs->query($sql_of_repair);
                    }
                }
            }

            while ($row = $query_of_tables->fetch_row()) {
                $query_of_check = $dbs->query('CHECK TABLE `' . $row[0] . '`');
                if ($query_of_check) {
                    while ($rowcheck = $query_of_check->fetch_assoc()) {
                        if (!(($rowcheck['Msg_type'] == "status") && ($rowcheck['Msg_text'] == "OK"))) {
                            if ($row[0] != $prevtable) {
                                $repair .= '<li>' . __('Table') . ' ' . $row[0] . ' ' . __('might need to be repaired.') . '</li>';
                            }
                            $prevtable = $row[0];
                            $is_repaired = true;
                        }
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
        <input type="submit" value="' . __('Click Here To Repair The Tables') . '" class="button btn btn-block btn-default">
        </form>';
            }
        }

        if (utility::havePrivilege('system', 'r') && utility::havePrivilege('system', 'w'))
        {
            // info
            $backupConfigStatus = config('database_backup.reminder') || config('database_backup.auto');
            $backupIsNoAuto = config('database_backup.reminder') && !config('database_backup.auto');
            $alreadyBackup = DB::hasBackup(by: DB::BACKUP_BASED_ON_DAY);


            if ($alreadyBackup === false && $backupConfigStatus) 
                $_SESSION['token'] = utility::createRandomString(32);
            
            if ($alreadyBackup === false && $is_repaired === false && $backupIsNoAuto === true) {
                echo '<div class="alert alert-info border-0 mt-3">';
                echo '<span>' . __('It looks like today you haven\'t backup your database.') . 
                '.&nbsp;&nbsp;<a href="'.MWB.'system/backup_proc.php" id="backupproc" class="notAJAX btn btn-primary">' . __('Backup Now') . '</a>' .
                '</span>';
                echo '</div>';
            }
        }

        // if there any warnings
        if ($warnings) {
            echo '<div class="alert alert-warning border-0 mt-3">';
            foreach ($warnings as $warning_msg) {
                echo '<div>' . $warning_msg . '</div>';
            }
            echo '</div>';
        }

        // admin page content
        if ($sysconf['admin_home']['mode'] == 'default') {
            require LIB . 'content.inc.php';
            $content = new content();
            $content_data = $content->get($dbs, 'adminhome');
            if ($content_data) {
                echo '<div class="contentDesc">' . $content_data['Content'] . '</div>';
                unset($content_data);
            }
        } else {
        $start_date = date('Y-m-d');
        ?>
        <div class="row">
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="s-widget-icon"><i class="fa fa-bookmark"></i></div>
                        <div class="s-widget-value biblio_total_all">0</div>
                        <div class="s-widget-title"><?php echo __('Total of Collections') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="s-widget-icon"><i class="fa fa-barcode"></i></div>
                        <div class="s-widget-value item_total_all">0</div>
                        <div class="s-widget-title"><?php echo __('Total of Items') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="s-widget-icon"><i class="fa fa-archive"></i></div>
                        <div class="s-widget-value item_total_lent">0</div>
                        <div class="s-widget-title"><?php echo __('Lent') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3 col-lg-3">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="s-widget-icon"><i class="fa fa-check"></i></div>
                        <div class="s-widget-value item_total_available">0</div>
                        <div class="s-widget-title"><?php echo __('Available') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col col-md-8 s-dashboard">
                <div class="card border-0">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo __('Latest Transactions') ?></h5>
                        <canvas id="line-chartjs" height="294"></canvas>
                        <div class="s-dashboard-legend">
                            <i class="fa fa-square" style="color:#F4CC17;"></i> <?php echo __('Loan') ?>
                            <i class="fa fa-square" style="color:#459CBD;"></i> <?php echo __('Return') ?>
                            <i class="fa fa-square" style="color:#5D45BD;"></i> <?php echo __('Extend') ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col col-md-4 s-dashboard">
                <div class="card border-0">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo __('Summary') ?></h5>
                        <div class="s-chart">
                            <canvas id="radar-chartjs" width="175" height="175"></canvas>
                        </div>
                        <table class="table">
                            <tr>
                                <td class="text-left"><i class="fa fa-square"
                                                         style="color:#f2f2f2;"></i>&nbsp;&nbsp;<?php echo __('Total') ?>
                                </td>
                                <td class="text-right loan_total">0</td>
                            </tr>
                            <tr>
                                <td class="text-left"><i class="fa fa-square"
                                                         style="color:#337AB7;"></i>&nbsp;&nbsp;<?php echo __('New') ?>
                                </td>
                                <td class="text-right loan_new">0</td>
                            </tr>
                            <tr>
                                <td class="text-left"><i class="fa fa-square"
                                                         style="color:#06B1CD;"></i>&nbsp;&nbsp;<?php echo __('Return') ?>
                                </td>
                                <td class="text-right loan_return">0</td>
                            </tr>
                            <tr>
                                <td class="text-left"><i class="fa fa-square"
                                                         style="color:#4AC49B;"></i>&nbsp;&nbsp;<?php echo __('Extends') ?>
                                </td>
                                <td class="text-right loan_extend">0</td>
                            </tr>
                            <tr>
                                <td class="text-left"><i class="fa fa-square"
                                                         style="color:#F4CC17;"></i>&nbsp;&nbsp;<?php echo __('Overdue') ?>
                                </td>
                                <td class="text-right loan_overdue">0</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <script src="<?php echo JWB ?>chartjs/Chart.min.js"></script>
    <script>
            
        $(function () {

            async function getTotal(url, selector = null) {
                if(selector !== null) $(selector).text('...');
                let res = await (await fetch(url,{headers: {'SLiMS-Http-Cache': 'cache'}})).json();
                if(selector !== null) $(selector).text(new Intl.NumberFormat('id-ID').format(res.data));
                return res.data;
            }

            getTotal('<?= SWB ?>index.php?p=api/biblio/total/all', '.biblio_total_all');
            getTotal('<?= SWB ?>index.php?p=api/item/total/all', '.item_total_all');
            getTotal('<?= SWB ?>index.php?p=api/item/total/lent', '.item_total_lent');
            getTotal('<?= SWB ?>index.php?p=api/item/total/available', '.item_total_available');

            // get summary
            fetch('<?= SWB ?>index.php?p=api/loan/summary', {headers: {'SLiMS-Http-Cache': 'cache'}})
                .then(res => res.json())
                .then(res => {

                    $('.loan_total').text(new Intl.NumberFormat('id-ID').format(res.data.total));
                    $('.loan_new').text(new Intl.NumberFormat('id-ID').format(res.data.new));
                    $('.loan_return').text(new Intl.NumberFormat('id-ID').format(res.data.return));
                    $('.loan_extend').text(new Intl.NumberFormat('id-ID').format(res.data.extend));
                    $('.loan_overdue').text(new Intl.NumberFormat('id-ID').format(res.data.overdue));

                    let data = [
                        {
                            value: parseInt(res.data.total),
                            color: "#f2f2f2",
                            label: "<?php echo __('Total'); ?>"
                        },
                        {
                            value: parseInt(res.data.new),
                            color: "#337AB7",
                            label: "<?php echo __('Loan'); ?>"
                        },
                        {
                            value: parseInt(res.data.return),
                            color: "#06B1CD",
                            label: "<?php echo __('Return'); ?>"
                        },
                        {
                            value: parseInt(res.data.extend),
                            color: "#4AC49B",
                            label: "<?php echo __('Extend'); ?>"
                        },
                        {
                            value: parseInt(res.data.overdue),
                            color: "#F4CC17",
                            label: "<?php echo __('Overdue'); ?>"
                        }

                    ];

                    let r = $('#radar-chartjs');
                    let container = $(r).parent();
                    let rt = r.get(0).getContext("2d");
                    $(window).resize(respondCanvas);

                    function respondCanvas() {
                        r.attr('width', $(container).width()); //max width
                        r.attr('height', $(container).height()); //max height
                        //Call a function to redraw other content (texts, images etc)
                        let myChart = new Chart(rt).Doughnut(data, {
                            animation: false,
                            segmentStrokeWidth: 1
                        });
                    }

                    respondCanvas()
                });

            // ===================================
            // bar chart
            // ===================================

            fetch('<?= SWB ?>index.php?p=api/loan/getdate/<?= $start_date ?>', {headers: {'SLiMS-Http-Cache': 'cache'}})
            .then(res => res.json())
            .then(res => {

                let a = getTotal('<?= SWB ?>index.php?p=api/loan/summary/<?= $start_date ?>');
                a.then(res_total => {

                    let lineChartData = {
                        labels: res.raw,
                        datasets: [
                            {
                                fillColor: '#F4CC17',
                                highlightFill: '#F4CC17',
                                data: res_total.loan
                            },
                            {
                                fillColor: '#459CBD',
                                highlightFill: '#459CBD',
                                data: res_total.return
                            },
                            {
                                fillColor: '#5D45BD',
                                highlightFill: '#5D45BD',
                                data: res_total.extend
                            },
                        ]
                    }

                    let c = $('#line-chartjs');
                    let container = $(c).parent();
                    let ct = c.get(0).getContext("2d");
                    $(window).resize(respondCanvas);

                    function respondCanvas() {
                        c.attr('width', $(container).width()); //max width
                        c.attr('height', $(container).height()); //max height
                        //Call a function to redraw other content (texts, images etc)
                        new Chart(ct).Bar(lineChartData, {
                            barShowStroke: false,
                            barDatasetSpacing: 4,
                            animation: {
                                onProgress: function(animation) {
                                    progress.value = animation.animationObject.currentStep / animation.animationObject.numSteps;
                                }
                            }
                        });
                    }

                    respondCanvas();
                })
            })
        });

        <?php if (utility::havePrivilege('system', 'r') && utility::havePrivilege('system', 'w')): ?>
            <?php if (config('database_backup.reminder') && !config('database_backup.auto')): ?>
                // Backup process
                $('#backupproc').click(function(e) {
                    e.preventDefault()
                    
                    let currentLabel = $(this).html()

                    $(this).removeClass('btn-primary').addClass('btn-secondary disabled')
                    $(this).html('<?= __('Please wait') ?>')

                    backupDatabase($(this).attr('href'), function(result) {
                        if (result.status)  {
                            window.location.href = '<?= $_SERVER['PHP_SELF'] ?>'
                        } else {
                            $(this).html(currentLabel)
                            console.error(result.message)
                            window.toastr.error(result.message, '<?= __('Error') ?>')
                        }
                    })                    
                })
            <?php endif; ?>

            function backupDatabase(href, callback) {
                $.post(href, {start:true,tkn:'<?= $_SESSION['token']??'' ?>',verbose:'no',response:'json'}, function(result, status, post){
                        var result = JSON.parse(result)
                        callback(result)
                });
            }

            <?php if (!$is_repaired && !$alreadyBackup && config('database_backup.auto')): ?>
                $('.contentDesc').slideUp();
                $('#backupProccess').slideDown();

                backupDatabase('<?= MWB.'system/backup_proc.php' ?>', function(result) {
                    if (result.status)  {
                        window.location.href = '<?= $_SERVER['PHP_SELF'] ?>'
                    } else {
                        $(this).html(currentLabel)
                        console.error(result.message)
                        window.toastr.error(result.message, '<?= __('Error') ?>')
                    }
                })
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($_SESSION['uid'] === '1') : ?>
        // get lastest release
        fetch('https://api.github.com/repos/slims/slims9_bulian/releases/latest')
            .then(res => res.json())
            .then(res => {
                if (res.tag_name > '<?= SENAYAN_VERSION_TAG; ?>') {
                    $('#new_version').text(res.tag_name);
                    $('#alert-new-version').removeClass('hidden');
                    $('#alert-new-version a').attr('href', res.html_url)
                }
            })
        <?php endif; ?>

    </script>
<?php } ?>
