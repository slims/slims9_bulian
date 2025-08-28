# SLiMS Cache

Pustaka ini berkaitan dengan konfigurasi file pada config/cache.php, secara bawaan akan menggunakan salah satu "Provider" yang tercantum pada opsi "default". Provider yang tersedia yaitu **Files** dan **Database**. Berikut ringkasan penggunaannya :

## Metoda yang tersedia (Pada Provider Files dan Database)
### Membuat cache baru
```php
<?php
use SLiMS\Cache;

/**
 * cacheName @string
 * contents @string|integer|array|objek 
 * yang nanti akan disimpan dalam format JSON
 */
Cache::set(cacheName: 'namacache', 'content');
```

### Mengambil cache yang sudah ada
```php
<?php
use SLiMS\Cache;

/**
 * cacheName @string
 * callBack @closure optional 
 */
Cache::get(cacheName: 'namacache', callBack: 'fungsi_kustom_anda');
```

### Memperbaharui cache yang sudah ada
```php
<?php
use SLiMS\Cache;

/**
 * cacheName @string
 * contents @string|integer|array|objek 
 * yang nanti akan disimpan dalam format JSON
 */
Cache::put(cacheName: 'namacache', contents: 'content');
```

### Menghapus cache yang sudah ada
```php
<?php
use SLiMS\Cache;

/**
 * cacheName @string
 */
Cache::destroy(cacheName: 'namacache');
```

### Mengkosongkan|Menghapus semua cache
```php
<?php
use SLiMS\Cache;

Cache::purge();
```

### Menampilkan semua cache yang tersimpan
```php
<?php
use SLiMS\Cache;

Cache::getList();
```

### Mengecek eksistensi cache
```php
<?php
use SLiMS\Cache;

/**
 * cacheName @string
 */
Cache::isExists(cacheName: 'namacache');
```

## Metoda yang hanya tersedia di Provider Database
### Mengecek apakah cache sudah kedaluwarsa
```php
<?php
use SLiMS\Cache;

/**
 * cacheName @string
 * terkait pengaturan/opsi kedaluwarsa nya anda
 * dapat melihat nya di config/cache.php pada 
 * providers database
 */
Cache::isExpire(cacheName: 'namacache');
```

### Mengupdate cache jika sudah kedaluwarsa
```php
<?php
use SLiMS\Cache;

/**
 * cacheName @string
 * contents @string|integer|array|objek 
 * 
 * sama dengan metoda Cache::put hanya saja ini
 * dikombinasikan dengan pengecak kedaluwarsa.
 */
Cache::putIfExpire(cacheName: 'namacache', contents: 'content');
```

## Membuat provider anda sendiri
Contoh kita akan membuat provider terkait Cache yang disimpan pada
Redis.

```php
<?php

class Redis extends \SLiMS\Cache\Contract
{
    private $options = null;

    /**
     * Register all options
     *
     * @param string $directory
     */
    public function __construct($optios)
    {
        $this->options = $options;
    }

    /**
     * Create a new cache files/value
     *
     * @param string $cacheName
     * @param mixed $contents
     * @return void
     */
    public function set(string $cacheName, $contents)
    {
        // tulis disini kode anda
    }

    /**
     * Get cache value
     *
     * @param string $cacheName
     * @param string $callBack
     * @return mixed
     */
    public function get(string $cacheName, $callBack = '')
    {
       // tulis disini kode anda
    }

    /**
     * Update cache value
     *
     * @param string $cacheName
     * @param mixed $contents
     * @return bool
     */
    public function put(string $cacheName, $contents)
    {
        // tulis disini kode anda
    }

    /**
     * Delete cache
     *
     * @param string $cacheName
     * @return void
     */
    public function destroy(string $cacheName)
    {
        // tulis disini kode anda
    }

    /**
     * Make cache clean as soon as posible
     *
     * @return void
     */
    public function purge()
    {
        // tulis disini kode anda
    }

    /**
     * Get path or key of cache
     *
     * @return string
     */
    public function getPath()
    {
        // tulis disini kode anda
    }

    /**
     * Get cache as list
     *
     * @return array
     */
    public function getList()
    {
        // tulis disini kode anda
    }

    /**
     * @return boolean
     */
    public function isExists(string $cacheName)
    {
        // tulis disini kode anda
    }
}

/**
 * Jika sudah membuat class seperti diatas, maka anda harus mendaftarkan provider anda
 * pada file config/cache.php pada opsi providers dengan format sebagai berikut
 * 
 *  'Redis' => [
 *     'class' => <another-cache-provider-namespace>
 *     'options' => [
 *         'prefix' => 'SLiMSCache:'
 *     ]
 *   ]
 */

```