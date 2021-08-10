<?php if (!defined('PmWiki')) exit();

function SA_log($x)
{
	echo "<pre>";
	print_r($x);
	echo "</pre>";
}

global $PageLogoAltUrl;
SDV($PageLogoAltUrl, "$PubDirUrl/skins/pmwiki/pmwiki-32.gif");

global $HTMLStylesFmt;
$HTMLStylesFmt['pmwiki'] = preg_replace("/  code.escaped \{ white-space: nowrap; \}\n/", '', $HTMLStylesFmt['pmwiki']);

# Skin Parts handling
global $SkinPartFmt, $WikiTitle;
SDVA($SkinPartFmt, array(
'wikititle' => "{\$VectorTitlePrefix}{\$Titlespaced} — $WikiTitle",
'title' => '{$VectorTitlePrefix}{$Titlespaced}',
'footer' => '
%lastmod%$[This page was last edited on {$LastModified}]
* %item rel=nofollow% %navbox% [[&#9650; $[Top] &#9650; -> #top]]
* %item rel=nofollow% [[$[Search] -> $[{$SiteGroup}/Search]]]
* %item rel=nofollow% [[$[Recent Changes] -> $[{$Group}/RecentChanges]]]
* %item rel=nofollow% [[$[All Recent Changes] -> $[{$SiteGroup}/AllRecentChanges]]]
',
'pageactions' => '
* %item rel=nofollow% [[$[View] -> {$FullName}?action=browse]] %comment%[[{$Groupspaced}/&hellip; -> {$Group}]]%%
* %item rel=nofollow% [[$[Edit Page] -> {$FullName}?action=edit]]
* %item rel=nofollow% [[$[Page Attributes] -> {$FullName}?action=attr]]
* %item rel=nofollow% [[$[Page History] -> {$FullName}?action=diff]]
* %item rel=nofollow% [[$[Upload] -> {$FullName}?action=upload]]
',
'attachalias' => 'AttachClip:',
));

function RenderPart($pagename, $part, $strip = '') {
	global $SkinPartFmt, $PCache, $VectorTitlePrefix;
	$n = "skin_$part";
	if(!isset($PCache[$pagename][$n])) {
		$t = '';
		if($SkinPartFmt[$part]) {
			$t = htmlspecialchars($SkinPartFmt[$part], ENT_NOQUOTES);
			if($part == "title" || $part == "wikititle") {
				$basepagename = PageVar($pagename, '$BaseName');
				$t = MarkupToHTML($basepagename, "<:block>$t", array('escape' => 0));
			} else {
				$t = MarkupToHTML($pagename, "<:block>$t", array('escape' => 0));
			}
		}
		$PCache[$pagename][$n] = $strip ? preg_replace($strip, '', $t) : $t;
	}
	print $PCache[$pagename][$n];
}

function RenderTitle($pagename) {
  RenderPart($pagename, 'wikititle', "/(<[^>]+>|\r\n?|\n\r?)/");
}

function RetrievePageMarkup($pagelist) {
  foreach($pagelist as $p) {
    if (PageExists($p)) {
      $page = RetrieveAuthPage($p, 'read', false, READPAGE_CURRENT);
      return array($page['text'], $page['title']);
      break;
    }
  }
  return null;
}

function RenderActions($pagename, $actionslist) {
  global $action, $SkinPartFmt;
  $pagelist = preg_split('/\s+/', $actionslist, -1, PREG_SPLIT_NO_EMPTY);
  list($text,) = RetrievePageMarkup($pagelist);
  SDV($text, preg_replace("/(\r\n|\n?\r)/", "\n", $SkinPartFmt['pageactions']));
  preg_match('/(<([uo])l.*>(?:.*)<\\/\\2l>)/si', MarkupToHTML($pagename, $text), $m);
  $ls = explode("</li>", str_replace("\n", "", $m[1]));
  $lRe = "/(.*?)<a.*?href='(.*?)'.*?>(.*?)<\\/a>(.*)/i";
  foreach($ls as $i => $l) {
    if(preg_match($lRe, $l, $l1)) {
      $laction = preg_match("/action=(.*)/i", $l1[2], $a) ? $a[1] : 'browse';
      if($action == $laction) {
          $ls[$i] = $l1[1];
        if($l1[4] && preg_match($lRe, $l1[4], $l2))
          $ls[$i] .= "<a class='active' href='" . $l2[2] . "'>" . $l2[3] . "</a>";
        else
          $ls[$i] .= "<p class='active'>" . $l1[3] . "</p>";
      }
    }
  }
  print implode("\n</li>", $ls);
}

function RenderTalkSelector($pagename) {
  global $ScriptUrl; ### Line added.
	$out = "		<ul id='article-talk-selector'>\n";
	if (preg_match('/-Talk$/', $pagename)) {
		$basename = PageVar($pagename, '$BaseName');
		$out .= "			<li><a href='{$ScriptUrl}/{$basename}'>Article</a>\n";
		$out .= "			<li><p class='active'>Talk</p>";
	}
	else {
		$out .= "			<li><p class='active'>Article</p>\n";
		$out .= "			<li><a href='{$ScriptUrl}/{$pagename}-Talk'>Talk</a>\n";
	}
	$out .= "		</ul>";
	
	print $out;
}

# links decoration
global $EnableSkinLinkDecoration;
if(IsEnabled($EnableSkinLinkDecoration, 1)) {
  global $SkinPartFmt, $IMapLinkFmt, $LinkFunctions, $IMap;
  global $LinkPageCreateFmt, $LinkUploadCreateFmt, $UrlLinkFmt;

  $intermap = $SkinPartFmt['attachalias'];

  $IMapLinkFmt['Attach:'] = "<a class='attachlink' href='\$LinkUrl' rel='nofollow'>\$LinkText</a>";
  $IMapLinkFmt[$intermap] = "<a class='attachlink' href='\$LinkUrl' rel='nofollow'>\$LinkText</a><a class='createlink' href='\$LinkUpload'><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAANCAMAAABSF4SHAAAAB3RJTUUH1gEeFxAvfW/GcwAAAAlwSFlzAAAewQAAHsEBw2lUUwAAAARnQU1BAACxjwv8YQUAAAAJUExURf///wBmzABm/0odWaIAAAABdFJOUwBA5thmAAAAJklEQVR42mNgYGBgYmJiAFFYMTpkhJJYaKAGEA0CjGAE4TAyAikADqwAQusNffkAAAAASUVORK5CYII=' alt='' /></a>";
  $LinkFunctions[$intermap] = 'LinkUpload';
  $IMap[$intermap] = '$1';

  $LinkPageCreateFmt = "<a class='createlinktext' href='\$PageUrl?action=edit'>\$LinkText</a>";
  $LinkUploadCreateFmt = "<a class='createlinktext' href='\$LinkUpload'>\$LinkText</a><a class='createlink' href='\$LinkUpload'><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAANCAMAAABSF4SHAAAAB3RJTUUH1gEeFwEtwLiETwAAAAlwSFlzAAAewQAAHsEBw2lUUwAAAARnQU1BAACxjwv8YQUAAAAGUExURf////8LC59859AAAAABdFJOUwBA5thmAAAAHklEQVR42mNgYGBgZGRkAFFYMQmQEaIeahbMCIjxAAhQACm+UQJbAAAAAElFTkSuQmCC' alt='' /></a>";
}

/******************************/
/* Automatic change summaries */
/******************************/

global $EditFunctions, $AutomaticChangeSummaries;
SDV($AutomaticChangeSummaries,1);
if($AutomaticChangeSummaries) {
	array_unshift($EditFunctions, 'ProvideDefaultSummary');
	function ProvideDefaultSummary($pagename,&$page,&$new)
	{
		global $ChangeSummary, $DiffFunction;
		if ($ChangeSummary || !function_exists(@$DiffFunction)) return;
		$diff = $DiffFunction($new['text'],@$page['text']);
		$difflines = explode("\n",$diff."\n");
		$in=array(); $out=array();
		foreach ($difflines as $d)
		{ if ($d=='' || $d[0]=='-' || $d[0]=='\\') continue;
		  if ($d[0]=='<' && count($out)<10) {$out[]=substr($d,2); continue;}
		  if ($d[0]=='>' && count($in)<10) {$in[]=substr($d,2); continue;}
		}
		$diff2=''; if (count($out)==0) {$out=$in; $diff2="[deleted] ";}
		foreach ($out as $s) {$diff2 .= $s." ";}
		$ChangeSummary=str_replace(array("<",">","\n"),array("&lt;","&gt;"," "),$diff2);
		$new['csum']=$ChangeSummary;
	}
}

/*************/
/* DictIndex */
/*************/

global $FPLDictIndexStartFmt, $FPLDictIndexEndFmt, $FPLDictIndexLFmt, $FPLDictIndexLEndFmt, $FPLDictIndexIFmt, $FPLDictIndexHeaderLink;
$FPLDictIndexLFmt = "<div class='fplindex_letter_block' id='\$IndexLetter'><dt>\$IndexLetter</dt>\n<dd><ul>\n";
$FPLDictIndexLEndFmt = "</dd></ul>\n</div>\n";
$FPLDictIndexIFmt = "<li><a href='\$PageUrl' title='\$Group : \$Title'>\$Titlespaced</a>\n"; 

/*******************/
/* MediaCategories */
/*******************/

global $McatLabelS, $McatLabelP;
$McatLabelS = '<a href="{$ScriptUrl}/Category/Category">Category</a>:';
$McatLabelP = '<a href="{$ScriptUrl}/Category/Category">Categories</a>:';

/****************/
/* Title prefix */
/****************/

global $action, $FmtPV, $VectorTitlePrefix;
$VectorTitlePrefix = ($action == "edit") ? "%font-style=normal%Editing%% " : "";
if(preg_match("/-Talk/", $pagename)) { $VectorTitlePrefix .= "Talk: "; }
$FmtPV['$VectorTitlePrefix'] = '$GLOBALS["VectorTitlePrefix"]';

/*****************/
/* Custom search */
/*****************/

## redefine searchbox format:
Markup_e('searchbox', '>links', '/\\(:searchbox(\\s.*?)?:\\)/', "SearchBox2");
function SearchBox2($m)
{
	global $SearchBoxOpt, $SearchQuery, $EnablePathInfo;
	extract($GLOBALS['MarkupToHTML']);

	SDVA($SearchBoxOpt, array(
		'size'   => '20', 
		'label'  => FmtPageName('$[Search]', $pagename),
		'value'  => str_replace("'", "&#039;", $SearchQuery)));

	$opt = array_merge((array)$SearchBoxOpt, ParseArgs($m[1]));
	$focus = $opt['focus'];
	$opt['action'] = 'search';
	
	if($opt['target'])
		$target = MakePageName($pagename, $opt['target']); 
	else
		$target = $pagename;
		
	$out = FmtPageName(" class='wikisearch' action='\$PageUrl' method='get'>", $target);
	$opt['n'] = IsEnabled($EnablePathInfo, 0) ? '' : $target;
	$out .= "<input type='search' name='q' value='{$opt['value']}' class='inputbox searchbox' autofocus autocomplete='off' size='{$opt['size']}' ";
	if ($focus)
		$out .= " onfocus=\"if(this.value=='{$opt['value']}') this.value=''\" onblur=\"if(this.value=='') this.value='{$opt['value']}'\" ";
	$out .= " /><input type='submit' class='inputbutton searchbutton' value='{$opt['label']}' />";
	
	foreach($opt as $k => $v)
	{
		if ($v == '' || is_array($v)) continue;
		if ($k=='q' || $k=='label' || $k=='value' || $k=='size') continue;
		$k = str_replace("'", "&#039;", $k);
		$v = str_replace("'", "&#039;", $v);
		$out .= "<input type='hidden' name='$k' value='$v' />";
	}
	return "<form ".Keep($out)." </form>";
}

if (($_GET['pagename'] == "Site/Search" && isset($_GET['q'])) || $action == "search")
{
	global $SearchResultsFmt;
	$SearchQuery = str_replace('$', '&#036;', PHSC(stripmagic(@$_REQUEST['q']), ENT_NOQUOTES));
	if (preg_match("/^link=/", $SearchQuery)) {
		$target_page = preg_replace("/^link=/",'',$SearchQuery);
		$SearchResultsFmt = "<div class='wikisearch'><p>Pages that link to <code>$target_page</code>:</p>
			\$MatchList
			<p>\$[SearchFound]</p><hr /></div>";
	} else {
		$SearchResultsFmt = "<div class='wikisearch'><p>Pages containing the text <code>\$SearchQuery</code>:</p>
			\$MatchList
			<p>\$[SearchFound]</p></div>";
	}
}

if ($action == "search" && $pagename != "Site.Search") {
	$params = $_GET;
	unset($params['n']);
	Redirect("Site/Search?" . http_build_query($params));
}

/************/
/* Jump-box */
/************/

global $VectorJumpBoxEnabled;
SDV($VectorJumpBoxEnabled, true);
if ($VectorJumpBoxEnabled && $_GET['pagename'] == "Site/Search" && isset($_GET['q'])) {
	global $DefaultGroup;
	$text = stripmagic($_GET['q']);
	if (PageExists($text))
		Redirect($text);
	else if (PageExists("{$DefaultGroup}.{$text}"))
		Redirect("{$DefaultGroup}.{$text}");
}

/**************/
/* Talk pages */
/**************/

# Automatically create a talk page when a non-talk page is edited
global $AutoCreateTalkPages, $TalkPageTemplate;
SDV($AutoCreateTalkPages, true);
SDV($TalkPageTemplate, '$SiteGroup.TalkTemplate');
$FmtPV['$TalkPageTemplate'] = "'" . FmtPageName($TalkPageTemplate, $pagename) . "'";
function CreateTalkPage($pagename, &$page, &$new) {
	global $IsPagePosted, $TalkPageTemplate;
	$talkpagename = $pagename . "-Talk";
	if (!$IsPagePosted)
		return;
	if (preg_match('/-Talk$/', $pagename) || PageExists($talkpagename))
		return;
	if (RetrieveAuthPage($talkpagename, 'edit', false, READPAGE_CURRENT) == false)
		return;

	$talkpageparameters = array('ctime' => $Now);
	$templatepage = RetrieveAuthPage(FmtPageName($TalkPageTemplate, $pagename), 'read', false, READPAGE_CURRENT);
	$talkpageparameters['text'] = (@$templatepage['text'] > '') ? $templatepage['text'] : "This is the Talk page for {$Basename}.\n";
	WritePage($talkpagename, $talkpageparameters);
}

if($AutoCreateTalkPages == true) {
	$EditFunctions[] = 'CreateTalkPage';
}

# Prevent talk pages from showing up in pagelists
# (either default or with list=normal
global $SearchPatterns;
$SearchPatterns['default'][] = '!.*-Talk$!';
$SearchPatterns['normal'][] = '!.*-Talk$!';

# Make {$BaseName} of talk pages refer to the article page (not the talk page)
global $BaseNamePatterns;
$BaseNamePatterns['/-Talk$/'] = '';

/**************************/
/* Searchbox Autocomplete */
/**************************/

// Disabled for now, until I figure out a lower-overhead way to do this.
// (jQuery is too bloated)

// global $HTMLHeaderFmt, $HTMLFooterFmt;
// $HTMLHeaderFmt['autocomplete'][0] = 
// "<script src='{$SkinDirUrl}/jquery.js'></script>
// <link rel='stylesheet' href='{$SkinDirUrl}/vector_searchbox.css' />
// <script src='{$SkinDirUrl}/jquery-ui.js'></script>";
// $HTMLFooterFmt['autocomplete'][0] = 
// 	"<script language='javascript' type='text/javascript'>$(\".searchbox\").autocomplete({
// 		source: function(request, response) {
// 			$.ajax({
// 				url: \"{\$ScriptUrl}\",
// 				dataType: \"jsonp\",
// 				data: {
// 					'action': \"suggest\",
// 					'q': request.term
// 				},
// 				success: function(data) {
// 					response(data[1]);
// 				}
// 			});
// 		}
// 	});</script>";
