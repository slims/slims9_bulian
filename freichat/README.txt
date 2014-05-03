								|---------------------------------|
				 				|--           FreiChat
								|---------------------------------|



=========================================================================================================================

1. INSTALLATION

=========================================================================================================================

a) PreRequisites
   * PHP 5.0 with PDO-MYSQL enabled.
   * MySql or any other additional database installed
   

b) Procedure
   * Unzip the downloaded archive FreiChat.
   * Place the freichat folder in your CMS installed directory.

    For Example, If you have installed joomla , freichat folder should be placed in
    /your/joomla/installed/directory/freichat

   * Enter the following path  for installation
     http://your_website.com/freichat/installation/index.php

   * Fill in the required details and after installation your settings will be
     saved in /freichat/arg.php
     You can change your password in that file.

     Please Note By default the password will be "adminpass"

c) Parameters
   * For customizing freichat please visit
     http://your_website.com/freichat/administrator

     Or you can directly edit the database table "frei_config" .
     The database and administration password are stored in
     	/freichat/hardcode.php
=========================================================================================================================

2.  Theming in FreiChat

=========================================================================================================================

The theme maker can be used to edit the themes in the backend itself . 

a) Themes may be found in ~/freichat/client/themes/

b) To design a theme just copy any default theme folder and rename it with
   any name of your choice
   Note:Do not create theme from scratch because freichat wont recognize
   your images ,always start by copying the default theme folder and then
   modifying it.

c) The default theme folder consists of images a css file(css.php)
   and argument.php(includes arguments for theme)that can be edited according to your needs

d) If you change the names of any images don't forget to edit argument.php in
   the theme folder

e) you can switch the themes via 'arg.php'  or from parameters as mentioned in 1.C

Please Note: There is also a WYSIWYG based theme maker in freichat backend


=========================================================================================================================

3.  Language Packs in FreiChat

=========================================================================================================================

a) There are language files with extension .php in folder lang and by default, currently
   freichat only supports english language 

b) You are free to  create your own language pack with the name of your language and with
   extension .php for example greek.php (Preferrably use full names of languages)
   and placing them in lang folder

c) you can switch the languages via 'arg.php'  or from parameters as mentioned in 1.C

d) While creating language file, please copy the default file and change only the text ,
   in double inverted commas, and do not change any variable names and please see to it
   that the top comments are in every language file

=========================================================================================================================

2.  TroubleShooting in FreiChat

=========================================================================================================================

-------------------------------------------------------------------------------------------------------------------------
NOTE::Before resolving any errors,
      Make sure your password and other details are correct in ~/freichat/arg.php
-------------------------------------------------------------------------------------------------------------------------

==> Please read the file problems_and_solutions.txt

=========================================================================================================================

4.  Debugging in FreiChat

=========================================================================================================================

a) To start debugging , enable PHP debug in FreiChat backend .

b) Once debugging is enabled freichat will create a log named freixlog.log in ~freichat/ ,
   you can then  check for any errors in that file.

c) please do not forget to disable debugging in ~/freichat/arg.php later .

