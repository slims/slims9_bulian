This is the README file for PHPlot
Last updated for PHPlot-5.1.0 on 2009-12-24
The project web site is http://sourceforge.net/projects/phplot/
The project home page is http://phplot.sourceforge.net/
-----------------------------------------------------------------------------

OVERVIEW:

PHPlot is a PHP class for creating scientific and business charts.

The release documentation contains only summary information. For more
complete information, download the PHPlot Reference Manual from the
Sourceforge project web site. You can also view the manual online at
http://phplot.sourceforge.net

For important changes in this release, see the NEWS.txt file.


CONTENTS:

   COPYING  . . . . . . . . . . . . LGPL 2.1 License file
   ChangeLog  . . . . . . . . . . . Lists changes to the sources
   NEWS.txt . . . . . . . . . . . . Highlights changes in releases
   README.txt   . . . . . . . . . . This file
   contrib  . . . . . . . . . . . . "Contributed" directory, add-ons
   phplot.php   . . . . . . . . . . The main PHPlot source file
   phplot_data.php  . . . . . . . . Auxiliary and extended functions
   rgb.inc.php  . . . . . . . . . . Optional extended color table


REQUIREMENTS:

You need a recent version of PHP5, and you are advised to use the latest
stable release.  This version of PHPlot has been tested with PHP-5.3.1 and
PHP-5.2.12 on Linux, and with PHP-5.3.1 on Windows/XP.  PHP4 is no longer
supported.

You need the GD extension to PHP either built in to PHP or loaded as a
module. Refer to the PHP documentation for more information - see the
Image Functions chapter in the PHP Manual. We test PHPlot only with the
PHP-supported, bundled GD library.

If you want to display PHPlot charts on a web site, you need a PHP-enabled
web server. You can also use the PHP CLI interface without a web server.

PHPlot supports TrueType fonts, but does not include any TrueType font
files.  If you want to use TrueType fonts on your charts, you need to have
TrueType support in GD, and some TrueType font files.  By default, PHPlot
uses a simple font built-in to the GD library.


INSTALLATION:

Unpack the distribution. (If you are reading this file, you have probably
already done that.)

Installation of PHPlot simply involves copying three script files somewhere
your PHP application scripts will be able to find them. The scripts are:
     phplot.php
     phplot_data.php
     rgb.inc.php
(Only phplot.php is necessary for most graphs.)
Make sure the protections on these files allow the web server to read them.

The ideal place is a directory outside your web server document area,
and on your PHP include path. You can add to the include path in the PHP
configuration file; consult the PHP manual for details.


KNOWN ISSUES:

Here are some of the problems we know about in PHPlot. See the bug tracker
on the PHPlot project web site for more information.

#1795969 The automatic range calculation for Y values needs to be rewritten.  
  This is especially a problem with small offset ranges (e.g. Y=[999:1001]).
  You can use SetPlotAreaWorld to set a specific range instead.

#1605558 Wide/Custom dashed lines don't work well
  This is partially a GD issue, partially PHPlot's fault.

#2919086 Improve tick interval calculations
  Tick interval calculations should try for intervals of 1, 2, or 5 times
  a power of 10.


If you think you found a problem with PHPlot, or want to ask questions or
provide feedback, please use the Help and Discussion forum at
     http://sourceforge.net/projects/phplot/
If you are sure you have found a bug, you can report it on the Bug tracker
at the same web site. There is also a Features Request tracker.


TESTING:

You can test your installation by creating the following two files somewhere
in your web document area. First, the HTML file:

------------ simpleplot.html ----------------------------
<html>
<head>
<title>Hello, PHPlot!</title>
</head>
<body>
<h1>PHPlot Test</h1>
<img src="simpleplot.php">
</body>
</html>
---------------------------------------------------------

Second, in the same directory, the image file producing PHP script file.
Depending on where you installed phplot.php, you may need to specify a path
in the 'require' line below.

------------ simpleplot.php -----------------------------
<?php
require 'phplot.php';
$plot = new PHPlot();
$data = array(array('', 0, 0), array('', 1, 9));
$plot->SetDataValues($data);
$plot->SetDataType('data-data');
$plot->DrawGraph();
---------------------------------------------------------

Access the URL to 'simpleplot.html' in your web browser. If you see a
simple graph, you have successfully installed PHPlot. If you see no
graph, check your web server error log for more information.


COPYRIGHT and LICENSE:

PHPlot is Copyright (C) 1998-2009 Afan Ottenheimer

This is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation;
version 2.1 of the License.

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this software; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
