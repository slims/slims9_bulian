<?php
/**
 *
 * MODS XML to SENAYAN
 *
 * Copyright (C) 2011,2012 Arie Nugraha (dicarve@gmail.com)
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

/**
 * MODS XML Record parser
 * @param   object  $modsrecords: XML record object from simpleXML
 * @return  array
 **/
function modsXMLslims($modsrecords)
{
    $data = array();

    # authors
    $data['authors'] = array();

    # title
    $data['title'] = (string)$modsrecords->titleInfo->title;
    if (isset($modsrecords->titleInfo->subTitle)) {
      $data['title'] .= ': '.(string)$modsrecords->titleInfo->subTitle;
    }

    # name/author (repeatable)
    if (isset($modsrecords->name) AND $modsrecords->name) {
      foreach ($modsrecords->name as $value) {
        $_author_type = $value['type'];
        if ($value->role->roleTerm == 'Primary Author') {
            $_level = 1;
        } else {
            $_level = 2;
        }
        $data['authors'][] = array('name' => (string)$value->namePart, 'authority_list' => (string)$value['authority'], 'level' => $_level, 'author_type' => (string)$_author_type);
        // $data['authors'][] = (string)$value->namePart;
      }
    }

    # mods->typeOfResource
    $data['manuscript'] = (boolean)$modsrecords->typeOfResource['manuscript'] == 'yes';
    $data['collection'] = (boolean)$modsrecords->typeOfResource['collection'] == 'yes';
    $data['resource_type'] = (string)$modsrecords->typeOfResource;

    # mods->genre
    $data['genre_authority'] = (string)$modsrecords->genre['authority'];
    $data['genre'] = (string)$modsrecords->genre;

    # mods->originInfo
    $data['publish_place'] = isset($modsrecords->originInfo->place->placeTerm)?(string)$modsrecords->originInfo->place->placeTerm:'';
    $data['publisher'] = (string)$modsrecords->originInfo->publisher;
    $data['publish_year'] = (string)$modsrecords->originInfo->dateIssued;
    $data['issuance'] = (string)$modsrecords->originInfo->issuance;
    $data['edition'] = (string)$modsrecords->originInfo->edition;

    # mods->language
    if (isset($modsrecords->language->languageTerm)) {
      foreach ($modsrecords->language->languageTerm as $_langterm) {
        if ($_langterm['type'] == 'code') {
          $data['language']['code'] = (string)$_langterm;
        } else {
          $data['language']['name'] = (string)$_langterm;
        }
      }
    }

    # mods->physicalDescription
    $data['gmd'] = (string)$modsrecords->physicalDescription->form;
    $data['collation'] = (string)$modsrecords->physicalDescription->extent;

    # mods->relatedItem
    if ($modsrecords->relatedItem['type'] == 'series') {
      $data['series_title'] = (string)$modsrecords->relatedItem->titleInfo->title;
    }

    # mods->note
    $data['notes'] = (string)$modsrecords->note;

    # mods->subject
    $_term_type = 'topical';
    $_term = '';
    foreach ($modsrecords->subject as $_subj) {
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
      $data['subjects'][] = array('term' => $_term, 'term_type' => $_term_type, 'authority' => $_authority);
    }

    # mods->classification
    $data['classification'] = (string)$modsrecords->classification;

    # mods->identifier
    if ($modsrecords->identifier['type'] == 'isbn') {
      $data['isbn_issn'] = (string)$modsrecords->identifier;
    }

    # mods->location
    $data['location'] = (string)$modsrecords->location->physicalLocation;
    $data['call_number'] = (string)$modsrecords->location->shelfLocator;

    # mods->recordInfo
    if (isset($modsrecords->recordInfo)) {
      $data['id'] = (string)$modsrecords->recordInfo->recordIdentifier;
      $data['create_date'] = (string)$modsrecords->recordInfo->recordCreationDate;
      $data['modified_date'] = (string)$modsrecords->recordInfo->recordChangeDate;
      $data['origin'] = (string)$modsrecords->recordInfo->recordOrigin;
    }

    return $data;
}
