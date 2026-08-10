<?php
/* Data API class for authentication and accessing endpoints */

defined( 'ABSPATH' ) || exit;

class DataApi {

    const STATUS_FOUND = 'found';
    const STATUS_NOT_FOUND = 'not_found';
    const STATUS_UNAVAILABLE = 'unavailable';

    /**
     * @param string     $status One of STATUS_FOUND, STATUS_NOT_FOUND, STATUS_UNAVAILABLE.
     * @param array|null $data   Payload when status is found.
     * @return array
     */
    private static function build_result( $status, $data = null ) {
        return array(
            'status' => $status,
            'data'   => $data,
        );
    }

    /**
     * Encryption key derived from WordPress auth salt.
     *
     * @return string Binary key material for XSalsa20-Poly1305 (32 bytes).
     */
    private static function get_token_encryption_key() {
        return hash( 'sha256', wp_salt( 'auth' ), true );
    }

    /**
     * Encrypt a bearer token for transient storage using XSalsa20-Poly1305.
     *
     * @param string $token Plaintext access token.
     * @return string|null Base64-encoded nonce + ciphertext, or null on failure.
     */
    private static function encrypt_token( $token ) {
        try {
            $nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
            $ciphertext = sodium_crypto_secretbox(
                $token,
                $nonce,
                self::get_token_encryption_key()
            );
        } catch ( \Throwable $throwable ) {
            return null;
        }

        return base64_encode( $nonce . $ciphertext );
    }

