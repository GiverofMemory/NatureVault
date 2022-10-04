<?php if (!defined('PmWiki')) exit();
/*	Copyright 2009 Hans Bracker. 
	This file is toggle.php; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published
	by the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	See https://www.pmwiki.org/wiki/Cookbook/Toggle for full documentation.
	
	(:toggle id=divname:) creates a toggle link, which can show or hide 
	a <div> element or other object on the page, for instance a div created with
	
	>>id=divname<< 
	text can be hidden/shown 
	>><< 
	
	Required parameter:
	
	id=objname			id attribute of object to be toggled
	
	Optional parameters:
	
	id2=obj2name		second object (div), for toggling betwen first and second object
	init=hide			hides the element initially
							(default: show)
	show=labelname		label of link or button when div is hidden
							(default: "Show")
	hide=labelname		label of link or button when div is shown
							(default: "Hide")
	label=labelname		label of link or button for both toggle states
						(shortcut for setting 'show' and 'hide' to the same value)
	ttshow=tooltiptext	text that appears when the user hovers over the "show" link 
							(default: "Show") 
	tthide=tooltiptext	text that appears when the user hovers over the "hide" link 
							(default: "Hide") 
	tt=tooltiptext		text that appears when the user hovers over the link in both states
						(shortcut for setting 'ttshow' and 'tthide' to the same value)
	group=classname		on clicking Show, show div with associated 'id' (standard behaviour), 
						but hide all other divs with class classname.
	display=value		what the 'display' CSS property of the specified element should 
						be set to, when itâ€™s shown ('block', 'inline-block', etc.;
						see https://www.w3schools.com/CSSref/pr_class_display.asp 
						for details)
							(default: "block")
	display2=value		like 'display', but for the alternate element (id2)
							(default: "block")
	set=1				sets a cookie to remember toggle state
							(default: 0)
	button=1			display a button instead of a link
							(default: 0)
	printhidden=1		show hidden elements when printing
							(default: 1)
	nojs=integer		set to 1 or 2 will show toggle links/buttons if browser 
						does not support javascript. Set to 2 will hide hidden 
						divs via style in page head and not via javascript, 
						so that for non-js browser initially hidden divs stay hidden. 
							(default: 0)

	Alternative syntax: (:toggle divname:)
	Alternative syntax with options: 
	(:toggle hide divname:)			initial hide
	(:toggle hide divname button:)	initial hide, button
	(:toggle name1 name2:)			toggle between name1 and name2

	See https://www.pmwiki.org/wiki/Cookbook/Toggle for additional info.
 */
# Recipe version (date).
$RecipeInfo['Toggle']['Version'] = '2022-06-17';

# Declare $Toggle for (:if enabled Toggle:) recipe installation check.
global $Toggle; $Toggle = 1;

# Defaults.
SDVA($ToggleConfig, [
	'id' => '',					// no default div name
	'id2' => '',				// no default div2 name
	'init' => 'show',			// initial state of element (visible)
	'show' => XL("Show"),		// link text â€˜Showâ€™
	'hide' => XL("Hide"),		// link text â€˜Hideâ€™
	'ttshow' => XL("Show"),		// tooltip text â€˜Showâ€™
	'tthide' => XL("Hide"),		// tooltip text â€˜Hideâ€™
	'group' => '',				// no default group (class) name
	'display' => 'block',		// default to `display:block`;
	'display2' => '',			// default alternate element to same as primary
	'set' => false,				// set no cookie to remember toggle state
	'button' => false,			// display as link by default
	'printhidden' => true, 		// hidden divs get printed
	'nojs' => 0,				// in no-js browser links are not shown, initial hidden divs are shown
]);
$ToggleConfigStack = [ $ToggleConfig ];

# Retrieve cookie.
global $CookiePrefix, $pagename;
$current_page_group = PageVar($pagename, '$Group');
$current_page_name = PageVar($pagename, '$Name');
$toggle_cookie_name = "{$CookiePrefix}_toggle_{$current_page_group}_{$current_page_name}";
$toggle_cookie = isset($_COOKIE[$toggle_cookie_name]) 
				 ? json_decode($_COOKIE[$toggle_cookie_name], true)
				 : null;

Markup('toggle', 'directives', '/\(:(toggle(?:set)?)\s+(.*?):\)/i', 'ToggleMarkup');

