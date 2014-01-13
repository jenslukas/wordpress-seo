<?php
/**
 * @package Admin
 */

if ( ! defined( 'WPSEO_VERSION' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

/**
 * Function used from AJAX calls, takes it variables from $_POST, dies on exit.
 */
function wpseo_set_option() {
	if ( ! current_user_can( 'manage_options' ) )
		die( '-1' );
	check_ajax_referer( 'wpseo-setoption' );

	$option = esc_attr( $_POST['option'] );
	if ( $option != 'page_comments' )
		die( '-1' );

	update_option( $option, 0 );
	die( '1' );
}

add_action( 'wp_ajax_wpseo_set_option', 'wpseo_set_option' );

/**
 * Function used to remove the admin notices for several purposes, dies on exit.
 */
function wpseo_set_ignore() {
	if ( ! current_user_can( 'manage_options' ) )
		die( '-1' );
	check_ajax_referer( 'wpseo-ignore' );

	$options                          = get_option( 'wpseo' );
	$ignore_key 					  = sanitize_text_field( $_POST['option'] );
	$options['ignore_' . $ignore_key] = true;
	update_option( 'wpseo', $options );
	die( '1' );
}

add_action( 'wp_ajax_wpseo_set_ignore', 'wpseo_set_ignore' );

/**
 * Function used to delete blocking files, dies on exit.
 */
function wpseo_kill_blocking_files() {
	if ( ! current_user_can( 'manage_options' ) )
		die( '-1' );
	check_ajax_referer( 'wpseo-blocking-files' );

	$message = 'There were no files to delete.';
	$options = get_option( 'wpseo' );
	if ( is_array( $options['blocking_files'] ) && count( $options['blocking_files'] ) > 0 ) {
		$message = 'success';
		$options['blocking_files'] = array_unique( $options['blocking_files'] );
		foreach ( $options['blocking_files'] as $k => $file ) {
			if ( ! @unlink( $file ) )
				$message = __( 'Some files could not be removed. Please remove them via FTP.', 'wordpress-seo' );
			else
				unset( $options['blocking_files'][$k] );
		}
		update_option( 'wpseo', $options );
	}

	die( $message );
}

add_action( 'wp_ajax_wpseo_kill_blocking_files', 'wpseo_kill_blocking_files' );

/**
 * Retrieve the suggestions from the Google Suggest API and return them to be
 * used in the suggest box within the plugin. Dies on exit.
 */
function wpseo_get_suggest() {
	check_ajax_referer( 'wpseo-get-suggest' );

	$term   = urlencode( $_GET['term'] );
	$result = wp_remote_get( 'http://www.google.com/complete/search?output=toolbar&q=' . $term );

	preg_match_all( '`suggestion data="([^"]+)"/>`u', $result['body'], $matches );

	$return_arr = array();

	foreach ( $matches[1] as $match ) {
		$return_arr[] = html_entity_decode( $match, ENT_COMPAT, 'UTF-8' );
	}
	echo json_encode( $return_arr );
	die();
}

add_action( 'wp_ajax_wpseo_get_suggest', 'wpseo_get_suggest' );

/**
 * @todo: add capacity check for below ajax methods, something along the lines of user_can_edit_post()
 */

/**
 * Save an individual SEO title from the Bulk Editor.
 */
function wpseo_save_title() {

	$new_title      = $_POST['new_title'] ;
	$id             = intval( $_POST['wpseo_post_id'] );
	$original_title = $_POST['existing_title'];

	$results = wpseo_upsert_new_title( $id, $new_title, $original_title );

	echo json_encode( $results );
	die();
}

add_action( 'wp_ajax_wpseo_save_title', 'wpseo_save_title' );

/**
 * Helper function for updating an existing seo title or create a new one
 * if it doesn't already exist.
 */
function wpseo_upsert_new_title( $post_id, $new_title, $original_title ) {

	$meta_key   = WPSEO_Meta::$meta_prefix . 'title';
	$return_key = 'title';
	return wpseo_upsert_meta( $post_id, $new_title, $original_title, $meta_key, $return_key );
}

/**
 * Helper function to update a post's meta data, returning relevant information
 * about the information updated and the results or the meta update.
 */
function wpseo_upsert_meta( $post_id, $new_meta_value, $orig_meta_value, $meta_key, $return_key ) {

	$res = update_post_meta( $post_id, $meta_key, $new_meta_value );

	return array(
		'status'                 => ( ( $res !== false ) ? 'success' : 'failure'),
		'post_id'                => $post_id,
		"new_{$return_key}"      => $new_meta_value,
		"original_{$return_key}" => $orig_meta_value,
		'results'                => $res,
	);
}

/**
 * Save all titles sent from the Bulk Editor.
 */
function wpseo_save_all_titles() {
	$new_titles      = $_POST['titles'];
	$original_titles = $_POST['existing_titles'];

	$results = array();

	foreach ( $new_titles as $id => $new_title ) {
		$original_title = $original_titles[ $id ];
		$results[]      = wpseo_upsert_new_title( $id, $new_title, $original_title );
	}
	echo json_encode( $results );

	die();
}

add_action( 'wp_ajax_wpseo_save_all_titles', 'wpseo_save_all_titles' );

/**
 * Save an individual meta description from the Bulk Editor.
 */
function wpseo_save_description() {

	$new_metadesc      = $_POST['new_metadesc'] ;
	$id                = intval( $_POST['wpseo_post_id'] );
	$original_metadesc = $_POST['existing_metadesc'];

	$results = wpseo_upsert_new_description( $id, $new_metadesc, $original_metadesc );

	echo json_encode( $results );
	die();
}

add_action( 'wp_ajax_wpseo_save_desc', 'wpseo_save_description' );

/**
 * Helper function to create or update a post's meta description.
 */
function wpseo_upsert_new_description( $post_id, $new_metadesc, $original_metadesc ) {

	$meta_key   = WPSEO_Meta::$meta_prefix . 'metadesc';
	$return_key = 'metadesc';
	return wpseo_upsert_meta( $post_id, $new_metadesc, $original_metadesc, $meta_key, $return_key );
}

/**
 * Save all description sent from the Bulk Editor.
 */
function wpseo_save_all_descriptions() {
	$new_metadescs      = $_POST['metadescs'];
	$original_metadescs = $_POST['existing_metadescs'];

	$results = array();

	foreach ( $new_metadescs as $id => $new_metadesc ) {
		$original_metadesc = $original_metadescs[ $id ];
		$results[]         = wpseo_upsert_new_description( $id, $new_metadesc, $original_metadesc );
	}
	echo json_encode( $results );

	die();
}

add_action( 'wp_ajax_wpseo_save_all_descs', 'wpseo_save_all_descriptions' );
