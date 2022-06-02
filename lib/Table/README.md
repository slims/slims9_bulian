# Cara menggunakan
Pustaka ini digunakan untuk berinteraksi dengan tabel pada basis data SLiMS seperti mengetahui keberadaan tabel, kolom, mengubah kolom dll.

### Membuat tabel
```PHP
<?php

function up()
{
    // Membuat tabel
    Schema::create('read_counter', function(Blueprint $table){
        // Format : {object_blueprint} -> {tipe_kolom} -> {isi bawaan}

        // storage engine
        $table->engine = 'MyISAM';

        // character set
        $table->charset = 'utf8mb4';

        // Collate
        $table->collation = 'utf8mb4_unicode_ci';

        // membuat kolom beranam id dengan fungsi ikrement
        $table->autoIncrement('id');

        // membuat kolom bernama item_code tipe varchar dengan
        // lebar data 20 dan tidak nulll
        $table->string('item_code', 20)->notNull();
        $table->string('title', 255)->notNull();
        
        // Membuat kolom dengan tipe datetime bernama created_at
        $table->datetime('created_at')->notNull();

        // pentunjuk selengkapnya ada di lib/Table/Blueprint.php
    });
}
```

### Menghapus tabel
```PHP
<?php

function down()
{
    // Membuat tabel
    Schema::drop('read_counter');
}
```

### Mengosongkan tabel
```PHP
<?php

function down()
{
    // Membuat tabel
    Schema::truncate('read_counter');
}
```

### Mengubah kolom pada tabel
```PHP
<?php

function down()
{
    // Membuat tabel
    Schema::table('read_counter', function(Blueprint $table){
        // Format : {object_blueprint} -> {tipe_kolom} -> {isi bawaan} -> {pernyataan (drop,change, add)}

        // Merubah lebar data
        $table->string('item_code', 50)->notNull()->change();

        // Menghapus kolom
        $table->drop('kolom_test');

        // Menambah kolom baru
        $table->string('gmd', 5)->nullable()->after('title')->add();
    });
}
```