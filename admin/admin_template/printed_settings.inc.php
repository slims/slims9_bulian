<?php
/**
 * SENAYAN application printable data configuration
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
 */

/**
 * Function to load and override print settings from database
 */
function loadPrintSettings($dbs, $type) {
  global $sysconf;
  $barcode_settings_q = $dbs->query("SELECT setting_value FROM setting WHERE setting_name='".$type."_print_settings'");
  if ($barcode_settings_q->num_rows) {
    $barcode_settings_d = $barcode_settings_q->fetch_row();
    if ($barcode_settings_d[0]) {
      $barcode_settings = @unserialize($barcode_settings_d[0]);
      if (is_array($barcode_settings) && count($barcode_settings) > 0) {
        foreach ($barcode_settings as $setting_name => $val) {
          $sysconf['print'][$type][$setting_name] = $val;
        }
      }
      return $sysconf['print'][$type];
    }
  }
}

// label print settings
/* measurement in cm */
$sysconf['print']['label']['page_margin'] = 0.2;
$sysconf['print']['label']['items_per_row'] = 3;
$sysconf['print']['label']['items_margin'] = 0.05;
$sysconf['print']['label']['box_width'] = 8;
$sysconf['print']['label']['box_height'] = 3.3;
$sysconf['print']['label']['include_header_text'] = 1; // change to 0 if dont want to use header in each label
$sysconf['print']['label']['header_text'] = ''; // keep empty if you want to use Library Name as a header text
$sysconf['print']['label']['fonts'] = "Arial, Verdana, Helvetica, 'Trebuchet MS'";
$sysconf['print']['label']['font_size'] = 11;
$sysconf['print']['label']['border_size'] = 1; // in pixels

// item barcode print settings
/* measurement in cm */
$sysconf['print']['barcode']['barcode_page_margin'] = 0.2;
$sysconf['print']['barcode']['barcode_items_per_row'] = 3;
$sysconf['print']['barcode']['barcode_items_margin'] = 0.1;
$sysconf['print']['barcode']['barcode_box_width'] = 7;
$sysconf['print']['barcode']['barcode_box_height'] = 5;
$sysconf['print']['barcode']['barcode_include_header_text'] = 1; // change to 0 if dont want to use header in each barcode
$sysconf['print']['barcode']['barcode_cut_title'] = 50; // maximum characters in title to appear in each barcode. change to 0 if you dont want the title cutted
$sysconf['print']['barcode']['barcode_header_text'] = ''; // keep empty if you want to use Library Name as a header text
$sysconf['print']['barcode']['barcode_fonts'] = "Arial, Verdana, Helvetica, 'Trebuchet MS'"; // font to use
$sysconf['print']['barcode']['barcode_font_size'] = 11;
$sysconf['print']['barcode']['barcode_scale'] = 70; // barcode scale in percent relative to box width and height
$sysconf['print']['barcode']['barcode_border_size'] = 1; // in pixels

// barcode generator print settings
$sysconf['print']['barcodegen']['box_width'] = 6;
$sysconf['print']['barcodegen']['page_margin'] = 0.2;
$sysconf['print']['barcodegen']['items_margin'] = 0.05;
$sysconf['print']['barcodegen']['include_border'] = 0;
$sysconf['print']['barcodegen']['items_per_row'] = 3;

/* Receipt Printing */
$sysconf['print']['receipt']['receipt_width'] = '7cm';
$sysconf['print']['receipt']['receipt_font'] = 'Courier';
$sysconf['print']['receipt']['receipt_color'] = '#000';
$sysconf['print']['receipt']['receipt_margin'] = '5px';
$sysconf['print']['receipt']['receipt_padding'] = '10px';
$sysconf['print']['receipt']['receipt_border'] = '1px dashed #000';
$sysconf['print']['receipt']['receipt_fontSize'] = '7pt';
$sysconf['print']['receipt']['receipt_header_fontSize'] = '7pt';
$sysconf['print']['receipt']['receipt_titleLength'] = 100;

// member card print settings
$sysconf['print']['membercard']['template'] = 'classic';

/* measurement in cm */
$sysconf['print']['membercard']['page_margin'] = 0.2;
$sysconf['print']['membercard']['items_margin'] = 0.1;
$sysconf['print']['membercard']['items_per_row'] = 1; //

// by Jushadi Arman Saz
/* measurement in cm*/
$sysconf['print']['membercard']['factor'] = "37.795275591"; //cm to px

// Items Settings
// change to 0 if dont want to use selected items
$sysconf['print']['membercard']['include_id_label'] = 1; // id
$sysconf['print']['membercard']['include_name_label'] = 1; // name
$sysconf['print']['membercard']['include_pin_label'] = 1; // identify
$sysconf['print']['membercard']['include_inst_label'] = 0; // institution
$sysconf['print']['membercard']['include_email_label'] = 0; // mail address
$sysconf['print']['membercard']['include_address_label'] = 1; // home or office address
$sysconf['print']['membercard']['include_barcode_label'] = 1; // barcode
$sysconf['print']['membercard']['include_expired_label'] = 1; // expired date

