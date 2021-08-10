<?php if (!defined('PmWiki')) exit();
/*  Copyright 2005-6 Benjamin C. Wilson (ameen@dausha.net)
	Copyright 2017-8 Said Achmiz (said@saidachmiz.net)
	
    This file is mediacat.php; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  

    This script simulates the style of the Mediawiki wiki markup. 
    When categories are listed on a line it will label them and 
    put them into a block.

    To use this script, simply copy it into the cookbook/ directory
    and add the following line to config.php (or a per-page/per-group
    customization file).

        include_once('cookbook/mediacat.php');

    For more details, visit http://pmwiki.org/wiki/Cookbook/MediaCategories
    Customizable Variables:
        :$McatLabelS     :This sets the singular form of the category
        label. Defaults to "Category:"
        :$McatLabelP     :This sets the plural form of the category
        label. Defaults to "Categories:"
        :$McatSep        :This determines what the seperator format is.
        Defaults to ' | '
        :$McatBlockStart :This creates the markup that creats the block.
        :$McatBlockEnd   :This creates the markup that terminates the block.
        :$McatCSSStyle   :This is the CSS that is added to
        $HTMLStylesFmt when categories are marked up by this script.

    Version History
    ---------------

    1.0 August 07, 2005 - by BenWilson: Initially published.
    2.0 May 07, 2006 - by BenWilson: Rewrote to address issues raised by
      my friend Mateusz. This version pulls all freelink categories, 
      builds an alphabetized category list, and then appends the
      categories at the end of the page.
    2.1 August 15, 2017 - by Said Achmiz: Updated to work with PmWiki's new 
      built-in category functionality.
    2.1.1 October 31, 2017 - by Said Achmiz: Corrected CSS bug (stray semicolon)
    2.2 November 30, 2017 - by Said Achmiz: Updated to allow [[!CategoryName]] markup
    2.2.1 November 30, 2017 - by Said Achmiz: Bug fix
	2.2.2 December 1, 2017 - by Said Achmiz: The same category link multiple times no 
	  longer shows up twice in the category listing block
	2.2.3 December 1, 2017 - by Said Achmiz: A similar category link (differing only by
	  whitespace) multiple times no longer shows up twice; also, an empty category link 
	  (containing nothing, or nothing but whitespace) does not show up
	2.2.4 December 6, 2017 - by Said Achmiz: Added $McatShowCategoryLinksAsTitles option
	2.2.5 January 8, 2018 - by Said Achmiz: Added $McatShowCategoriesOnIncludedPages option
*/

$RecipeInfo['MediaCategories']['Version'] = '2.2.5 (2018-01-08)';

SDV($McatLabelS, 'Category:');
SDV($McatLabelP, 'Categories:');
SDV($McatSep,    ' | ');
SDV($McatBlockStart, "<div class='category'>");
SDV($McatBlockEnd,   "</div>");
SDV($McatCSSStyle, "
div.category { 
	border: 1px solid #666;
	padding: 0.5em;
	background-color:  #EEE;
}
");
SDV($McatShowCategoryLinksAsTitles, false);
SDV($McatShowCategoriesOnIncludedPages, false);

Markup('multicat_preprocess','<fulltext','/(\[\[Category:)/', '[[!');
Markup('multicat','fulltext','[[!', 'return Multicat($x);');

if (!$McatShowCategoriesOnIncludedPages) {
	SDVA($QualifyPatterns, array(
		'/\\[\\[!(.+)\\]\\]/' => function ($m) { return ''; },
	));
}

$mcategories = array();
function Multicat($t) {
	global $McatLabelS, $McatLabelP, $McatSep;
	global $McatBlockStart, $McatBlockEnd;
	global $HTMLStylesFmt, $McatCSSStyle;
	global $mcategories;
	
	$t = preg_replace_callback('/\[\[(!)\\s*(.*?)\]\]/mx', '_MulticatCallback', $t);
	
	if (empty($mcategories))
		return $t;

	sort($mcategories);
	$label = (count($mcategories) > 1) ? $McatLabelP : $McatLabelS;
	$input = implode($McatSep, $mcategories);
	$HTMLStylesFmt[] = $McatCSSStyle;
	$mcats = "$McatBlockStart $label $input $McatBlockEnd";
	$t .= $mcats;
	return $t;
}
function _MulticatCallback($m) {
	if ($m[2] == '')
		return '';
		
// 	OW_log($m);

	global $CategoryGroup, $mcategories, $McatShowCategoryLinksAsTitles;
	$cat = $McatShowCategoryLinksAsTitles ? "[[(Category.){$m[2]}|+]]" : "[[(Category.){$m[2]}]]";
	if (!in_array($cat, $mcategories)) $mcategories[] = $cat;
	return '';
}
