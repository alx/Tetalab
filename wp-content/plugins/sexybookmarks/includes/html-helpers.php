<?php
//list all bookmarks in the plugin options page
function sexy_network_input_select($name, $hint) {
	global $sexy_plugopts;
	return sprintf('<label class="%s" title="%s"><input %sname="bookmark[]" type="checkbox" value="%s"  id="%s" /></label>',
		$name,
		$hint,
		@in_array($name, $sexy_plugopts['bookmark'])?'checked="checked" ':"",
		$name,
		$name
	);
}

// returns the option tag for a form select element
// $opts array expecting keys: field, value, text
function sexy_form_select_option($opts) {
	global $sexy_plugopts;
	$opts=array_merge(
		array(
			'field'=>'',
			'value'=>'',
			'text'=>'',
		),
		$opts
	);
	return sprintf('<option%s value="%s">%s</option>',
		($sexy_plugopts[$opts['field']]==$opts['value'])?' selected="selected"':"",
		$opts['value'],
		$opts['text']
	);
}

// given an array $options of data and $field to feed into sexy_form_select_option
function sexy_select_option_group($field, $options) {
	$h='';
	foreach ($options as $value=>$text) {
		$h.=sexy_form_select_option(array(
			'field'=>$field,
			'value'=>$value,
			'text'=>$text,
		));
	}
	return $h;
}

// function to list bookmarks that have been chosen by admin
function bookmark_list_item($name, $opts=array()) {
	global $sexy_plugopts, $sexy_bookmarks_data;

	$url=$sexy_bookmarks_data[$name]['baseUrl'];
	foreach ($opts as $key=>$value) {
		$url=str_replace(strtoupper($key), $value, $url);
	}
	
	return sprintf(
		"\t\t".'<li class="%s">'."\n\t\t\t".'<a href="%s" rel="%s"%s title="%s">%s</a>'."\n\t\t".'</li>'."\n",
		$name,
		$url,
		$sexy_plugopts['reloption'],
		$sexy_plugopts['targetopt']=="_blank"?' class="external"':'',
		$sexy_bookmarks_data[$name]['share'],
		$sexy_bookmarks_data[$name]['share']
	);
}

?>