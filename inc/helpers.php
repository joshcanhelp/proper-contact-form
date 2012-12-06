<?php

if (! function_exists('proper_get_content_array')) {
function proper_get_content_array($type = 'page') {
	
	$content = array(
		'' => 'None'
	);

	$items = get_posts(array(
		'post_type' => $type,
		'numberposts'     => -1
	));
	
	
	if (!empty($items)) :
		foreach ($items as $item) :
			$content[$item->ID] = $item->post_title;
		endforeach;
	endif;
	
	return $content;
	
}
}

if (! function_exists('proper_get_textarea_opts')) {
function proper_get_textarea_opts($txt) {
	
	$opts = array();
	
	if (empty($txt)) return $opts;
	
	$txt = str_replace("\r", "\n", $txt);
	$txt_arr = explode("\n", $txt);
	
	foreach ($txt_arr as $opt) :
	
		$opt = trim($opt);
		if (empty($opt)) continue;
		
		$opts[$opt] = $opt;
		
	endforeach;
	
	return $opts;
	
}
}

if (! function_exists('proper_display_errors')) {
function proper_display_errors($errs) {
	$output = '
	<div class="proper_error_box">
		<h6>Please correct the following errors:</h6>
		<ul>';
	
	foreach ($errs as $err) :
		$output .= "
		<li>$err</li>";
	endforeach;
	
	$output .= '
		</ul>
	</div>';
	
	return $output;
}
}