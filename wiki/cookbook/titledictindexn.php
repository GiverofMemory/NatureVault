<div style="background: ghostwhite;  
            padding: 10px; 
            border: 1px solid lightgray; 
            margin: 10px;">
<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com).

    This file adds a "dictindex" (dictionary index) format for
    the (:pagelist:) and (:searchresults:) directives.  To see results
    in dictionary format, simply add fmt=dictindex to the directive, as in

        (:pagelist group=Main fmt=dictindex:)

    By default the items are display as a simple definition list, but
    this can be controlled by $FPLDictIndex...Fmt variables:

        $IndexnStartFmt   - start string
        $IndexnEndFmt     - end string
        $IndexnLFmt       - string to output for each new letter
        $IndexnIFmt       - string to output for each item in list
        $IndexnLEndFmt	- string to output at end of each letter block
        $IndexnHeaderLink - string for the link list at the upper page

    To enable this module, simply add the following to config.php:

        include_once('cookbook/titledictindex.php');

*/

$FPLFunctions['dictindexn'] = 'Indexn';
global $DictIndexShowLetterLinksByDefaultn;
SDV($DictIndexShowLetterLinksByDefaultn, true);

function Indexn($pagename,&$matches,$opt) {
global $IndexnStartFmt, 
	$IndexnEndFmt,
	$IndexnLFmt,
	$IndexnIFmt,
	$IndexnLEndFmt,
	$IndexnHeaderLink,
	$DictIndexShowLetterLinksByDefaultn,
	$FmtV;
	
	$opt['order']='title';
	$matches = MakePageList($pagename, $opt);
	SDV($IndexnStartFmt,"<dl class='fpldictindex'>\n");
	SDV($IndexnEndFmt,'</dl>');
	SDV($IndexnLFmt,"<dt><a href='#dictindexheader' id='\$IndexLetter'>&#9650;</a> \$IndexLetter</dt>\n");
	SDV($IndexnLEndFmt,"");
	SDV($IndexnIFmt,"<dd><a href='\$PageUrl' title='\$Group : \$Title'>\$Title</a></dd>\n");
	SDV($IndexnHeaderLink,"\n".'<a href="#$IndexLetter">$IndexLetter</a>');

	$out = array();
	$headerlinks= array();
	foreach($matches as $item) {
		#$pletter = substr($item['=title'],0,1);
		$FmtV['$IndexLetter'] = $pletter;
		if (strcasecmp($pletter,@$lletter)!=0) {
			if($lletter) { $out[] = FmtPageName($IndexnLEndFmt,$item['pagename']); }
			$out[] = FmtPageName($IndexnLFmt,$item['pagename']);
			$headerlinks[] = FmtPageName($IndexnHeaderLink,$item['pagename']);
			$lletter = $pletter; 
		}
		$out[] = FmtPageName($IndexnIFmt,$item['pagename']);
	}
	if(!empty($headerlinks)) { $out[] = FmtPageName($IndexnLEndFmt,$item['pagename']); }
	$FmtV['$IndexLinks']=implode(' &bull; ',$headerlinks);
	
	$show_letter_links = isset($opt['letterlinks']) ? $opt['letterlinks'] : $DictIndexShowLetterLinksByDefaultn;

	return 
		FmtPageName(($show_letter_links ? "<p id='dictindexheader'>\$IndexLinks</p><hr>" : ""),$pagename) . 
		FmtPageName($IndexnStartFmt,$pagename) . 
		implode('',$out) . 
		FmtPageName($IndexnEndFmt,$pagename);
}

?>
</div>