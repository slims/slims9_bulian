####Inisialisasi
<hr>
Menu Initialize digunakan untuk memulai stock opname. Pada menu ini, terdapat sub-sub menu:
- Stock Take Name Adalah nama dari kegiatan stock opname yang dilakukan. Sesuaikan namanya dengan keinginan Anda. Sub menu ini HARUS diisi.
- GMD (Lihat panduan modul Master File --> GMD (di bawah Authority Files)).
- Collection Type (Lihat panduan modul Master File --> Collection Type (di bawah Lookup Files)).
- Location (Lihat panduan modul Master File --> Location (di bawah Authority Files)).
- Site/Placement Mengacu ke informasi item pada modul Bibliography.
- Classification Mengacu ke sub menu class pada modul Bibliography. Untuk penulisan class menggunakan wildcard (*), misal, apabila kita ingin melakukan stock opname dengan kisaran class 100 s.d.300, cukup masukkan 1* to 3*. Apabila kisaran class yang kita lakukan stock opname hanya pada class 100, masukkan 1*.

Setalah proses Initialize dilakukan, maka menu current stoke take dan stock take report akan berfungsi sebagai menu untuk melakukan kegiatan stock take ditambah dengan adanya menu menu tambahan yang akan digunakan untuk melakukan kegiatan stock take,
yaitu menu:
- Finish Stock Take,
- Current Lost Items,
- Stock Take Log,
- Resyncronize.
