# Plugin System

## Bagaimana membuat sebuah plugin?
Untuk membuat sebuah plugin, mohon ikuti beberapa aturan berikut ini agar 
plugin anda bisa berjalan dengan baik.

- Letakan plugin anda pada folder `<slims root>/plugins`
- Anda dapat meletakannya langsung pada directory tersebut atau didalam 
*subfolder*. Direkomendasikan membuat *subfolder* untuk setiap plugin yang anda
kembangkan untuk alasan kemudahan pengelolaan kode sumber (*source code*).
- Nama plugin harus berakhiran `.plugin.php`, contoh: `contoh_saja.plugin.php`.
- Tambahkan informasi plugin anda dengan format sebagai berikut ini, dan 
letakan pada bagian paling atas file:

```php
    /**
    * Plugin Name: Contoh Nama Plugin
    * Plugin URI: <isikan alamat url dari repository plugin anda>
    * Description: Deskripsi dari plugin anda
    * Version: 0.0.1
    * Author: Nama Anda
    * Author URI: <isikan url dari profil anda>
    */
```
- Saat ini ada 2 jenis plugin:
    1. Plugin yang berdiri sendiri sebagai sebuah menu.
    2. Plugin yang melakukan _hooking_ terhadap fitur yang sudah ada.

## Menu Plugin
Jenis plugin ini akan menambahkan submenu pada sebuah modul. Berikut ini contoh
meregistrasikannya:

```php
// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering plugin in bibliography module
// Parameter 1 = nama module
// Parameter 2 = Text untuk menunya
// Parameter 3 = full path dari file yang akan digunakan
$plugin->registerMenu('bibliography', 'Label & Barcode', __DIR__ . '/index.php');
```

## Group Menu Plugin
Submenu yang ditambahkan dari plugin dapat dikelempokan. Berikut ini contoh 
untuk meregistrasikannya, pada contoh kali ini juga akan ditampilkan bagaimana 
meregistrasikan plugin dengan `static method`.

```php
use \SLiMS\Plugins;

Plugins::group('Nama Group', function() {
    Plugins::menu('nama_module', 'nama menu 1', '/path/dari/endpoint/pluginya/1.php');
    Plugins::menu('nama_module', 'nama menu 2', '/path/dari/endpoint/pluginya/2.php');
    Plugins::menu('nama_module', 'nama menu 3', '/path/dari/endpoint/pluginya/3.php');
});
```

Kelompok menu-menu ini juga dapat kita letakan sebelum atau setelah menu 
bawaan SLiMS. Sebagai contoh kita akan meletakan contoh menu plugin diatas 
setelah kelompok `Eksemplar` pada module `Bibliography`.

```php
Plugins::group('Nama Group', function() {
    # your code here
})->after(__('ITEMS'));
```

## Hooking Plugin
Jenis plugin ini akan berjalan saat sebuah fitur berada pada state tertentu.
Berikut ini contoh untuk meregistrasikannya:

```php
// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering hook
// parameter 1 = tag hook
// parameter 2 = callback
$plugin->register('bibliography_on_delete', function () {});

// Registering Hook via handler class
// opsi 1
Plugins::use(\Namespace\Library\Anda::class)->for(function($plugins){
    // chain style
    // parameter 1 = tag hook
    // parameter 2 = method yang ada di \Namespace\Library\Anda::class
    $plugins->registerHook('bibliography_after_save', 'saveTo3rdPartyIndexer');
    // atau menggunakan static class
    Plugins::hook('bibliography_after_save', 'saveTo3rdPartyIndexer');
});
// opsi 2 (jika tidak digabungkan dalam satu closure pada metode for())
Plugins::use(\Namespace\Library\Anda::class);
$plugins->registerHook('bibliography_after_save', 'saveTo3rdPartyIndexer');
```
Berikut ini `tag hook` yang tersedia:
- Modul bibliografi
    - `bibliography_init`: run on bibliography ready to go.
    - `bibliography_before_save`: run before bibliography data tobe saved. The 
    data will be save available in params.
    - `bibliography_after_save`: run after bibliography data saved 
    successfully. The data saved available in params.
    - `bibliography_before_update`: run before bibliography data tobe updated.
    The data will be update available in params.
    - `bibliography_after_update`: run after bibliography data updated 
    successfully. The data updated available in params.
    - `bibliography_before_delete`: run before data has been deleted.The data
    that will be deleted available in params.
    - `bibliography_on_delete`: run after data has been deleted. Bibliography
    ID available in param.

- Modul Keanggotaan.
    - `membership_init`: run on membership ready to go.
    - `membership_before_update`: run after member data before updated.
    - `membership_after_update`: run after member data after updated.
    - `membership_after_save`: run after new member data is created/saved.

