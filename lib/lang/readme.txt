HOW TO ADD TRANSLATIONS

TOC
---------------------------------
0. Foreword
1. Get PoEdit
2. Create a language folder
3. Create a catalog for the translation
4. Start translation
5. Make translation available
6. Hints
APPENDIX - List of commom words/phrases in Senayan


0. FOREWORD
---------------------------------
This file only gives a quick start for adding a translation. A better 
(general) explanation might be found here 
http://carsonified.com/blog/features/webapps/give-your-web-app-international-appeal/
and
http://carsonified.com/blog/dev/give-your-web-app-international-appeal-part-ii/


1. GET POEDIT
---------------------------------
Download PoEdit from http://www.poedit.net/ and install it. Versions are available for 
Win/Linux/Mac.


2. CREATE A LANGUAGE FOLDER
---------------------------------
a)  Go to senayan3\lib\lang\locale
b)  Create a folder describing the language and the country the translation is
    for. For example en_US stands for english/United States. While it is not 
    strictly necessary to use this convention, it would be nice if you could 
    stick to it anyway :)
    - You can find the short code for your language at 
      http://www.gnu.org/software/gettext/manual/gettext.html#Usual-Language-Codes
    - You can find the short code for your country at 
      http://www.gnu.org/software/gettext/manual/gettext.html#Country-Codes
c)  Enter the folder and create another folder called: LC_MESSAGES
Now you got something like id_ID/LC_MESSAGES


3. CREATE A CATALOG FOR THE TRANSLATION
---------------------------------
a)  Start PoEdit
b)  Goto Menu "File" > "New Catalog"
b1) Tab "Project info":
    ------------
    "Project Name and Version":
      -> (optional) Enter Senayan3
    "Team"
      -> (optional) Enter your name   
    "Team's email adress"
      -> (optional) Enter your mail
    "Language"
      -> Select the language you want to translate to
    "Country"
      -> Select the country for the language
    "Charset"
      -> Use utf-8
    "Source code charset"
      -> Use utf-8
    "Plural forms"
      ->  This is a bit more complicated. If your language has just one plural 
          form use:  nplurals=2; plural=n != 1;
          For other languages you can find a list of possible plural forms at
          http://translate.sourceforge.net/wiki/l10n/pluralforms
    ------------
    "Base Path"
      -> Enter: ../../../../../
    "Paths" (Click button "New Item")
      -> Enter: . (yes, just a dot)
b3) Tab "Keywords"
    ------------
    Add two lines:
    	__ (two underscores)
    	_ngettext
c)  Press ok and save the file under senayan3\lib\lang\locale\{YOUR LANGUAGE}\LC_MESSAGES.
    Name the file messages.po
    

4. START TRANSLATION
---------------------------------   
a)  First you have to fetch all translatable string. Go to "Catalog" > "Update 
    from source" (or just click the third button from the left in the main window)
b)  Now just start translating each line by clicking on it and entering the 
    translation at the bottom part of the window
c)  When finished go to "File" > "Save" (or press the second button from the left)


5. Make translation available
---------------------------------
Open file \senayan3\lib\lang\localisation.php and at a line like this at the end:
$available_languages[] = array('xx_XX', _('ENGLISH NAME OF LANGUAGE'), 'NATIVE NAME OF LANGUAGE');
I think it should be clear what to fill in.


6. HINTS
---------------------------------
- If you right click on a row, you can see in which context (source code) the string
  is used.
- Maybe at some point you want to change a translation you already did over and over
  again. PoEdit doesn't support search & replace operations. But you can open the
  messages.po file in a common text editor and do a search & replace there (close
  PoEdit first). The Appendix here might help to prevent this situation.


APPENDIX - LIST OF COMMOM WORDS/PHRASES IN SENAYAN
---------------------------------   
This is just a small list with words/phrases that I came up with for my 
german translation. It might help you to find translations for the most
"troublesome" terms. Also you might end up with less synonyms usage ;)

add                               =>  
Are You Sure Want to              =>  
Authority Files                   =>  
Authority Type                    =>  
bibliographic                     =>  
cant be empty / can not be empty  =>  
Call Number                       =>  
cancel                            =>  
circulation                       =>  
check                             =>  
checkout (items)                  =>  
Code                              =>  
Collection                        =>  
Collection Type                   =>  
confirm                           =>  
content                           =>  
copies = items                    =>  
Credit                            =>  
data                              =>  
database backup                   =>  
Debit                             =>  
due date                          =>  
expired                           =>  
Expiry Date                       =>  
Export XXX Data To CSV format     =>  
fines                             =>  
Forbidden                         =>  
frequency                         =>  
gmd                               =>  
group                             =>  
history                           =>  
holiday                           =>  
id                                =>  
image thumbnail                   =>  
Import Data to XXX from CSV file  =>  
initialize                        =>  
Inventory Code                    =>  
item = copies                     =>  
Item Code                         =>  
label                             =>  
Labels                            =>  
library automatation (system)     =>  
library members                   =>  
list                              =>  
loan                              =>  
Loan Limit                        =>  
Location                          =>  
login                             =>  
Lookup Files                      =>  
make sure                         =>  
member ID                         =>  
Member Type                       =>  
Member Type Name                  =>  
Membership                        =>  
module                            =>  
must be set = cant be empty       =>  
overdue                           =>  
(overdue) fines                   =>  
Override(Overide)                 =>  
pending                           =>  
PERMITTED                         =>  
Place                             =>  
previous                          =>  
privileges                        =>  
promote(ed)                       =>  
quick return                      =>  
Real Name                         =>  
Reborrow                          =>  
repository                        =>  
Required                          =>  
reservation                       =>  
return                            =>  
section                           =>  
stock take                        =>  
Subject                           =>  
Subject Type                      =>  
Subscription                      =>  
supplier                          =>  
System Users                      =>  
template                          =>  
transaction                       =>  
uncheck                           =>  
update                            =>  
user/user name                    =>  
view                              =>  
writable                          =>  
you                               =>  
You are going to edit xxx         =>  