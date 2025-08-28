<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-16 15:51:49
 * @modify date 2022-10-16 17:53:52
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\DB;
use SLiMS\Mail\TemplateContract;

class reserveMail extends TemplateContract
{
    public function render()
    {
         // date
         $_curr_date = date('Y-m-d H:i:s');

        $ids = [];
        foreach ($_SESSION['m_mark_biblio'] as $id) {
            $ids[] = (integer)$id;
        }

        if (count($ids) === 0) throw new Exception("Biblio basket is empty!");
        $ids = '(' . implode(',', $ids) . ')';

        // query
        $biblioStatement = DB::getInstance()->query("SELECT biblio_id, title, image FROM biblio WHERE biblio_id IN $ids");
         
        if ($biblioStatement->rowCount() === 0) throw new Exception("Biblio data not found!");

        $reserveData = '';
        while ($biblioData = $biblioStatement->fetchObject()) {
            // Get cover url
            $bookCover = $this->generateCoverUrl($biblioData->image);
            
            // Concating loanData variable
            $reserveData .= <<<HTML
            <tr>
                <td>
                    <img style="width: 100px; margin-right: 1em; border-radius: 5px;" src="{$bookCover}">
                </td>
                <td valign="top">
                    <h2 style="margin: 0; display: block">{$biblioData->title}</h2>
                </td>
            </tr>
            HTML;
        }

        // library name
        $libraryName = config('library_name');

        // Institution logo
        $logo = '';
        if (!$this->isLocal() && file_exists(SB . 'images/default/' . config('logo_image', 'notfound.png')))
        {
            $url = 'https://' . $_SERVER['SERVER_NAME'] . SWB . 'images/default/' . config('logo_image', 'notfound.png');
            $logo = '<img src="'.$url.'" style="width: 60px; height: 60px"/>';
        }

        
        // compile reservation data
        $formatedTemplate = <<<HTML
        <div style="font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; padding: 2em;">
            <div style="display: block;">
                <div style="display:">
                    {$logo}
                    <h2 style="padding-left: 0.3em;">{$libraryName}</h2>
                </div>
                <p>Member: <!--MEMBER_NAME--> (<!--MEMBER_ID-->)</p>
                <p>Reserve Date: <!--DATE--></p>
                <table style="display: block">
                    {$reserveData}
                </table>
            </div>
        </div>
        HTML;

         // message
         $this->contents = str_ireplace(array('<!--MEMBER_ID-->', '<!--MEMBER_NAME-->', '<!--DATE-->'), [$_SESSION['mid'], $_SESSION['m_name'], date('Y-m-d H:i:s')], $formatedTemplate);

         return $this;
    }
}