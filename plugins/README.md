# Plugin System

### Bagaimana membuat sebuah plugin?
Untuk membuat sebuah plugin, mohon ikuti beberapa aturan berikut ini agar plugin anda bisa berjalan dengan baik.

- Letakan plugin anda pada folder `<slims root>/plugins`
- Anda dapat meletakannya langsung pada directory tersebut atau berada di subfolder
- Pastikan nama plugin berakhiran `.plugin.php` contohnya `contoh_saja.plugin.php`
- Patikan juga tambahkan informasi plugin anda dengan format sebagai berikut ini dan letakan sebagai komentar pada bagian paling atas file.
```
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
1. Plugin yang berdiri sendiri sebagai sebuah menu
2. Plugin yang melakukan _hooking_ terhadap fitur yang sudah ada

#### Menu Plugin
Jenis plugin ini akan menambahkan submenu pada sebuah modul.
Berikut ini contoh untuk meregistrasikannya:

```
// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering plugin in bibliography module
// Parameter 1 = nama module
// Parameter 2 = Text untuk menunya
// Parameter 3 = full path dari file yang akan digunakan
$plugin->registerMenu('bibliography', 'Label & Barcode', __DIR__ . '/index.php');
```

#### Hooking Plugin
Jenis plugin ini akan berjalan saat sebuah fitur berada pada state tertentu.
Berikut ini contoh untuk meregistrasikannya:
```
// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering hook
// parameter 1 = tag hook
// parameter 2 = callback
$plugin->register('bibliography_on_delete', function () {});
```
Berikut ini `tag hook` yang tersedia:

- **bibliography_init**: run on bibliography ready to go
- **bibliography_before_save**: run before bibliography data tobe saved. The data will be save available in params
- **bibliography_after_save**: run after bibliography data saved successfully. The data saved available in params
- **bibliography_before_update**: run before bibliography data tobe updated. The data will be update available in params
- **bibliography_after_update**: run after bibliography data updated successfully. The data updated available in params
- **bibliography_on_delete**: run after data has been deleted. Bibliography ID available in param

Secara default plugin tidak akan aktif. Anda harus mengaktifkannya di menu `System -> Plugins`