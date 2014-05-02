<?php
/**
 * simbio_qparser class
 * A search query parser, mapping from keyword query to SQL query
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

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) { 
    die("can not access this file directly");
}

class simbio_qparser
{
    private $keyword_query = '';
    private $default_fields = array();
    private $field_aliases = array();
    public $string_search_fields = array();
    public $exact_match_fields = array();

    /**
     * Class constructor
     *
     * @param   string  $str_query
     * @param   array   $arr_default_fields
     */
    public function __construct($str_query, $arr_default_fields)
    {
        $this->keyword_query = trim($str_query);
        $this->default_fields = $arr_default_fields;
    }


    /**
     * Method to add boolean symbol to each word in query
     *
     * @param   string  $str_words
     * @return  string
     */
    private function addBoolean($str_words)
    {
        $_result = '';
        foreach (explode(' ', $str_words) as $word) {
            $word = trim($word);
            if (stripos('+', $word) === false) {
                $_result .= '+'.$word.' ';
            } else { $_result .= $word.' '; }
        }

        return trim($_result);
    }


    /*
     * Method to extract phrase from string
     *
     * @param   array   $arr_phrase
     * return   void
     */
    private function phraseExtract(&$arr_phrase, $str_query = '')
    {
        // get the phrase query
        $_phrase_matches = array();
        $_phrase = preg_match_all('@(".+?")@i', !empty($str_query)?$str_query:$this->keyword_query, $_phrase_matches);
        $arr_phrase = $_phrase_matches[0];
        // remove all phrase from original query
        $str_query = trim(str_replace($arr_phrase, '', $this->keyword_query));
    }


    /**
     * Method to parse the query
     *
     */
    public final function parse()
    {
        if (!$this->keyword_query) {
            return;
        }
        $_result = '';
        $_original_query = $this->keyword_query;
        // get query with field qualifier
        $_qualifier_matches = array();
        $_qualifier = preg_match_all('@([a-z0-9_.]+:[^:]+[^a-z0-9_.:])@i', $this->keyword_query.' ', $_qualifier_matches);
        $_qualifier_query = $_qualifier_matches[0];
        // remove all qualifiers query from original query
        $this->keyword_query = trim(str_replace($_qualifier_query, '', $this->keyword_query.' '));

        // parse and map keyword with field qualifier
        if ($_qualifier_query) {
            foreach ($_qualifier_query as $_each_qualifier) {
                list($_field, $_words) = explode(':', $_each_qualifier);
                $_words = trim($_words);
                // check for field alias
                $_field = trim($_field);
                if (isset($this->field_aliases[$_field])) {
                    $_field = $this->field_aliases[$_field];
                }
                // get phrases
                $_phrases = array();
                $this->phraseExtract($_phrases, $_words);
                // check for field search method
                if (in_array($_field, $this->string_search_fields)) {
                    $_tmp_result = '( ';
                    $_tmp_words = explode(' ', $_words);
                    foreach ($_tmp_words as $_word) {
                        $_tmp_result .= "`$_field` LIKE '%$_word%' AND ";
                    }
                    $_tmp_result = substr_replace($_tmp_result, '', -5);
                    $_result .= $_tmp_result.' ) AND ';
                } else if (in_array($_field, $this->exact_match_fields)) {
                    if (preg_match('@.+\|.+@i', $_words)) {
                        $_concat_words = '';
                        $_tmp_words = explode('|', $_words);
                        foreach ($_tmp_words as $_word) {
                            $_concat_words .= "'$_word',";
                        }
                        $_concat_words = substr_replace($_concat_words, '', -1);
                        $_result .= $_field.' IN ('.$_concat_words.') AND ';
                    } else if ($_words == 'NOEMPTY') {
                        $_result .= '('.$_field.'!=\'\' OR '.$_field.' IS NOT NULL) AND ';
                    } else if ($_words == 'EMPTY') {
                        $_result .= '('.$_field.'=\'\' OR '.$_field.' IS NULL) AND ';
                    } else {
                        $_result .= $_field.'=\''.$_words.'\' AND ';
                    }
                } else {
                    $_words_str = $this->addBoolean($_words);
                    $_words_str = trim($_words_str);
                    if ($_words_str == '+') {
                        $_words_str = '';
                    }
                    $_phrases_str = '';
                    foreach ($_phrases as $_phrase) {
                        $_phrases_str .= $_phrase.' ';
                    }
                    $_phrases_str = trim($_phrases_str)?' '.$_phrases_str:'';
                    $_result .= "(MATCH(`$_field`) AGAINST ('$_words_str"."$_phrases_str' IN BOOLEAN MODE)) AND ";
                }
            }
            // remove the last AND
            $_result = substr_replace($_result, '', -5);
        }

        // check for the rest of query
        $_rest_result = '';
        $_rest_result_phrase = '';
        if ($this->keyword_query) {
            // phrase extract
            $_phrases2 = array();
            $this->phraseExtract($_phrases2);
            if ($_phrases2) {
                foreach ($this->default_fields as $_field) {
                    foreach ($_phrases2 as $_phrase) {
                        $_phrase = str_replace('"', '', $_phrase);
                        $_rest_result_phrase .= " `$_field` LIKE '%$_phrase%' OR ";
                    }
                }
                // remove the last AND
                $_rest_result_phrase = substr_replace($_rest_result_phrase, '', -4);
            }

            if ($this->keyword_query) {
                // explode string by space
                $_words = explode(' ', $this->keyword_query);
                foreach ($this->default_fields as $_field) {
                    $_rest_result .= "( ";
                    foreach ($_words as $_word) {
                        $_rest_result .= "`$_field` LIKE '%$_word%' AND ";
                    }
                    $_rest_result = substr_replace($_rest_result, '', -5);
                    $_rest_result .= " ) OR ";
                }
                // remove the last OR
                $_rest_result = substr_replace($_rest_result, '', -4);
            }
        }

        if ($_rest_result_phrase) {
            $_result .= ' AND ('.$_rest_result_phrase.')';
        }
        if ($_rest_result) {
            $_result .= ' AND ('.$_rest_result.')';
        }
        $_result = preg_replace('@^\s*OR\s|^\s*AND\s@i', '', $_result);
        return $_result;
    }


    /**
     * Method to set field aliasing
     *
     * @param   array   $arr_field_aliases
     * @return  void
     */
    public function setFieldAliases($arr_field_aliases)
    {
        $this->field_aliases = $arr_field_aliases;
    }


    /**
     * Method to set which field is searched by string method
     *
     * @param   array   $arr_fields
     * @return  void
     */
    public function setSearchStringFields($arr_fields)
    {
        $this->string_search_fields = $arr_fields;
    }


    /**
     * Method to set which field is searched with exact match
     *
     * @param   array   $arr_fields
     * @return  void
     */
    public function setExactMatchFields($arr_fields)
    {
        $this->exact_match_fields = $arr_fields;
    }
}
?>
