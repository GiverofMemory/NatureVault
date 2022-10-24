<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com).

    This file adds a "dictindex" (dictionary index) format for
    the (:pagelist:) and (:searchresults:) directives.  To see results
    in dictionary format, simply add fmt=dictindex to the directive, as in

        (:pagelist group=Main fmt=dictindex:)

    By default the items are display as a simple definition list, but
    this can be controlled by $FPLDictIndex...Fmt variables:

        $FPLDictIndexStartFmt - start string
        $FPLDictIndexEndFmt   - end string
        $FPLDictIndexLFmt     - string to output for each new letter
        $FPLDictIndexIFmt     - string to output for each item in list

    To enable this module, simply add the following to config.php:

        include_once('cookbook/dictindex.php');

*/

$FPLFunctions['extdictindex'] = 'FPLExtDictIndex';

function FPLExtDictIndex($pagename,&$matches,$opt) {
  global $FPLExtDictIndexStartFmt,$FPLExtDictIndexEndFmt,
    $FPLExtDictIndexLFmt,$FPLExtDictIndexIFmt,$FExtmtV;
  $matches = MakePageList($pagename, $opt);
  for($n=0;$n<count($matches);$n++) 
    $matches[$n]['name'] = FmtPageName('$Name',$matches[$n]['pagename']);
  $cmp = create_function('$x,$y',
    "return strcasecmp(\$x['name'],\$y['name']);");
  usort($matches,$cmp);
  SDV($FPLExtDictIndexStartFmt,"<a name='searchheader'></a><dl class='fplextdictindex'><span style='font-size:83%'>\$IndexLinks</span><hr><p class='vspace'></p>");
  SDV($FPLExtDictIndexEndFmt,'</dl>');
  SDV($FPLExtDictIndexLFmt,"<dt><a href='#searchheader'>&#9650;</a> <a name='\$IndexLetter'></a>\$IndexLetter</dt>");
  SDV($FPLExtDictIndexIFmt,"<dd><a href='\$PageUrl'>\$Name</a></dd>");
  SDV($FPLExtDictIndexHeaderLink,'<a href="#$IndexLetter">$IndexLetter</a>');
  $out = array();
  $headerlinks= array();
  foreach($matches as $item) {
    $pletter = substr($item['name'],0,1);
    $FExtmtV['$IndexLetter'] = $pletter;
    if ($pletter!=@$lletter) { 
      $out[] = FmtPageName($FPLExtDictIndexLFmt,$item['pagename']);
      $headerlinks[] = FmtPageName($FPLExtDictIndexHeaderLink,$item['pagename']);
      $lletter = $pletter; 
    }
    $out[] = FmtPageName($FPLExtDictIndexIFmt,$item['pagename']);
  }
  $FExtmtV['$IndexLinks']=implode(' &#9679; ',$headerlinks);
  return FmtPageName($FPLExtDictIndexStartFmt,$pagename).implode('',$out).
    FmtPageName($FPLExtDictIndexEndFmt,$pagename);
}

?>