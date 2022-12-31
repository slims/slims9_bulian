# Cara menggunakan
Pustaka ini digunakan untuk berinteraksi dengan tabel pada basis data SLiMS seperti mengetahui keberadaan tabel, kolom, mengubah kolom dll.

### Membuat tabel
```PHP
<?php
use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;

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
use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;

function down()
{
    // Membuat tabel
    Schema::drop('read_counter');
}
```

### Mengosongkan tabel
```PHP
<?php
use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;

function down()
{
    // Membuat tabel
    Schema::truncate('read_counter');
}
```

### Mengubah kolom pada tabel
```PHP
<?php
use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;

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

### Mendapatkan meta data dari subah Tabel dan Kolom
```PHP
<?php
use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;

function up()
{
    // Ketersediaan
    Schema::table('biblio')->isExists(); // mengecek ketersediaan tabel
    Schema::table('biblio')->isEmpty();
    Schema::table('biblio')->column('title')->isExists(); // mengecek ketersediaan kolom title pada tabel biblio

    // Tipe data
    Schema::table('biblio')->column('title')->isNull(); // cek apakah isi bawaan mua itu null?
    Schema::table('biblio')->column('title')->isPrimary(); // apakah kolom tersebut adalah primary?
    Schema::table('biblio')->column('title')->isUnique(); // apakah kolom title memiliki key unique?
    Schema::table('biblio')->column('title')->isFullText(); // apakah kolom teks menggunakan key full text
    Schema::table('biblio')->column('title')->isAutoincrement(); // apakah nilai kolom title bertambah setiap ada databaru?

    // mengambil informasi tabel
    Schema::table('biblio')->getEngine(); // mendapat kan storage engine . Contoh : MyISAM,Aria,InnoDB
    Schema::table('biblio')->getRowCount(); // medapatkan jumlah baris pada tabel
    Schema::table('biblio')->getCollation(); // mendapatkan kolasi dari tabel
    Schema::table('biblio')->getAutoincrement(); // mendapatkan nilai terakhir dari AutoIncrement
    Schema::table('biblio')->getComment()); // mendapatkan komentar pada tabel

    // mengambil informasi kolom
    Schema::table('biblio')->column('title')->getType(); // mendapatkan tipe data. Contoh : varchar dll
    Schema::table('biblio')->column('title')->getPosition(); // mendapatkan posisi lokasi kolom pada tabel tersebut
    Schema::table('biblio')->column('title')->getCollation(); // medapatkan kolasinya
    Schema::table('biblio')->column('title')->getAutoincrement(); // mendapatkan status apakah dia auto increment atau tidak
    Schema::table('biblio')->column('title')->getComment(); // mendapatkan komentar
    Schema::table('biblio')->column('title')->getMaxLength(); // mendapatkan panjang maksimal dari kolom tersebut.
    Schema::table('biblio')->column('title')->getKey(); // mendapatkan key, seperti PrimaryKey, Unique, Index, Mul
}
```