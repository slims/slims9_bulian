####Aturan Peminjaman
<hr>
fasilitas ini digunakan untuk mendefinisikan aturan peminjaman yang didasarkan pada
- Member Type,
- Collection Type,
- GMD.

Aturan yang ditetapkan dalam fasilitas ini adalah
- Batas Jumlah Peminjaman (Loan Limit),
- Periode Peminjaman (Loan Period),
- Batas Perpanjangan (Reborrow Limit),
- Denda per Hari (Fine Each Day) dan
- Toleransi Keterlambatan (Overdue Grace Periode)

Contoh Pendefinisian Loan Rules:

1. diperpustakaan anda ada 3 tipe koleksi: Buku, AudioVisual (AV), Skripsi.
2. Salah satu tipe keanggotaan di perpustakaan anda adalah: Mahasiswa dengan jatah pinjam total 2 koleksi, yaitu: 1 untuk tipe koleksi Buku dan 1 lagi untuk tipe koleksi AV.
3. Untuk itu tentu anda harus membuat tipe membership "Mahasiswa" dengan total peminjaman dua koleksi.
4. Kemudian di loan rulesnya yang harus didefinisikan:
   - jenis member "Mahasiswa" jatah pinjem koleksi "Buku" adalah 1.
   - jenis member "Mahasiswa" jatah pinjem koleksi "AV" adalah 1.
   - jenis member "Mahasiswa" jatah pinjem koleksi "Skripsi" adalah 0.

 Semuanya harus didefinisikan, jika tidak maka bisa jadi terlewati.  
