### Pembuat barcode
<hr>
Masukkan kode-kode yang akan dibuat menjadi barcode pada kolom-kolom yang ada di layar. Tentukan ukuran barcode (Small, Medium, atau Big), kemudian klik tombol Generate Barcode. Maka barcode dapat dilihat dalam bentuk .html dan dapat dicetak dalam printer. Default encoding barcode yang digunakan adalah 128B. Anda dapat merubah encoding barcode ini pada file konfigurasi global SLiMS, sysconfig.inc.php. Temukan baris yang tertulis:

$sysconf['barcode_encoding'] = '128B';

Ubah nilai 128B menjadi tipe encoding yang anda inginkan. Pastikan direktori images bisa ditulis oleh web server anda.

Catatan: untuk kepentingan kualitas barcode, karakter yang dapat diproses dalam barcode generator hanyalah kumpulan angka dan huruf.
