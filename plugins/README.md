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

-----------

# Plugin System - English version

### How to build a plugin?
To build a plugin, follow these rules in order to have a plugin to run without problem.

- Place your plugin to `<slims root>/plugins` folder
- Place your plugin within the folder or a folder within it
- Make sure your plugin has `.plugin.php` for file name extension. For instance `example.plugin.php`
- Also make sure to have additional information about your plugin, as follow and place it with comment at the top of the file.
```
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
1. An independent plugin as part of a submenu
2. Plugin called _hooking_ to an existed feature

#### Menu Plugin
A submenu will be added on a module, with this kind of plugin.
How to register it is explain below:

```
// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering plugin in bibliography module
// Parameter 1 = Module's name
// Parameter 2 = Menu's text
// Parameter 3 = File's full path
$plugin->registerMenu('bibliography', 'Label & Barcode', __DIR__ . '/index.php');
```

#### Hooking Plugin
This type of plugin will run over a feature under certain state.
Below is an example of registering it:
```
// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering hook
// parameter 1 = hook's tag
// parameter 2 = callback
$plugin->register('bibliography_on_delete', function () {});
```
Available `tag hook` are:

- **bibliography_init**: run on bibliography ready to go
- **bibliography_before_save**: run before bibliography data to be saved. The data will be saved available in params
- **bibliography_after_save**: run after bibliography data saved successfully. The data saved available in params
- **bibliography_before_update**: run before bibliography data tobe updated. The data will be update available in params
- **bibliography_after_update**: run after bibliography data updated successfully. The data updated available in params
- **bibliography_on_delete**: run after data has been deleted. Bibliography ID available in params

By default, this plugin is inactive. You need to activate it in `System -> Plugins` menu