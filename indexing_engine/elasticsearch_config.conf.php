<?php
/**
 * @author              : Waris Agung Widodo
 * @Date                : 23/12/18 21.35
 * @Last Modified by    : ido
 * @Last Modified time  : 23/12/18 21.35
 *
 * Copyright (C) 2017  Waris Agung Widodo (ido.alit@gmail.com)
 */

$params['body'] = [
  'settings' => [
    'number_of_shards' => 3,
    'number_of_replicas' => 1
  ],
  'mappings' => array (
    'bibliography' => [
      '_source' => [
        'enabled' => true
      ],
      'properties' =>
        array (
          'id' =>
            array (
              'type' => 'keyword',

            ),
          'biblio_id' =>
            array (
              'type' => 'integer',
            ),
          'title' =>
            array (
              'type' => 'text',
            ),
          'gmd_name' =>
            array (
              'type' => 'keyword',

            ),
          'sor' =>
            array (
              'type' => 'keyword',

            ),
          'edition' =>
            array (
              'type' => 'keyword',

            ),
          'isbn_issn' =>
            array (
              'type' => 'keyword',

            ),
          'publisher_name' =>
            array (
              'type' => 'keyword',

            ),
          'publish_year' =>
            array (
              'type' => 'keyword',

            ),
          'collation' =>
            array (
              'type' => 'keyword',

            ),
          'series_title' =>
            array (
              'type' => 'keyword',
            ),
          'call_number' =>
            array (
              'type' => 'keyword',

            ),
          'language_name' =>
            array (
              'type' => 'keyword',

            ),
          'source' =>
            array (
              'type' => 'keyword',

            ),
          'place' =>
            array (
              'type' => 'keyword',

            ),
          'classification' =>
            array (
              'type' => 'keyword',

            ),
          'notes' =>
            array (
              'type' => 'keyword',

            ),
          'image' =>
            array (
              'type' => 'keyword',

            ),
          'opac_hide' =>
            array (
              'type' => 'keyword',

            ),
          'promoted' =>
            array (
              'type' => 'keyword',

            ),
          'labels' =>
            array (
              'type' => 'keyword',

            ),
          'frequency' =>
            array (
              'type' => 'keyword',

            ),
          'spec_detail_info' =>
            array (
              'type' => 'keyword',

            ),
          'content_type' =>
            array (
              'type' => 'keyword',

            ),
          'media_type' =>
            array (
              'type' => 'keyword',

            ),
          'carrier_type' =>
            array (
              'type' => 'keyword',

            ),
          'uid' =>
            array (
              'type' => 'keyword',

            ),
          'authors' =>
            array (
              'type' => 'nested',
              'properties' => [
                'author_name' => [
                  'type' => 'text',
                  'fielddata' => true
                ]
              ]
            ),
          'subjects' =>
            array (
              'type' => 'nested',
              'properties' => [
                'topic' => [
                  'type' => 'text',
                  'fielddata' => true
                ]
              ]
            ),
          'items' =>
            array (
              'type' => 'nested',
              'properties' => [
                'inventory_code' => [
                  'type' => 'text',
                ]
              ]
            ),
          'hash' =>
            array (
              'properties' =>
                array (
                  'biblio' =>
                    array (
                      'type' => 'keyword',

                    ),
                  'classification' =>
                    array (
                      'type' => 'keyword',

                    ),
                  'authors' =>
                    array (
                      'type' => 'keyword',

                    ),
                  'subjects' =>
                    array (
                      'type' => 'keyword',

                    ),
                  'image' =>
                    array (
                      'type' => 'keyword',

                    ),
                ),
            ),
          'input_date' =>
            array (
              'type' => 'keyword',

            ),
          'last_update' =>
            array (
              'type' => 'keyword',

            ),
        ),
    ]
  )
];
