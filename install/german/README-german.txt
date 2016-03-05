SENAYAN 3.0 stable

Core Senayan Developer :
Hendro Wicaksono - hendrowicaksono@yahoo.com
Arie Nugraha - dicarve@yahoo.com

Folgende Anleitung gilt für eine Neuinstallation von SENAYAN (die deutsche Version gilt
für Senayan3 Stable11):
1. 	Entpacken Sie den Inhalt des SENAYAN-Ordners in das Web-Rootverzeichnis (oder
		einen Ordner darunter) ihres Webservers.

2.	Erstellen Sie eine MySQL-Datenbank mit dem Namen "senayan3-stableVERSION" 
		(z.B. "senayan3-stable10")

3a.	Öffnen Sie phpMyAdmin oder einen anderen MySQL-Client und führen Sie das 
		MySQL-Script "\install\senayan.sql" für die erstellte Datenbank aus.
		
3b.	Für die deutsche Version führen Sie DANACH noch das MySQL-Script
		"\install\german\senayan-german_(update).sql" aus.

4a.	OPTIONAL: Für die deutsche Version kopieren Sie bitte 
		"\install\german\sysconfig.inc.php" ins Stammverzeichnis (die vorhandene 
		Datei ersetzen).  

4b.	Passen Sie die Konfiguration in sysconfig.inc.php an. Unbedingt notwendig sind die
		Angaben zur Datenbankverbindung in den Zeilen:
	   define('DB_HOST', 'FILL WITH YOUR MYSQL SERVER HOST - default to "localhost"');
	   define('DB_PORT', 'FILL WITH YOUR MYSQL SERVER PORT - default to "3306"');
	   define('DB_NAME', 'FILL WITH YOUR SENAYAN'S DATABASE NAME');
	   define('DB_USERNAME', 'USERNAME TO CONNECT TO MYSQL SERVER - VERY UNRECOMMENDED TO USE "root"');
	   define('DB_PASSWORD', 'PASSWORD TO CONNECT TO MYSQL SERVER');
   
5.	Rufen sie die Seite auf und melden Siche sich mit dem Benutzernamen "admin" 
		und dem Passwort "admin" an.
		
Nun können Sie losRAKern :)


Anmerkung: 	Um den Z39.50-Service unter "Katalogisierung" verwenden zu können, 
						muss für PHP die YAZ-Bibliothek (http://www.indexdata.com/yaz) 
						installiert sein. Sie können Senayan aber auch so verwenden, allerdings
						können Sie dann keine Daten automatisch von anderen Katalogen übernehmen.