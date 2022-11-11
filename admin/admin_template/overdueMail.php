<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-08 11:10:32
 * @modify date 2022-11-12 06:31:55
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\DB;
use SLiMS\Mail\TemplateContract;

class overdueMail extends TemplateContract
{
    private $circulation = null;
    private $member = [];
    private $overdueData = [];

    public function __construct($member)
    {
        $this->member = $member;
    }

    /**
     * SEt circulation instance
     * to calculate overdue and overdue data
     *
     * @param mysqli $db
     * @param array $overdueLoan
     * @return void
     */
    public function setCirculationData($db, $overdueLoan)
    {
        require MDLBS . 'circulation/circulation_base_lib.inc.php';

        $this->circulation = new circulation($db, $this->member->member_id);
        $this->circulation->ignore_holidays_fine_calc = config('ignore_holidays_fine_calc');
        $this->circulation->holiday_dayname = $_SESSION['holiday_dayname'];
        $this->circulation->holiday_date = $_SESSION['holiday_date'];

        // overude data
        $this->overdueData = $overdueLoan;
    }

    /**
     * Mail content output process
     *
     * @return overdueMail
     */
    public function render()
    {
        // library name
        $libraryName = config('library_name');

        // Header information
        $header = __('To <strong><!--MEMBER_NAME--> (<!--MEMBER_ID-->)</strong>
         This is notification e-mail to inform you that you have <strong>OVERDUED</strong> library loan,
        the overdued collection(s) are:');

        // Closing
        $closing = __('Please return all overdued collections immediately to library. If you have
        any complaint regarding to this overdue notification,
        please contact our circulation desk.');
        

        // footer information about library management etc
        $footer = __('<p>Thank You.</p>

        <strong><!--DATE--></strong>
        <br />Library Management'); 

        // Institution logo
        $logo = '';
        // if (!$this->isLocal() && file_exists(SB . 'images/default/' . config('logo_image', 'notfound.png')))
        // {
        //     $url = 'https://' . $_SERVER['SERVER_NAME'] . SWB . 'images/default/' . config('logo_image', 'notfound.png');
        //     $logo = '<img src="" style="width: 60px; height: 60px"/>';
        // }

        $loanData = '';
        foreach ($this->overdueData as $overdueData) {
            // extract array key into variable
            extract($overdueData);

            // Get cover url
            $bookCover = $this->generateCoverUrl($image);

            // count overdue day
            $overdue = ($this->circulation->countOverdueValue($loan_id, date('Y-m-d'))['days']??0) . ' ' . __('days');

            // Concating loanData variable
            $loanData .= <<<HTML
            <tr>
                <td>
                    <img style="width: 100px; margin-right: 1em; border-radius: 5px;" src="{$bookCover}">
                </td>
                <td valign="top">
                    <h2 style="margin: 0; display: block">{$title}</h2>
                    <div style="display: block">
                        <span style="display: block">Item Code : {$item_code}</span>
                        <span style="display: block">Loan Date : {$loan_date}</span>
                        <span style="display: block">Due Date : {$due_date}</span>
                        <span style="display: block">Overdue : <strong>{$overdue}</strong></span>
                    </div>
                </td>
            </tr>
            HTML;
        }

        $formatedTemplate = <<<HTML
        <div style="font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; padding: 2em;">
            <div style="display: block;">
                <div style="display:">
                    {$logo}
                    <h2 style="padding-left: 0.3em;">{$libraryName}</h2>
                </div>
                <p style="margin: 1em 0 1em 0;">{$header}</p>
                <table style="display: block">
                    {$loanData}
                </table>
                {$footer}
            </div>
        </div>
        HTML;

        $this->contents = str_ireplace(['<!--MEMBER_ID-->', '<!--MEMBER_NAME-->','<!--DATE-->'], [$this->member->member_id, $this->member->member_name, date('Y-m-d H:i:s')], $formatedTemplate);

        return $this;
    }
}