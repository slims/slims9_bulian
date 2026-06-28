#### Biblio  Item Import
<hr>
Item import is used to insert item data into a SLiMS database via a CSV format file. If this activity is done from a single SLiMS database to another SLiMS database, then the item import is done after the bibliographic data import. This means the item will adjust the bibliographic data that has been imported earlier.

You must specify the file containing the data, the field separator character, the field enclosing character, the number of records to import, which record to start from, and whether the first record consists of the field names.

Generally, the defaults will OK to use unless the CSV file is unusual, in which case you can adjust the settings to match the format of the CSV file you are importing.

The CSV file for importing should generally be in the same format as a standard SLiMS CSV export file, so it is worthwhile studying one of those for format, field names, and field order, if you are importing a CSV from a different application.

