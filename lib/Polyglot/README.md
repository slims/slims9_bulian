# Polyglot
ini merupakan pustaka berdiri diatas pustaka **gettext** yang digunakan untuk mengelola penerjemahan bahasa dari bahasa Inggris ke bahasa lokal yang diatur via modul sistem. Disamping itu juga dapat dimanfaatkan untuk proses ekstensi terjemahan dari pengguna via sistem plugin, dengan demikian pengguna/pengembang tidak perlu repot mengganti isi terjamahan SLiMS sepenuh nya hanya untuk menambahkan satu atau dua terjamhan untuk perubahan yang dilakukan dimana terjemahan tidak tersedia secara langsung di SLiMS.

## Informasi Terjemahan
Format yang digunakan di SLiMS untuk perihal terjemahan bahasa menggunakan format .mo. Format tersebut meruapakan versi binary dari format .po (Portable Object) yang umumnya digunakan untuk proses penerjemahan bahasa pada perangkat lunak umumnya. Guna dapat mengubah isi .mo anda memerlukan perangkat lunak seperti [Poedit](https://poedit.net/).
Setiap terjemahan SLiMS disimpan pada folder ``` <slims-root>/lib/lang/locale/<code_CODE>/LC_MESSAGES/messages.(mo|po) ``` contoh misalnya ``` <slims-root>/lib/lang/locale/id_ID/LC_MESSAGES/messages.mo ```. 

## Terjemahan non file .mo
Jika anda tidak begitu paham cara kerja dari aplikasi poedit, anda dapat membuat terjemahan anda dalam bentuk file **.php**. Berikut cara nya:
1. Buat sebuah plugin baru pada folder plugins/
2. Misal saja translate.plugin.php
3. Isi informasi plugin tersebut sesuai dengan [standar informasi plugin di SLiMS](https://slims.github.io/docs/development-guide/Plugin/Intro#format-isi-plugin)
4. Buat sebuah file bernama ``lang.php`` (atau bisa anda custom), lalu contoh isinya sebagai berikut:
    ```php
    <?php
    return [
        'id_ID' => [
            'Stand By Me' => 'Tetap lah bersama ku',
            'Do the best' => 'Lakukan yang terbaik'
        ]
    ];
    ```
    Skrip diatas akan meterjemahkan kata 'Stand By Me' jika SLiMS anda sedang menggunakan Bahasa Indonesia. Contoh penggunaan terjemahan dalam kode anda sebagai berikut:
    ```php
    echo __('Stand By Me'); // menjadi Tetap lah bersama ku
    ```
    atau jika anda ingin menterjemahkan ke bahasa yang lain selain Bahasa Indonesia maka anda dapat membuat nya sebagai berikut:
    ```php
    <?php
    return [
        'id_ID' => [
            'Stand By Me' => 'Tetap lah bersama ku',
            'Do the best' => 'Lakukan yang terbaik'
        ],
         'ar_SA' => [
            'Stand By Me' => 'كن بجانبي'
        ]
    ];
    ```
    kode ar_SA, id_ID dapat anda lihat pada [laman ini](https://developers.staffbase.com/references/languages-and-locale-codes/#list-of-languages-and-locale-codes)
5. Setelah file diatas dibuat anda perlu menulis beberapa skrip didalam file ```*.plugin.php``` yang sudah anda buat sebelum nya sebagai berikut:
    ```php
    use SLiMS\Polyglot\Learn;
    Learn::newLanguage()->fromPhpFile(__DIR__ . '/lang.php');
    ```
6. Setelah itu aktifkan plugin anda pada module Sistem sub-menu Plugin