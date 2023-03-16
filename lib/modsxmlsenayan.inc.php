<?php
/**
 *
 * MODS XML to SENAYAN converter
 *
 * Copyright (C) 2010 Hendro Wicaksono (hendrowicaksono@yahoo.com)
 * Copyright (C) 2011,2012 Arie Nugraha (dicarve@gmail.com)
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

define('MODS_XML_PARSE_ERROR', 199);

use SLiMS\Url;
use SLiMS\Http\Client;

/**
 * MODS XML parser for SENAYAN 3
 * @param string $str_modsxml : can be string, file or uri
 * @return  mixed
 **/
function modsXMLsenayan($str_modsxml, $str_xml_type = 'string')
{
  // initiate records array
  $_records = array();

  // load XML
  if ($str_xml_type == 'file') {
    // load from file
    if (file_exists($str_modsxml)) {
      $xml = @simplexml_load_file($str_modsxml);
    } else {
      return 'File ' . $str_modsxml . ' not found! Please supply full path to MODS XML file';
    }
  } else {
    // load from string
    try {
      $modsxml = $str_modsxml;
      if ($str_xml_type == 'uri' && Url::isValid($modsxml))
      {
        $getModsxml = Client::get($modsxml);
        if (!empty($getModsxml->getError())) throw new Exception($getModsxml->getError());
        $modsxml = $getModsxml->getContent();
      }

      // parse xml string 
      $xml = @new SimpleXMLElement($modsxml, LIBXML_NSCLEAN);
    } catch (Exception $xmlerr) {
      return $xmlerr->getMessage();
      // die($xmlerr->getMessage());
    }
  }

  // get result information from SLiMS Namespaced node
  $_slims = $xml->children('http://senayan.diknas.go.id');
  if (!$_slims) {
    $_slims = $xml->children('http://slims.web.id');
  }

  if ($_slims) {
    if (isset($_slims->resultInfo)) {
      $_records['result_num'] = (integer)$_slims->resultInfo->modsResultNum;
      $_records['result_page'] = (integer)$_slims->resultInfo->modsResultPage;
      $_records['result_showed'] = (integer)$_slims->resultInfo->modsResultShowed;
    } else {
      $_records['result_num'] = (integer)$_slims->modsResultNum;
      $_records['result_page'] = (integer)$_slims->modsResultPage;
      $_records['result_showed'] = (integer)$_slims->modsResultShowed;
    }
  } else {
    $_records['result_num'] = isset($xml->modsResultNum) ? $xml->modsResultNum : '';
    $_records['result_page'] = isset($xml->modsResultPage) ? $xml->modsResultPage : '';
    $_records['result_showed'] = isset($xml->modsResultShowed) ? $xml->modsResultShowed : '';
  }

  $record_num = 0;
  // start iterate records
  foreach ($xml->mods as $record) {
    $data = array();

    $data['id'] = (string)$record['ID'];
    if (!$data['id']) {
      $data['id'] = (string)$record['id'];
    }
    # authors
    $data['authors'] = array();

    # title
    $data['title'] = (string)$record->titleInfo->title;
    if (isset($record->titleInfo->subTitle)) {
      $data['title'] .= (string)$record->titleInfo->subTitle;
    }

    # name/author (repeatable)
    if (isset($record->name) AND $record->name) {
      foreach ($record->name as $value) {
        $_author_type = $value['type'];
        if ($value->role->roleTerm == 'Primary Author') {
          $_level = 1;
        } else {
          $_level = 2;
        }
        $data['authors'][] = array('name' => (string)$value->namePart, 'authority_list' => (string)$value['authority'], 'level' => $_level, 'author_type' => (string)$_author_type);
      }
    }

    # mods->typeOfResource
    $data['manuscript'] = (boolean)$record->typeOfResource['manuscript'] == 'yes';
    $data['collection'] = (boolean)$record->typeOfResource['collection'] == 'yes';
    $data['resource_type'] = (string)$record->typeOfResource;

    # mods->genre
    $data['genre_authority'] = (string)$record->genre['authority'];
    $data['genre'] = (string)$record->genre;

    # mods->originInfo
    $data['publish_place'] = isset($record->originInfo->place->placeTerm) ? (string)$record->originInfo->place->placeTerm : '';
    $data['publisher'] = (string)$record->originInfo->publisher;
    $data['publish_year'] = (string)$record->originInfo->dateIssued;
    $data['issuance'] = (string)$record->originInfo->issuance;
    $data['edition'] = (string)$record->originInfo->edition;

    # mods->language
    if (isset($record->language->languageTerm)) {
      foreach ($record->language->languageTerm as $_langterm) {
        if ($_langterm['type'] == 'code') {
          $data['language']['code'] = (string)$_langterm;
        } else {
          $data['language']['name'] = (string)$_langterm;
        }
      }
    }

    # mods->physicalDescription
    $data['gmd'] = (string)$record->physicalDescription->form;
    $data['collation'] = (string)$record->physicalDescription->extent;

    # mods->relatedItem
    if ($record->relatedItem['type'] == 'series') {
      $data['series_title'] = (string)$record->relatedItem->titleInfo->title;
    }

    # mods->note
    $data['notes'] = (string)$record->note;

    # mods->subject
    foreach ($record->subject as $_subj) {
      $_authority = (string)$_subj['authority'];
      if (isset($_subj->topic)) {
        $_term_type = 'topical';
        $_term = (string)$_subj->topic;
      }
      if (isset($_subj->geographic)) {
        $_term_type = 'geographic';
        $_term = (string)$_subj->geographic;
      }
      if (isset($_subj->name)) {
        $_term_type = 'name';
        $_term = (string)$_subj->name;
      }
      if (isset($_subj->temporal)) {
        $_term_type = 'temporal';
        $_term = (string)$_subj->temporal;
      }
      if (isset($_subj->genre)) {
        $_term_type = 'genre';
        $_term = (string)$_subj->genre;
      }
      if (isset($_subj->occupation)) {
        $_term_type = 'occupation';
        $_term = (string)$_subj->occupation;
      }
      $data['subjects'][] = array('term' => $_term ?? '', 'term_type' => $_term_type ?? '', 'authority' => $_authority);
    }

    # mods->classification
    $data['classification'] = (string)$record->classification;

    # mods->identifier
    $data['isbn_issn'] = '';
    if ($record->identifier['type'] == 'isbn') {
      $data['isbn_issn'] = (string)$record->identifier;
    }

    # mods->location
    $data['location'] = (string)$record->location->physicalLocation;
    $data['call_number'] = (string)$record->location->shelfLocator;

    # mods->recordInfo
    if (isset($record->recordInfo)) {
      $data['id'] = (string)$record->recordInfo->recordIdentifier;
      $data['create_date'] = (string)$record->recordInfo->recordCreationDate;
      $data['modified_date'] = (string)$record->recordInfo->recordChangeDate;
      $data['origin'] = (string)$record->recordInfo->recordOrigin;
    }

    $_slims = $record->children('http://slims.web.id');

    # images
    if (isset($_slims->image)) {
      $data['image'] = (string)$_slims->image;
    }

    # digital files
    if (isset($_slims->digitals)) {
      foreach ($_slims->digitals->digital_item as $_dig_item) {
        // get attributes
        $_attr = (array)$_dig_item->attributes();
        $data['digitals'][] = array('id' => $_attr['@attributes']['id'],
          'title' => (string)$_dig_item,
          'path' => $_attr['@attributes']['path'],
          'mimetype' => $_attr['@attributes']['mimetype'],
          'url' => $_attr['@attributes']['url']);
      }
    }

    $_records['records'][] = $data;
    $record_num++;
  }
  return $_records;
}