// Cardbox Settings
$sysconf['print']['membercard']['box_width'] = 8.6;
$sysconf['print']['membercard']['box_height'] = 5.4;
$sysconf['print']['membercard']['front_side_image'] = 'bg-front.svg';
$sysconf['print']['membercard']['back_side_image'] = 'bg-back.svg';

// Logo Setting
$sysconf['print']['membercard']['logo'] = "logo.png";
$sysconf['print']['membercard']['front_logo_width'] = "";
$sysconf['print']['membercard']['front_logo_height'] = "";
$sysconf['print']['membercard']['front_logo_left'] = "";
$sysconf['print']['membercard']['front_logo_top'] = "";
$sysconf['print']['membercard']['back_logo_width'] = "";
$sysconf['print']['membercard']['back_logo_height'] = "";
$sysconf['print']['membercard']['back_logo_left'] = "";
$sysconf['print']['membercard']['back_logo_top'] = "";

// Photo Settings
$sysconf['print']['membercard']['photo_left'] = "";
$sysconf['print']['membercard']['photo_top'] = "";
$sysconf['print']['membercard']['photo_width'] = 1.5;
$sysconf['print']['membercard']['photo_height'] = 1.8;

// Header Settings
$sysconf['print']['membercard']['front_header1_text'] = 'Library Member Card'; // use <br /> tag to make another line
$sysconf['print']['membercard']['front_header1_font_size'] = '12';
$sysconf['print']['membercard']['front_header2_text'] = 'My Library';
$sysconf['print']['membercard']['front_header2_font_size'] = '12';
$sysconf['print']['membercard']['back_header1_text'] = 'My Library';
$sysconf['print']['membercard']['back_header1_font_size'] = "12";
$sysconf['print']['membercard']['back_header2_text'] = 'My Library Full Address and Website';
$sysconf['print']['membercard']['back_header2_font_size'] = "5";
$sysconf['print']['membercard']['header_color'] = "#0066FF"; //e.g. :#0066FF, green, etc.

//biodata settings
$sysconf['print']['membercard']['bio_font_size'] = "11";
$sysconf['print']['membercard']['bio_font_weight'] = "bold";
$sysconf['print']['membercard']['bio_label_width'] = "100";

// Stamp Settings
$sysconf['print']['membercard']['city'] = "City Name";
$sysconf['print']['membercard']['title'] = "Library Manager";
$sysconf['print']['membercard']['officials'] = "Librarian Name";
$sysconf['print']['membercard']['officials_id'] = "Librarian ID";
$sysconf['print']['membercard']['stamp_file'] = "stamp.png"; // stamp image, use transparent image
$sysconf['print']['membercard']['signature_file'] = "signature.png"; // sign picture, use transparent image
$sysconf['print']['membercard']['stamp_left'] = "";
$sysconf['print']['membercard']['stamp_top'] = "";
$sysconf['print']['membercard']['stamp_width'] = "";
$sysconf['print']['membercard']['stamp_height'] = "";

//expired
$sysconf['print']['membercard']['exp_left'] = "";
$sysconf['print']['membercard']['exp_top'] = "";
$sysconf['print']['membercard']['exp_width'] = "";
$sysconf['print']['membercard']['exp_height'] = "";

// Barcode Setting
$sysconf['print']['membercard']['barcode_scale'] = 100; // barcode scale in percent relative to box width and height
$sysconf['print']['membercard']['barcode_left'] = "";
$sysconf['print']['membercard']['barcode_top'] = "";
$sysconf['print']['membercard']['barcode_width'] = "";
$sysconf['print']['membercard']['barcode_height'] = "";

// Rules
$sysconf['print']['membercard']['rules'] = "<ul>
<li>This card is published by Library.</li>
<li>Please return this card to its owner if you found it.</li>
</ul>";
$sysconf['print']['membercard']['rules_font_size'] = "8";

// address
$sysconf['print']['membercard']['address'] = 'My Library<br />website: http://slims.web.id, email : librarian@slims.web.id';
$sysconf['print']['membercard']['address_font_size'] = "7";
$sysconf['print']['membercard']['address_left'] = "";
$sysconf['print']['membercard']['address_top'] = "";

// catalog card settings
$sysconf['print']['catalog']['self_list_card'] = '1';
$sysconf['print']['catalog']['title_card'] = '1';
$sysconf['print']['catalog']['author_card'] = '1';
$sysconf['print']['catalog']['subject_card'] = '1';

