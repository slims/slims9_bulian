<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-08 11:10:32
 * @modify date 2023-01-07 13:11:28
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\DB;
use SLiMS\Mail\TemplateContract;

class duedateMail extends TemplateContract
{
    private $circulation = null;
    private $member = [];
    private $duedateData = [];

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
    public function setCirculationData($duedateLoan)
    {
        // overude data
        $this->duedateData = $duedateLoan;
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
        $header = __('To <strong><!--MEMBER_NAME--> (<!--MEMBER_ID-->)</strong>&nbsp;
         This is notification e-mail to inform you that you have <strong>DUE DATE</strong> library loan,
        the overdued collection(s) are:');

        // Closing
        $closing = __('Please return all collections immediately to library at or before due date. If you have
        any complaint regarding to this due date notification,
        please contact our circulation desk.');
        

        // footer information about library management etc
        $footer = __('<p>Thank You.</p>

        <strong><!--DATE--></strong>
        <br />Library Management'); 

        // Institution logo
        $logo = '';

        $loanData = '';
        foreach ($this->duedateData as $duedateData) {
            // Get cover url
            $bookCover = $this->generateCoverUrl($duedateData->image);

            // count overdue day
            $overdue = $duedateData->overdue_days . ' ' . __('days');

            // Concating loanData variable
            $loanData .= <<<HTML
            <tr>
                <td>
                    <img style="width: 100px; margin-right: 1em; border-radius: 5px;" src="{$bookCover}">
                </td>
                <td valign="top">
                    <h2 style="margin: 0; display: block">{$duedateData->title}</h2>
                    <div style="display: block">
                        <span style="display: block">Item Code : {$duedateData->item_code}</span>
                        <span style="display: block">Loan Date : {$duedateData->loan_date}</span>
                        <span style="display: block">Due Date : {$duedateData->due_date}</span>
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