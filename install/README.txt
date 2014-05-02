SENAYAN 5 stable

Core Senayan Developer :
Hendro Wicaksono - hendrowicaksono@yahoo.com
Arie Nugraha - dicarve@yahoo.com

Below are the instructions for new installation of SENAYAN :
1. Put extracted SENAYAN's folder inside your web document root.

2. create "senayan3-stableVERSION" (example: "senayan3-stable10") database in mysql.

3. Open your phpMyAdmin or mysql client utility (or other mysql manager softwares) and 
   run "senayan.sql" sql script inside your SENAYAN's database.
   
4. Re-check your database configurations and others configuration in sysconfig.inc.php.
   Check for these lines :
   define('DB_HOST', 'FILL WITH YOUR MYSQL SERVER HOST - default to "localhost"');
   define('DB_PORT', 'FILL WITH YOUR MYSQL SERVER PORT - default to "3306"');
   define('DB_NAME', 'FILL WITH YOUR SENAYAN'S DATABASE NAME');
   define('DB_USERNAME', 'USERNAME TO CONNECT TO MYSQL SERVER - VERY UNRECOMMENDED TO USE "root"');
   define('DB_PASSWORD', 'PASSWORD TO CONNECT TO MYSQL SERVER');

5. If you have your own custom template, Adjust detail_template.php file or just overwrite it
   with "detail_template.php" from "default" template folder.
   
-------------------------------------------------------------------------------------------------

Berikut adalah instruksi untuk instalasi baru SENAYAN :
1. Letakkan folder hasil ekstraksi SENAYAN di web document root anda.

2. buat database "senayan3-stableVERSISENAYAN" (misal: "senayan3-stable10") pada server mysql anda.

3. Buka phpMyAdmin atau mysql client (atau program manager mysql lainnya) dan jalankan
   script "senayan.sql" pada database SENAYAN anda.
   
4. Cek kembali semua konfigurasi database dan konfigurasi lain pada file sysconfig.inc.local.php.
   Cek pada bagian :
   define('DB_HOST', 'ISI DENGAN NAMA HOST RDBMS MYSQL ANDA - defaultnya "localhost"');
   define('DB_PORT', 'ISI DENGAN NOMOR PORT RDBMS MYSQL ANDA - defaultnya "3306"');
   define('DB_NAME', 'ISI DENGAN NAMA DATABASE SENAYAN YANG SUDAH ANDA BUAT');
   define('DB_USERNAME', 'USERNAME KONEKSI RDBMS MYSQL ANDA - SANGAT DISARANKAN TIDAK MEMAKAI USER "root"');
   define('DB_PASSWORD', 'PASSWORD KONEKSI RDBMS MYSQL ANDA');

5. Apabila anda memiliki template buatan anda sendiri, sesuaikan file "detail_template.php" atau
   timpa saja dengan "detail_template.php" yang ada pada direktori template "default".
