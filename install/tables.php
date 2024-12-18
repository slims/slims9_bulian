<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2020-01-12 11:05
 * @File name           : tables.php
 */

return [
  [
    'table' => 'backup_log',
    'column' => [
      [
        'field' => 'backup_log_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'user_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'backup_time',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'backup_file',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'biblio',
    'column' => [
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'gmd_id',
        'type' => 'int(3)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'title',
        'type' => 'text',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'sor',
        'type' => 'varchar(200)	',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'edition',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'isbn_issn',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'publisher_id',
        'type' => 'int(11)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'publish_year',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'collation',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'series_title',
        'type' => 'varchar(200)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'call_number',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'language_id',
        'type' => 'char(5)',
        'null' => true,
        'default' => 'en'
      ],
      [
        'field' => 'source',
        'type' => 'varchar(3)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'publish_place_id',
        'type' => 'int(11)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'classification',
        'type' => 'varchar(40)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'notes',
        'type' => 'text',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'image',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'file_att',
        'type' => 'varchar(255)	',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'opac_hide',
        'type' => 'smallint(1)',
        'null' => true,
        'default' => 0
      ],
      [
        'field' => 'promoted',
        'type' => 'smallint(1)',
        'null' => true,
        'default' => 0
      ],
      [
        'field' => 'labels',
        'type' => 'text',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'frequency_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 0
      ],
      [
        'field' => 'spec_detail_info',
        'type' => 'text',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'content_type_id',
        'type' => 'int(11)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'media_type_id',
        'type' => 'int(11)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'carrier_type_id',
        'type' => 'int(11)',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'datetime',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'uid',
        'type' => 'int(11)',
        'null' => true,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'biblio_attachment',
    'column' => [
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'file_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'access_type',
        'type' => "enum('public', 'private')",
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'access_limit',
        'type' => 'text',
        'null' => true,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'biblio_author',
    'column' => [
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'author_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'level',
        'type' => 'int(1)',
        'null' => false,
        'default' => '1'
      ],
    ]
  ],
  [
    'table' => 'biblio_custom',
    'column' => [
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'biblio_log',
    'column' => [
      [
        'field' => 'biblio_log_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'user_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'realname',
        'type' => 'varchar(100)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'title',
        'type' => 'text',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'ip',
        'type' => 'varchar(200)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'action',
        'type' => 'varchar(50)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'affectedrow',
        'type' => 'varchar(50)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'rawdata',
        'type' => 'text',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'additional_information',
        'type' => 'text',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'biblio_relation',
    'column' => [
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'rel_biblio_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'rel_type',
        'type' => 'int(1)',
        'null' => true,
        'default' => '1'
      ],
    ]
  ],
  [
    'table' => 'biblio_topic',
    'column' => [
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'topic_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'level',
        'type' => 'int(1)',
        'null' => false,
        'default' => '1'
      ],
    ]
  ],
  [
    'table' => 'comment',
    'column' => [
      [
        'field' => 'comment_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'member_id',
        'type' => 'varchar(20)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'comment',
        'type' => 'text',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'datetime',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'content',
    'column' => [
      [
        'field' => 'content_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'content_title',
        'type' => 'varchar(255)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'content_desc',
        'type' => 'text',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'content_path',
        'type' => 'varchar(20)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'is_news',
        'type' => 'smallint(1)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'content_ownpage',
        'type' => "enum('1', '2')",
        'null' => false,
        'default' => '1'
      ],
    ]
  ],
  [
    'table' => 'files',
    'column' => [
      [
        'field' => 'file_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'file_title',
        'type' => 'text',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'file_name',
        'type' => 'text',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'file_url',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'file_dir',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'mime_type',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'file_desc',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'file_key',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'uploader_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'fines',
    'column' => [
      [
        'field' => 'fines_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'fines_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'member_id',
        'type' => 'varchar(20)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'debet',
        'type' => 'int(11)',
        'null' => true,
        'default' => '0'
      ],
      [
        'field' => 'credit',
        'type' => 'int(11)',
        'null' => true,
        'default' => '0'
      ],
      [
        'field' => 'description',
        'type' => 'varchar(255)',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'group_access',
    'column' => [
      [
        'field' => 'group_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'module_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'r',
        'type' => 'int(1)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'w',
        'type' => 'int(1)',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'holiday',
    'column' => [
      [
        'field' => 'holiday_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'holiday_dayname',
        'type' => 'varchar(20)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'holiday_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'description',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'item',
    'column' => [
      [
        'field' => 'item_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'call_number',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'coll_type_id',
        'type' => 'int(3)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'item_code',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'inventory_code',
        'type' => 'varchar(200)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'received_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'supplier_id',
        'type' => 'varchar(6)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'order_no',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'location_id',
        'type' => 'varchar(3)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'order_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'item_status_id',
        'type' => 'char(3)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'site',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'source',
        'type' => 'int(1)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'invoice',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'price',
        'type' => 'int(11)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'price_currency',
        'type' => 'varchar(10)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'invoice_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'uid',
        'type' => 'int(11)',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'kardex',
    'column' => [
      [
        'field' => 'kardex_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'date_expected',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'date_received',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'seq_number',
        'type' => 'varchar(25)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'notes',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'serial_id',
        'type' => 'int(11)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'loan',
    'column' => [
      [
        'field' => 'loan_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'item_code',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'member_id',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'loan_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'due_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'renewed',
        'type' => 'int(11)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'loan_rules_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'actual',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'is_lent',
        'type' => 'int(1)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'is_return',
        'type' => 'int(1)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'return_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'datetime',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'uid',
        'type' => 'int(11)',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'member',
    'column' => [
      [
        'field' => 'member_id',
        'type' => 'varchar(100)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'member_name',
        'type' => 'varchar(100)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'gender',
        'type' => 'int(1)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'birth_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'member_type_id',
        'type' => 'int(6)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'member_address',
        'type' => 'varchar(255)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'member_mail_address',
        'type' => 'varchar(255)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'member_email',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'postal_code',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'inst_name',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'is_new',
        'type' => 'int(1)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'member_image',
        'type' => 'varchar(200)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'pin',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'member_phone',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'member_fax',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'member_since_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'register_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'expire_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'member_notes',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'is_pending',
        'type' => 'smallint(1)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'mpasswd',
        'type' => 'varchar(64)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_login',
        'type' => 'datetime',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_login_ip',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'member_custom',
    'column' => [
      [
        'field' => 'member_id',
        'type' => 'varchar(20)',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'mst_author',
    'column' => [
      [
        'field' => 'author_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'author_name',
        'type' => 'varchar(100)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'author_year',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'authority_type',
        'type' => "enum('p', 'o', 'c')",
        'null' => false,
        'default' => 'p'
      ],
      [
        'field' => 'auth_list',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_carrier_type',
    'column' => [
      [
        'field' => 'id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'carrier_type',
        'type' => 'varchar(100)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'code',
        'type' => 'varchar(5)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'code2',
        'type' => 'char(1)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'mst_coll_type',
    'column' => [
      [
        'field' => 'coll_type_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'coll_type_name',
        'type' => 'varchar(30)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_content_type',
    'column' => [
      [
        'field' => 'id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'content_type',
        'type' => 'varchar(100)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'code',
        'type' => 'varchar(5)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'code2',
        'type' => 'char(1)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'mst_frequency',
    'column' => [
      [
        'field' => 'frequency_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'frequency',
        'type' => 'varchar(25)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'language_prefix',
        'type' => 'varchar(5)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'time_increment',
        'type' => 'smallint(6)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'time_unit',
        'type' => "enum('day', 'week', 'month', 'year')",
        'null' => false,
        'default' => 'day'
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'mst_gmd',
    'column' => [
      [
        'field' => 'gmd_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'gmd_code',
        'type' => 'varchar(3)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'gmd_name',
        'type' => 'varchar(30)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'icon_image',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_item_status',
    'column' => [
      [
        'field' => 'item_status_id',
        'type' => 'char(3)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'item_status_name',
        'type' => 'varchar(30)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'rules',
        'type' => 'varchar(255)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'no_loan',
        'type' => 'smallint(1)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'skip_stock_take',
        'type' => 'smallint(1)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_label',
    'column' => [
      [
        'field' => 'label_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'label_name',
        'type' => 'varchar(20)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'label_desc',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'label_image',
        'type' => 'varchar(200)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'mst_language',
    'column' => [
      [
        'field' => 'language_id',
        'type' => 'char(5)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'language_name',
        'type' => 'varchar(20)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_loan_rules',
    'column' => [
      [
        'field' => 'loan_rules_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'member_type_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'coll_type_id',
        'type' => 'int(11)',
        'null' => true,
        'default' => '0'
      ],
      [
        'field' => 'gmd_id',
        'type' => 'int(11)',
        'null' => true,
        'default' => '0'
      ],
      [
        'field' => 'loan_limit',
        'type' => 'int(3)',
        'null' => true,
        'default' => '0'
      ],
      [
        'field' => 'loan_periode',
        'type' => 'int(3)',
        'null' => true,
        'default' => '0'
      ],
      [
        'field' => 'reborrow_limit',
        'type' => 'int(3)',
        'null' => true,
        'default' => '0'
      ],
      [
        'field' => 'fine_each_day',
        'type' => 'int(3)',
        'null' => true,
        'default' => '0'
      ],
      [
        'field' => 'grace_periode',
        'type' => 'int(2)',
        'null' => true,
        'default' => '0'
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_location',
    'column' => [
      [
        'field' => 'location_id',
        'type' => 'varchar(3)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'location_name',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'mst_media_type',
    'column' => [
      [
        'field' => 'id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'media_type',
        'type' => 'varchar(100)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'code',
        'type' => 'varchar(5)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'code2',
        'type' => 'char(1)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'mst_member_type',
    'column' => [
      [
        'field' => 'member_type_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'member_type_name',
        'type' => 'varchar(50)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'loan_limit',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'loan_periode',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'enable_reserve',
        'type' => 'int(11)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'reserve_limit',
        'type' => 'int(11)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'member_periode',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'reborrow_limit',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'fine_each_day',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'grace_periode',
        'type' => 'int(2)',
        'null' => true,
        'default' => 0
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_module',
    'column' => [
      [
        'field' => 'module_id',
        'type' => 'int(3)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'module_name',
        'type' => 'varchar(50)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'module_path',
        'type' => 'varchar(200)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'module_desc',
        'type' => 'varchar(255)',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_place',
    'column' => [
      [
        'field' => 'place_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'place_name',
        'type' => 'varchar(30)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_publisher',
    'column' => [
      [
        'field' => 'publisher_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'publisher_name',
        'type' => 'varchar(100)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_relation_term',
    'column' => [
      [
        'field' => 'ID',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'rt_id',
        'type' => 'varchar(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'rt_desc',
        'type' => 'varchar(50)',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'mst_servers',
    'column' => [
      [
        'field' => 'server_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'name',
        'type' => 'varchar(255)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'uri',
        'type' => 'text',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'server_type',
        'type' => 'tinyint(1)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_supplier',
    'column' => [
      [
        'field' => 'supplier_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'supplier_name',
        'type' => 'varchar(100)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'address',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'postal_code',
        'type' => 'char(10)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'phone',
        'type' => 'char(14)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'contact',
        'type' => 'char(30)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'fax',
        'type' => 'char(14)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'account',
        'type' => 'char(12)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'e_mail',
        'type' => 'char(80)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_topic',
    'column' => [
      [
        'field' => 'topic_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'topic',
        'type' => 'varchar(50)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'topic_type',
        'type' => "enum('t', 'g', 'n', 'tm', 'gr', 'oc')",
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'auth_list',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'classification',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'mst_voc_ctrl',
    'column' => [
      [
        'field' => 'vocabolary_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'topic_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'rt_id',
        'type' => 'varchar(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'related_topic_id',
        'type' => 'varchar(250)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'scope',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'reserve',
    'column' => [
      [
        'field' => 'reserve_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'member_id',
        'type' => 'varchar(20)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'item_code',
        'type' => 'varchar(20)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'reserve_date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'search_biblio',
    'column' => [
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'title',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'edition',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'isbn_issn',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'author',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'topic',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'gmd',
        'type' => 'varchar(30)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'publisher',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'publish_place',
        'type' => 'varchar(30)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'language',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'classification',
        'type' => 'varchar(40)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'spec_detail_info',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'carrier_type',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'content_type',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'media_type',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'location',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'publish_year',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'notes',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'series_title',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'items',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'collection_types',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'call_number',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'opac_hide',
        'type' => 'smallint(1)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'promoted',
        'type' => 'smallint(1)',
        'null' => false,
        'default' => '0'
      ],
      [
        'field' => 'labels',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'collation',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'image',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'datetime',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'serial',
    'column' => [
      [
        'field' => 'serial_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'date_start',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'date_end',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'period',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'notes',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'biblio_id',
        'type' => 'int(11)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'gmd_id',
        'type' => 'int(11)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => false,
        'default' => ''
      ]
    ]
  ],
  [
    'table' => 'setting',
    'column' => [
      [
        'field' => 'setting_id',
        'type' => 'int(3)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'setting_name',
        'type' => 'varchar(30)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'setting_value',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'stock_take',
    'column' => [
      [
        'field' => 'stock_take_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'stock_take_name',
        'type' => 'varchar(200)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'start_date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'end_date',
        'type' => 'datetime',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'init_user',
        'type' => 'varchar(50)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'total_item_stock_taked',
        'type' => 'int(11)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'total_item_lost',
        'type' => 'int(11)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'total_item_exists',
        'type' => 'int(11)',
        'null' => true,
        'default' => '0'
      ],
      [
        'field' => 'total_item_loan',
        'type' => 'int(11)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'stock_take_users',
        'type' => 'mediumtext',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'is_active',
        'type' => 'int(1)',
        'null' => true,
        'default' => '0'
      ],
      [
        'field' => 'report_file',
        'type' => 'varchar(255)',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'stock_take_item',
    'column' => [
      [
        'field' => 'stock_take_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'item_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'item_code',
        'type' => 'varchar(20)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'title',
        'type' => 'varchar(255)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'gmd_name',
        'type' => 'varchar(30)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'classification',
        'type' => 'varchar(30)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'coll_type_name',
        'type' => 'varchar(30)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'call_number',
        'type' => 'varchar(50)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'location',
        'type' => 'varchar(100)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'status',
        'type' => "enum('e', 'm', 'u', 'l')",
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'checked_by',
        'type' => 'varchar(50)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'last_update',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'system_log',
    'column' => [
      [
        'field' => 'log_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'log_type',
        'type' => "enum('staff', 'member', 'system')",
        'null' => false,
        'default' => 'staff'
      ],
      [
        'field' => 'id',
        'type' => 'varchar(50)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'log_location',
        'type' => 'varchar(50)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'log_msg',
        'type' => 'text',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'log_date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
  [
    'table' => 'user',
    'column' => [
      [
        'field' => 'user_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'username',
        'type' => 'varchar(50)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'realname',
        'type' => 'varchar(100)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'passwd',
        'type' => 'varchar(64)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => '2fa',
        'type' => 'text',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'email',
        'type' => 'varchar(200)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'user_type',
        'type' => 'smallint(2)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'user_image',
        'type' => 'varchar(250)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'social_media',
        'type' => 'text',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_login',
        'type' => 'datetime',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_login_ip',
        'type' => 'char(15)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'groups',
        'type' => 'varchar(200)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'forgot',
        'type' => 'varchar(80)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'admin_template',
        'type' => 'text',
        'null' => true,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'user_group',
    'column' => [
      [
        'field' => 'group_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'group_name',
        'type' => 'varchar(30)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'input_date',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'last_update',
        'type' => 'date',
        'null' => true,
        'default' => null
      ],
    ]
  ],
  [
    'table' => 'visitor_count',
    'column' => [
      [
        'field' => 'visitor_id',
        'type' => 'int(11)',
        'null' => false,
        'default' => 'AUTO_INCREMENT'
      ],
      [
        'field' => 'member_id',
        'type' => 'varchar(20)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'member_name',
        'type' => 'varchar(255)',
        'null' => false,
        'default' => ''
      ],
      [
        'field' => 'institution',
        'type' => 'varchar(100)',
        'null' => true,
        'default' => null
      ],
      [
        'field' => 'checkin_date',
        'type' => 'datetime',
        'null' => false,
        'default' => ''
      ],
    ]
  ],
];