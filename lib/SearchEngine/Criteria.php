<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 01/01/2022 13:25
 * @File name           : Criteria.php
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

namespace SLiMS\SearchEngine;


use Generator;

class Criteria
{
    protected array $queries = [];
    protected array $properties = [];
    public ?string $keywords = null;

    public function __get($name)
    {
        return $this->properties[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->properties[$name] = trim($value);
        return $this;
    }

    public function exact($field, $value)
    {
        if (!empty($this->queries)) $this->queries[] = ['boolean', 'exact'];
        $this->addCriteria($field, $value);
    }

    public function and($field, $value)
    {
        if (!empty($this->queries)) $this->queries[] = ['boolean', 'and'];
        $this->addCriteria($field, $value);
    }

    public function or($field, $value)
    {
        if (!empty($this->queries)) $this->queries[] = ['boolean', 'or'];
        $this->addCriteria($field, $value);
    }

    public function not($field, $value)
    {
        if (!empty($this->queries)) $this->queries[] = ['boolean', 'not'];
        $this->addCriteria($field, $value);
    }

    private function addCriteria($field, $value) {
        $this->queries[] = [$field, $value];
        $this->{$field} = $value;
    }

    public function getQueries(): string
    {
        $q = [];
        foreach ($this->queries as $query) {
            if ($query[0] !== 'boolean') $q[] = implode('=', $query);
        }
        return implode('&', $q);
    }

    public function removeCriteria($fields)
    {
        if (!is_array($fields)) $fields = [$fields];
        foreach ($this->queries as $index => $arr) {
            if (in_array($arr[0], $fields)) {
                // unset current fielad
                unset($this->queries[$index]);
                // unset next field if it's a boolean
                if (isset($this->queries[$index+1]) && $this->queries[$index+1][0] == 'boolean')
                    unset($this->queries[$index+1]);
                
            }
        }
    }

    public function isBool($value)
    {
        return preg_match('@\b(exact|and|or|not)\b@i', $value);
    }

    public function separateBooleanChar($value)
    {
        return array_filter(explode(' ', $value), function($value){
            if (!$this->isBool($value)) return true;
        });
    }

    public function convertToBooleanChar($value)
    {
        $result = '';
        $matchBoolean = '+';
        preg_match('@\b(exact|and|or|not)\b@i', $value, $match);
        
        if (count($match))
        {
            $bool = strtolower($match[0]??'AND');
            switch ($bool) {
                case 'exact':
                    $matchBoolean = '++';
                    break;
                case 'or':
                    $matchBoolean = '*';
                    break;
                case 'not':
                    $matchBoolean = '-';
                    break;
            }
        }

        return [implode(' ' .$matchBoolean, $this->separateBooleanChar($value)), $matchBoolean];
    }

    /**
     * CQL Tokenizer
     * Tokenize CQL string to array for easy processing
     *
     * This method implement simbio_tokenizeCQL by Arie Nugraha (dicarve@yahoo.com)
     *
     * @param array $stop_words
     * @param int $max_words
     * @return Generator
     */
    function toCQLToken(array $stop_words = [], int $max_words = 20): Generator
    {
        $inside_quote = false;
        $phrase = '';
        $last_boolean = '+';
        $word_count = 0;
        foreach ($this->queries as $item) {
            // SAFEGUARD!
            if ($word_count > $max_words) break;

            list($key, $value) = $item;

            // check for stop words
            if (in_array($value, $stop_words)) continue;

            // check for boolean mode
            if ($this->isBool($value)) {
                list($query, $last_boolean) = $this->convertToBooleanChar($value);
                yield ['f' => $key, 'q' => $query, 'b' => $last_boolean];
                continue;
            }

            // check if we are inside quotes
            if ($value[0] === '"') {
                $inside_quote = true;
                // remove the first quote
                $value = substr_replace($value, '', 0, 1);
            }
            if ($inside_quote) {
                if (strpos($value, '"') === strlen($value) - 1) {
                    $inside_quote = false;
                    $phrase .= str_replace('"', '', $value);
                    yield ['f' => $key, 'b' => $last_boolean, 'q' => $phrase, 'is_phrase' => true];
                    // reset phrase
                    $phrase = '';
                } else {
                    $phrase .= str_replace('"', '', $value) . ' ';
                    continue;
                }
            } else {
                if (stripos($value, '(') === true) {
                    yield ['f' => 'opengroup', 'b' => $last_boolean];
                } elseif (stripos($value, ')') === true) {
                    yield ['f' => 'closegroup', 'b' => $last_boolean];
                } else {
                    yield ['f' => $key, 'b' => $last_boolean, 'q' => $value];
                }
            }
            $word_count++;
        }
        yield ['f' => 'cql_end'];
    }
}