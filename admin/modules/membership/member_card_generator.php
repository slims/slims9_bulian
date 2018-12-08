<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 */

/* Member card print */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-membership');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('membership', 'r');

if (!$can_read) {
    die('<div class="errorBox">You dont have enough privileges to view this section</div>');
}

// local settings
$max_print = 10;

// clean print queue
if (isset($_GET['action']) AND $_GET['action'] == 'clear') {
    // update print queue count object
    echo '<script type="text/javascript">parent.$(\'#queueCount\').html(\'0\');</script>';
    utility::jsAlert(__('Print queue cleared!'));
    unset($_SESSION['card']);
    exit();
}

if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!$can_read) {
        die();
    }
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array($_POST['itemID']);
    }
    // loop array
    if (isset($_SESSION['card'])) {
        $print_count = count($_SESSION['card']);
    } else {
        $print_count = 0;
    }
    // card size
    $size = 2;
    // create AJAX request
    echo '<script type="text/javascript" src="'.JWB.'jquery.js"></script>';
    echo '<script type="text/javascript">';
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        if ($print_count == $max_print) {
            $limit_reach = true;
            break;
        }
        if (isset($_SESSION['card'][$itemID])) {
            continue;
        }
        if (!empty($itemID)) {
            $card_text = trim($itemID);
            echo '$.ajax({url: \''.SWB.'lib/phpbarcode/barcode.php?code='.$card_text.'&encoding='.$sysconf['barcode_encoding'].'&scale='.$size.'&mode=png\', type: \'GET\', error: function() { alert(\'Error creating member card!\'); } });'."\n";
            // add to sessions
            $_SESSION['card'][$itemID] = $itemID;
            $print_count++;
        }
    }
    echo '</script>';
    if (isset($limit_reach)) {
        $msg = str_replace('{max_print}', $max_print, __('Selected items NOT ADDED to print queue. Only {max_print} can be printed at once')); //mfc
        utility::jsAlert($msg);
    } else {
        // update print queue count object
        echo '<script type="text/javascript">parent.$(\'#queueCount\').html(\''.$print_count.'\');</script>';
        utility::jsAlert(__('Selected items added to print queue'));
    }
    exit();
}

