<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * Functions used by database updater go here
 */

/**
 * Renames all "sermon_date_old" fields to "sermon_date" if "sermon_date" is not set
 */
function sm_update_28_revert_old_dates() {
	if ( get_option( 'wpfc_sm_dates_restore_done' ) ) {
		return;
	}

	global $wpdb;

	foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_date FROM $wpdb->posts WHERE post_type = %s AND post_status NOT IN ('auto-draft', 'inherit')", 'wpfc_sermon' ) ) as $sermon ) {
		if ( get_post_meta( $sermon->ID, 'sermon_date', true ) === '' &&
		     $date = get_post_meta( $sermon->ID, 'sermon_date_old', true ) !== '' ) {
			update_post_meta( $sermon->ID, 'sermon_date', is_numeric( $date ) ?: strtotime( $date ) );
			delete_post_meta( $sermon->ID, 'sermon_date_old' );
		}
	}

	// clear all cached data
	wp_cache_flush();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * Final dates conversion for users who skipped converters in previous SM versions
 *
 * Basically, converts "sermon_date" value to Unix time if it's not numeric
 */
function sm_update_28_convert_dates_to_unix() {
	global $wpdb;

	// All sermons
	$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_date FROM $wpdb->posts WHERE post_type = %s AND post_status NOT IN ('auto-draft', 'inherit')", 'wpfc_sermon' ) );

	foreach ( $sermons as $sermon ) {
		if ( $date = get_post_meta( $sermon->ID, 'sermon_date', true ) ) {
			if ( ! is_numeric( $date ) ) {
				update_post_meta( $sermon->ID, 'sermon_date', strtotime( $date ) );
			}
		}
	}

	// clear all cached data
	wp_cache_flush();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * Fills out dates of sermons that don't have `sermon_date` set. Takes "Published" date for them and marks
 * them as auto-filled, so they get updated when Published date gets updated
 */
function sm_update_28_fill_out_empty_dates() {
	global $wpdb;

	// All sermons
	$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_date FROM $wpdb->posts WHERE post_type = %s AND post_status NOT IN ('auto-draft', 'inherit')", 'wpfc_sermon' ) );

	foreach ( $sermons as $sermon ) {
		if ( get_post_meta( $sermon->ID, 'sermon_date', true ) === '' ) {
			update_post_meta( $sermon->ID, 'sermon_date', strtotime( $sermon->post_date ) );
			update_post_meta( $sermon->ID, 'sermon_date_auto', '1' );
		}
	}

	// clear all cached data
	wp_cache_flush();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * For enabling sorting by series date
 *
 * @see SM_Dates_WP::update_series_date()
 */
function sm_update_28_fill_out_series_dates() {
	SM_Dates_WP::update_series_date();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * Renders sermon HTML and saves as "post_content", for better search compatibility
 */
function sm_update_28_save_sermon_render_into_post_content() {
	global $wpdb;
	global $post;

	$original_post = $post;

	// All sermons
	$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_date FROM $wpdb->posts WHERE post_type = %s", 'wpfc_sermon' ) );

	foreach ( $sermons as $sermon ) {
		$post = $sermon;
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_content = '%s' WHERE ID = $sermon->ID", wpfc_sermon_single( true ) ) );
	}

	$post = $original_post;

	// clear all cached data
	wp_cache_flush();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * We had a bug from 2.8 to 2.8.3, so we will do it again
 */
function sm_update_284_resave_sermons() {
	sm_update_28_save_sermon_render_into_post_content();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * There was a bug in function for 2.8, so we will do it again
 */
function sm_update_29_fill_out_series_dates() {
	sm_update_28_fill_out_series_dates();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * Settings storage has been changed in 2.9
 */
function sm_update_29_convert_settings() {
	$original_settings = get_option( 'wpfc_options', array() );

	foreach ( $original_settings as $key => $value ) {
		add_option( 'sermonmanager_' . $key, $value );
	}

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * SB and SE import did not import dates correctly. This function imports them for those who did import
 */
function sm_update_293_fix_import_dates() {
	sm_update_28_fill_out_empty_dates();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * Removed Bibly so we will change option names
 */
function sm_update_210_update_options() {
	if ( is_bool( SermonManager::getOption( 'bibly' ) ) ) {
		add_option( 'sermonmanager_verse_popup', SermonManager::getOption( 'bibly' ) ? 'yes' : 'no' );
	}

	if ( $bible_version = SermonManager::getOption( 'bibly_version' ) ) {
		add_option( 'sermonmanager_verse_bible_version', $bible_version );
	}

	if ( is_bool( SermonManager::getOption( 'use_old_player' ) ) ) {
		add_option( 'sermonmanager_player', SermonManager::getOption( 'use_old_player' ) ? 'wordpress' : 'plyr' );
	}

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}