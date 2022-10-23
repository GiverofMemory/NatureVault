<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com).

    This file adds a "dictindex" (dictionary index) format for
    the (:pagelist:) and (:searchresults:) directives.  To see results
    in dictionary format, simply add fmt=dictindex to the directive, as in

        (:pagelist group=Main fmt=dictindex:)

    By default the items are display as a simple definition list, but
    this can be controlled by $FPLDictIndex...Fmt variables:

        $FPLDictIndexStartFmt   - start string
        $FPLDictIndexEndFmt     - end string
        $FPLDictIndexLFmt       - string to output for each new letter
        $FPLDictIndexIFmt       - string to output for each item in list
        $FPLDictIndexLEndFmt	- string to output at end of each letter block
        $FPLDictIndexHeaderLink - string for the link list at the upper page

    To enable this module, simply add the following to config.php:

        include_once('cookbook/titledictindex.php');

*/

$FPLFunctions['dictindex'] = 'FPLDictIndex';
global $DictIndexShowLetterLinksByDefault;
SDV($DictIndexShowLetterLinksByDefault, true);

function FPLDictIndex($pagename,&$matches,$opt) {
global $FPLDictIndexStartFmt, 
	$FPLDictIndexEndFmt,
	$FPLDictIndexLFmt,
	$FPLDictIndexIFmt,
	$FPLDictIndexLEndFmt,
	$FPLDictIndexHeaderLink,
	$DictIndexShowLetterLinksByDefault,
	$FmtV;
	
	$opt['order']='title';
	$matches = MakePageList($pagename, $opt);
	SDV($FPLDictIndexStartFmt,"<dl class='fpldictindex'>\n");
	SDV($FPLDictIndexEndFmt,'</dl>');
	SDV($FPLDictIndexLFmt,"<dt><a href='#dictindexheader' id='\$IndexLetter'>&#9650;</a> \$IndexLetter</dt>\n");
	SDV($FPLDictIndexLEndFmt,"");
	SDV($FPLDictIndexIFmt,"<dd><a href='\$PageUrl' title='\$Group : \$Title'>\$Title</a></dd>\n"); 
	SDV($FPLDictIndexHeaderLink,"\n".'<a href="#$IndexLetter">$IndexLetter</a>');

	$out = array();
	$headerlinks= array();
	foreach($matches as $item) {
		$pletter = substr($item['=title'],0,1);
		$FmtV['$IndexLetter'] = $pletter;
		if (strcasecmp($pletter,@$lletter)!=0) {
			if($lletter) { $out[] = FmtPageName($FPLDictIndexLEndFmt,$item['pagename']); }
			$out[] = FmtPageName($FPLDictIndexLFmt,$item['pagename']);
			$headerlinks[] = FmtPageName($FPLDictIndexHeaderLink,$item['pagename']);
			$lletter = $pletter; 
		}
		$out[] = FmtPageName($FPLDictIndexIFmt,$item['pagename']);
	}
	if(!empty($headerlinks)) { $out[] = FmtPageName($FPLDictIndexLEndFmt,$item['pagename']); }
	$FmtV['$IndexLinks']=implode(' &bull; ',$headerlinks);
	
	$show_letter_links = isset($opt['letterlinks']) ? $opt['letterlinks'] : $DictIndexShowLetterLinksByDefault;

	return 
		FmtPageName(($show_letter_links ? "<p id='dictindexheader'>\$IndexLinks</p><hr>" : ""),$pagename) . 
		FmtPageName($FPLDictIndexStartFmt,$pagename) . 
		implode('',$out) . 
		FmtPageName($FPLDictIndexEndFmt,$pagename);
}

?>
