<?php
/**
 * Copyright (C) 2010 Arie Nugraha (dicarve@yahoo.com)
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


/* Loan Circulation Receipt Pop Windows */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SB.'admin/admin_template/printed_settings.inc.php';

// page title
$page_title = 'Loan Receipt';

// start the output buffer
ob_start();
/* main content */
?>
<style type="text/css">
#receiptBody {
  margin: <?php echo $sysconf['print']['receipt']['receipt_margin']; ?>;
  padding: <?php echo $sysconf['print']['receipt']['receipt_padding']; ?>;
  color: <?php echo $sysconf['print']['receipt']['receipt_color']; ?>;
  font-family: <?php echo $sysconf['print']['receipt']['receipt_font']; ?>;
  width: <?php echo $sysconf['print']['receipt']['receipt_width']; ?>;
  border: <?php echo $sysconf['print']['receipt']['receipt_border']; ?>;
}

#receiptBody * {
  color: <?php echo $sysconf['print']['receipt']['receipt_color']; ?>;
  font-family: <?php echo $sysconf['print']['receipt']['receipt_font']; ?>;
  font-size: <?php echo $sysconf['print']['receipt']['receipt_fontSize']; ?>;
  font-weight: bold;
}

.receiptHeader {
  font-weight: bold;
  font-size: <?php echo $sysconf['print']['receipt']['receipt_header_fontSize']; ?>;
  border-bottom: <?php echo $sysconf['print']['receipt']['receipt_border']; ?>;
}

.receiptFooter {
  font-weight: bold;
  font-size: <?php echo $sysconf['print']['receipt']['receipt_header_fontSize']; ?>;
  border-top: <?php echo $sysconf['print']['receipt']['receipt_border']; ?>;    
}
</style>
<?php ob_start(); ?>
<div id="receiptBody">
    <div class="receiptHeader">
      <div id="receiptTitle"><?php echo $sysconf['library_name'] ?><br /><?php echo $sysconf['library_subname'] ?></div></td>
        <td><div id="receiptMember"><?php echo $_SESSION['receipt_record']['memberName'] ?> (<?php echo $_SESSION['receipt_record']['memberID'] ?>)</div>
        <div id="receiptDate"><?php echo $_SESSION['receipt_record']['date'] ?></div>
    </div>

    <hr />
    <div id="receiptInfo">
        <!-- LOAN -->
        <?php if (isset($_SESSION['receipt_record']['loan']) || isset($_SESSION['receipt_record']['extend'])) { ?>
        <div class="receiptHeader">Type of Transaction: <?php echo __('Loan'); ?>/<?php echo __('Extended'); ?> (<?php echo mt_rand(000000000, 999999999); ?>)</div>
        <hr size="1" noshade="noshade" />
        <?php
        if (isset($_SESSION['receipt_record']['loan'])) {
            foreach ($_SESSION['receipt_record']['loan'] as $loan) {
                echo '<div class="receiptLoans">';
                echo '<div class="receiptItemCode">'.$loan['itemCode'].'</div>';
                echo '<div class="receiptItemTitle">'.substr($loan['title'], 0, $sysconf['print']['receipt']['receipt_titleLength']);
                if (strlen($loan['title']) > $sysconf['print']['receipt']['receipt_titleLength']) {
                    echo ' ...';
                }
                echo '.</div>';
                echo '<div class="receiptItemLoanDate">'.$loan['loanDate'].'</div>';
                echo '<div class="receiptItemDueDate">'.$loan['dueDate'].'</div>';
                echo '</div>';
            }
        }

        // loan extend
        if (isset($_SESSION['receipt_record']['extend'])) {
            foreach ($_SESSION['receipt_record']['extend'] as $ext) {
                echo '<div class="receiptLoans">';
                echo '<div class="receiptItemCode">'.$ext['itemCode'].'</div>';
                #echo '<td>'.substr($ext['title'], 0, 50).'...<br />-- extended --</td>';

                echo '<div class="receiptItemTitle">'.substr($ext['title'], 0, $sysconf['print']['receipt']['receipt_titleLength']);
                if (strlen($ext['title']) > $sysconf['print']['receipt']['receipt_titleLength']) {
                    echo ' ...';
                }
                echo '. <strong>(Loan Extended)</strong></div>';

                echo '<div class="receiptItemLoanDate">'.$ext['loanDate'].'</div>';
                echo '<div class="receiptItemDueDate">'.$ext['dueDate'].'</div>';
                echo '</div>';
            }
        }
        ?>

        <?php } ?>

        <?php
        # to remove extended items from return session list
        if (isset($_SESSION['receipt_record']['return']) AND isset($_SESSION['receipt_record']['extend'])) {
            foreach ($_SESSION['receipt_record']['extend'] as $key => $value) {
                if ($_SESSION['receipt_record']['extend'][$key]['itemCode'] == $_SESSION['receipt_record']['return'][$key]['itemCode']) {
                    unset($_SESSION['receipt_record']['return'][$key]);
                }
            }
        }
        ?>

        <!-- RETURN -->
        <?php if (isset($_SESSION['receipt_record']['return']) AND (count($_SESSION['receipt_record']['return']) != 0)) { ?>
        <div class="receiptHeader">Type of Transaction: <?php echo __('Return'); ?> (<?php echo mt_rand(000000000, 999999999); ?>)</div>
        <hr size="1" noshade="noshade" />
        <?php
        foreach ($_SESSION['receipt_record']['return'] as $ret) {
            echo '<div class="receiptReturns">';
            echo '<div class="receiptItemCode">'.$ret['itemCode'].'</div>';
            echo '<div class="receiptItemTitle">'.substr($ret['title'], 0, $sysconf['print']['receipt']['receipt_titleLength']);
            if (strlen($ret['title']) > $sysconf['print']['receipt']['receipt_titleLength']) {
                echo ' ...';
            }
            echo '.</div>';
            echo '<div class="receiptItemReturn">'.$ret['returnDate'].'</div>';
            if ($ret['overdues']) {
                echo '<span class="receiptLoanOverdue">'.$ret['overdues']['days'].' days overdue</span>';
            }
            echo '</div>';
        }
        ?>

        <?php } ?>
    </div>
    <hr size="1" noshade="noshade" />
    <div class="receiptFooter">
      <div class="receiptLibraryStaff">Library Staf<p><?php echo $_SESSION['realname']; ?></p></div>
      <div class="receiptLibraryMember">Library member<p><?php echo $_SESSION['receipt_record']['memberName']; ?></p></div>
    </div>
</div>
<?php $buffer_receipt = ob_get_contents(); ob_end_clean(); echo $buffer_receipt; ?>
<script type="text/javascript">window.print()</script>
<?php
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
