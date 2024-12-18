<?php
/**
 * @author              : Waris Agung Widodo
 * @Date                : 23/12/18 21.47
 * @Last Modified by    : ido
 * @Last Modified time  : 23/12/18 21.47
 *
 * Copyright (C) 2017  Waris Agung Widodo (ido.alit@gmail.com)
 */

class biblio_indexer
{

  private $db = null;
  private $client = null;

  public function __construct($dbs, Elasticsearch\Client $client)
  {
    $this->db = $dbs;
    $this->client = $client;
  }

  function createIndex($name = 'slims') {

  }

  function deleteIndex($name) {}

  function isIndexExist($name) {
    // if ($this->client->info());
  }

}