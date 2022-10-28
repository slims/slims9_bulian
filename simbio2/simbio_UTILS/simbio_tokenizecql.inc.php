<?php
/**
 *
 * CQL Tokenizer
 *
 * Copyright (C) 2009 Arie Nugraha (dicarve@yahoo.com)
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

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
  die("can not access this file directly");
} elseif (INDEX_AUTH != 1) { 
  die("can not access this file directly");
}

/**
 * CQL Tokenizer
 * Tokenize CQL string to array for easy proccessing
 *
 * @param   string  $str_query
 * @param   array   $arr_stop_words
 * @return  array
 **/
function simbio_tokenizeCQL($str_query, $arr_searcheable_fields, $arr_stop_words = array(), $int_max_words = 20)
{
  // buffer vars
  $_array_queries = array();
  $_new_q = '';
  $_last_boolean = '+';
  $_previous_field = 'title';
  $_current_field = 'title';
  $_phrase = '';
  // make sure there is no spaces between equation mark
  $str_query = preg_replace('@\s+=\s+@i', '=', $str_query);
  // inside quote flag
  $_inside_quote = false;
  // tokenizing string one by one
  // $_token = strtok(strtolower($str_query), " \n\t");
  $_token = strtok($str_query, " \n\t");
  // word counter
  $_word_count = 0;
  while ($_token !== false) {
    // SAFEGUARD!
    if ($_word_count > $int_max_words) {
      break;
    }
    $_token = trim($_token);
    // check for stopwords
    if (in_array($_token, $arr_stop_words) AND !$_inside_quote) {
      // do nothing
      $_token = strtok(" \n\t");
      continue;
    }
    // check boolean mode
    // if (in_array($_token, array('exact', 'and', 'or', 'not'))) {
    if (preg_match('@\b(exact|and|or|not)\b@i', $_token)) {
      $_bool = strtolower($_token);
      if ($_bool == 'exact' AND !$_inside_quote) {
        $_last_boolean = '++';
      } else if ($_bool == 'or' AND !$_inside_quote) {
        $_last_boolean = '*';
      } else if ($_bool == 'not' AND !$_inside_quote) {
        $_last_boolean = '-';
      } else {
        $_last_boolean = '+';
      }
      // we continue to the next loop
      $_token = strtok(" \n\t");
      continue;
    }
    // check for current field
    foreach ($arr_searcheable_fields as $_search_field) {
      if ((strpos($_token, $_search_field.'=') === 0) AND !$_inside_quote) {
        $_current_field = $_search_field;
        $_token = str_replace($_search_field.'=', '', $_token);
        if ($_word_count > 0) {
          $_array_queries[] = array('f' => 'boolean', 'b' => $_last_boolean);
        }
        $_last_boolean = '+';
      }
    }
    // check if we are inside quotes
    if (strpos($_token, '"') === 0) {
      $_inside_quote = true;
      // remove the first quote
      $_token = substr_replace($_token, '', 0, 1);
    }
    if ($_inside_quote) {
      if (strpos($_token, '"') === strlen($_token)-1) {
        $_inside_quote = false;
        $_phrase .= str_replace('"', '', $_token);
        $_array_queries[] = array('f' => $_current_field, 'b' => $_last_boolean, 'q' => $_phrase, 'is_phrase' => 1);
        // reset
        $_phrase = '';
      } else {
        $_phrase .= str_replace('"', '', $_token).' ';
        // we continue to the next loop and concatenating words
        $_token = strtok(" \n\t");
        continue;
      }
    } else {
      if (stripos($_token, '(') === true) {
        $_array_queries[] = array('f' => 'opengroup', 'b' => $_last_boolean);
      } else if (stripos($_token, ')') === true) {
        $_array_queries[] = array('f' => 'closegroup', 'b' => $_last_boolean);
      } else {
        $_array_queries[] = array('f' => $_current_field, 'b' => $_last_boolean, 'q' => $_token); 
      }
    }
    // set previous field flag
    $_previous_field = $_current_field;
    // re-toke
    $_token = strtok(" \n\t");
    // add word counter
    $_word_count++;
  }
  $_array_queries[] = array('f' => 'cql_end');
  return $_array_queries;
}
