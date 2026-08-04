### Installing SLiMS 9 Bulian on a Red Hat Enterprise Linux (RHEL) LAMP Stack

 SLiMS 9 Bulian is officially supported on WAMP or XAMPP servers running on Windows. 
 However, this guide provides a method to get SLiMS working on RHEL, CentOS, and Fedora servers. While this approach has been tested and works, it is not officially supported by the SLiMS development team. Use this method at your own discretion, and consider that some features may not function as expected without official support.


 #### Configure a classic LAMP stack

A Classic LAMP stack needs to be configured, and the requiered PHP modules as per the Slims 9 Bulian documentation need to be installed.


#### Download the Slims 9 Bulian source Code

Download the SLiMS 9 Bulian archive from the GitHub releases:

```bash
wget https://github.com/slims/slims9_bulian/releases/download/v9.6.1/slims9_bulian-9.6.1.zip
```

Extract the downloaded ZIP archive:

```bash
unzip slims9_bulian-9.6.1.zip
```

Copy the extracted SLiMS directory to the web server's document root:

```bash
sudo cp -r slims9_bulian-9.6.1 /var/www/html/slims
```

#### Create the Database

Log into MySQL and create the database and user:

```bash
sudo mysql -u root -p
```

Within the MySQL prompt, execute the following commands:

```sql
CREATE DATABASE slims;
CREATE USER 'slims'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON slims.* TO 'slims'@'localhost';
FLUSH PRIVILEGES;
```

#### Set Ownership and Permissions

Set the correct ownership for the SLiMS directory:

```bash
sudo chown -R apache:apache /var/www/html/slims
```

Set the appropriate permissions:

```bash
sudo chmod -R 755 /var/www/html/slims
```

#### Configure SELinux (Security Enhanced Linux) Contexts

If SELinux is enforced, apply the necessary SELinux contexts to allow web server access (only Recommended if absolutly nessesary):

```bash
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/slims/config(/.*)?'
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/slims/files(/.*)?'
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/slims/images(/.*)?'
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/slims/repository(/.*)?'
sudo restorecon -Rv '/var/www/html/slims'
```

#### Install SLiMS
Open your web browser and navigate to `http://0.0.0.0/slims` to complete the SLiMS installation through the web interface.

Follow the on-screen instructions to finalize the setup.

#### Known limitations.

The PHP YAZ module may be missing. The installation of this module is not covered here.