// card pdf download
if (isset($_GET['action']) AND $_GET['action'] == 'print') {
    // check if label session array is available
    if (!isset($_SESSION['card'])) {
        utility::jsAlert(__('There is no data to print!'));
        die();
    }
    if (count($_SESSION['card']) < 1) {
        utility::jsAlert(__('There is no data to print!'));
        die();
    }
    // concat all ID together
    $member_ids = '';
    foreach ($_SESSION['card'] as $id) {
        $member_ids .= '\''.$id.'\',';
    }
    // strip the last comma
    $member_ids = substr_replace($member_ids, '', -1);
    // send query to database
    /*$member_q = $dbs->query('SELECT m.member_name, m.member_id, m.member_image, mt.member_type_name FROM member AS m
        LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
        WHERE m.member_id IN('.$member_ids.')'); */
	/*
	member_id 	member_name 	member_image member_type_id 	member_address 	member_mail_address 	member_email 	postal_code 	inst_name 	 	 	member_phone 	member_since_date 	register_date 	expire_date 	input_date

	*/

	$member_q = $dbs->query('SELECT m.member_name, m.member_id, m.member_image, m.member_address, m.member_email, m.inst_name, m.postal_code, m.pin, m.member_phone, m.expire_date, m.register_date, mt.member_type_name FROM member AS m
        LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
        WHERE m.member_id IN('.$member_ids.')');
    $member_datas = array();
    while ($member_d = $member_q->fetch_assoc()) {
        if ($member_d['member_id']) {
            $member_datas[] = $member_d;
        }
    }

    // include printed settings configuration file
    include SB.'admin'.DS.'admin_template'.DS.'printed_settings.inc.php';
    // check for custom template settings
    $custom_settings = SB.'admin'.DS.$sysconf['admin_template']['dir'].DS.$sysconf['template']['theme'].DS.'printed_settings.inc.php';
    if (file_exists($custom_settings)) {
        include $custom_settings;
    }

	  // load print settings from database to override value from printed_settings file
    loadPrintSettings($dbs, 'membercard');

    // chunk cards array
    $chunked_card_arrays = array_chunk($member_datas, $sysconf['print']['membercard']['items_per_row']);
    // create html ouput
    $html_str = '<!DOCTYPE html>'."\n";
    $html_str .= '<html><head><title>Member Card by Jushadi Arman Saz</title>'."\n";
    $html_str .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    $html_str .= '<meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" /><meta http-equiv="Expires" content="Fry, 02 Oct 2012 12:00:00 GMT" />';
    $html_str .= '<style type="text/css">'."\n";
    $html_str .= '*{font:'.$sysconf['print']['membercard']['bio_font_size'].'px Arial, Helvetica, sans-serif;}'."\n";
    $html_str .= 'p, li{position: relative;}'."\n";
    $html_str .= 'p{margin-bottom: 0px;margin-top: 0px;font-weight: bold;}'."\n";
    $html_str .= 'li{margin-bottom: 0px; margin-top: 0px;list-style-type: disc;font-size: '.$sysconf['print']['membercard']['rules_font_size'].'px;}'."\n";
    $html_str .= 'ul{margin: 0px;padding-left: 10px;}'."\n";
    $html_str .= 'h1{margin: 0px;font-weight: bold;text-align: center;font-size:'.$sysconf['print']['membercard']['front_header1_font_size'].'px;}'."\n";
    $html_str .= 'h2{margin: 0px;font-weight: bold;text-align: center;padding-bottom:3px;font-size:'.$sysconf['print']['membercard']['front_header2_font_size'].'px;}'."\n";
    $html_str .= 'h3{margin: 0px;font-weight: bold;text-align: center;padding-bottom:3px;font-size:'.$sysconf['print']['membercard']['back_header2_font_size'].'px;}'."\n";
    $html_str .= 'hr{margin: 0px;border: 1px solid '.$sysconf['print']['membercard']['header_color'].';position: relative;}'."\n";
    $html_str .= '#header1_div{z-index:2;position: absolute;left: 61px;top: 4px;width:245px;height: 45px;color:'.$sysconf['print']['membercard']['header_color'].';}'."\n";
    $html_str .= '#header2_div{z-index:3;position: absolute;left: 10px;top: 4px;width:300px;height: 43px;color:'.$sysconf['print']['membercard']['header_color'].';}'."\n";
    $html_str .= '#rules_div{z-index:4;position: absolute;left: 12px;top: 58px;width:300px;height: 142px;text-align: justify;}'."\n";
    $html_str .= '#address_div{z-index:4;position: absolute;left: 9px;top: 175px;width:300px;height: 20px;font-size:'.$sysconf['print']['membercard']['address_font_size'].'px;}'."\n";
    $html_str .= '#logo_div{z-index:5;position: absolute;left: 10px;top: 4px;width: 35px;height:35px;}'."\n";
    $html_str .= '#photo_blank_div{z-index:5;position: absolute;left: 10px;top:130px;font-size: 7px;text-align: center;border:#cccccc solid 1px;width:'.($sysconf['print']['membercard']['photo_width']*$sysconf['print']['membercard']['factor']).'px; height:'.($sysconf['print']['membercard']['photo_height']*$sysconf['print']['membercard']['factor']).'px;}'."\n";
    $html_str .= '#photo_div{z-index:6;position: absolute;left: 10px;top:130px;border:#cccccc solid 1px;width:'.($sysconf['print']['membercard']['photo_width']*$sysconf['print']['membercard']['factor']).'px; height:'.($sysconf['print']['membercard']['photo_height']*$sysconf['print']['membercard']['factor']).'px;}'."\n";
    $html_str .= '#front_side{background: url('.SWB.'files/membercard/'.$sysconf['print']['membercard']['front_side_image'].') center center;}'."\n";
    $html_str .= '#back_side{background: url('.SWB.'files/membercard/'.$sysconf['print']['membercard']['back_side_image'].') center center;}'."\n";
    $html_str .= '.container_div{z-index:1;position: relative; width:'.($sysconf['print']['membercard']['box_width']*$sysconf['print']['membercard']['factor']).'px; height:'.($sysconf['print']['membercard']['box_height']*$sysconf['print']['membercard']['factor']).'px;margin-bottom:'.($sysconf['print']['membercard']['items_margin']*$sysconf['print']['membercard']['factor']).'px;;border:#CCCCCC solid 1px;-moz-border-radius: 8px;border-radius: 8px;}'."\n";
    $html_str .= '.bio_div{z-index:7;position: absolute;left: 0px;top:48px;height: 110px;margin: 0px;text-align: justify;}'."\n";
    $html_str .= '.bio_address{z-index:8;top: 0px;}'."\n";
    $html_str .= '.bio_label{ z-index:9;float: left;width: 100px;text-align:left;padding-left: 10px;}'."\n";
    $html_str .= '.label_address{z-index:10;float: left; width: 200px;margin-bottom:0px;margin-left:3px;}'."\n";
    $html_str .= '.stamp_div{z-index:11;position: absolute;left: 100px;top:140px;margin-bottom: 34px;width: 118px;}'."\n";
    $html_str .= '.stamp{z-index:12;text-align: left;margin: 0px;}'."\n";
    $html_str .= '.city{z-index:13;font-size:8px;margin: 0px;}'."\n";
    $html_str .= '.title{z-index:14;font-size:8px;margin: 0px;}'."\n";
    $html_str .= '.officials{z-index:15;top: 0px;font-size: 8px;margin: 0px;}'."\n";
    $html_str .= '.sign_file_div{z-index:16;position: absolute;left: -10px;top: 10px;width:107px;height: 25px;}'."\n";
    $html_str .= '.stamp_file_div{z-index:17;position: absolute;left:-20px;top: 5px;width: 40px;height: 40px;}'."\n";
    $html_str .= '.exp_div{z-index:18;position: absolute;left: 200px;top: 142px;width:110px;height: 12px;font-size: 8px;text-align: right;}'."\n";
    $html_str .= '.barcode_div{z-index:19;position: absolute;left: 200px;top: 154px;width:112px;height: 42px;}'."\n";
    $html_str .= '</style>'."\n";
    $html_str .= '</head>'."\n";
    $html_str .= '<body>'."\n";
    $html_str .= '<a href="#" onclick="window.print()">' . __('Print Again') . '</a><br /><br />'."\n";
    $html_str .= '<table style="margin: 0; padding: 0;" cellspacing="0" cellpadding="0">'."\n";
    // loop the chunked arrays to row
    foreach ($chunked_card_arrays as $membercard_rows) {
        $html_str .= '<tr>'."\n";
        foreach ($membercard_rows as $card) {
          $html_str .= '<td valign="top">';
          $html_str .= '<div class="container_div" id="front_side">';
          $html_str .= '<div></div>';
          $html_str .= '<div id="logo_div"><img height="40px" width="40px" src="'.SWB.'files/membercard/'.$sysconf['print']['membercard']['logo'].'" /></div>';
          $html_str .= '<div id="header1_div">';
          $html_str .= '<h1>'.$sysconf['print']['membercard']['front_header1_text'].'</h1>';
          $html_str .= '<h2>'.$sysconf['print']['membercard']['front_header2_text'].'</h2></div>';
          $html_str .= '<div class="bio_div">';
          $html_str .= ''.( $sysconf['print']['membercard']['include_id_label']?'':'<!--').'<p class="bio"><label class="bio_label">'.__('Member ID').'</label><span>: </span>'.$card['member_id'].'</p>'.( $sysconf['print']['membercard']['include_id_label']?'':'-->').'';
          $html_str .= ''.( $sysconf['print']['membercard']['include_name_label']?'':'<!--').'<p class="bio"><label class="bio_label">'.__('Member Name').'</label><span>: </span>'.$card['member_name'].'</p>'.( $sysconf['print']['membercard']['include_name_label']?'':'-->').'';
          $html_str .= ''.( $sysconf['print']['membercard']['include_pin_label']?'':'<!--').'<p class="bio"><label class="bio_label">'.__('Personal ID Number').'</label><span>: </span>'.$card['pin'].'</p>'.( $sysconf['print']['membercard']['include_pin_label']?'':'-->').'';
          $html_str .= ''.( $sysconf['print']['membercard']['include_inst_label']?'':'<!--').'<p class="bio_address"><label class="bio_label">'.__('Institution').'</label><span style="float:left">: </span>'.( $sysconf['print']['membercard']['include_inst_label']?'':'-->').'';
          $html_str .= ''.( $sysconf['print']['membercard']['include_inst_label']?'':'<!--').'<span class="label_address">'.$card['inst_name'].'</span></p>'.( $sysconf['print']['membercard']['include_inst_label']?'':'-->').'';
          $html_str .= ''.( $sysconf['print']['membercard']['include_email_label']?'':'<!--').'<p class="bio"><label class="bio_label">'.__('E-mail').'</label><span>: </span>'.$card['member_email'].'</p>'.( $sysconf['print']['membercard']['include_email_label']?'':'-->').'';
          $html_str .= ''.( $sysconf['print']['membercard']['include_address_label']?'':'<!--').'<p class="bio_address"><label class="bio_label">'.__('Address').' / '.__('Phone Number').'</label><span style="float:left">: </span>'.( $sysconf['print']['membercard']['include_address_label']?'':'-->').'';
          $html_str .= ''.( $sysconf['print']['membercard']['include_address_label']?'':'<!--').'<span class="label_address">'.$card['member_address'].' / '.$card['member_phone'].'</span></p>'.( $sysconf['print']['membercard']['include_address_label']?'':'-->').'';
          $html_str .= '</div>';
          $html_str .= '<div id="photo_blank_div"><br />Photo size:<br />'.$sysconf['print']['membercard']['photo_width'].' X '.$sysconf['print']['membercard']['photo_height'].' cm</div>';
          $html_str .= '<div id="photo_div"><img width="'.($sysconf['print']['membercard']['photo_width']*$sysconf['print']['membercard']['factor']).'px" height="'.($sysconf['print']['membercard']['photo_height']*$sysconf['print']['membercard']['factor']).'px" src="'.SWB.IMG.'/persons/'.$card['member_image'].'" /></div>';
          $html_str .= ''.( $sysconf['print']['membercard']['include_expired_label']?'':'<!--').'<div class="exp_div">'.__('Expiry Date').' : '.$card['expire_date'].'</div>'.( $sysconf['print']['membercard']['include_expired_label']?'':'-->').'';
          $html_str .= ''.( $sysconf['print']['membercard']['include_barcode_label']?'':'<!--').'<div class="barcode_div">';
          $html_str .= '<img  width="175px" height="40px" src="'.SWB.IMG.'/barcodes/'.str_replace(array(' '), '_', $card['member_id']).'.png" style="width: '.$sysconf['print']['membercard']['barcode_scale'].'%; border="0px" /></img></div>'.( $sysconf['print']['membercard']['include_barcode_label']?'':'-->').'';
          $html_str .= '<div class="stamp_div">';
          $html_str .= '<div class="stamp_file_div"><img class="" height="35px" width="35px" src="'.SWB.'files/membercard/'.$sysconf['print']['membercard']['stamp_file'].'"></img></div>';
          $html_str .= '<div class="sign_file_div"><img class="" height="30px" width="100px" src="'.SWB.'files/membercard/'.$sysconf['print']['membercard']['signature_file'].'"></img></div>';
          $html_str .= '<p class="stamp city">'.$sysconf['print']['membercard']['city'].', '.$card['register_date'].'</p><p class="stamp title">'.$sysconf['print']['membercard']['title'].'</p><br>';
          $html_str .= '<p class="stamp officials">'.$sysconf['print']['membercard']['officials'].'<br />'.$sysconf['print']['membercard']['officials_id'].'</p></div></div></td>';
          $html_str .= '<td valign="top">';
          $html_str .= '<div class="container_div" id="back_side">';
          $html_str .= '<div></div>';
          $html_str .= '<div id="logo_div"><img height="35px" width="35px" src="'.SWB.'files/membercard/'.$sysconf['print']['membercard']['logo'].'" /></div>';
          $html_str .= '<div id="header2_div">';
          $html_str .= '<h1>'.$sysconf['print']['membercard']['back_header1_text'].'</h1>';
          $html_str .= '<h3>'.$sysconf['print']['membercard']['back_header2_text'].'</h3><hr></div>';
          $html_str .= '<div id="rules_div">'.$sysconf['print']['membercard']['rules'].'</div>';
          $html_str .= '<div id="address_div">'.$sysconf['print']['membercard']['address'].'</div></div>';
          $html_str .= '</td>';
        }
        $html_str .= '<tr>'."\n";
    }
    $html_str .= '</table>'."\n";
    $html_str .= '<script type="text/javascript">self.print();</script>'."\n";
    $html_str .= '</body></html>'."\n";
    // unset the session
    unset($_SESSION['card']);
    // write to file
    $print_file_name = 'member_card_gen_print_result_'.strtolower(str_replace(' ', '_', $_SESSION['uname'])).'.html';
    $file_write = @file_put_contents(UPLOAD.$print_file_name, $html_str);
    if ($file_write) {
        // update print queue count object
        echo '<script type="text/javascript">parent.$(\'#queueCount\').html(\'0\');</script>';
        // open result in window
        echo '<script type="text/javascript">top.jQuery.colorbox({href: "'.SWB.FLS.'/'.$print_file_name.'", iframe: true, width: 800, height: 500, title: "'.__('Member Card Printing').'"})</script>';
    } else { utility::jsAlert(str_replace('{directory}', SB.FLS, __('ERROR! Cards failed to generate, possibly because {directory} directory is not writable'))); }
    exit();
}

?>
<fieldset class="menuBox">
<div class="menuBoxInner printIcon">
	<div class="per_title">
    	<h2><?php echo __('Member Card Printing'); ?></h2>
    </div>
	<div class="sub_section">
		<div class="btn-group">
		<a target="blindSubmit" href="<?php echo MWB; ?>membership/member_card_generator.php?action=clear" class="notAJAX btn btn-default" style="color: #f00;"><i class="glyphicon glyphicon-trash"></i>&nbsp;<?php echo __('Clear Print Queue'); ?></a>
		<a target="blindSubmit" href="<?php echo MWB; ?>membership/member_card_generator.php?action=print" class="notAJAX btn btn-default"><i class="glyphicon glyphicon-print"></i>&nbsp;<?php echo __('Print Member Cards for Selected Data'); ?></a>
		<a href="<?php echo MWB; ?>bibliography/pop_print_settings.php?type=membercard" class="notAJAX btn btn-default openPopUp" title="<?php echo __('Member card print settings'); ?>"><i class="glyphicon glyphicon-wrench"></i></a>
    </div>
	    <form name="search" action="<?php echo MWB; ?>membership/member_card_generator.php" id="search" method="get" style="display: inline;"><?php echo __('Search'); ?>:
	    <input type="text" name="keywords" size="30" />
	    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="button" />
	    </form>
    </div>
    <div class="infoBox">
    <?php
    echo __('Maximum').' <font style="color: #f00">'.$max_print.'</font> '.__('records can be printed at once. Currently there is').' '; //mfc
    if (isset($_SESSION['card'])) {
        echo '<font id="queueCount" style="color: #f00">'.count($_SESSION['card']).'</font>';
    } else { echo '<font id="queueCount" style="color: #f00">0</font>'; }
    echo ' '.__('in queue waiting to be printed.'); //mfc
    ?>
    </div>
</div>
</fieldset>
<?php
/* search form end */
/* ITEM LIST */
// table spec
$table_spec = 'member AS m
    LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id';
// create datagrid
$datagrid = new simbio_datagrid();
$datagrid->setSQLColumn('m.member_id',
    'm.member_id AS \''.__('Member ID').'\'',
    'm.member_name AS \''.__('Member Name').'\'',
    'mt.member_type_name AS \''.__('Membership Type').'\'');
$datagrid->setSQLorder('m.last_update DESC');
// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $keyword = $dbs->escape_string(trim($_GET['keywords']));
    $words = explode(' ', $keyword);
    if (count($words) > 1) {
        $concat_sql = ' (';
        foreach ($words as $word) {
            $concat_sql .= " (m.member_id LIKE '%$word%' OR m.member_name LIKE '%$word%'";
        }
        // remove the last AND
        $concat_sql = substr_replace($concat_sql, '', -3);
        $concat_sql .= ') ';
        $datagrid->setSQLCriteria($concat_sql);
    } else {
        $datagrid->setSQLCriteria("m.member_id LIKE '%$keyword%' OR m.member_name LIKE '%$keyword%'");
    }
}
// set table and table header attributes
$datagrid->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// edit and checkbox property
$datagrid->edit_property = false;
$datagrid->chbox_property = array('itemID', __('Add'));
$datagrid->chbox_action_button = __('Add To Print Queue');
$datagrid->chbox_confirm_msg = __('Add to print queue?');
$datagrid->column_width = array('10%', '70%', '15%');
// set checkbox action URL
$datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];
// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, $can_read);
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    echo '<div class="infoBox">'.__('Found').' '.$datagrid->num_rows.' '.__('from your search with keyword').': "'.$_GET['keywords'].'"</div>'; //mfc
}
echo $datagrid_result;
/* main content end */
