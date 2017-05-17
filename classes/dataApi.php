<?php 
/* Data API class for authentication and accessing endpoints */

require_once( dirname( plugin_dir_path( __FILE__ ) ) . "/config.php" );

class DataApi {

    //Sends authentication request to DataAPI using app's client key and client id
    public static function authenticate(){

        $body = array('clientid' => GFEDF_Config::get_data_api_clientid(), 'clientkey' => GFEDF_Config::get_data_api_clientkey());
        $auth_url = GFEDF_Config::get_data_api_url() . GFEDF_Config::get_data_api_stub_auth();
        $resp = wp_remote_post($auth_url, array(
                        'method' => 'POST', 'sslverify' => false,
                        'body' => $body));

        if ( is_wp_error( $resp ) ) {
            $error_message = $resp->get_error_message();
            error_log( print_r("GF External Data Fields plugin :: error authenticating to Data API - " . $error_message, true) );
            return null;
        } else {
            //var_dump($resp['body']);
            $json = json_decode($resp['body'], true);
            //var_dump($json);
            if ( !empty($json['token'])) {
                //get token from response and save
                $token = $json['token'];
                update_option(GFEDF_Config::get_data_api_token_option_name(), $token);
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
        $stu_url = sprintf($stu_url, $_username);
        return self::get_url($stu_url);
    }

    // Get employee information from the employee data DataAPI endpoint
    public static function get_employee( $_username ){
        $emp_url = GFEDF_Config::get_data_api_url() . GFEDF_Config::get_data_api_stub_emp();
        $emp_url = sprintf( $emp_url, $_username );
        return self::get_url( $emp_url );
    }

    // Make call to DataApi endpoint
    public static function get_url( $_url ) {

        // get valid authorization header
        $headers = self::create_authorization_header();
        try {
            //make url call
            $resp = wp_remote_get( $_url, array( 'headers' => $headers, 'sslverify' => false ) );

            if ( is_wp_error( $resp ) ) {

                //if error, log it
                $error_message = $resp->get_error_message();
                error_log( print_r( "GF External Data Fields plugin :: error authenticating to Data API - " . $error_message, true ) );
                return null;
            } else {

                //handle non-error response
                $json = $resp;

                if ( isset( $json ) && $json['body'] == 'Unauthorized.' ) {

                    // token is likely expired so reauthenticate and retry data call
                    self::authenticate();
                    return self::get_url( $_url );
                
                } else if ( isset( $json ) ) {

                    // it's a valid response, so use it
                    $body = json_decode( $json['body'], true );
                    //var_dump($body);
                    if ( !empty( $body ) ) {
                        return $body['data'];
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            }
        } catch ( Exception $e ){
            error_log( print_r("GF External Data Fields plugin :: error building and saving model - " . $e->getMessage(), true) );
            return null;
        }
    }

    // get token from saved plugin option or authenticate to get valid token
    public static function get_token(){
        $token_val = get_option(GFEDF_Config::get_data_api_token_option_name());
        if ( empty($token_val) ){
            //token not set so authenticate to generate
            $token_val = self::authenticate();
        }
        return $token_val;
    }

    // create authorization bearer/token header to use in request
    public static function create_authorization_header(){
        $token = self::get_token();

        $headers = array('Authorization' => 'Bearer ' . $token);

        return $headers;
    }
}