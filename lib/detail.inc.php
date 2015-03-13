<?php
/**
 * detail class
 * Class for document/record detail
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Some security patches by Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

// require 'content_list.inc.php';

// class detail extends content_list
class detail
{
    private $obj_db = false;
    private $record_detail = array();
    private $detail_id = 0;
    private $error = false;
    private $output_format = 'html';
    private $template = 'html';
    protected $detail_prefix = '';
    protected $detail_suffix = '';
    public $record_title;
    public $metadata;

    /**
     * Class Constructor
     *
     * @param   object  $obj_db
     * @param   integer $int_detail_id
     * @param   str     $str_output_format
     * @return  void
     */
    public function __construct($obj_db, $int_detail_id, $str_output_format = 'html')
    {
        if (!in_array($str_output_format, array('html', 'xml', 'mods', 'dc', 'json', 'json-ld'))) {
            $this->output_format = trim($str_output_format);
        } else { $this->output_format = $str_output_format; }

        $this->obj_db = $obj_db;
        $this->detail_id = $int_detail_id;
        $_sql = sprintf('SELECT b.*, l.language_name, p.publisher_name,
              pl.place_name AS `publish_place`, gmd.gmd_name, fr.frequency,
              rct.content_type,
              rmt.media_type, rcrt.carrier_type
              FROM biblio AS b
            LEFT JOIN mst_gmd AS gmd ON b.gmd_id=gmd.gmd_id
            LEFT JOIN mst_language AS l ON b.language_id=l.language_id
            LEFT JOIN mst_publisher AS p ON b.publisher_id=p.publisher_id
            LEFT JOIN mst_place AS pl ON b.publish_place_id=pl.place_id
            LEFT JOIN mst_frequency AS fr ON b.frequency_id=fr.frequency_id
            LEFT JOIN mst_content_type rct ON b.content_type_id=rct.id
            LEFT JOIN mst_media_type AS rmt ON b.media_type_id=rmt.id
            LEFT JOIN mst_carrier_type AS rcrt ON b.carrier_type_id=rcrt.id
            WHERE biblio_id=%d', $int_detail_id);
        // for debugging purpose only
        // die($_sql);
        // query the data to database
        $_det_q = $obj_db->query($_sql);
        if ($obj_db->error) {
            $this->error = $obj_db->error;
        } else {
            $this->error = false;
            $this->record_detail = $_det_q->fetch_assoc();
            // free the memory
            $_det_q->free_result();
        }
    }
    
    public function setTemplate($str_template_path)
    {
      $this->template = $str_template_path;
    }


    /**
     * Method to print out the document detail based on template
     *
     * @return  void
     */
    public function showDetail()
    {
        global $sysconf;
        if ($this->error) {
            return '<div class="error">Error Fetching data for record detail. Server return error message: '.$this->error.'</div>';
        } else {
            if ($this->output_format == 'html') {
                ob_start();
                $detail = $this->htmlOutput();
                extract($detail, EXTR_OVERWRITE);
                include $this->template;
                $detail_html = ob_get_clean();
                return $detail_html;
            } else if ($this->output_format == 'mods') {
                return $this->MODSoutput();
            } else if ($this->output_format == 'json-ld') {
                return $this->JSONLDoutput();
            } else {
                // external output function
                if (function_exists($this->output_format)) {
                    $_ext_func = $this->output_format;
                    return $_ext_func();
                }
                return null;
            }
        }
    }
    

    /**
     * Method to get file attachments information of biblio
     *
     * @param   boolean     $bool_return_raw
     *
     * @return  mix
     */
    public function getAttachments($bool_return_raw = false) {
      $items = array();
      $_output = '';
      $attachment_q = $this->obj_db->query(sprintf('SELECT att.*, f.* FROM biblio_attachment AS att
        LEFT JOIN files AS f ON att.file_id=f.file_id WHERE att.biblio_id=%d AND att.access_type=\'public\' LIMIT 20', $this->detail_id));
      if ($attachment_q->num_rows < 1) {
        $_output = '<div class="alert alert-error no-attachment">'.__('No Attachment').'</div>';
        if (!$bool_return_raw) {
          return $_output;
        }
        return false;
      } else {
        $_output .= '<ul class="attachList">';
        while ($attachment_d = $attachment_q->fetch_assoc()) {
          // check member type privileges
          if ($attachment_d['access_limit']) {
            if (utility::isMemberLogin()) {
              $allowed_mem_types = @unserialize($attachment_d['access_limit']);
              if (!in_array($_SESSION['m_member_type_id'], $allowed_mem_types)) {
                continue;
              }
            } else {
              continue;
            }
          }
          #if (preg_match('@(video|audio|image)/.+@i', $attachment_d['mime_type'])) {
          if ($attachment_d['mime_type'] == 'application/pdf') {
            $_output .= '<li class="attachment-pdf" style="list-style-image: url(images/labels/ebooks.png)" itemscope itemtype="http://schema.org/MediaObject"><a itemprop="name" property="name" class="openPopUp" title="'.$attachment_d['file_title'].'" href="./index.php?p=fstream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'&fname='.$attachment_d['file_name'].'" width="780" height="520">'.$attachment_d['file_title'].'</a>';
            $_output .= '<div class="attachment-desc" itemprop="description" property="description">'.$attachment_d['file_desc'].'</div>';
            if (trim($attachment_d['file_url']) != '') { $_output .= '<div><a href="'.trim($attachment_d['file_url']).'" itemprop="url" property="url" title="Other Resource related to this book" target="_blank">Other Resource Link</a></div>'; }
            $_output .= '</li>';
          } else if (preg_match('@(video)/.+@i', $attachment_d['mime_type'])) {
            $_output .= '<li class="attachment-audio-video" itemprop="video" property="video" itemscope itemtype="http://schema.org/VideoObject" style="list-style-image: url(images/labels/auvi.png)">'
              .'<a itemprop="name" property="name" class="openPopUp" title="'.$attachment_d['file_title'].'" href="./index.php?p=multimediastream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" width="640" height="480">'.$attachment_d['file_title'].'</a>';
            $_output .= '<div class="attachment-desc" itemprop="description" property="description">'.$attachment_d['file_desc'].'</div>';
            if (trim($attachment_d['file_url']) != '') { $_output .= '<div><a href="'.trim($attachment_d['file_url']).'" itemprop="url" property="url" title="Other Resource Link" target="_blank">Other Resource Link</a></div>'; }
            $_output .= '</li>';
          } else if (preg_match('@(audio)/.+@i', $attachment_d['mime_type'])) {
            $_output .= '<li class="attachment-audio-audio" itemprop="audio" property="audio" itemscope itemtype="http://schema.org/AudioObject" style="list-style-image: url(images/labels/auvi.png)">'
              .'<a itemprop="name" property="name" class="openPopUp" title="'.$attachment_d['file_title'].'" href="./index.php?p=multimediastream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" width="640" height="480">'.$attachment_d['file_title'].'</a>';
            $_output .= '<div class="attachment-desc" itemprop="description" property="description">'.$attachment_d['file_desc'].'</div>';
            if (trim($attachment_d['file_url']) != '') { $_output .= '<div><a href="'.trim($attachment_d['file_url']).'" itemprop="url" property="url" title="Other Resource Link" target="_blank">Other Resource Link</a></div>'; }
            $_output .= '</li>';
          } else if ($attachment_d['mime_type'] == 'text/uri-list') {
            $_output .= '<li class="attachment-url-list" style="list-style-image: url(images/labels/url.png)" itemscope itemtype="http://schema.org/MediaObject"><a itemprop="name" property="name"  href="'.trim($attachment_d['file_url']).'" title="Click to open link" target="_blank">'.$attachment_d['file_title'].'</a><div class="attachment-desc">'.$attachment_d['file_desc'].'</div></li>';
          } else if (preg_match('@(image)/.+@i', $attachment_d['mime_type'])) {
            $file_loc = REPOBS.'/'.$attachment_d['file_dir'].'/'.$attachment_d['file_name'];
            $imgsize = GetImageSize($file_loc);
            $imgwidth = $imgsize[0] + 16;
            if ($imgwidth > 600) {
              $imgwidth = 600;
            }
            $imgheight = $imgsize[1] + 16;
            if ($imgheight > 400) {
              $imgheight = 400;
            }
            $_output .= '<li class="attachment-image" style="list-style-image: url(images/labels/ebooks.png)" itemprop="image" itemscope itemtype="http://schema.org/ImageObject"><a itemprop="name" property="name" class="openPopUp" title="'.$attachment_d['file_title'].'" href="index.php?p=fstream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" width="'.$imgwidth.'" height="'.$imgheight.'">'.$attachment_d['file_title'].'</a>';
            if (trim($attachment_d['file_url']) != '') { $_output .= ' [<a href="'.trim($attachment_d['file_url']).'" itemprop="url" property="url" title="Other Resource related to this file" target="_blank" style="font-size: 90%;">Other Resource Link</a>]'; }
            $_output .= '<div class="attachment-desc" itemprop="description" property="description">'.$attachment_d['file_desc'].'</div></li>';
          } else {
            $_output .= '<li class="attachment-image" style="list-style-image: url(images/labels/ebooks.png)" itemscope itemtype="http://schema.org/MediaObject"><a itemprop="name" property="name" title="Click To View File" href="index.php?p=fstream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" target="_blank">'.$attachment_d['file_title'].'</a>';
            if (trim($attachment_d['file_url']) != '') { $_output .= ' [<a href="'.trim($attachment_d['file_url']).'" itemprop="url" property="url" title="Other Resource related to this file" target="_blank" style="font-size: 90%;">Other Resource Link</a>]'; }
            $_output .= '<div class="attachment-desc" itemprop="description" property="description">'.$attachment_d['file_desc'].'</div></li>';
          }
        }
        $_output .= '</ul>';
        if (!$bool_return_raw) {
          return $bool_return_raw;
        }
        return $items;
      }
    }


    /**
     * Method to get items/copies information of biblio
     *
     * @param   boolean     $bool_return_raw
     *
     * @return  mix
     */
    public function getItemCopy($bool_return_raw = false) {
      $items = array();
      $_output = '';
      $copy_q = $this->obj_db->query(sprintf('SELECT i.item_code, i.call_number, loc.location_name, stat.*, i.site FROM item AS i
          LEFT JOIN mst_item_status AS stat ON i.item_status_id=stat.item_status_id
          LEFT JOIN mst_location AS loc ON i.location_id=loc.location_id
          WHERE i.biblio_id=%d', $this->detail_id));
      if ($copy_q->num_rows < 1) {
          $_output = '<div class="alert alert-error no-copies">'.__('There is no item/copy for this title yet').'</div>';
          if (!$bool_return_raw) {
            return $_output;
          }
          return false;
      }
      
      $_output = '<table class="table table-bordered table-small itemList">';
      while ($copy_d = $copy_q->fetch_assoc()) {
        // check if this collection is on loan
        $loan_stat_q = $this->obj_db->query('SELECT due_date FROM loan AS l
            LEFT JOIN item AS i ON l.item_code=i.item_code
            WHERE l.item_code=\''.$copy_d['item_code'].'\' AND is_lent=1 AND is_return=0');
        $_output .= '<tr>';
        $_output .= '<td class="biblio-item-code">'.$copy_d['item_code'].'</td>';
        $_output .= '<td class="biblio-call-number">'.$copy_d['call_number'].'</td>';
        $_output .= '<td class="biblio-location">'.$copy_d['location_name'];
        if (trim($copy_d['site']) != "") {
            $_output .= ' ('.$copy_d['site'].')';
        }
        $_output .= '</td>';
        $_output .= '<td width="30%">';
        /* DEPRECATED
        $_rules = @unserialize($copy_d['rules']);
        */
        if ($loan_stat_q->num_rows > 0) {
            $loan_stat_d = $loan_stat_q->fetch_row();
            $_output .= '<span class="label label-important status-on-loan">'.__('Currently On Loan (Due on').date($sysconf['date_format'], strtotime($loan_stat_d[0])).')</span>'; //mfc
        } else if ($copy_d['no_loan']) {
            $_output .= '<span class="label label-important status-not-loan">'.__('Available but not for loan').' - '.$copy_d['item_status_name'].'</span>';
        } else {
            $_output .= '<span class="label label-info status-available">'.__('Available').(trim($copy_d['item_status_name'])?' - '.$copy_d['item_status_name']:'').'</span>';
        }
        $loan_stat_q->free_result();
        $_output .= '</td>';
        $_output .= '</tr>';
      }
      $_output .= '</table>';
      if (!$bool_return_raw) {
        return $_output;
      }
      return $items;
    }


    /**
     * Record detail output in HTML mode
     * @return  array
     *
     */
    protected function htmlOutput()
    {
        // get global configuration vars array
        global $sysconf;
        $_detail_link = SWB.'index.php?p=show_detail&id='.$this->detail_id;

        foreach ($this->record_detail as $idx => $data) {
          if ($idx == 'notes') {
            $data = nl2br($data);
          } else {
            $data = trim(strip_tags($data));
          }
          $this->record_detail[$idx] = $data;
        }

        // get title and set it to public record_title property
        $this->record_title = $this->record_detail['title'];
        $this->metadata = '<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />';
        $this->metadata .= '<meta name="DC.title" content="'.$this->record_title.'" />';
        $this->metadata .= '<meta name="DC.identifier" content="'.$this->record_detail['isbn_issn'].'" />';
        $this->metadata .= '<meta name="DC.format" content="'.$this->record_detail['gmd_name'].'" />';
        $this->metadata .= '<meta name="DC.type" content="'.$this->record_detail['gmd_name'].'" />';
        $this->metadata .= '<meta name="DC.language" content="'.$this->record_detail['language_name'].'" />';
        $this->metadata .= '<meta name="DC.publisher" content="'.$this->record_detail['publisher_name'].'" />';
        $this->metadata .= '<meta name="DC.date" content="'.$this->record_detail['publish_year'].'" />';
        $this->metadata .= '<meta name="DC.coverage" content="'.$this->record_detail['publish_place'].'" />';
        $this->metadata .= '<meta name="DC.description" content="'.strip_tags($this->record_detail['notes']).'" />';
        $this->metadata .= '<meta name="Physical Description" content="'.$this->record_detail['collation'].'" />';
        $this->metadata .= '<meta name="Statement of Responsibility" content="'.$this->record_detail['sor'].'" />';
        $this->metadata .= '<meta name="Classification" content="'.$this->record_detail['classification'].'" />';
        $this->metadata .= '<meta name="Series Title" content="'.$this->record_detail['series_title'].'" />';
        $this->metadata .= '<meta name="Edition" content="'.$this->record_detail['edition'].'" />';
        $this->metadata .= '<meta name="Call Number" content="'.$this->record_detail['call_number'].'" />';

        // check image
        if (!empty($this->record_detail['image'])) {
          if ($sysconf['tg']['type'] == 'minigalnano') {
            $this->record_detail['image'] = '<img itemprop="image" alt="'.sprintf('Image of %s', $this->record_title).'" src="./lib/minigalnano/createthumb.php?filename='.$sysconf['tg']['relative_url'].'images/docs/'.urlencode($this->record_detail['image']).'&width=200" border="0" />';
          }
        } else {
          $this->record_detail['image'] = '<img src="./images/default/image.png" alt="No image available for this title" border="0" />';
        }

        // get the authors data
        $_biblio_authors_q = $this->obj_db->query('SELECT author_name, authority_type FROM mst_author AS a'
            .' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id='.$this->detail_id.' ORDER BY level ASC');
        $authors = '';
        // authors for metadata
        $this->metadata .= '<meta name="DC.creator" content="';
        while ($data = $_biblio_authors_q->fetch_row()) {
            if ($data[1] == 'p') {
                $data[1] = "Personal Name";
            } elseif ($data[1] == 'o') {
                $data[1] = "Organizational Body";
            } elseif ($data[1] == 'c') {
                $data[1] = "Conference";
            }
            $authors .= '<a href="?author='.urlencode('"'.$data[0].'"').'&search=Search" title="'.__('Click to view others documents with this author').'">'.$data[0]."</a> - ".$data[1]."<br />";
            $this->metadata .= $data[0].'; ';
        }
        $this->metadata .= '" />';
        $this->record_detail['authors'] = $authors;
        // free memory
        $_biblio_authors_q->free_result();

        // get the topics data
        $_biblio_topics_q = $this->obj_db->query('SELECT topic FROM mst_topic AS a
            LEFT JOIN biblio_topic AS ba ON a.topic_id=ba.topic_id WHERE ba.biblio_id='.$this->detail_id);
        $topics = '';
        $this->metadata .= '<meta name="DC.subject" content="';
        while ($data = $_biblio_topics_q->fetch_row()) {
            $topics .= '<a href="?subject='.urlencode('"'.$data[0].'"').'&search=Search" title="'.__('Click to view others documents with this subject').'">'.$data[0]."</a><br />";
            $this->metadata .= $data[0].'; ';
        }
        $this->metadata .= '" />';
        $this->record_detail['subjects'] = $topics;
        // free memory
        $_biblio_topics_q->free_result();

        $this->record_detail['availability'] = $this->getItemCopy();
        $this->record_detail['file_att'] = $this->getAttachments();
        /*
        // availability
        $this->record_detail['availability'] = '<div id="itemListLoad">LOADING LIST...</div>';
        $this->record_detail['availability'] .= '<script type="text/javascript">'
            .'jQuery(document).ready(function() { jQuery.ajax({url: \''.SWB.'lib/contents/item_list.php\',
                type: \'POST\',
                data: \'id='.$this->detail_id.'&ajaxsec_user='.$sysconf['ajaxsec_user'].'&ajaxsec_passwd='.$sysconf['ajaxsec_passwd'].'\',
                success: function(ajaxRespond) { jQuery(\'#itemListLoad\').html(ajaxRespond); } }); });</script>';

        // attachments
        $this->record_detail['file_att'] = '<div id="attachListLoad">LOADING LIST...</div>';
        $this->record_detail['file_att'] .= '<script type="text/javascript">'
            .'jQuery(document).ready(function() { jQuery.ajax({url: \''.SWB.'lib/contents/attachment_list.php\',
                type: \'POST\',
                data: \'id='.$this->detail_id.'&ajaxsec_user='.$sysconf['ajaxsec_user'].'&ajaxsec_passwd='.$sysconf['ajaxsec_passwd'].'\',
                success: function(ajaxRespond) { jQuery(\'#attachListLoad\').html(ajaxRespond); } }); });</script>';
        */
        
        if ($sysconf['social_shares']) {
		  // share buttons
		  $_detail_link_encoded = urlencode('http://'.$_SERVER['SERVER_NAME'].$_detail_link);
		  $_share_btns = "\n".'<ul class="share-buttons">'.
            '<li>'.__('Share to').': </li>'.
            '<li><a href="http://www.facebook.com/sharer.php?u='.$_detail_link_encoded.'" title="Facebook" target="_blank"><img src="./images/default/fb.gif" alt="Facebook" /></a></li>'.
            '<li><a href="http://twitter.com/share?url='.$_detail_link_encoded.'&text='.urlencode($this->record_title).'" title="Twitter" target="_blank"><img src="./images/default/tw.gif" alt="Twitter" /></a></li>'.
            '<li><a href="https://plus.google.com/share?url='.$_detail_link_encoded.'" title="Google Plus" target="_blank"><img src="./images/default/gplus.gif" alt="Google" /></a></li>'.
            '<li><a href="http://www.digg.com/submit?url='.$_detail_link_encoded.'" title="Digg It" target="_blank"><img src="./images/default/digg.gif" alt="Digg" /></a></li>'.
            '<li><a href="http://reddit.com/submit?url='.$_detail_link_encoded.'&title='.urlencode($this->record_title).'" title="Reddit" target="_blank"><img src="./images/default/rdit.gif" alt="Reddit" /></a></li>'.
            '<li><a href="http://www.linkedin.com/shareArticle?mini=true&url='.$_detail_link_encoded.'" title="LinkedIn" target="_blank"><img src="./images/default/lin.gif" alt="LinkedIn" /></a></li>'.
            '<li><a href="http://www.stumbleupon.com/submit?url='.$_detail_link_encoded.'&title='.urlencode($this->record_title).'" title="Stumbleupon" target="_blank"><img src="./images/default/su.gif" alt="StumbleUpon" /></a></li>'.
            '</ul>'."\n";

          $this->record_detail['social_shares'] = $_share_btns;
		}

        return $this->record_detail;
    }


    /**
     * Record detail output in MODS (Metadata Object Description Schema) XML mode
     * @return  array
     *
     */
    public function MODSoutput()
    {
        // get global configuration vars array
        global $sysconf;

        // convert to htmlentities
        foreach ($this->record_detail as $_field => $_value) {
            if (is_string($_value)) {
                $this->record_detail[$_field] = preg_replace_callback('/&([a-zA-Z][a-zA-Z0-9]+);/S',
                  'utility::convertXMLentities', htmlspecialchars(trim($_value)));
            }
        }

        // set prefix and suffix
        $this->detail_prefix = '<modsCollection xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.loc.gov/mods/v3" xmlns:slims="http://slims.web.id" xsi:schemaLocation="http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-3.xsd">'."\n";
        $this->detail_suffix = '</modsCollection>';

        $_xml_output = '<mods version="3.3" ID="'.$this->detail_id.'">'."\n";

        // parse title
        $_title_sub = '';
        $_title_statement_resp = '';
        if (stripos($this->record_detail['title'], ':') !== false) {
            $_title_main = trim(substr_replace($this->record_detail['title'], '', stripos($this->record_detail['title'], ':')+1));
            $_title_sub = trim(substr_replace($this->record_detail['title'], '', 0, stripos($this->record_detail['title'], ':')+1));
        } else if (stripos($this->record_detail['title'], '/') !== false) {
            $_title_statement_resp = trim(substr_replace($this->record_detail['title'], '', stripos($this->record_detail['title'], '/')+1));
        } else {
            $_title_main = trim($this->record_detail['title']);
        }

        $_xml_output .= '<titleInfo>'."\n".'<title>'.$_title_main.'</title>'."\n";
        if ($_title_sub) {
            $_xml_output .= '<subTitle>'.$_title_sub.'</subTitle>'."\n";
        }
        $_xml_output .= '</titleInfo>'."\n";

        // personal name
        // get the authors data
        $_biblio_authors_q = $this->obj_db->query('SELECT a.*,ba.level FROM mst_author AS a'
            .' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id='.$this->detail_id);
        while ($_auth_d = $_biblio_authors_q->fetch_assoc()) {
            $_xml_output .= '<name type="'.$sysconf['authority_type'][$_auth_d['authority_type']].'" authority="'.$_auth_d['auth_list'].'">'."\n"
              .'<namePart>'.$_auth_d['author_name'].'</namePart>'."\n"
              .'<role><roleTerm type="text">'.$sysconf['authority_level'][$_auth_d['level']].'</roleTerm></role>'."\n"
            .'</name>'."\n";
        }
        $_biblio_authors_q->free_result();

        // resources type
        $_xml_output .= '<typeOfResource manuscript="yes" collection="yes">mixed material</typeOfResource>'."\n";

        // gmd
        $_xml_output .= '<genre authority="marcgt">bibliography</genre>'."\n";

        // imprint/publication data
        $_xml_output .= '<originInfo>'."\n";
        $_xml_output .= '<place><placeTerm type="text">'.$this->record_detail['publish_place'].'</placeTerm></place>'."\n"
          .'<publisher>'.$this->record_detail['publisher_name'].'</publisher>'."\n"
          .'<dateIssued>'.$this->record_detail['publish_year'].'</dateIssued>'."\n";
        if ((integer)$this->record_detail['frequency_id'] > 0) {
            $_xml_output .= '<issuance>continuing</issuance>'."\n";
            $_xml_output .= '<frequency>'.$this->record_detail['frequency'].'</frequency>'."\n";
        } else {
            $_xml_output .= '<issuance>monographic</issuance>'."\n";
        }
        $_xml_output .= '<edition>'.$this->record_detail['edition'].'</edition>'."\n";
        $_xml_output .= '</originInfo>'."\n";

        // language
        $_xml_output .= '<language>'."\n";
        $_xml_output .= '<languageTerm type="code">'.$this->record_detail['language_id'].'</languageTerm>'."\n";
        $_xml_output .= '<languageTerm type="text">'.$this->record_detail['language_name'].'</languageTerm>'."\n";
        $_xml_output .= '</language>'."\n";

        // Physical Description/Collation
        $_xml_output .= '<physicalDescription>'."\n";
        $_xml_output .= '<form authority="gmd">'.$this->record_detail['gmd_name'].'</form>'."\n";
        $_xml_output .= '<extent>'.$this->record_detail['collation'].'</extent>'."\n";
        $_xml_output .= '</physicalDescription>'."\n";

        // Series title
        if ($this->record_detail['series_title']) {
            $_xml_output .= '<relatedItem type="series">'."\n";
            $_xml_output .= '<titleInfo>'."\n";
            $_xml_output .= '<title>'.$this->record_detail['series_title'].'</title>'."\n";
            $_xml_output .= '</titleInfo>'."\n";
            $_xml_output .= '</relatedItem>'."\n";
        }

        // Note
        $_xml_output .= '<note>'.$this->record_detail['notes'].'</note>'."\n";
        if ($_title_statement_resp) {
            $_xml_output .= '<note type="statement of responsibility">'.$_title_statement_resp.'</note>';
        }

        // subject/topic
        $_biblio_topics_q = $this->obj_db->query('SELECT t.topic, t.topic_type, t.auth_list, bt.level FROM mst_topic AS t
            LEFT JOIN biblio_topic AS bt ON t.topic_id=bt.topic_id WHERE bt.biblio_id='.$this->detail_id.' ORDER BY t.auth_list');
        while ($_topic_d = $_biblio_topics_q->fetch_assoc()) {
            $_subject_type = strtolower($sysconf['subject_type'][$_topic_d['topic_type']]);
            $_xml_output .= '<subject authority="'.$_topic_d['auth_list'].'">';
            $_xml_output .= '<'.$_subject_type.'>'.$_topic_d['topic'].'</'.$_subject_type.'>';
            $_xml_output .= '</subject>'."\n";
        }

        // classification
        $_xml_output .= '<classification>'.$this->record_detail['classification'].'</classification>';

        // ISBN/ISSN
        $_xml_output .= '<identifier type="isbn">'.str_replace(array('-', ' '), '', $this->record_detail['isbn_issn']).'</identifier>';


        // Location and Copies information
        $_xml_output .= '<location>'."\n";
        $_xml_output .= '<physicalLocation>'.$sysconf['library_name'].' '.$sysconf['library_subname'].'</physicalLocation>'."\n";
        $_xml_output .= '<shelfLocator>'.$this->record_detail['call_number'].'</shelfLocator>'."\n";
        $_copy_q = $this->obj_db->query('SELECT i.item_code, i.call_number, stat.item_status_name, loc.location_name, stat.rules, i.site FROM item AS i '
            .'LEFT JOIN mst_item_status AS stat ON i.item_status_id=stat.item_status_id '
            .'LEFT JOIN mst_location AS loc ON i.location_id=loc.location_id '
            .'WHERE i.biblio_id='.$this->detail_id);
        if ($_copy_q->num_rows > 0) {
            $_xml_output .= '<holdingSimple>'."\n";
            while ($_copy_d = $_copy_q->fetch_assoc()) {
                $_xml_output .= '<copyInformation>'."\n";
                $_xml_output .= '<numerationAndChronology type="1">'.$_copy_d['item_code'].'</numerationAndChronology>'."\n";
                $_xml_output .= '<sublocation>'.$_copy_d['location_name'].( $_copy_d['site']?' ('.$_copy_d['site'].')':'' ).'</sublocation>'."\n";
                $_xml_output .= '<shelfLocator>'.$_copy_d['call_number'].'</shelfLocator>'."\n";
                $_xml_output .= '</copyInformation>'."\n";
            }
            $_xml_output .= '</holdingSimple>'."\n";
        }
        $_xml_output .= '</location>'."\n";

        // digital files
        $attachment_q = $this->obj_db->query('SELECT att.*, f.* FROM biblio_attachment AS att
            LEFT JOIN files AS f ON att.file_id=f.file_id WHERE att.biblio_id='.$this->detail_id.' AND att.access_type=\'public\' LIMIT 20');
        if ($attachment_q->num_rows > 0) {
            $_xml_output .= '<slims:digitals>'."\n";
            while ($attachment_d = $attachment_q->fetch_assoc()) {
                // check member type privileges
                if ($attachment_d['access_limit']) { continue; }
                $_xml_output .= '<slims:digital_item id="'.$attachment_d['file_id'].'" url="'.trim($attachment_d['file_url']).'" '
                    .'path="'.htmlentities($attachment_d['file_dir'].'/'.$attachment_d['file_name']).'" mimetype="'.$attachment_d['mime_type'].'">';
                $_xml_output .= htmlentities($attachment_d['file_title']);
                $_xml_output .= '</slims:digital_item>'."\n";
            }
            $_xml_output .= '</slims:digitals>';
        }

        // image
        if (!empty($this->record_detail['image'])) {
          $_image = urlencode($this->record_detail['image']);
			    $_xml_output .= '<slims:image>'.htmlentities($_image).'</slims:image>'."\n";
        }

        // record info
        $_xml_output .= '<recordInfo>'."\n";
        $_xml_output .= '<recordIdentifier>'.$this->detail_id.'</recordIdentifier>'."\n";
        $_xml_output .= '<recordCreationDate encoding="w3cdtf">'.$this->record_detail['input_date'].'</recordCreationDate>'."\n";
        $_xml_output .= '<recordChangeDate encoding="w3cdtf">'.$this->record_detail['last_update'].'</recordChangeDate>'."\n";
        $_xml_output .= '<recordOrigin>machine generated</recordOrigin>'."\n";
        $_xml_output .= '</recordInfo>';

        $_xml_output .= '</mods>';

        return $_xml_output;
    }


    /**
     * Record detail output in Dublin Core XML
     * @return  string
     *
     */
    public function DublinCoreOutput()
    {
        // get global configuration vars array
        global $sysconf;

        // set prefix and suffix
        $this->detail_prefix = '';
        $this->detail_suffix = '';

        $_xml_output = '';

        // parse title
        $_title_sub = '';
        $_title_statement_resp = '';
        if (stripos($this->record_detail['title'], ':') !== false) {
            $_title_main = trim(substr_replace($this->record_detail['title'], '', stripos($this->record_detail['title'], ':')+1));
            $_title_sub = trim(substr_replace($this->record_detail['title'], '', 0, stripos($this->record_detail['title'], ':')+1));
        } else if (stripos($this->record_detail['title'], '/') !== false) {
            $_title_statement_resp = trim(substr_replace($this->record_detail['title'], '', stripos($this->record_detail['title'], '/')+1));
        } else {
            $_title_main = trim($this->record_detail['title']);
        }

        $_xml_output .= '<dc:title><![CDATA['.$_title_main;
        if ($_title_sub) {
            $_xml_output .= ' '.$_title_sub;
        }
        $_xml_output .= ']]></dc:title>'."\n";

        // get the authors data
        $_biblio_authors_q = $this->obj_db->query('SELECT a.*,ba.level FROM mst_author AS a'
            .' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id='.$this->detail_id);
        while ($_auth_d = $_biblio_authors_q->fetch_assoc()) {
            $_xml_output .= '<dc:creator><![CDATA['.$_auth_d['author_name'].']]></dc:creator>'."\n";
        }
        $_biblio_authors_q->free_result();

        // imprint/publication data
        $_xml_output .= '<dc:publisher><![CDATA['.$this->record_detail['publisher_name'].']]></dc:publisher>'."\n";

        // date
        $_xml_output .= '<dc:date><![CDATA['.$this->record_detail['publish_year'].']]></dc:date>'."\n";

        // edition
        $_xml_output .= '<dc:hasVersion><![CDATA['.$this->record_detail['edition'].']]></dc:hasVersion>'."\n";

        // language
        $_xml_output .= '<dc:language><![CDATA['.$this->record_detail['language_name'].']]></dc:language>'."\n";

        // Physical Description/Collation
        $_xml_output .= '<dc:medium><![CDATA['.$this->record_detail['gmd_name'].']]></dc:medium>'."\n";
        $_xml_output .= '<dc:format><![CDATA['.$this->record_detail['gmd_name'].']]></dc:format>'."\n";
        if ((integer)$this->record_detail['frequency_id'] > 0) {
            $_xml_output .= '<dc:format>Serial</dc:format>'."\n";
        }
        $_xml_output .= '<dc:extent><![CDATA['.$this->record_detail['collation'].']]></dc:extent>'."\n";

        // Series title
        if ($this->record_detail['series_title']) {
          $_xml_output .= '<dc:isPartOf><![CDATA['.$this->record_detail['series_title'].']]></dc:isPartOf>'."\n";
        }

        // Note
        $_xml_output .= '<dc:description><![CDATA['.$this->record_detail['notes'].']]></dc:description>'."\n";
        $_xml_output .= '<dc:abstract><![CDATA['.$this->record_detail['notes'].']]></dc:abstract>'."\n";

        // subject/topic
        $_biblio_topics_q = $this->obj_db->query('SELECT t.topic, t.topic_type, t.auth_list, bt.level FROM mst_topic AS t
          LEFT JOIN biblio_topic AS bt ON t.topic_id=bt.topic_id WHERE bt.biblio_id='.$this->detail_id.' ORDER BY t.auth_list');
        while ($_topic_d = $_biblio_topics_q->fetch_assoc()) {
          $_xml_output .= '<dc:subject><![CDATA['.$_topic_d['topic'].']]></dc:subject>'."\n";
        }

        // classification
        $_xml_output .= '<dc:subject><![CDATA['.$this->record_detail['classification'].']]></dc:subject>';

        // Permalink
        $_xml_output .= '<dc:identifier><![CDATA[http://'.$_SERVER['SERVER_NAME'].SWB.'index.php?p=show_detail&id='.$this->detail_id.']]></dc:identifier>';

        // ISBN/ISSN
        $_xml_output .= '<dc:identifier><![CDATA['.str_replace(array('-', ' '), '', $this->record_detail['isbn_issn']).']]></dc:identifier>';

        // Call Number
        $_xml_output .= '<dc:identifier><![CDATA['.$this->record_detail['call_number'].']]></dc:identifier>'."\n";

        $_copy_q = $this->obj_db->query('SELECT i.item_code, i.call_number, stat.item_status_name, loc.location_name, stat.rules, i.site FROM item AS i '
            .'LEFT JOIN mst_item_status AS stat ON i.item_status_id=stat.item_status_id '
            .'LEFT JOIN mst_location AS loc ON i.location_id=loc.location_id '
            .'WHERE i.biblio_id='.$this->detail_id);
        if ($_copy_q->num_rows > 0) {
            while ($_copy_d = $_copy_q->fetch_assoc()) {
                $_xml_output .= '<dc:hasPart><![CDATA['.$_copy_d['item_code'].']]></dc:hasPart>'."\n";
            }
        }

        // digital files
        $attachment_q = $this->obj_db->query('SELECT att.*, f.* FROM biblio_attachment AS att
            LEFT JOIN files AS f ON att.file_id=f.file_id WHERE att.biblio_id='.$this->detail_id.' AND att.access_type=\'public\' LIMIT 20');
        if ($attachment_q->num_rows > 0) {
          while ($attachment_d = $attachment_q->fetch_assoc()) {
              $_xml_output .= '<dc:relation><![CDATA[';
              // check member type privileges
              if ($attachment_d['access_limit']) { continue; }
              $_xml_output .= trim($attachment_d['file_title']);
              $_xml_output .= ']]></dc:relation>'."\n";
          }
        }

        // image
        if (!empty($this->record_detail['image'])) {
          $_image = urlencode($this->record_detail['image']);
	  $_xml_output .= '<dc:relation><![CDATA['.htmlentities($_image).']]></dc:relation>'."\n";
        }

        return $_xml_output;
    }


    /**
     * Record detail output in JSON-LD (JSON-Linked Data)
     * @return  string
     *
     */
    public function JSONLDoutput() {
      // get global configuration vars array
      global $sysconf;
      
      // set prefix and suffix
      $this->detail_prefix = '';
      $this->detail_suffix = '';
      
      $jsonld['@context'] = 'http://schema.org';
      $jsonld['@type'] = 'Book';
      
      // parse title
      $_title_sub = '';
      $_title_statement_resp = '';
      if (stripos($this->record_detail['title'], ':') !== false) {
          $_title_main = trim(substr_replace($this->record_detail['title'], '', stripos($this->record_detail['title'], ':')+1));
          $_title_sub = trim(substr_replace($this->record_detail['title'], '', 0, stripos($this->record_detail['title'], ':')+1));
      } else if (stripos($this->record_detail['title'], '/') !== false) {
          $_title_statement_resp = trim(substr_replace($this->record_detail['title'], '', stripos($this->record_detail['title'], '/')+1));
      } else {
          $_title_main = trim($this->record_detail['title']);
      }
      
      $jsonld['name'] = $_title_main;
      if ($_title_sub) {
        $jsonld['alternativeHeadline'] = $_title_sub;
      }
      
      // get the authors data
      $jsonld['author']['@type'] = 'Person'; 
      $_biblio_authors_q = $this->obj_db->query('SELECT a.*,ba.level FROM mst_author AS a'
          .' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id='.$this->detail_id);
      while ($_auth_d = $_biblio_authors_q->fetch_assoc()) {
          $jsonld['author']['name'][] = $_auth_d['author_name'];
      }
      $_biblio_authors_q->free_result();
      
      // imprint/publication data
      $jsonld['publisher']['@type'] = 'Organization';
      $jsonld['publisher']['name'] = $this->record_detail['publisher_name'];
      
      // date
      $jsonld['dateCreated'] = $this->record_detail['publish_year'];
      
      // edition
      $jsonld['version'] = $this->record_detail['edition'];
      
      // language
      $jsonld['inLanguage'] = $this->record_detail['language_name'];
      
      // Physical Description/Collation
      $jsonld['bookFormat'] = $this->record_detail['gmd_name'];
      
      // collation
      $jsonld['numberOfPages'] = $this->record_detail['collation'];
      
      // Series title
      if ($this->record_detail['series_title']) {
        $jsonld['alternativeHeadline'] = $this->record_detail['series_title'];
      }
      
      // Note
      $jsonld['description'] = $this->record_detail['notes'];
      
      // subject/topic
      $jsonld['keywords'] = '';
      $_biblio_topics_q = $this->obj_db->query('SELECT t.topic, t.topic_type, t.auth_list, bt.level FROM mst_topic AS t
        LEFT JOIN biblio_topic AS bt ON t.topic_id=bt.topic_id WHERE bt.biblio_id='.$this->detail_id.' ORDER BY t.auth_list');
      while ($_topic_d = $_biblio_topics_q->fetch_assoc()) {
        $jsonld['keywords'] .= $_topic_d['topic'].' ';
      }
      
      // classification
      $jsonld['keywords'] .= $this->record_detail['classification'];
      
      // Permalink
      $jsonld['url'] = 'http://'.$_SERVER['SERVER_NAME'].SWB.'index.php?p=show_detail&id='.$this->detail_id;
      
      // ISBN/ISSN
      $jsonld['isbn'] = str_replace(array('-', ' '), '', $this->record_detail['isbn_issn']);
      
      // digital files
      $jsonld['associatedMedia']['@type'] = 'MediaObject';
      $attachment_q = $this->obj_db->query('SELECT att.*, f.* FROM biblio_attachment AS att
          LEFT JOIN files AS f ON att.file_id=f.file_id WHERE att.biblio_id='.$this->detail_id.' AND att.access_type=\'public\' LIMIT 20');
      if ($attachment_q->num_rows > 0) {
        while ($attachment_d = $attachment_q->fetch_assoc()) {
            $_xml_output .= '<dc:relation><![CDATA[';
            // check member type privileges
            if ($attachment_d['access_limit']) { continue; }
            $jsonld['associatedMedia']['name'] = trim($attachment_d['file_title']);
        }
      }
      
      // image
      if (!empty($this->record_detail['image'])) {
        $_image = urlencode($this->record_detail['image']);
	  	  $jsonld['image'] = 'http://'.$_SERVER['SERVER_NAME'].IMGBS.'docs/'.urlencode($_image);
      }
      
      return json_encode($jsonld);
    }


    /**
     * Get Record detail prefix
     */
    public function getPrefix()
    {
        return $this->detail_prefix;
    }


    /**
     * Get Record detail suffix
     */
    public function getSuffix()
    {
        return $this->detail_suffix;
    }
}
