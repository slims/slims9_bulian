<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 01/01/2022 9:31
 * @File name           : Contract.php
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


abstract class Contract
{
    use SearchFilter;

    protected int $page = 1;
    protected int $offset = 0;
    protected int $limit = 10;
    protected int $num_rows = 0;
    protected array $documents = [];
    protected array $custom_fields = [];
    protected Criteria $criteria;
    protected Criteria $filter;
    protected array $execute = [];
    protected string $error = '';
    public float $query_time = 0;
    public array $searchable_fields = ['title', 'author', 'isbn', 'subject', 'location', 'gmd', 'colltype', 'publisher', 'callnumber'];
    public array $stop_words = array('a', 'an', 'of', 'the', 'to', 'so', 'as', 'be');

    public function __construct()
    {
        // get current page
        $this->page = abs((int)($_GET['page'] ?? 1));
        // setup offset
        $this->offset = ($this->page - 1) * $this->limit;
    }

    /**
     * @param Criteria $criteria
     */
    public function setCriteria(Criteria $criteria): void
    {
        $this->criteria = $criteria;
    }

    /**
     * @param Criteria $criteria
     */
    public function setFilter(Criteria $criteria): void
    {
        $this->filter = $criteria;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getNumRows(): int
    {
        return $this->num_rows;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    abstract function dump(array $query);

    abstract function getDocuments();

    abstract function toArray();

    abstract function toJSON();

    abstract function toHTML();

    abstract function toXML();

    abstract function toRSS();
}