SENAYAN 3.0 stable

Core Senayan Developer :
Hendro Wicaksono - hendrowicaksono@yahoo.com
Arie Nugraha - dicarve@yahoo.com

Below are the instructions for upgrading to SENAYAN Stable 3 from SENAYAN Stable 5 :
1. Create backup of your previous SENAYAN's source directory

2. Overwrite all your previous SENAYAN source files with the latest

3. Open your phpMyAdmin or mysql client utility (or other mysql manager softwares) and 
   run upgrade_stable5.sql inside your SENAYAN application database.
   
4. Re-check your database configurations and others configuration in sysconfig.inc.php.

5. If you have your own custom template, Adjust index_template.html and detail_template.php
   with the latest template, because there is many improvements in new template.
   
-------------------------------------------------------------------------------------------------

Berikut adalah instruksi untuk upgrade ke SENAYAN Stable 3 dari SENAYAN Stable 5 :
1. Buat backup dari direktori source SENAYAN terdahulu anda

2. Timpa semua file source SENAYAN yang terdahulu dengan yang terbaru

3. Buka phpMyAdmin atau mysql client (atau program manager mysql lainnya) dan jalankan
   script upgrade_stable5.sql pada database SENAYAN anda
   
4. Cek kembali semua konfigurasi database dan konfigurasi lain pada file sysconfig.inc.php.

5. Apabila anda memiliki template buatan anda sendiri, sesuaikan file index_template.html dan detail_template.php
   dengan template yang terbaru karena banyak terjadi perubahan pada model template baru.
