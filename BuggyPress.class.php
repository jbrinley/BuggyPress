<?php
/**
 * User: jbrinley
 * Date: 5/15/11
 * Time: 4:58 PM
 */

abstract class BuggyPress {
	const TEXT_DOMAIN = 'buggypress';
	const VERSION = '0.2';
	const DB_VERSION = 1;
	const PLUGIN_NAME = 'BuggyPress';
	const DEBUG = FALSE;
	const MIN_WP_VERSION = '3.1';
	const MIN_PHP_VERSION = '5.2';


	/**
	 * A wrapper around WP's __() to add the plugin's text domain
	 *
	 * @param string $string
	 * @return string|void
	 */
	final public static function __( $string ) {
		return __($string, self::TEXT_DOMAIN);
	}

	/**
	 * A wrapper around WP's _e() to add the plugin's text domain
	 *
	 * @param string $string
	 * @return void
	 */
	final public static function _e( $string ) {
		return _e($string, self::TEXT_DOMAIN);
	}


	/**
	 * @static
	 * @return string The system path to this plugin's directory, with no trailing slash
	 */
	final public static function plugin_path() {
		return WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) );
	}

	/**
	 * @static
	 * @return string The url to this plugin's directory, with no trailing slash
	 */
	final public static function plugin_url() {
		return WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) );
	}
}