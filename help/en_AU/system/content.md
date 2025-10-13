###Content

This function is used to change the appearance of the SLiMS application content. By default, contents that already exist and can be modified via this screen are: 
- **Homepage info**, (located at the front of the OPAC & displayed when “Home” is clicked)
- **Welcome to Admin page** [the initial display when entering the Admin menu - SLiMS management console]
- **Help on usage** (accessed from the OPAC)
- **Library information** (also accessed from the OPAC)

Example of usage: consider a path created for the new content that is stored as '*librarian*'. To display this content then we need to write the url:

http://localhost/slims5_meranti/index.php?p=librarian

We can create the navigation to this URL in the OPAC by editing the OPAC template.

Information displayed in the Content list:

* *Content title* (user-friendly title for the content)
* *Path (must be unique)*  (a variable)
* *Last updated* (generated automatically)

The usual facility to sort the list by clicking on a field name is available.



To add new a new content item click the **Add new content** button . An editor screen will open requiring you to enter :

* *Content title* [ required]

* *This is news* [ Yes/No]

* *Path (must be unique)* [ ]
* *Content description* [ this is the actual content and can be edited as html , using the inbuilt WYSIWYG editor which provides a wide range of functions including image insert, text formatting, and source editing ]

When the fields are completed, click the **Save** button.



The usual edit and search functions are also available. 