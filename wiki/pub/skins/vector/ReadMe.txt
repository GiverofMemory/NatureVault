Name: Vector
Version: 0.9.3 (2017-08-27)
Description: Skin for PmWiki
Author: Said Achmiz
Contact: said@saidachmiz.net
Copyright: Copyright 2017 Said Achmiz
html5 validation: Passed (http://validator.w3.org/)
css validation: Passed (http://jigsaw.w3.org/css-validator/)

Files:
vector.css - cascading style sheet
vector.php - PHP script (functions)
vector.tmpl - template file
ReadMe.txt - this file
guiedit — folder of custom GUI editing buttons
Place contents in wiki.d – folder contents should be placed in wiki/wiki.d

This skin is released under the GNU General Public License
as published by the Free Software Foundation; either version
2 of the License, or (at your option) any later version.


Installation:
1) Un-zip vector.zip into your skins directory
2) Place contents of “Place contents in wiki.d” folder in wiki/wiki.d
3) Enable the vector skin in your config.php with:

$Skin = 'vector';

4) If you have GUI edit buttons enabled ($EnableGUIButtons = 1 in config.php), add the following line in order to use Vector’s custom GUI edit buttons:

$GUIButtonDirUrlFmt = '$FarmPubDirUrl/skins/vector/guiedit';

5) See http://www.pmwiki.org/wiki/Skins/Vector for additional instructions.

Changelog

v. 0.9.2 2017-08-24
 * Several optimizations and bugfixes.

v. 0.9.2 2017-08-24
 * Several improvements and bug-fixes to the Search function; Backlinks bug fixed.

v. 0.9.1 2017-08-22
 * Slightly improved support for tablets & similarly-sized devices.

v. 0.9 2017-08-22
 * Vector is now responsive & mobile-friendly.

v. 0.8.2 2017-08-21
 * Minor cosmetic fixes.

v. 0.8.1 2017-08-20
 * Minor cosmetic fixes.

v. 0.8 2017-08-19
 * Added Talk page feature.

v. 0.7.6 2017-08-17
 * Various minor fixes.

v. 0.7.5 2017-08-17
 * Fixed critical vulnerabilities (& other bugs)

v. 0.7 2017-08-17
 * Added custom search form (Site.Search)

v. 0.6 2017-08-16
 * Assorted bug & compatibility fixes

v. 0.5 2017-08-15
 * Incorporated AutomaticChangeSummary
 * Added custom Category.Category page
 * Updated to work with DictIndex

v. 0.4 2017-08-15
 * Updated to work with MediaCategories
 * Modified edit form to put preview first (like MediaWiki)

v. 0.3 2017-08-15
 * Added custom edit form

v. 0.2 2017-08-13
 * Updated to work with SectionEdit and AutoTOC or HandyTOC

v. 0.1 2017-08-13
 * original release