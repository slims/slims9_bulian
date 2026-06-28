###Database backup

A facility to backup the SLiMS database. 

The screen displays details of existing backups, with fields as follows:

* **Backup executor** (the user who initiated the backup)
* **Backup time** (the time and date the backup was created)
* **Backup file location** (the path to the backup file and its name )
* **File size** (the size of the backup)

To create a backup, click the  **Start new backup** button and SLiMS will back up automatically. The format of backup files created by SLIMS is .sql in layout and compressed using TAR.GZ . They are named according to the creation date and store within  the directory *files/backup*.

Backup files can be downloaded by clicking the **Download** button.

Unwanted backups can be deleted by selection using the checkbox, followed by clicking on the **Delete selected data** button.

The normal search and sort functions are available to manage a large number of backups.

Backups should be carried out regularly and methodically according to a well-planned schedule.

A full database backup provides one of the key functions for migrating a SLiMS system to a new server, and for recovery from system failure.



<u>Note:</u> 
*To do this backup, the mysql database user must have the right to LOCK TABLES*.

<u>Note:</u>

*This function backs up the SLiMS database only. Uploaded content such book covers images, attached files, member photos etc., must be backed-up separately.*

