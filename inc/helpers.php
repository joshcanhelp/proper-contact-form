<?php

if ( ! function_exists( 'proper_get_content_array' ) ) {
	function proper_get_content_array( $type = 'page' ) {

		$content = array(
			'' => __( 'None', 'proper-contact' )
		);

		$items = get_posts( array(
			'post_type'   => $type,
			'numberposts' => - 1
		) );


		if ( ! empty( $items ) ) :
			foreach ( $items as $item ) :
				$content[$item->ID] = $item->post_title;
			endforeach;
		endif;

		return $content;

	}
}

if ( ! function_exists( 'proper_get_textarea_opts' ) ) {
	function proper_get_textarea_opts( $txt ) {

		$opts = array();

		if ( empty( $txt ) ) return $opts;

		$txt     = str_replace( "\r", "\n", $txt );
		$txt_arr = explode( "\n", $txt );

		foreach ( $txt_arr as $opt ) :

			$opt = trim( $opt );
			if ( empty( $opt ) ) continue;

			$opts[stripslashes( $opt )] = stripslashes( $opt );

		endforeach;

		return $opts;

	}
}

// Display formatted error listing
if ( ! function_exists( 'proper_display_errors' ) ) {
	function proper_display_errors( $errs ) {
		$output = '
	<div class="proper_error_box">
		<p><strong>'. __( 'Please correct the following errors', 'proper-contact' ) . ':</strong></p>
		<ul>';

		foreach ( $errs as $err ) :
			$output .= "
		<li>$err</li>";
		endforeach;

		$output .= '
		</ul>
	</div>';

		return $output;
	}
}

// Get blacklist IPs and emails from the Discussion settings
if ( ! function_exists( 'proper_get_blacklist' ) ) {
	function proper_get_blacklist() {
		$final_blocked_arr = array();

		$blocked = get_option( 'blacklist_keys' );
		$blocked = str_replace( "\r", "\n", $blocked );

		$blocked_arr = explode( "\n", $blocked );
		$blocked_arr = array_map( 'trim', $blocked_arr );

		foreach ( $blocked_arr as $ip_or_email ) {
			$ip_or_email = trim( $ip_or_email );
			if (
					filter_var( $ip_or_email, FILTER_VALIDATE_IP ) ||
					filter_var( $ip_or_email, FILTER_VALIDATE_EMAIL )
			) {
				$final_blocked_arr[] = $ip_or_email;
			}
		}

		return $final_blocked_arr;
	}
}

// Get blacklist IPs and emails from the Discussion settings
if ( ! function_exists( 'proper_prepare_label' ) ) {
	function proper_prepare_label( $key ) {

		$label_text = proper_contact_get_key( $key );

		if ( ! empty( $label_text ) ) {
			$label_text = sanitize_text_field( $label_text );
			$label_text = stripslashes( $label_text );
		}

		return $label_text;
	}
}