- Modul Sirkulasi.
    - `circulation_after_successful_transaction`: run after circulation 
    transaction is done.

Secara default plugin tidak akan aktif. Anda harus mengaktifkannya di menu 
`System -> Plugins`.

## Migration
Sebuah plugin mungkin membutuhkan sebuah table tersendiri atau menambahkan 
kolom pada table yang sudah ada. Untuk memudahkan dalam manajemen database 
jika ada peningkatan versi, maka tool ini dapat membantu anda.

Untuk melakukannya, anda hanya perlu menambahkan folder `migration` di dalam 
folder plugin anda. Kemudian membuat sebuah file class dengan pola nama 
`<versi-migration>_<nama-class>.php`. Class ini harus memiliki method `up` dan 
`down` atau untuk memudahkan anda dapat meng-extend abstract class 
`\SLiMS\Migration\Migration`.

Contoh `1_CreateReadCounterTable.php` merupakan file class migration dalam 
plugin `read_counter`.

```php
class CreateReadCounterTable extends \SLiMS\Migration\Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    function up() {...}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    function down() {...}
}
```

Method `up` berisi kode untuk menjalankan migrasi dan method `down` untuk 
membalikannya. Sebagai contoh, method `up` berisi kode untuk membuat table 
`biblio` maka method `down` berisi kode untuk menghapus table `biblio`.

Berikut contoh struktur folder dari plugin `read_counter`:
- `read_counter`
    - index.php
    - read_counter_plugin.php
    - `migration`
        - 1_CreateReadCounterTable.php
        - 2_AddUIDColumn.php

-----------

# Plugin System - English version

## How to build a plugin?
To build a plugin, follow these rules in order to have a plugin to run without
problem.

- Place your plugin to `<slims root>/plugins` folder.
- Place your plugin within the folder or a folder within it.
- Make sure your plugin has `.plugin.php` for file name extension. For instance
  `example.plugin.php`.
- Also make sure to have additional information about your plugin, as follow 
and place it with comment at the top of the file.

```php
    /**
     * Plugin Name: Plugin name example
     * Plugin URI: <place here you plugin repository address>
     * Description: Your plugin description
     * Version: 0.0.1
     * Author: Your Name
     * Author URI: <place here url of your profile>
     */
```
- Currently, there are 2 (two) types of plugin:
    1. An independent plugin as part of a submenu.
    2. Plugin called _hooking_ to an existed feature.

## Menu Plugin
A submenu will be added on a module, with this kind of plugin.
How to register it is explain below:

```php
// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering plugin in bibliography module
// Parameter 1 = Module's name
// Parameter 2 = Menu's text
// Parameter 3 = File's full path
$plugin->registerMenu('bibliography', 'Label & Barcode', __DIR__ . '/index.php');
```

## Hooking Plugin
This type of plugin will run over a feature under certain state.
Below is an example of registering it:

```php
// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering hook
// parameter 1 = hook's tag
// parameter 2 = callback
$plugin->register('bibliography_on_delete', function () {});

// Registering Hook via handler class
// option 1
Plugins::use(\Your\Library\Namespace::class)->for(function($plugins){
    // chain style
    // parameter 1 = tag hook
    // parameter 2 = public method at \Namespace\Library\Anda::class
    $plugins->registerHook('bibliography_after_save', 'saveTo3rdPartyIndexer');
    // or use static class
    Plugins::hook('bibliography_after_save', 'saveTo3rdPartyIndexer');
});
// option 2 (if not combined in one closure in for() method)
Plugins::use(\Your\Library\Namespace::class);
$plugins->registerHook('bibliography_after_save', 'saveTo3rdPartyIndexer');
```
Available `tag hook` are:

- Bibliography module
    - `bibliography_init`: run on bibliography ready to go.
    - `bibliography_before_save`: run before bibliography data tobe saved. The 
    data will be save available in params.
    - `bibliography_after_save`: run after bibliography data saved 
    successfully. The data saved available in params.
    - `bibliography_before_update`: run before bibliography data tobe updated.
    The data will be update available in params.
    - `bibliography_after_update`: run after bibliography data updated 
    successfully. The data updated available in params.
    - `bibliography_before_delete`: run before data has been deleted.The data
    that will be deleted available in params.
    - `bibliography_on_delete`: run after data has been deleted. Bibliography
    ID available in param.

- Membership module.
    - `membership_init`: run on membership ready to go.
    - `membership_before_update`: run after member data before updated.
    - `membership_after_update`: run after member data after updated.
    - `membership_after_save`: run after new member data is created/saved.

- Circulation module.
    - `circulation_after_successful_transaction`: run after circulation 
    transaction is done.

By default, this plugin is inactive. You need to activate it in 
`System -> Plugins` menu.