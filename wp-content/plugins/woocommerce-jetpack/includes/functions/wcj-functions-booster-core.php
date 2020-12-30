<?php
/**
 * Booster for WooCommerce - Functions - Booster Core
 *
 * @version 4.3.0
 * @since   2.9.0
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'wcj_handle_deprecated_options' ) ) {
	/**
	 * wcj_handle_deprecated_options.
	 *
	 * @version 3.8.0
	 * @since   3.8.0
	 */
	function wcj_handle_deprecated_options() {
		foreach ( WCJ()->modules as $module ) {
			$module->handle_deprecated_options();
		}
	}
}

if ( ! function_exists( 'wcj_plugin_url' ) ) {
	/**
	 * wcj_plugin_url.
	 *
	 * @version 2.3.0
	 * @todo    (maybe) add `WCJ_PLUGIN_URL` constant instead
	 */
	function wcj_plugin_url() {
		return untrailingslashit( plugin_dir_url( realpath( dirname( __FILE__ ) . '/..' ) ) );
	}
}

if ( ! function_exists( 'wcj_plugin_path' ) ) {
	/**
	 * Get the plugin path.
	 *
	 * @return string
	 * @todo   use `WCJ_PLUGIN_PATH` constant instead
	 */
	function wcj_plugin_path() {
		return untrailingslashit( realpath( plugin_dir_path( __FILE__ ) . '/../..' ) );
	}
}

if ( ! function_exists( 'wcj_is_rest' ) ) {
	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 *
	 * @author  matzeeable
	 * @see     https://wordpress.stackexchange.com/a/317041/25264
	 * @return  boolean
	 */
	function wcj_is_rest() {
		$prefix = rest_get_url_prefix();
		if (
			defined( 'REST_REQUEST' ) && REST_REQUEST || // After WP_REST_Request initialisation
			isset( $_GET['rest_route'] ) && 0 === strpos( trim( $_GET['rest_route'], '\\/' ), $prefix , 0 ) // Support "plain" permalink settings
		) {
			return true;
		}
		// URL Path begins with wp-json/ (your REST prefix)
		// Also supports WP installations in subfolders
		$rest_url    = wp_parse_url( site_url( $prefix ) );
		$current_url = wp_parse_url( add_query_arg( array() ) );
		return ( 0 === strpos( $current_url['path'], $rest_url['path'], 0 ) );
	}
}

if ( ! function_exists( 'wcj_check_modules_by_user_roles' ) ) {
	/**
	 * wcj_check_modules_by_user_roles.
	 *
	 * @version 4.3.0
	 * @since   4.3.0
	 * @return  boolean
	 * @todo    [fix] re-implement `wcj_wp_get_current_user()` instead of requiring `pluggable.php`
	 */
	function wcj_check_modules_by_user_roles( $module_id ) {
		global $wcj_modules_by_user_roles_data;
		if ( ! isset( $wcj_modules_by_user_roles_data ) ) {
			if ( ! function_exists( 'wp_get_current_user' ) ) {
				require_once( ABSPATH . 'wp-includes/pluggable.php' );
			}
			$current_user = wp_get_current_user();
			$wcj_modules_by_user_roles_data['role'] = ( isset( $current_user->roles ) && is_array( $current_user->roles ) && ! empty( $current_user->roles ) ?
				reset( $current_user->roles ) : 'guest' );
			$wcj_modules_by_user_roles_data['role'] = ( '' != $wcj_modules_by_user_roles_data['role'] ? $wcj_modules_by_user_roles_data['role'] : 'guest' );
			$wcj_modules_by_user_roles_data['modules_incl'] = wcj_get_option( 'wcj_modules_by_user_roles_incl_' . $wcj_modules_by_user_roles_data['role'], '' );
			$wcj_modules_by_user_roles_data['modules_excl'] = wcj_get_option( 'wcj_modules_by_user_roles_excl_' . $wcj_modules_by_user_roles_data['role'], '' );
		}
		return (
			( ! empty( $wcj_modules_by_user_roles_data['modules_incl'] ) && ! in_array( $module_id, $wcj_modules_by_user_roles_data['modules_incl'] ) ) ||
			( ! empty( $wcj_modules_by_user_roles_data['modules_excl'] ) &&   in_array( $module_id, $wcj_modules_by_user_roles_data['modules_excl'] ) )
		) ? false : true;
	}
}

if ( ! function_exists( 'wcj_is_module_enabled' ) ) {
	/*
	 * wcj_is_module_enabled.
	 *
	 * @version 4.3.0
	 * @since   2.4.0
	 * @return  boolean
	 */
	function wcj_is_module_enabled( $module_id ) {
		return ( 'modules_by_user_roles' != $module_id && wcj_is_module_enabled( 'modules_by_user_roles' ) && ! wcj_is_rest() && ! wcj_check_modules_by_user_roles( $module_id ) ?
			false : ( 'yes' === wcj_get_option( 'wcj_' . $module_id . '_enabled', 'no' ) ) );
	}
}
