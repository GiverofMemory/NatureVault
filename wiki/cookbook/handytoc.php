<?php if (!defined('PmWiki')) exit();
/*
 * Copyright 2006 Kathryn Andersen
 * 
 * This program is free software; you can redistribute it and/or modify it
 * under the Gnu Public Licence or the Artistic Licence.
 */ 

/** \file handytoc.php
 * \brief JavaScript Table of Contents
 *
 * See also: http://www.pmwiki.org/wiki/Cookbook/HandyTableOfContents
 *
 * Markup changed for PHP 7.2 compatibility by Hans Bracker 
 * $HandyTocAnchorAfterElement & $HandyTocDefaultTitle features added by Said Achmiz (& bug fixes)
 * $HandyTocSmartAnchors added by Said Achmiz
 * $HandyTocAutomaticAnchors added by Said Achmiz
 * $HandyTocExcludeWithin added by Said Achmiz
 * $HandyTocVersionedAssets added by Said Achmiz
 */
$RecipeInfo['HandyTableOfContents']['Version'] = '2021-11-10';

SDV($HandyTocPubDir, "$FarmD/pub/handytoc");
SDV($HandyTocPubDirUrl, "\$FarmPubDirUrl/handytoc");
SDV($HandyTocStartAt, 1);
SDV($HandyTocEndAt, 6);
SDV($HandyTocIgnoreSoleH1, true);
SDV($HandyTocAnchorAfterElement, true);
SDV($HandyTocSmartAnchors, false);
SDV($HandyTocAutomaticAnchors, false);
SDV($HandyTocDefaultTitle, '');
SDV($HandyTocExcludeWithin, '');
SDV($HandyTocVersionedAssets, true);

Markup("handytoc", "directives", "/\\(:htoc\s*(.*?):\\)/", "HandyTocProcessMarkup");
  
function HandyTocProcessMarkup($m) {
	global $HTMLHeaderFmt, $HandyTocPubDir, $HandyTocPubDirUrl, $HandyTocVersionedAssets;

	$filepath_js = "$HandyTocPubDir/handytoc.js";
	$filepath_css = "$HandyTocPubDir/handytoc.css";
	$path_js = "\$HandyTocPubDirUrl/handytoc.js";
	$path_css = "\$HandyTocPubDirUrl/handytoc.css";
	
	if ($HandyTocVersionedAssets) {
		$path_js .= "?v=" . filemtime($filepath_js) . ".js";
		$path_css .= "?v=" . filemtime($filepath_css) . ".css";
	}
	
	$HTMLHeaderFmt['handytoc'] = 
	   "<link rel='stylesheet' href='$path_css' type='text/css' />\n";
	$HTMLHeaderFmt['handytoc'] .= " 
	  <script language='javascript' type='text/javascript' src='$path_js'></script>";

	global $HandyTocEndAt, $HandyTocStartAt, $HandyTocIgnoreSoleH1, $HandyTocAnchorAfterElement, $HandyTocSmartAnchors, $HandyTocAutomaticAnchors, $HandyTocDefaultTitle, $HandyTocExcludeWithin, $HTMLFooterFmt;
	extract($GLOBALS['MarkupToHTML']);
	$args = ParseArgs($m[1]);
	$title = ($args[''] ? implode(' ', $args['']) : $HandyTocDefaultTitle);
	$start = ($args['start'] ? $args['start'] : $HandyTocStartAt);
	$end = ($args['end'] ? $args['end'] : $HandyTocEndAt);
	$ignoreh1 = ($args['ignoreh1'] ? $args['ignoreh1'] : $HandyTocIgnoreSoleH1);
	$anchorafterelement = ($args['anchorafterelement'] ? $args['anchorafterelement'] : ($HandyTocAnchorAfterElement ? 'true' : 'false'));
	$smartanchors = ($args['smartanchors'] ? $args['smartanchors'] : ($HandyTocSmartAnchors ? 'true' : 'false'));
	$automaticanchors = ($args['automaticanchors'] ? $args['automaticanchors'] : ($HandyTocAutomaticAnchors ? 'true' : 'false'));
	$excludewithin = ($args['excludewithin'] ? $args['excludewithin'] : $HandyTocExcludeWithin);
	$class = ($args['class'] ? ' class="' . $args['class'] . '" ' : '');
	$script_set_args = "<script language='javascript' type='text/javascript'>TOC.set_args({ start:$start, end:$end, ignoreh1:$ignoreh1, anchorafterelement:$anchorafterelement, smartanchors:$smartanchors, automaticanchors:$automaticanchors, excludewithin:'$excludewithin' });</script>";
	if ($title) {
		return Keep("$script_set_args<div ${class}id='htoc'><h3>$title</h3></div>");
	} else {
		return Keep("$script_set_args<div ${class}id='htoc'></div>");
	}
}