    /**
     * Decrypt a bearer token from transient storage using XSalsa20-Poly1305.
     *
     * @param string $encrypted_token Base64-encoded nonce + ciphertext.
     * @return string|null Plaintext token, or null on failure.
     */
    private static function decrypt_token( $encrypted_token ) {
        $decoded = base64_decode( $encrypted_token, true );
        if ( false === $decoded ) {
            return null;
        }

        if ( strlen( $decoded ) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ) {
            return null;
        }

        $nonce = substr( $decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
        $ciphertext = substr( $decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

        try {
            $token = sodium_crypto_secretbox_open(
                $ciphertext,
                $nonce,
                self::get_token_encryption_key()
            );
        } catch ( \Throwable $throwable ) {
            return null;
        }

        if ( false === $token || $token === '' ) {
            return null;
        }

        return $token;
    }

    /**
     * Persist an encrypted token in a transient and remove any legacy plaintext option.
     *
     * @param string $token Plaintext access token.
     * @return void
     */
    private static function store_token( $token ) {
        $token_key = GFEDF_Config::get_data_api_token_option_name();
        $encrypted_token = self::encrypt_token( $token );

        if ( ! empty( $encrypted_token ) ) {
            set_transient( $token_key, $encrypted_token, GFEDF_Config::get_data_api_token_ttl() );
        }

        delete_option( $token_key );
    }

    // get token from encrypted transient or authenticate to get valid token
    public static function get_token(){
        $token_key = GFEDF_Config::get_data_api_token_option_name();
        $encrypted_token = get_transient( $token_key );
        $token_val = '';

        if ( ! empty( $encrypted_token ) && is_string( $encrypted_token ) ) {
            $token_val = self::decrypt_token( $encrypted_token );
        }

        if ( empty( $token_val ) ){
            // Remove any legacy plaintext option; do not migrate it.
            delete_option( $token_key );
            //token not set so authenticate to generate
            $token_val = self::authenticate();
        }
        return $token_val;
    }

    //Sends authentication request to DataAPI using app's client key and client id
    public static function authenticate(){

        $body = array('clientid' => GFEDF_Config::get_data_api_clientid(), 'clientkey' => GFEDF_Config::get_data_api_clientkey());
        $auth_url = GFEDF_Config::get_data_api_url() . GFEDF_Config::get_data_api_stub_auth();
        $resp = wp_remote_post($auth_url, array(
                        'method' => 'POST', 'sslverify' => GFEDF_Config::get_data_api_sslverify(),
                        'body' => $body));

        if ( is_wp_error( $resp ) ) {
            $error_message = $resp->get_error_message();
            error_log( print_r("GF External Data Fields plugin :: error authenticating to Data API - " . $error_message, true) );
            return null;
        } else {
            //var_dump($resp['body']);
            $json = json_decode($resp['body'], true);
            //var_dump($json);
            if ( !empty($json['access_token'])) {
                //get token from response and save
                $token = $json['access_token'];
                self::store_token( $token );
                return $token;
            } else {
                error_log( print_r("GF External Data Fields plugin :: error authenticating to Data API.", true) );
                return null;
            }
        } 
    }

    // Get student information from the student data DataAPI endpoint
    public static function get_student($_username) {
        $stu_url = GFEDF_Config::get_data_api_url() . GFEDF_Config::get_data_api_stub_stu();
        $stu_url = sprintf($stu_url, rawurlencode($_username));
        return self::get_url($stu_url);
    }

    // Get employee information from the employee data DataAPI endpoint
    public static function get_employee( $_username ){
        $emp_url = GFEDF_Config::get_data_api_url() . GFEDF_Config::get_data_api_stub_emp();
        $emp_url = sprintf( $emp_url, rawurlencode($_username) );
        return self::get_url( $emp_url );
    }

    // Make call to DataApi endpoint
    public static function get_url( $_url, $isRetry = false ) {

        // get valid authorization header
        $headers = self::create_authorization_header();
        if ( is_null( $headers ) ) {
            error_log( print_r( "GF External Data Fields plugin :: Data API token unavailable; cannot call endpoint.", true ) );
            debug_log( print_r( "GF External Data Fields plugin :: Data API token unavailable; cannot call endpoint.", true ) );
            return self::build_result( self::STATUS_UNAVAILABLE );
        }

        try {
            //make url call
            $resp = wp_remote_get( $_url, array( 'headers' => $headers, 'sslverify' => GFEDF_Config::get_data_api_sslverify() ) );

            if ( is_wp_error( $resp ) ) {

                //if error, log it
                $error_message = $resp->get_error_message();
                error_log( print_r( "GF External Data Fields plugin :: error calling Data API - " . $error_message, true ) );
                debug_log( print_r( "GF External Data Fields plugin :: error calling Data API - " . $error_message, true ) );
                return self::build_result( self::STATUS_UNAVAILABLE );
            }

            $response_code = (int) wp_remote_retrieve_response_code( $resp );

            if ( $response_code == 401 ) {

                if ( $isRetry ) {
                    error_log( print_r( "GF External Data Fields plugin :: Data API still unauthorized after re-authentication.", true ) );
                    debug_log( print_r( "GF External Data Fields plugin :: Data API still unauthorized after re-authentication.", true ) );
                    return self::build_result( self::STATUS_UNAVAILABLE );
                }

                // token is likely expired so reauthenticate and retry data call
                debug_log( print_r("GFEDF :: Authentication token is likely expired. Attempting to reauthenticate.", true) );
                $token = self::authenticate();
                if ( empty( $token ) ) {
                    error_log( print_r( "GF External Data Fields plugin :: re-authentication failed; Data API unavailable.", true ) );
                    debug_log( print_r( "GF External Data Fields plugin :: re-authentication failed; Data API unavailable.", true ) );
                    return self::build_result( self::STATUS_UNAVAILABLE );
                }

                return self::get_url( $_url, true );

            }

            if ( $response_code == 404 ) {
                return self::build_result( self::STATUS_NOT_FOUND );
            }

            // Anything outside 2xx (after 401/404 handling) is treated as unavailable.
            if ( $response_code < 200 || $response_code >= 300 ) {
                error_log( print_r( "GF External Data Fields plugin :: unexpected Data API response code " . $response_code, true ) );
                debug_log( print_r( "GF External Data Fields plugin :: unexpected Data API response code " . $response_code, true ) );
                return self::build_result( self::STATUS_UNAVAILABLE );
            }

            // it's a valid response, so use it
            $body = json_decode( wp_remote_retrieve_body( $resp ), true );
            //var_dump($body);
            if ( ! empty( $body['data'] ) ) {
                return self::build_result( self::STATUS_FOUND, $body['data'] );
            } else {
                return self::build_result( self::STATUS_NOT_FOUND );
            }
        } catch ( Exception $e ){
            error_log( print_r("GF External Data Fields plugin :: error building and saving model - " . $e->getMessage(), true) );
            debug_log( print_r("GF External Data Fields plugin :: error building and saving model - " . $e->getMessage(), true) );
            return self::build_result( self::STATUS_UNAVAILABLE );
        }
    }

    // create authorization bearer/token header to use in request
    public static function create_authorization_header(){
        $token = self::get_token();
        if ( empty( $token ) ) {
            return null;
        }

        $headers = array('Authorization' => 'Bearer ' . $token );

        return $headers;
    }
}
