<?php
/**
 *
 * MARCXML to SENAYAN converter
 *
 * Copyright (C) 2009 Arie Nugraha (dicarve@yahoo.com), Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

/**
 * simple MARC XML parser for SENAYAN 3
 * @param   string  $str_marcxml
 * @return  array
 **/
function marcXMLsenayan($str_marcxml, $str_xml_type = 'string')
{
    // MARC XML file path
    $_records = array();

    // load XML
    if ($str_xml_type == 'file') {
        // load from file
        if (file_exists($str_marcxml)) {
            $xml = simplexml_load_file($str_marcxml);
        } else {
            return 'File '.$str_marcxml.' not found! Please supply full path to MARC XML file';
        }
    } else {
        // load from string
        try {
            $xml = new SimpleXMLElement($str_marcxml);
        } catch (Exception $xmlerr) {
            die($xmlerr->getMessage());
        }
    }


    $record_num = 0;
    $corp_authors = array();
    $conf_authors = array();
    // start iterate records
    foreach ($xml->record as $record) {
        // default elements
        $isbn_issn = '';
        $classification = '';
        $language = '';
        $main_author = '';
        $title = '';
        $title_main = '';
        $title_sub = '';
        $title_statement_resp = '';
        $gmd = '';
        $edition = '';
        $publish_place = '';
        $publisher = '';
        $publish_year = '';
        $physical = '';
        $series = '';
        $notes = '';
        $topics = array();
        $authors = array();
        $copies = array();
        $call_number = '';
        $collection_type = '';
        $corp_authors[$record_num] = '';
        $conf_authors[$record_num] = '';
        // field
        foreach ($record->datafield as $field) {
            // subfield
            foreach ($field->subfield as $subfield) {
                // author
                if ((string)$field['tag'] == '020' OR (string)$field['tag'] == '022') {
                    if ((string)$subfield['code'] == 'a') {
                        $isbn_issn = $subfield;
                    }
                } else if ((string)$field['tag'] == '041') {
                    // language
                    if ((string)$subfield['code'] == 'a') {
                        $language = $subfield;
                    }
                } else if ((string)$field['tag'] == '082') {
                    if ((string)$subfield['code'] == 'a') {
                        // DDC classification
                        $classification = $subfield;
                    }
                } else if ((string)$field['tag'] == '100') {
                    if ((string)$subfield['code'] == 'a') {
                        $main_author = (string)$subfield;
                    }
                } else if ((string)$field['tag'] == '245') {
                    // title
                    if ((string)$subfield['code'] == 'a') {
                        $title_main = $subfield;
                    } else if ((string)$subfield['code'] == 'b') {
                        $title_sub = $subfield;
                    } else if ((string)$subfield['code'] == 'c') {
                        $title_statement_resp = $subfield;
                    } else if ((string)$subfield['code'] == 'h') {
                        $gmd = $subfield;
                    }
                } else if ((string)$field['tag'] == '250') {
                    // edition
                    if ((string)$subfield['code'] == 'a') {
                        $edition = $subfield;
                    } else if ((string)$subfield['code'] == 'b') {
                        $edition .= ' '.$subfield;
                    }
                } else if ((string)$field['tag'] == '260') {
                    // imprint
                    if ((string)$subfield['code'] == 'a') {
                        $publish_place = $subfield;
                    } else if ((string)$subfield['code'] == 'b') {
                        $publisher = $subfield;
                    } else if ((string)$subfield['code'] == 'c') {
                        $publish_year = $subfield;
                    }
                } else if ((string)$field['tag'] == '300') {
                    // physical
                    if ((string)$subfield['code'] == 'a') {
                        $physical = $subfield;
                    } else if ((string)$subfield['code'] == 'b') {
                        $physical .= ' - '.$subfield;
                    }
                } else if ((string)$field['tag'] == '440') {
                    // series
                    if ((string)$subfield['code'] == 'a') {
                        $series = $subfield;
                    } else if ((string)$subfield['code'] == 'v') {
                        $series .= ' '.$subfield;
                    }
                } else if (preg_match('@^5.+@i', (string)$field['tag'])) {
                    // notes
                    $notes .= $subfield.'; ';
                } else if ((string)$field['tag'] == '600') {
                    // authors subject headings
                    if ((string)$subfield['code'] == 'a') {
                        $authors[] = (string)$subfield;
                    }
                } else if ((string)$field['tag'] == '610') {
                    // authors corporate subject heading
                    if ((string)$subfield['code'] == 'a') {
                        $corp_author_buff .= (string)$subfield;
                    } else if ((string)$subfield['code'] == 'b') {
                        $corp_author_buff .= (string)$subfield;
                    }
                } else if ((string)$field['tag'] == '611') {
                    // authors conference/meeting subject heading
                    if ((string)$subfield['code'] == 'a') {
                        $conf_author_buff .= (string)$subfield;
                    } else if ((string)$subfield['code'] == 'c') {
                        $conf_author_buff .= '.'.(string)$subfield;
                    }
                } else if ((string)$field['tag'] == '650') {
                    // topics
                    if ((string)$subfield['code'] == 'a') {
                        $topics[] = (string)$subfield;
                    }
                } else if ((string)$field['tag'] == '700') {
                    // additional authors
                    if ((string)$subfield['code'] == 'a') {
                        $authors[] = (string)$subfield;
                    }
                } else if ((string)$field['tag'] == '710') {
                    // additional authors corporate
                    if ((string)$subfield['code'] == 'a') {
                        $corp_authors[$record_num] .= (string)$subfield;
                    } else if ((string)$subfield['code'] == 'b') {
                        $corp_authors[$record_num] .= (string)$subfield;
                    }
                } else if ((string)$field['tag'] == '711') {
                    // additional authors conference/meeting
                    if ((string)$subfield['code'] == 'a') {
                        $conf_authors[$record_num] .= (string)$subfield;
                    } else if ((string)$subfield['code'] == 'c') {
                        $conf_authors[$record_num] .= '.'.(string)$subfield;
                    }
                } else if ((string)$field['tag'] == '852') {
                    // copies
                    if ((string)$subfield['code'] == 'p') {
                        $copies[] = (string)$subfield;
                    } else if ((string)$subfield['code'] == 'h') {
                        $call_number = $subfield;
                    } else if ((string)$subfield['code'] == 'c') {
                        $collection_type = $subfield;
                    }
                }
            }
            // concat titles
            $title = (string)$title_main.( $title_sub?(string)$title_sub:'' ).( $title_statement_resp?(string)$title_statement_resp:'' );
        }

        // reset $data array
        $data = array();
        // clear array
        if (!$corp_authors[$record_num]) { unset($corp_authors[$record_num]); }
        if (!$conf_authors[$record_num]) { unset($conf_authors[$record_num]); }

        $data['title'] = (string)$title;
        $data['gmd'] = (string)$gmd;
        $data['gmd'] = str_replace(array('[', ']'), '', trim($data['gmd']));
        $data['edition'] = (string)$edition;
        $data['isbn_issn'] = (string)$isbn_issn;
        $data['publisher'] = (string)$publisher;
        $data['publish_year'] = (integer)$publish_year;
        $data['collation'] = (string)$physical;
        $data['series_title'] = (string)$series;
        $data['call_number'] = (string)$call_number;
        $data['language'] = (string)$language;
        $data['publish_place'] = (string)$publish_place;
        $data['classification'] = (string)$classification;
        $data['notes'] = (string)$notes;
        $data['author_main'] = $main_author;
        $data['authors_add'] = $authors;
        $data['authors_corp'] = $corp_authors;
        $data['authors_conf'] = $conf_authors;
        $data['subjects'] = $topics;
        $data['copies'] = $copies;

        $_records[] = $data;
        $record_num++;
    }
    return $_records;
}
