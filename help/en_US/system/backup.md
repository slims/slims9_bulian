### Salin pangkalan data
<hr>
Pada fitur ini anda dapat melakukan pencadangan database SLiMS. Untuk dapat membuat cadangan, perlu terlebih dahulu didefinisikan letak/path file mysqldump. Caranya adalah dengan mengedit file sysconfig.inc.php. Temukan baris yang tertulis:

$sysconf['mysqldump'] = 'xxxxxx';

gantilah xxx sesuai dengan letak mysqldump di komputer server. Setelah path mysqldump tepat, klik Start New Backup maka SLiMS akan membuat cadangan secara otomatis. Format file cadangan yang dibuat Senayan adalan .sql dan diberi nama sesuai tanggal pembuatan, misalnya: backup_20080501_123106.sql. Nama file cadangan di atas berarti: dibuat pada tanggal 1 bulan 5 tahun 2008, pada pukul 12:31:06. Letak file pencadangan  ada di folder backup pada folder dokumen SLiMS. Lihat tampilan layar.
catatan: untuk melakukan backup ini, user database mysql harus mempunyai hak LOCK TABLES
