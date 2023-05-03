<?php
/**
 * detail class
 * Class for document/record detail
 *
 * Copyright (C) 2015  Arie Nugraha (dicarve@yahoo.com)
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
    private $db = false;
    private $biblio = false;
    private $record_detail = array();
    private $detail_id = 0;
    private $error = false;
    private $output_format = 'html';
    private $template = 'html';
    private $total_item_available = 0;
    protected $detail_prefix = '';
    protected $detail_suffix = '';
    public $record_title;
    public $metadata;
    public $image_src;
    public $notes;
    public $subjects;

    /**
     * Class Constructor
     *
     * @param   object  $obj_db
     * @param   integer $int_detail_id
     * @param   str     $str_output_format
     * @return  void
     */
    public function __construct($dbs, $int_detail_id, $str_output_format = 'html')
    {
        if (!in_array($str_output_format, array('html', 'xml', 'mods', 'dc', 'json', 'json-ld', 'marc'))) {
            $this->output_format = trim($str_output_format);
        } else { $this->output_format = $str_output_format; }
        $this->db = $dbs;
        $this->detail_id = $int_detail_id;
        $this->biblio = new Biblio($this->db, $int_detail_id);
        $this->record_detail = $this->biblio->detail();
        $this->error = $this->biblio->getError();
        if (isset($this->record_detail['title'])) {
          $this->record_title = $this->record_detail['title'];
          $this->notes = $this->record_detail['notes'];
          $this->subjects = $this->record_detail['subjects'];
        } else if (!$this->error) {
          $this->error = 'Data not found!';
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
            return '<div class="error alert alert-error">Error Fetching data for record detail. Server return error message: '.$this->error.'</div>';
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
    public function getAttachments() {
        $_output = '';
        $_output .= '<ul class="attachList list-unstyled">';
        if (!$this->record_detail['attachments']) {
          return false;
        }
        foreach ($this->record_detail['attachments'] as $attachment_d) {
          // Restricted attachment check
          if (!is_null($attachment_d['access_limit'])) {
              // need member login access
              if (!utility::isMemberLogin()) {
                $_output .= '<li class="attachment-locked" style="list-style-image: url(images/labels/locked.png)"><a class="font-italic" href="index.php?p=member&destination=' . (\SLiMS\Url::getSlimsFullUri('#attachment')->encode()) . '">'.__('Please login to see this attachment').'</a></li>';
                continue;
              // member type access check 
              } else if (utility::isMemberLogin() && !in_array($_SESSION['m_member_type_id'], unserialize($attachment_d['access_limit']))) {
                $_output .= '<li class="attachment-locked cursor-pointer" style="list-style-image: url(images/labels/locked.png)">'. __('You have no authorization to download this file.') . '</li>';
                continue;
              }
          }

          if ($attachment_d['mime_type'] == 'application/pdf') {
            $_output .= '<li class="attachment-pdf" style="list-style-image: url(images/labels/ebooks.png)" itemscope itemtype="http://schema.org/MediaObject"><a itemprop="name" property="name" '.(utility::isMobileBrowser() ? 'target="_blank"' : 'class="openPopUp"').' title="'.$attachment_d['file_title'].'" href="./index.php?p=fstream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" width="780" height="520">'.$attachment_d['file_title'].'</a>';
            $_output .= '<div class="attachment-desc" itemprop="description" property="description">'.$attachment_d['file_desc'].'</div>';
            if (trim($attachment_d['file_url']) != '') { $_output .= '<div><a href="'.trim($attachment_d['file_url']).'" itemprop="url" property="url" title="Other Resource related to this book" target="_blank">Other Resource Link</a></div>'; }
            $_output .= '</li>';
          } else if (preg_match('@(video)/.+@i', $attachment_d['mime_type'])) {
              switch ($attachment_d['placement']) {
                  case 'embed':
                      $_output .= '<li style="list-style: none">'.$this->embed('./index.php?p=multimediastream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id']).'</li>';
                      break;
                  case 'popup':
                      $_output .= '<li class="attachment-audio-video" itemprop="video" property="video" itemscope itemtype="http://schema.org/VideoObject" style="list-style-image: url(images/labels/auvi.png)">'
                          .'<a itemprop="name" property="name" class="openPopUp" title="'.$attachment_d['file_title'].'" href="./index.php?p=multimediastream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" width="640" height="480">'.$attachment_d['file_title'].'</a>';
                      $_output .= '<div class="attachment-desc" itemprop="description" property="description">'.$attachment_d['file_desc'].'</div>';
                      break;
                  default:
                      $_output .= '<li class="attachment-audio-video" itemprop="video" property="video" itemscope itemtype="http://schema.org/VideoObject" style="list-style-image: url(images/labels/auvi.png)">'
                          .'<a itemprop="name" property="name" title="'.$attachment_d['file_title'].'" href="./index.php?p=multimediastream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" target="_blank">'.$attachment_d['file_title'].'</a>';
                      $_output .= '<div class="attachment-desc" itemprop="description" property="description">'.$attachment_d['file_desc'].'</div>';
              }
            if (trim($attachment_d['file_url']) != '') { $_output .= '<div><a href="'.trim($attachment_d['file_url']).'" itemprop="url" property="url" title="Other Resource Link" target="_blank">Other Resource Link</a></div>'; }
            $_output .= '</li>';
          } else if (preg_match('@(audio)/.+@i', $attachment_d['mime_type'])) {
            $_output .= '<li class="attachment-audio-audio" itemprop="audio" property="audio" itemscope itemtype="http://schema.org/AudioObject" style="list-style-image: url(images/labels/auvi.png)">'
              .'<a itemprop="name" property="name" class="openPopUp" title="'.$attachment_d['file_title'].'" href="./index.php?p=multimediastream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" width="640" height="480">'.$attachment_d['file_title'].'</a>';
            $_output .= '<div class="attachment-desc" itemprop="description" property="description">'.$attachment_d['file_desc'].'</div>';
            if (trim($attachment_d['file_url']) != '') { $_output .= '<div><a href="'.trim($attachment_d['file_url']).'" itemprop="url" property="url" title="Other Resource Link" target="_blank">Other Resource Link</a></div>'; }
            $_output .= '</li>';
          } else if ($attachment_d['mime_type'] == 'text/uri-list') {
              switch ($attachment_d['placement']) {
                  case 'embed':
                      $_output .= '<li style="list-style: none">'.$this->embed($attachment_d['file_url']).'</li>';
                      break;
                  case 'popup':
                      $_output .= '<li class="attachment-url-list" style="list-style-image: url(images/labels/url.png)" itemscope itemtype="http://schema.org/MediaObject"><a itemprop="name" property="name"  href="'.trim($attachment_d['file_url']).'" title="Click to open link" class="openPopUp" width="560" height="315">'.$attachment_d['file_title'].'</a><div class="attachment-desc">'.$attachment_d['file_desc'].'</div></li>';
                      break;
                  default:
                      $_output .= '<li class="attachment-url-list" style="list-style-image: url(images/labels/url.png)" itemscope itemtype="http://schema.org/MediaObject"><a itemprop="name" property="name"  href="'.trim($attachment_d['file_url']).'" title="Click to open link" target="_blank">'.$attachment_d['file_title'].'</a><div class="attachment-desc">'.$attachment_d['file_desc'].'</div></li>';
              }
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
        return $_output;
    }


    function embed($url) {
        return <<<HTML
<div class="embed-responsive embed-responsive-16by9">
  <iframe class="embed-responsive-item" width="560" height="315" src="{$url}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
</div>
HTML;

    }


    /**
     * Method to get items/copies information of biblio
     *
     *
     * @return  string
     */
    public function getItemCopy() {
      global $sysconf;
      $_output = '';
      $copies = $this->record_detail['copies'];
      if (!$copies) {
        return false;
      }
      $_output = '<div class="flex flex-col">';
      foreach ($copies as $copy_d) {
        // check if this collection is on loan
        $loan_stat_q = $this->db->query('SELECT due_date FROM loan AS l
            LEFT JOIN item AS i ON l.item_code=i.item_code
            WHERE l.item_code=\''.$copy_d['item_code'].'\' AND is_lent=1 AND is_return=0');

        if ($loan_stat_q->num_rows > 0) {
            $loan_stat_d = $loan_stat_q->fetch_row();
            list($avail_class, $avail_status) = ['item-onloan', __('Currently On Loan (Due on ').date($sysconf['date_format'], strtotime($loan_stat_d[0])).')'];
        } else if ($copy_d['no_loan']) {
            list($avail_class, $avail_status) = ['item-notforloan', __('Available but not for loan').' - '.$copy_d['item_status_name']];
        } else {
            $this->total_item_available++;
            list($avail_class, $avail_status) = ['item-available', __('Available').(trim($copy_d['item_status_name']??'')?' - '.$copy_d['item_status_name']:'')];
        }

        extract($copy_d);
        $location_name = empty($location_name) ? __('Location name is not set') : $location_name;

        if (trim($copy_d['site']) != "") {
          $location_name .= ' ('.$copy_d['site'].')';
        }

        $call_number = empty($call_number) ? __('Location name is not set') : $call_number;
        $_output .= <<<HTML
            <div class="w-100 flex flex-row w-full">
                <div class="col-7 flex flex-row border border-gray-300 p-3">
                    <div>#</div>
                    <div class="mx-2">
                      <span class="block mx-auto">{$location_name}</span>
                      <small class="text-sm text-muted">{$call_number}</small>
                    </div>
                </div>
                <div class="col-2 border border-gray-300 p-3">
                    {$item_code}
                </div>
                <div class="col-3 border border-gray-300 p-3">
                  <b class="text-sm availability-item {$avail_class}">{$avail_status}</b>
                </div>
            </div>
        HTML;

        $loan_stat_q->free_result();
      }
      $_output .= '</div>';
      return $_output;
    }


    /**
     * Method to get other version of biblio
     *
     * @return  string
     */
    public function getRelatedBiblio() {
        $_output = '<table class="table table-bordered table-small itemList">';
        $_output .= '<tr>';
        $_output .= '<th>'.__('Title').'</th>';
        $_output .= '<th>'.__('Edition').'</th>';
        $_output .= '<th>'.__('Language').'</th>';
        $_output .= '</tr>';
        // get parent id
        $parent_q = $this->db->query(sprintf('SELECT b.biblio_id, title, edition, language_id
            FROM biblio_relation AS br INNER JOIN biblio AS b ON br.biblio_id=b.biblio_id
            WHERE rel_biblio_id=%d', $this->detail_id));
        $parent_d = $parent_q->fetch_assoc();
        if ($parent_d) {
            $_output .= '<tr>';
            $_output .= '<td class="biblio-title relation"><a href="'.SWB.'index.php?p=show_detail&id='.$parent_d['biblio_id'].'">'.$parent_d['title'].'</a></td>';
            $_output .= '<td class="biblio-edition relation">'.$parent_d['edition'].'</td>';
            $_output .= '<td class="biblio-language relation">'.$parent_d['language_id'].'</td>';
            $_output .= '</tr>';
        }
        // check related data
        $rel_q = $this->db->query(sprintf('SELECT b.biblio_id, title, edition, language_id FROM biblio_relation AS br
          INNER JOIN biblio AS b ON br.rel_biblio_id=b.biblio_id
          WHERE br.biblio_id IN (SELECT biblio_id FROM biblio_relation WHERE rel_biblio_id=%d) OR br.biblio_id=%d',
          $this->detail_id, $this->detail_id));

        if ($rel_q->num_rows < 1) {
            return null;
        }

        while ($rel_d = $rel_q->fetch_assoc()) {
            if ($rel_d['biblio_id'] == $this->detail_id) {
                continue;
            }
            $_output .= '<tr>';
            $_output .= '<td class="biblio-title relation"><a href="'.SWB.'index.php?p=show_detail&id='.$rel_d['biblio_id'].'">'.$rel_d['title'].'</a></td>';
            $_output .= '<td class="biblio-edition relation">'.$rel_d['edition'].'</td>';
            $_output .= '<td class="biblio-language relation">'.$rel_d['language_id'].'</td>';
            $_output .= '</tr>';
        }

        $_output .= '</table>';
        return $_output;
    }

    /**
     * Method to get biblio custom data
     *
     * @return  array
     */
    public function getBiblioCustom() {
      $_return = array();
      // include custom fields file
      if (file_exists(MDLBS.'bibliography/custom_fields.inc.php')) {
        include MDLBS.'bibliography/custom_fields.inc.php';
      }
      $columns = '';
      if (isset($biblio_custom_fields)) {
        foreach ($biblio_custom_fields as $custom_field) {
          if (isset($custom_field['is_public']) && $custom_field['is_public'] == '1')
            $columns .= $custom_field['dbfield'] . ', ';
        }
        if ($columns !== '') {
          $columns = substr($columns, 0, -2);
        }
      } else {
        $columns = '*';
      }

      $query = $this->db->query(sprintf("SELECT %s FROM biblio_custom WHERE biblio_id=%d", $columns, $this->detail_id));
      if ($query) {
        $data = $query->fetch_assoc();
        if (isset($biblio_custom_fields)) {
          foreach ($biblio_custom_fields as $custom_field) {
            if (isset($custom_field['is_public']) && $custom_field['is_public'] == '1' && isset($data[$custom_field['dbfield']])) {

              $data_field = unserialize($custom_field['data']??'');
              $data_record  = $data[$custom_field['dbfield']];

              switch ($custom_field['type']) {
                case 'dropdown':
                case 'choice':
                  $value = end($data_field[$data_record]);
                  break;
                case 'checklist':
                  $data_record = unserialize($data_record);
                  foreach ($data_record as $key => $val) {
                    if(isset($data_field[$val])){
                    $arr[] = end($data_field[$val]);
                    }
                  }
                  // convert array to string
                  $value = implode(' -- ',$arr);
                  break;
                default:
                  $value = $data[$custom_field['dbfield']];
                  break;
              }

              $_return[] = array(
                'label' => $custom_field['label'],
                'value' => $value
              );
            }
          }
        }
      }

      return $_return;
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
            $data = nl2br($data??'');
          } else {
            if (is_string($data)) {
              $data = trim(strip_tags($data));
            }
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

        // get the authors data
        $authors = '';
        $data = array();
        // authors for metadata
        $this->metadata .= '<meta name="DC.creator" content="';
        foreach ($this->record_detail['authors'] as $data) {
          $authors .= '<a href="?author='.urlencode('"'.$data['author_name'].'"').'&search=Search" title="'.__('Click to view others documents with this author').'">'.$data['author_name']."</a> - ".__($data['authority_type'])."<br />";
          $this->metadata .= $data['author_name'].'; ';
        }
        $this->metadata .= '" />';
        $this->record_detail['authors'] = $authors;

        // get the topics data
        $topics = '';
        $data = array();
        $this->metadata .= '<meta name="DC.subject" content="';
        foreach ($this->record_detail['subjects'] as $data) {
            $topics .= '<a href="?subject='.urlencode('"'.$data['topic'].'"').'&search=Search" title="'.__('Click to view others documents with this subject').'">'.$data['topic']."</a><br />";
            $this->metadata .= $data['topic'].'; ';
        }
        $this->metadata .= '" />';
        $this->record_detail['subjects'] = $topics;

        $this->record_detail['availability'] = $this->getItemCopy();
        $this->record_detail['file_att'] = $this->getAttachments();
        $this->record_detail['related'] = $this->getRelatedBiblio();
        $this->record_detail['biblio_custom'] = $this->getBiblioCustom();

        // check image
        if (!empty($this->record_detail['image'])) {
          if ($sysconf['tg']['type'] == 'minigalnano') {
            $isItemAvailable = $this->total_item_available > 0;
            $this->record_detail['image_src'] = 'lib/minigalnano/createthumb.php?filename=images/docs/'.urlencode($this->record_detail['image']).'&amp;width=200';
            $this->record_detail['image'] = '<img class="' . ($isItemAvailable ? 'available' : 'not-available') . '" title="' . ($isItemAvailable ? $this->record_title : __('Items is not available')) . '" loading="lazy" itemprop="image" alt="'.sprintf('Image of %s', $this->record_title).'" src="./'.$this->record_detail['image_src'].'" border="0" alt="'.$this->record_detail['title'].'" />';
          }
        } else {
          $this->record_detail['image_src'] = "images/default/image.png";
          $this->record_detail['image'] = '<img src="./'.$this->record_detail['image_src'].'" alt="No image available for this title" border="0" alt="'.$this->record_detail['title'].'" />';
        }

        // get image source
        $this->image_src = $this->record_detail['image_src'];

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
        $mods_version = '3.3';
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);

        // set prefix and suffix
        $this->detail_prefix = '<modsCollection xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.loc.gov/mods/v3" xmlns:slims="http://slims.web.id" xsi:schemaLocation="http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-3.xsd">'."\n";
        $this->detail_suffix = '</modsCollection>';

        // $_xml_output = '<mods version="3.3" ID="'.$this->detail_id.'">'."\n";
        // MODS main tag
        $xml->startElement('mods');
        $xml->writeAttribute('version', $mods_version);
        $xml->writeAttribute('id', $this->detail_id);

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

        // $_xml_output .= '<titleInfo>'."\n".'<title><![CDATA['.$_title_main.']]></title>'."\n";
        $xml->startElement('titleInfo');
        $xml->startElement('title');
        $this->xmlWrite($xml, $_title_main);
        $xml->endElement();
        if ($_title_sub) {
            // $_xml_output .= '<subTitle><![CDATA['.$_title_sub.']]></subTitle>'."\n";
            $xml->startElement('subTitle');
            $this->xmlWrite($xml, $_title_sub);
            $xml->endElement();
        }
        // $_xml_output .= '</titleInfo>'."\n";
        $xml->endElement();

        // personal name
        // get the authors data
        foreach ($this->record_detail['authors'] as $_auth_d) {
            /*
            $_xml_output .= '<name type="'.$_auth_d['authority_type'].'" authority="'.$_auth_d['auth_list'].'">'."\n"
              .'<namePart><![CDATA['.$_auth_d['author_name'].']]></namePart>'."\n"
              .'<role><roleTerm type="text"><![CDATA['.$sysconf['authority_level'][$_auth_d['level']].']]></roleTerm></role>'."\n"
              .'</name>'."\n";
              */

            // $xml->startElement('name'); $xml->writeAttribute('type', $sysconf['authority_type'][$_auth_d['authority_type']]); $xml->writeAttribute('authority', $_auth_d['auth_list']);
            $xml->startElement('name'); $xml->writeAttribute('type', $_auth_d['authority_type']); $xml->writeAttribute('authority', $_auth_d['auth_list']??'');
            $xml->startElement('namePart'); $this->xmlWrite($xml, $_auth_d['author_name']); $xml->endElement();
            $xml->startElement('role');
                $xml->startElement('roleTerm'); $xml->writeAttribute('type', 'text');
                $this->xmlWrite($xml, $sysconf['authority_level'][$_auth_d['level']]);
                $xml->endElement();
            $xml->endElement();
            $xml->endElement();
        }

        // resources type
        // $_xml_output .= '<typeOfResource manuscript="yes" collection="yes"><![CDATA[mixed material]]></typeOfResource>'."\n";
        $xml->startElement('typeOfResource'); $xml->writeAttribute('manuscript', 'no'); $xml->writeAttribute('collection', 'yes'); $this->xmlWrite($xml, 'mixed material'); $xml->endElement();

        // $_xml_output .= '<genre authority="marcgt"><![CDATA[bibliography]]></genre>'."\n";
        $xml->startElement('genre'); $xml->writeAttribute('authority', 'marcgt'); $this->xmlWrite($xml, 'bibliography'); $xml->endElement();

        // imprint/publication data
        /*
        $_xml_output .= '<originInfo>'."\n";
        $_xml_output .= '<place><placeTerm type="text"><![CDATA['.$this->record_detail['publish_place'].']]></placeTerm></place>'."\n"
          .'<publisher><![CDATA['.$this->record_detail['publisher_name'].']]></publisher>'."\n"
          .'<dateIssued><![CDATA['.$this->record_detail['publish_year'].']]></dateIssued>'."\n";
        if ((integer)$this->record_detail['frequency_id'] > 0) {
            $_xml_output .= '<issuance>continuing</issuance>'."\n";
            $_xml_output .= '<frequency><![CDATA['.$this->record_detail['frequency'].']]></frequency>'."\n";
        } else {
            $_xml_output .= '<issuance><![CDATA[monographic]]></issuance>'."\n";
        }
        $_xml_output .= '<edition><![CDATA['.$this->record_detail['edition'].']]></edition>'."\n";
        $_xml_output .= '</originInfo>'."\n";
        */
        $xml->startElement('originInfo');
            $xml->startElement('place');
              $xml->startElement('placeTerm'); $xml->writeAttribute('type', 'text'); $this->xmlWrite($xml, $this->record_detail['publish_place']);$xml->endElement();
            $xml->endElement();
            $xml->startElement('publisher'); $this->xmlWrite($xml, $this->record_detail['publisher_name']); $xml->endElement();
            $xml->startElement('dateIssued'); $this->xmlWrite($xml, $this->record_detail['publish_year']); $xml->endElement();
        $xml->endElement();

        // language
        /*
        $_xml_output .= '<language>'."\n";
        $_xml_output .= '<languageTerm type="code"><![CDATA['.$this->record_detail['language_id'].']]></languageTerm>'."\n";
        $_xml_output .= '<languageTerm type="text"><![CDATA['.$this->record_detail['language_name'].']]></languageTerm>'."\n";
        $_xml_output .= '</language>'."\n";
        */
        $xml->startElement('language');
        $xml->startElement('languageTerm'); $xml->writeAttribute('type', 'code'); $this->xmlWrite($xml, $this->record_detail['language_id']); $xml->endElement();
        $xml->startElement('languageTerm'); $xml->writeAttribute('type', 'text'); $this->xmlWrite($xml, $this->record_detail['language_name']); $xml->endElement();
        $xml->endElement();

        // Physical Description/Collation
        /*
        $_xml_output .= '<physicalDescription>'."\n";
        $_xml_output .= '<form authority="gmd"><![CDATA['.$this->record_detail['gmd_name'].']]></form>'."\n";
        $_xml_output .= '<extent><![CDATA['.$this->record_detail['collation'].']]></extent>'."\n";
        $_xml_output .= '</physicalDescription>'."\n";
        */
        $xml->startElement('physicalDescription');
        $xml->startElement('form'); $xml->writeAttribute('authority', 'gmd'); $this->xmlWrite($xml, $this->record_detail['gmd_name']); $xml->endElement();
        $xml->startElement('extent'); $this->xmlWrite($xml, $this->record_detail['collation']); $xml->endElement();
        $xml->endElement();

        // Series title
        if ($this->record_detail['series_title']) {
            /*
            $_xml_output .= '<relatedItem type="series">'."\n";
            $_xml_output .= '<titleInfo>'."\n";
            $_xml_output .= '<title><![CDATA['.$this->record_detail['series_title'].']]></title>'."\n";
            $_xml_output .= '</titleInfo>'."\n";
            $_xml_output .= '</relatedItem>'."\n";
            */
            $xml->startElement('relatedItem'); $xml->writeAttribute('type', 'series');
            $xml->startElement('titleInfo'); $xml->endElement();
            $xml->startElement('title'); $this->xmlWrite($xml, $this->record_detail['series_title']); $xml->endElement();
            $xml->endElement();
        }

        // Note
        // $_xml_output .= '<note>'.$this->record_detail['notes'].'</note>'."\n";
        $xml->startElement('note'); $this->xmlWrite($xml, $this->record_detail['notes']); $xml->endElement();
        if (isset($this->record_detail['sor'])) {
            $xml->startElement('note'); $xml->writeAttribute('type', 'statement of responsibility'); $this->xmlWrite($xml, $this->record_detail['sor']); $xml->endElement();
            // $_xml_output .= '<note type="statement of responsibility"><![CDATA['.$_title_statement_resp.']]></note>';
        }

        // subject/topic
        foreach ($this->record_detail['subjects'] as $_topic_d) {
            $_subject_type = strtolower($sysconf['subject_type'][$_topic_d['topic_type']]);
            /*
            $_xml_output .= '<subject authority="'.$_topic_d['auth_list'].'">';
            $_xml_output .= '<'.$_subject_type.'><![CDATA['.$_topic_d['topic'].']]></'.$_subject_type.'>';
            $_xml_output .= '</subject>'."\n";
            */
            $xml->startElement('subject'); $xml->writeAttribute('authority', $_topic_d['auth_list']??'');
            $xml->startElement($_subject_type); $this->xmlWrite($xml, $_topic_d['topic']); $xml->endElement();
            $xml->endElement();
        }

        // classification
        // $_xml_output .= '<classification><![CDATA['.$this->record_detail['classification'].']]></classification>';
        $xml->startElement('classification'); $this->xmlWrite($xml, $this->record_detail['classification']); $xml->endElement();

        // ISBN/ISSN
        // $_xml_output .= '<identifier type="isbn"><![CDATA['.str_replace(array('-', ' '), '', $this->record_detail['isbn_issn']).']]></identifier>';
        $xml->startElement('identifier'); $xml->writeAttribute('type', 'isbn'); $this->xmlWrite($xml, str_replace(array('-', ' '), '', $this->record_detail['isbn_issn'])); $xml->endElement();

        // Location and Copies information
        $_copy_q = $this->db->query(sprintf('SELECT i.item_code, i.call_number, stat.item_status_name, loc.location_name, stat.rules, i.site FROM item AS i '
            .'LEFT JOIN mst_item_status AS stat ON i.item_status_id=stat.item_status_id '
            .'LEFT JOIN mst_location AS loc ON i.location_id=loc.location_id '
            .'WHERE i.biblio_id=%d', $this->detail_id));
        /*
        $_xml_output .= '<location>'."\n";
        $_xml_output .= '<physicalLocation><![CDATA['.$sysconf['library_name'].' '.$sysconf['library_subname'].']]></physicalLocation>'."\n";
        $_xml_output .= '<shelfLocator><![CDATA['.$this->record_detail['call_number'].']]></shelfLocator>'."\n";
        if ($_copy_q->num_rows > 0) {
            $_xml_output .= '<holdingSimple>'."\n";
            while ($_copy_d = $_copy_q->fetch_assoc()) {
                $_xml_output .= '<copyInformation>'."\n";
                $_xml_output .= '<numerationAndChronology type="1"><![CDATA['.$_copy_d['item_code'].']]></numerationAndChronology>'."\n";
                $_xml_output .= '<sublocation><![CDATA['.$_copy_d['location_name'].( $_copy_d['site']?' ('.$_copy_d['site'].')':'' ).']]></sublocation>'."\n";
                $_xml_output .= '<shelfLocator><![CDATA['.$_copy_d['call_number'].']]></shelfLocator>'."\n";
                $_xml_output .= '</copyInformation>'."\n";
            }
            $_xml_output .= '</holdingSimple>'."\n";
        }
        $_xml_output .= '</location>'."\n";
        */
        $xml->startElement('location');
        $xml->startElement('physicalLocation'); $this->xmlWrite($xml, $sysconf['library_name'].' '.$sysconf['library_subname']); $xml->endElement();
        $xml->startElement('shelfLocator'); $this->xmlWrite($xml, $this->record_detail['call_number']); $xml->endElement();
        if ($_copy_q->num_rows > 0) {
            $xml->startElement('holdingSimple');
            while ($_copy_d = $_copy_q->fetch_assoc()) {
                $xml->startElement('copyInformation');
                    $xml->startElement('numerationAndChronology'); $xml->writeAttribute('type', '1'); $this->xmlWrite($xml, $_copy_d['item_code']); $xml->endElement();
                    $xml->startElement('sublocation'); $this->xmlWrite($xml, $_copy_d['location_name'].( $_copy_d['site']?' ('.$_copy_d['site'].')':'' )); $xml->endElement();
                    $xml->startElement('shelfLocator'); $this->xmlWrite($xml, $_copy_d['call_number']); $xml->endElement();
                $xml->endElement();
            }
            $xml->endElement();
        }
        $xml->endElement();

        // digital files
        $attachment_q = $this->db->query('SELECT att.*, f.* FROM biblio_attachment AS att
            LEFT JOIN files AS f ON att.file_id=f.file_id WHERE att.biblio_id='.$this->detail_id.' AND att.access_type=\'public\' LIMIT 20');
        if ($attachment_q->num_rows > 0) {
            /*
            $_xml_output .= '<slims:digitals>'."\n";
            while ($attachment_d = $attachment_q->fetch_assoc()) {
                // check member type privileges
                if ($attachment_d['access_limit']) { continue; }
                $_xml_output .= '<slims:digital_item id="'.$attachment_d['file_id'].'" url="'.trim($attachment_d['file_url']).'" '
                    .'path="'.$attachment_d['file_dir'].'/'.$attachment_d['file_name'].'" mimetype="'.$attachment_d['mime_type'].'">';
                $_xml_output .= '<![CDATA['.$attachment_d['file_title'].']]>';
                $_xml_output .= '</slims:digital_item>'."\n";
            }
            $_xml_output .= '</slims:digitals>';
            */
            $xml->startElementNS('slims','digitals', null);
            while ($attachment_d = $attachment_q->fetch_assoc()) {
                // check member type privileges
                if ($attachment_d['access_limit']) { continue; }
                $xml->startElementNS('slims','digital_item', null);
                $xml->writeAttribute('id', $attachment_d['file_id']);
                $xml->writeAttribute('url', trim($attachment_d['file_url']));
                $xml->writeAttribute('path', $attachment_d['file_dir'].'/'.$attachment_d['file_name']);
                $xml->writeAttribute('mimetype', $attachment_d['mime_type']);
                $this->xmlWrite($xml, $attachment_d['file_title']);
                $xml->endElement();
            }
            $xml->endElement();
        }

        // image
        if (!empty($this->record_detail['image'])) {
          $_image = urlencode($this->record_detail['image']);
          $xml->startElementNS('slims','image', null);
          $this->xmlWrite($xml, urlencode($_image));
          $xml->endElement();
        }

        // record info
        /*
        $_xml_output .= '<recordInfo>'."\n";
        $_xml_output .= '<recordIdentifier><![CDATA['.$this->detail_id.']]></recordIdentifier>'."\n";
        $_xml_output .= '<recordCreationDate encoding="w3cdtf"><![CDATA['.$this->record_detail['input_date'].']]></recordCreationDate>'."\n";
        $_xml_output .= '<recordChangeDate encoding="w3cdtf"><![CDATA['.$this->record_detail['last_update'].']]></recordChangeDate>'."\n";
        $_xml_output .= '<recordOrigin><![CDATA[machine generated]]></recordOrigin>'."\n";
        $_xml_output .= '</recordInfo>';
        */
        $xml->startElement('recordInfo');
        $xml->startElement('recordIdentifier'); $this->xmlWrite($xml, $this->detail_id); $xml->endElement();
        $xml->startElement('recordCreationDate'); $xml->writeAttribute('encoding', 'w3cdtf'); $this->xmlWrite($xml, $this->record_detail['input_date']); $xml->endElement();
        $xml->startElement('recordChangeDate'); $xml->writeAttribute('encoding', 'w3cdtf'); $this->xmlWrite($xml, $this->record_detail['last_update']); $xml->endElement();
        $xml->startElement('recordOrigin'); $this->xmlWrite($xml, 'machine generated'); $xml->endElement();
        $xml->endElement();

        // $_xml_output .= '</mods>';
        $xml->endElement();

        return $xml->flush();
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
        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);

        // set prefix and suffix
        $this->detail_prefix = '';
        $this->detail_suffix = '';

        $_xml_output = '';

        $_title_main = utf8_encode($this->record_detail['title']);
        // title
        $xml->startElementNS('dc', 'title', null);
        $this->xmlWrite($xml, $_title_main);
        $xml->endElement();

        // get the authors data
        $_biblio_authors_q = $this->db->query('SELECT a.*,ba.level FROM mst_author AS a'
            .' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id='.$this->detail_id);
        while ($_auth_d = $_biblio_authors_q->fetch_assoc()) {
          $xml->startElementNS('dc', 'creator', null);
          $this->xmlWrite($xml, $_auth_d['author_name']);
          $xml->endElement();
        }
        $_biblio_authors_q->free_result();

        // imprint/publication data
        $xml->startElementNS('dc', 'publisher', null);
        $this->xmlWrite($xml, $this->record_detail['publisher_name']);
        $xml->endElement();

        if ($this->record_detail['publish_year']) {
          $xml->startElementNS('dc', 'date', null);
          $this->xmlWrite($xml, $this->record_detail['publish_year']);
          $xml->endElement();
        } else {
          $xml->startElementNS('dc', 'date', null);
          $xml->fullEndElement();
        }

        // edition
        $xml->startElementNS('dc', 'hasVersion', null);
        $this->xmlWrite($xml, $this->record_detail['edition']);
        $xml->endElement();

        // language
        $xml->startElementNS('dc', 'language', null);
        $this->xmlWrite($xml, $this->record_detail['language_name']);
        $xml->endElement();

        // Physical Description/Collation
        $xml->startElementNS('dc', 'medium', null);
        $this->xmlWrite($xml, $this->record_detail['gmd_name']);
        $xml->endElement();

        $xml->startElementNS('dc', 'format', null);
        $this->xmlWrite($xml, $this->record_detail['gmd_name']);
        $xml->endElement();

        $xml->startElementNS('dc', 'extent', null);
        $this->xmlWrite($xml, $this->record_detail['collation']);
        $xml->endElement();

        if ((integer)$this->record_detail['frequency_id'] > 0) {
          $xml->startElementNS('dc', 'format', null);
          $this->xmlWrite($xml, 'serial');
          $xml->endElement();
        }

        // Series title
        if ($this->record_detail['series_title']) {
          $xml->startElementNS('dc', 'isPartOf', null);
          $this->xmlWrite($xml, $this->record_detail['series_title']);
          $xml->endElement();
        }

        // Note
        $xml->startElementNS('dc', 'description', null);
        $this->xmlWrite($xml, $this->record_detail['notes']);
        $xml->endElement();

        $xml->startElementNS('dc', 'abstract', null);
        $this->xmlWrite($xml, $this->record_detail['notes']);
        $xml->endElement();

        // subject/topic
        $_biblio_topics_q = $this->db->query('SELECT t.topic, t.topic_type, t.auth_list, bt.level FROM mst_topic AS t
          LEFT JOIN biblio_topic AS bt ON t.topic_id=bt.topic_id WHERE bt.biblio_id='.$this->detail_id.' ORDER BY t.auth_list');
        while ($_topic_d = $_biblio_topics_q->fetch_assoc()) {
          $xml->startElementNS('dc', 'subject', null);
          $this->xmlWrite($xml, $_topic_d['topic']);
          $xml->endElement();
        }
        $_biblio_topics_q->free_result();

        // classification
        $xml->startElementNS('dc', 'subject', null);
        $this->xmlWrite($xml, $this->record_detail['classification']);
        $xml->endElement();

        // Permalink
        $permalink = $protocol.'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].SWB.'index.php?p=show_detail&id='.$this->detail_id;
        $xml->startElementNS('dc', 'identifier', null);
        $this->xmlWrite($xml, $permalink);
        $xml->endElement();

        // ISBN/ISSN
        $xml->startElementNS('dc', 'identifier', null);
        $this->xmlWrite($xml, str_replace(array('-', ' '), '', $this->record_detail['isbn_issn']));
        $xml->endElement();

        // Call Number
        $xml->startElementNS('dc', 'identifier', null);
        $this->xmlWrite($xml, $this->record_detail['call_number']);
        $xml->endElement();

        $_copy_q = $this->db->query('SELECT i.item_code, i.call_number, stat.item_status_name, loc.location_name, stat.rules, i.site FROM item AS i '
            .'LEFT JOIN mst_item_status AS stat ON i.item_status_id=stat.item_status_id '
            .'LEFT JOIN mst_location AS loc ON i.location_id=loc.location_id '
            .'WHERE i.biblio_id='.$this->detail_id);
        if ($_copy_q->num_rows > 0) {
            while ($_copy_d = $_copy_q->fetch_assoc()) {
              $xml->startElementNS('dc', 'hasPart', null);
              $this->xmlWrite($xml, $_copy_d['item_code']);
              $xml->endElement();
            }
        }
        $_copy_q->free_result();

        // digital files
        $attachment_q = $this->db->query('SELECT att.*, f.* FROM biblio_attachment AS att
            LEFT JOIN files AS f ON att.file_id=f.file_id WHERE att.biblio_id='.$this->detail_id.' AND att.access_type=\'public\' LIMIT 20');
        if ($attachment_q->num_rows > 0) {
          while ($attachment_d = $attachment_q->fetch_assoc()) {
              $dir = '';
              if ($attachment_d['file_dir']) {
                $dir = $attachment_d['file_dir'].'/';
              }
              // check member type privileges
              if ($attachment_d['access_limit']) { continue; }
              $xml->startElementNS('dc', 'relation', null);
              $this->xmlWrite($xml, $protocol.'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].REPO_WBS.$dir.trim(urlencode($attachment_d['file_name'])));
              $xml->endElement();
          }
        }

        // image
        if (!empty($this->record_detail['image'])) {
          $_image = $protocol.'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].SWB.'images/docs/'.urlencode($this->record_detail['image']);
          $xml->startElementNS('dc', 'relation', null);
          $this->xmlWrite($xml, $_image);
          $xml->endElement();
        }

        return $xml->flush();
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
      $_biblio_authors_q = $this->db->query('SELECT a.*,ba.level FROM mst_author AS a'
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
      $_biblio_topics_q = $this->db->query('SELECT t.topic, t.topic_type, t.auth_list, bt.level FROM mst_topic AS t
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
      $attachment_q = $this->db->query('SELECT att.*, f.* FROM biblio_attachment AS att
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

    private function xmlWrite(&$xmlwriter, $data, $mode = 'Text') {
        if ($mode == 'CData') {
            $xmlwriter->writeCData($data);
        } else {
            $xmlwriter->text($data??'');
        }
    }
}
