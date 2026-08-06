<?php
/**
 * Plugin configuration resolved from wp-config.php constants.
 *
 * Define GFEDF_* constants in wp-config.php to override defaults.
 */

defined( 'ABSPATH' ) || exit;

class GFEDF_Config {

	/**
	 * Shortcode attribute name used for user-type restriction.
	 *
	 * @return string
	 */
	public static function get_restrict_attr() {
		return defined( 'GFEDF_RESTRICT_ATTR' ) ? GFEDF_RESTRICT_ATTR : 'allow';
	}

	/**
	 * Shortcode attribute name for redirect URL when restriction fails.
	 *
	 * @return string
	 */
	public static function get_restrict_fail_redirect_attr() {
		return defined( 'GFEDF_RESTRICT_FAIL_REDIRECT_ATTR' )
			? GFEDF_RESTRICT_FAIL_REDIRECT_ATTR
			: 'failure_redirect_url';
	}

	/**
	 * Default redirect URL when restriction fails and no shortcode URL is set.
	 *
	 * @return string
	 */
	public static function get_default_redirect_url() {
		return defined( 'GFEDF_DEFAULT_REDIRECT_URL' ) ? GFEDF_DEFAULT_REDIRECT_URL : '';
	}

	/**
	 * Base URL of the Data API (with trailing slash).
	 *
	 * @return string
	 */
	public static function get_data_api_url() {
		return defined( 'GFEDF_DATA_API_URL' ) ? GFEDF_DATA_API_URL : '';
	}

	/**
	 * Path stub for employee lookup (sprintf format with username).
	 *
	 * @return string
	 */
	public static function get_data_api_stub_emp() {
		return defined( 'GFEDF_DATA_API_STUB_EMP' ) ? GFEDF_DATA_API_STUB_EMP : 'employee/%s';
	}

	/**
	 * Path stub for student lookup (sprintf format with username).
	 *
	 * @return string
	 */
	public static function get_data_api_stub_stu() {
		return defined( 'GFEDF_DATA_API_STUB_STU' ) ? GFEDF_DATA_API_STUB_STU : 'student/%s';
	}

	/**
	 * Path stub for Data API authentication.
	 *
	 * @return string
	 */
	public static function get_data_api_stub_auth() {
		return defined( 'GFEDF_DATA_API_STUB_AUTH' ) ? GFEDF_DATA_API_STUB_AUTH : 'auth/login';
	}

	/**
	 * Data API client ID.
	 *
	 * @return string
	 */
	public static function get_data_api_clientid() {
		return defined( 'GFEDF_DATA_API_CLIENT_ID' ) ? GFEDF_DATA_API_CLIENT_ID : '';
	}

	/**
	 * Data API client key.
	 *
	 * @return string
	 */
	public static function get_data_api_clientkey() {
		return defined( 'GFEDF_DATA_API_CLIENT_KEY' ) ? GFEDF_DATA_API_CLIENT_KEY : '';
	}

	/**
	 * WordPress transient key used to store the encrypted Data API access token.
	 *
	 * @return string
	 */
	public static function get_data_api_token_option_name() {
		return defined( 'GFEDF_DATA_API_TOKEN_OPTION_NAME' )
			? GFEDF_DATA_API_TOKEN_OPTION_NAME
			: 'gfedf_dataapi_token';
	}

	/**
	 * TTL in seconds for the encrypted Data API token transient.
	 *
	 * @return int
	 */
	public static function get_data_api_token_ttl() {
		return defined( 'GFEDF_DATA_API_TOKEN_TTL' )
			? (int) GFEDF_DATA_API_TOKEN_TTL
			: HOUR_IN_SECONDS;
	}

	/**
	 * Whether SSL is verified on Data API requests.
	 *
	 * Defaults to true; set GFEDF_DATA_API_SSLVERIFY to false only for broken local/dev certs.
	 *
	 * @return bool
	 */
	public static function get_data_api_sslverify() {
		return defined( 'GFEDF_DATA_API_SSLVERIFY' ) ? (bool) GFEDF_DATA_API_SSLVERIFY : true;
	}
}
