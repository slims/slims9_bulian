####Lihat Daftar Anggota
<hr>
Digunakan untuk melihat anggota yang telah terdaftar dalam sistem. Informasi yang ditampilkan adalah
- Member ID,
- Member Name,
- Membership Type,
- Email,
- Last update.

####Tambah Anggota Baru
<hr>
Fasilitas untuk menambahkan data anggota baru ke dalam sistem Senayan. Data anggota yang dimasukkan adalah:
- Member ID (ID Anggota -barcode/RFID-),
- Member Name (Nama Lengkap Anggota),
- Register Date (tanggal mendatar),
- Expiry Date (tanggal kadaluarsa),
- Institution (nama institusi, nama kantor atau nama organisasi),
- Membership Type (Tipe Keanggotaan),
- Gender (Jenis Kelamin),
- E-mail,
- Address (Alamat rumah atau kantor),
- Postal Code (Kode Pos),
- Phone Number (Nomor Telepon),
- Fax Number (Nomor Fax),
- Personal ID Number (Nomor ID Personal seperti no. KTP),
- Notes (Catatan singkat),
- dan Upload Photo (File foto anggota).

Dalam Expiry date terdapat Auto Set, maksud dari fasilitas ini, jika auto set di check maka tanggal expired anggota akan dihitung berdasar Membership Type. Namun jika di uncheck, maka Expiry date dapat ditentukan secara manual, dengan memilih tanggal Expirednya.

Pada form ini pula, disediakan fitur Pending Membership. Jika Pending Membership ini di check, maka anggota yang bersangkutan tidak akan dapat melakukan sirkulasi, meskipun masih aktif. Hal ini dapat diterapkan sebagai sanksi kepada anggota yang melanggar peraturan perpustakaan.

SLiMS mempunyai fitur yang membantu pustakawan dalam memasukkan data foto anggota. Fitur tersebut adalah fitur untuk mengambil foto anggota dan langsung disimpan dalam aplikasi SLiMS. Fitur tersebut dapat anda temukan ketika mengedit membership atau mengisikan data member baru. Tampilan fitur tersebut adalah seperti di bawah ini:

Untuk dapat mengambil foto, lakukan langkah sebagai berikut:

- aktifkan fitur ini dengan memastikan value pada $sysconf['webcam'] = true; yang ada di sysconfig.inc.php bernilai true
- pastikan browser anda support flash player
- klik load camera
- klik capture
- klik use!!!
- jangan lupa menentukan format file dan ukurannya