# All in one function.
function ToggleMarkup($m) {
	global $HTMLHeaderFmt, $HTMLFooterFmt, $HTMLStylesFmt, $UploadUrlFmt, $UploadPrefixFmt;
	global $ToggleConfigStack, $toggle_cookie_name, $toggle_cookie;
	global $MarkupMarkupLevel;
	extract($GLOBALS['MarkupToHTML']);

	// Parse directive arguments.
	$parsed_args = ParseArgs($m[2]); 

	// Get parameters without keys.
	if (   isset($parsed_args['']) 
		&& is_array($parsed_args[''])) {
		while (count($parsed_args['']) > 0) {
			$parameter = array_shift($parsed_args['']);
			if ($parameter == 'button')
				$parsed_args['button'] = 1;
			else if ($parameter == 'set')
				$parsed_args['set'] = 1;
			else if ($parameter == 'printhidden')
				$parsed_args['printhidden'] = 1;
			else if ($parameter == 'hide')
				$parsed_args['init'] = 'hide';
			else if ($parameter == 'show')
				$parsed_args['init'] = 'show';
			else if (!isset($parsed_args['id']))
				$parsed_args['id'] = $parameter;
			else if (!isset($parsed_args['id2']))
				$parsed_args['id2'] = $parameter;		 
		}
	}

	// Ensure that (:toggleset:) in (:markup:) only affects things on that 
	// and deeper markup levels.
	for ($i = ($MarkupMarkupLevel ?? 0); !($ToggleConfig = ($ToggleConfigStack[$i] ?? null)); $i--);
	if ($m[1] == 'toggleset') {
		// Just setting options for toggles on the page.
		$ToggleConfig = array_merge($ToggleConfig, $parsed_args);
		unset($ToggleConfig['#']);
		$ToggleConfigStack[$MarkupMarkupLevel] = $ToggleConfig;
		return '';
	} else {	
		// An actual toggle. Fill in un-specified parameters with defaults.
		$opt = array_merge($ToggleConfig, $parsed_args);
	}

	$HTMLStylesFmt['toggle'] = 
		" @media print { .toggle { display: none; } } \n" . 
		".toggle img { border: none; } \n";

	$HTMLHeaderFmt['toggle'] = <<<'EOT'
<style id="toggle-initial-hide-toggles">
	.toggle:not(.no-js-visible) { display: none; }
</style>
<script type='text/javascript'>
	document.querySelector("#toggle-initial-hide-toggles").remove();
</script>
EOT;

	# javascript for toggling and cookie setting
	$HTMLFooterFmt['toggleobj'] = <<<EOT
<script type='text/javascript'><!--
	window.toggleData = { };
	window.toggleData.toggle_cookie_name = '{$toggle_cookie_name}';
	
	function toggleObj(id_of_element_to_toggle) {
		// Retrieve the Toggle state/data for the specified element.
		let T = window.toggleData[id_of_element_to_toggle];
		
		// If weâ€™re *showing* an element thatâ€™s part of a defined group,
		// hide all the elements of the group first (including the specified
		// element itself, which will be re-shown immediately below).
		if (   T.group != '' 
			&& T.new_state_to_toggle_to == 'show') {
			// Get all elements of the given class.
			document.querySelectorAll(`.\${T.group}`).forEach(element_in_group => { 
				setToggleState(element_in_group, 'hide') 
			});
		}
		
		// Set the new state of the element.
		setToggleState(document.getElementById(id_of_element_to_toggle), 
					   T.new_state_to_toggle_to);
		
		// Toggle the alternate element, if any.
		// (T.new_state_to_toggle_to has now been reversed, by the line above.)
		if (T.id_of_alternate_element != '')
			setToggleState(document.getElementById(T.id_of_alternate_element), 
						   T.new_state_to_toggle_to, 
						   T.display_of_alternate_element);
	}
	
	function setToggleState(element, state, display = null) {
		// Retrieve the Toggle state/data for the specified element (if any).
		let T_e = window.toggleData[element.id];
		
		// Update the elementâ€™s display.
		element.style.display = (state == 'show') 
								? (display || (T_e ? T_e.display : 'initial')) 
								: 'none';

		// If the element has an entry in the saved data 
		// (i.e. if it has a toggle element of its own),
		// update that saved data, and also update the toggle link/button.
		if (T_e) {
			// Set the new state, and update the saved data for the element.
			T_e.new_state_to_toggle_to = (state == 'show') 
										 ? 'hide' 
										 : 'show';			

			// Adjust the toggle link for the element.
			let label = (state == 'show') 
						? T_e.toggle_link_label_in_visible_state 
						: T_e.toggle_link_label_in_hidden_state;
			let tooltip = (state == 'show') 
						  ? T_e.toggle_link_tooltip_in_visible_state 
						  : T_e.toggle_link_tooltip_in_hidden_state;
			document.getElementById(`\${element.id}-tog`).innerHTML = 
				(T_e.is_button == 1) 
				? `<input 
					type='button' 
					class='inputbutton togglebutton' 
					title='\${tooltip}' 
					value='\${label}' 
					onclick='javascript:toggleObj("\${element.id}")' 
					/>` 
				: `<a 
					class='togglelink' 
					title='\${tooltip}' 
					href='javascript:toggleObj("\${element.id}")'
					>\${label}</a>`;
		
			// If cookie setting is enabled, save the new state in a cookie.
			if (T_e.set_cookie == 1)
				updateToggleCookie(element.id, state);
		}
	}
	
	function updateToggleCookie(element_id, state) {
		// Retrieve...
		let toggleCookieName = window.toggleData.toggle_cookie_name;
		let toggleCookieNameRegex = new RegExp(`\${toggleCookieName}=([^;]+)`);
		let toggleCookieData = document.cookie.match(toggleCookieNameRegex);
		let toggleElementStates = toggleCookieData 
								  ? JSON.parse(toggleCookieData[1]) 
								  : { };
		
		// Modify...
		toggleElementStates[element_id] = state;
		
		// Store.
 		document.cookie = `\${toggleCookieName}=\${JSON.stringify(toggleElementStates)}; path=/`;
	}	
--></script>\n
EOT;

	// Styling for errors.
	$error_opening_tag = '<span style="color:red; font-weight:bold;">';
	$error_closing_tag = '</span>';

	// Retrieve the ids of both the primary and (if specified) alternate elements.
	// (the 'div' options are for backwards compatibility with ShowHide and ToggleLink recipes)
	$id  = $opt['div']  ?? $opt['id'];
	$id2 = $opt['div2'] ?? $opt['id2'];
	if ($id == '') {
		$error_message = '[Toggle] No object id specified.';
		return Keep($error_opening_tag . $error_message . $error_closing_tag); 
	}

	// Verify that the ids of both elements do not contain special characters
	// which are forbidden in CSS identifiers. (Among other things, this forbids
	// quotation marks, which prevents Javascript injection attacks via ids.)
	$CSS_forbidden_characters_regex = '/[!"#\$%&\'\(\)\*\+,\.\/\:;<=>\?@\[\\\\\]\^`\{\|\}~]/';
	$error_message_template = '[Toggle] Invalid ID specified for element: <code>ELEMENT_ID</code>';
	$error_messages = [ ];
	if (preg_match($CSS_forbidden_characters_regex, $id))
		$error_messages[] = str_replace('ELEMENT_ID', $id, $error_message_template);
	if (preg_match($CSS_forbidden_characters_regex, $id2))
		$error_messages[] = str_replace('ELEMENT_ID', $id2, $error_message_template);
	if (count($error_messages) > 0)
		return Keep(implode('<br>', array_map(function ($msg) { return ($error_opening_tag . $msg . $error_closing_tag); }, $error_messages)));
	
	// Values for the â€˜displayâ€™ CSS property.
	$display  = $opt['display'];
	$display2 = $opt['display2'] ?? $display;
	
	// Set labels for (both states of) the toggle link/button.
	$labels = [ ];
	$labels['show'] = $opt['label'] ?? $opt['lshow'] ?? $opt['show'];
	$labels['hide'] = $opt['label'] ?? $opt['lhide'] ?? $opt['hide'];

	// Same with tooltips.
	$tooltips = [ ];
	$tooltips['show'] = $opt['tt'] ?? $opt['ttshow'];
	$tooltips['hide'] = $opt['tt'] ?? $opt['tthide'] ;

	// Transform label text into image tags, if appropriate.
	// (since we wonâ€™t be putting the label text through pmwiki markup 
	//  processing, which would normally process image attach links)
	// (but maybe we should? TODO: investigate this)
	// Also encode apostrophes (for non-images).
	$ipat = '/^(.+\.(png|gif|jpg|jpeg|ico|svg))(?:\s*\|\s*(.+?))?$/i';
	foreach ($labels as $k => $val) {
		$is_image = preg_match($ipat, $val, $m);
		if ($is_image) {
			// Check for image, make image tag
			$image = $m[1];
			$prefix = (strstr($image, '/')) ? '/' : $UploadPrefixFmt; 
			$path = FmtPageName($UploadUrlFmt.$prefix, $pagename);
			$tooltips[$k] = $m[3] ?? $tooltips[$k];
			$labels[$k] = "<img src={$path}/{$image} class='toggle-image' />";
			$opt['button'] = '';
		} else {
			// Apostrophe encoding
			$labels[$k] = str_replace("'", "&rsquo;", $val);
		}
	}
	
	// If the element is part of a defined group, then hide it, unless itâ€™s explicitly set to be initially-shown.
 	if (   $opt['group'] != '' 
 		&& ($parsed_args['init'] ?? '') != 'show')
 		$opt['init'] = 'hide';

	// If set=1 (i.e. cookie setting enabled), then check if a cookie is set;
	// if it is, read the elementâ€™s initial state from the cookie.
	if ($opt['set'] == 1)
		$opt['init'] = $toggle_cookie[$id] ?? $opt['init'];
	
	/* OPTION RETRIEVAL ENDS; NOW PROCESSING BEGINS */

	// Set initial state, and update labels and target state 
	// (for when user clicks the toggle/link button).
	$display_property_value = ($opt['init'] == 'show') ? $display : 'none';
	if (!($toggle_cookie[$id2] ?? null))
		$alternate_element_display_property_value = ($opt['init'] == 'show') ? 'none' : $display2;
	$label = ($opt['init'] == 'show') ? $labels['hide'] : $labels['show'];
	$tooltip = ($opt['init'] == 'show') ? $tooltips['hide'] : $tooltips['show'];
	$state_to_toggle_to = ($opt['init'] == 'show') ? 'hide' : 'show';

	if (   $opt['nojs'] < 2
		|| $opt['init'] == 'show')  {
		if (!isset($HTMLHeaderFmt['toggle-styles']))
			$HTMLHeaderFmt['toggle-styles'] = '<style id="toggle-styles"></style>';

		// Open script tag.
		$HTMLHeaderFmt['toggle-styles'] .= '<script type="text/javascript"><!--';

		// Set initial state of element via JS (that sets embedded CSS).
		$HTMLHeaderFmt['toggle-styles'] .= "\n document.getElementById(`toggle-styles`).innerHTML += `\t #{$id} { display: {$display_property_value}; } \\n`; \n";

		// Set initial state of alternate element (in the same way).
		if ($id2)
			$HTMLHeaderFmt['toggle-styles'] .= "\n document.getElementById(`toggle-styles`).innerHTML += `\t #{$id2} { display: {$alternate_element_display_property_value}; } \\n`; \n";

		// Close script tag.
		$HTMLHeaderFmt['toggle-styles'] .= '--></script>';
	} else {
		// Set initial state of element via embedded CSS.
		$HTMLStylesFmt[] = "#{$id} { display: {$display_property_value}; } \n";

		// Set initial state of alternate element (in the same way).
		if ($id2)
			$HTMLStylesFmt[] = " #{$id2} { display: {$alternate_element_display_property_value}; } \n";
	}

	// Set separate styles for print view, if â€˜printhiddenâ€™ option is set.
	if ($opt['printhidden'] == 1) { 
		$HTMLStylesFmt[] = "@media print { #{$id} { display: {$display} !important; } } \n";
		if ($id2)
			$HTMLStylesFmt[] = "@media print { #{$id2} { display: {$display} !important; } } \n";
	}

	// Save the Toggle state/data for this element.
	$HTMLFooterFmt[] = <<<EOT
<script type='text/javascript'><!--
	window.toggleData["{$id}"] = { 
		"new_state_to_toggle_to": "{$state_to_toggle_to}",
		"toggle_link_label_in_hidden_state": "{$labels['show']}",
		"toggle_link_label_in_visible_state": "{$labels['hide']}",
		"toggle_link_tooltip_in_hidden_state": "{$tooltips['show']}",
		"toggle_link_tooltip_in_visible_state": "{$tooltips['hide']}",
		"id_of_alternate_element": "{$id2}",
		"display": "{$display}",
		"display_of_alternate_element": "{$display2}",
		"is_button": "{$opt['button']}",
		"group": "{$opt['group']}",
		"set_cookie": "{$opt['set']}"
	};
--></script>
EOT;

	// Construct toggle link or button (later it is modified with javascript).
	$out  = "<span id='{$id}-tog' class='toggle" 
		  . ($opt['nojs'] > 0 ? " no-js-visible" : "") 
		  . "'>"
		  . ($opt['button'] == 1
			 ? "<input type='button' class='inputbutton togglebutton' title='{$tooltip}' value='{$label}' onclick='javascript:toggleObj(\"{$id}\")' />"
			 : "<a class='togglelink' title='{$tooltip}' href='javascript:toggleObj(\"{$id}\")'>{$label}</a>")
		  . "</span>";
	return Keep($out);
}