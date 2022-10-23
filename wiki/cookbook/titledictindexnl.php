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

$FPLFunctions['dictindexnl'] = 'FPLDictIndexNL';
global $DictIndexShowLetterLinksByDefault;
SDV($DictIndexShowLetterLinksByDefault, true);

function FPLDictIndexNL($pagename,&$matches,$opt) {
global $FPLDictIndexNLStartFmt, 
	$FPLDictIndexNLEndFmt,
	$FPLDictIndexNLLFmt,
	$FPLDictIndexNLIFmt,
	$FPLDictIndexNLLEndFmt,
	$FPLDictIndexNLHeaderLink,
	$DictIndexShowLetterLinksByDefault,
	$FmtV;
	
	##Remove /n's (new lines) so all lines are on the same line
	##
	$opt['order']='title';
	$matches = MakePageList($pagename, $opt);
	#SDV($FPLDictIndexNLStartFmt,"<dl class='fpldictindexnl'>\n");
	SDV($FPLDictIndexNLStartFmt,"<dl class='fpldictindexnl'>\n");
	SDV($FPLDictIndexNLEndFmt,'</dl>');
	#SDV($FPLDictIndexNLLFmt,"<dt><a href='#dictindexnlheader' id='\$IndexLetter'>&#9650;</a> \$IndexLetter</dt>\n");
	SDV($FPLDictIndexNLLFmt,"<dt><a href='#dictindexnlheader' id='\$IndexLetter'>&#9650;</a> \$IndexLetter</dt>\n");
	SDV($FPLDictIndexNLLEndFmt,"");
	#SDV($FPLDictIndexNLIFmt,"<dd><a href='\$PageUrl' title='\$Group : \$Title'>\$Title</a></dd>\n"); 
	SDV($FPLDictIndexNLIFmt,"<dd><a href='\$PageUrl' title='\$Group : \$Title'>\$Title</a></dd>\n");
	#SDV($FPLDictIndexNLHeaderLink,"\n".'<a href="#$IndexLetter">$IndexLetter</a>');
	SDV($FPLDictIndexNLHeaderLink,"\n".'<a href="#$IndexLetter">$IndexLetter</a>');

	$out = array();
	$headerlinks= array();
	foreach($matches as $item) {
		$pletter = substr($item['=title'],0,1);
		$FmtV['$IndexLetter'] = $pletter;
		if (strcasecmp($pletter,@$lletter)!=0) {
			if($lletter) { $out[] = FmtPageName($FPLDictIndexNLLEndFmt,$item['pagename']); }
			$out[] = FmtPageName($FPLDictIndexNLLFmt,$item['pagename']);
			$headerlinks[] = FmtPageName($FPLDictIndexNLHeaderLink,$item['pagename']);
			$lletter = $pletter; 
		}
		$out[] = FmtPageName($FPLDictIndexNLIFmt,$item['pagename']);
	}
	if(!empty($headerlinks)) { $out[] = FmtPageName($FPLDictIndexNLLEndFmt,$item['pagename']); }
	$FmtV['$IndexLinks']=implode(' &bull; ',$headerlinks);
	
	$show_letter_links = isset($opt['letterlinks']) ? $opt['letterlinks'] : $DictIndexShowLetterLinksByDefault;

	return 
		FmtPageName(($show_letter_links ? "<p id='dictindexnlheader'>\$IndexLinks</p><hr>" : ""),$pagename) . 
		FmtPageName($FPLDictIndexNLStartFmt,$pagename) . 
		implode('',$out) . 
		FmtPageName($FPLDictIndexNLEndFmt,$pagename);
}

?>
