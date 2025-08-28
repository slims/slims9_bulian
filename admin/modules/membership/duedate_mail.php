<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-07 12:35:55
 * @modify date 2023-01-07 13:16:11
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\{DB,Mail,Plugins};

// key to authenticate
define('INDEX_AUTH', '1');

require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-membership');
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

// privileges checking
$can_read = utility::havePrivilege('membership', 'r');
if (!$can_read) { die(); }

if (is_null(config('mail'))) die('<div class="alert alert-warning">'.__('E-Mail configuration is not ready!').'</div>');

// member name
$memberStatement = DB::getInstance()->prepare('SELECT member_name, member_email, member_phone, member_mail_address, member_id FROM member WHERE member_id=?');
$memberStatement->execute([$_POST['memberID']??0]);
$memberData = $memberStatement->fetchObject();

$loanStatement = DB::getInstance()->prepare(<<<SQL
    SELECT l.item_code, b.title, b.image, l.loan_date,
        l.due_date, (TO_DAYS(due_date)-TO_DAYS(DATE(NOW()))) AS 'overdue_days'
        FROM loan AS l
            LEFT JOIN item AS i ON l.item_code=i.item_code
            LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
        WHERE (l.is_lent=1 AND l.is_return=0 AND ( (TO_DAYS(due_date)-TO_DAYS(?)) BETWEEN 0 AND 3) AND l.member_id= ?)
SQL);
$loanStatement->execute([date('Y-m-d'), $_POST['memberID']]);

$loadData = [];
while ($loanResult = $loanStatement->fetchObject()) {
    $loadData[] = $loanResult;
}

// execute registered hook
Plugins::getInstance()->execute(Plugins::DUEDATE_NOTICE_INIT, ['member' => $memberData]);

try {
    // Template
    include SB . 'admin/admin_template/duedateMail.php';
    $duedateTemplate = new duedateMail($memberData);
    $duedateTemplate->setMinify(true);
    $duedateTemplate->setCirculationData($loadData);
    
    // Send email to member
    Mail::to($memberData->member_email, $memberData->member_name)
            ->subject(str_replace(['{member_name}', '{member_email}'], [$memberData->member_name, $memberData->member_email], __('Due Date Warning for Member {member_name} ({member_email})')))
            ->loadTemplate($duedateTemplate)
            ->send();
    
    echo '<div class="alert alert-success">' . __('Due date notification E-Mail have been sent to ') . $memberData->member_email . '</div>';
} catch (Exception $exception) {
    echo '<div class="alert alert-danger">' . __('Message could not be sent. Mailer Error: ') . Mail::getInstance()->ErrorInfo . '</div>';
}