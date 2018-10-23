<?php
/***
* Config class
* Add values for config variables
***/
class GFEDF_Config
{
    // user authentication and restriction config
    protected static $restrict_attr = "allow";  // user restriction attribute used in gf shortcode
    protected static $restrict_fail_redirect_attr = "failure_redirect_url"; // gf shortcode attribute for redirect url if fails restriction
    protected static $default_redirect_url = "";   // a default redirect url in case other is not provided in shortcode

    //config items for data api access
    protected static $data_api_url = "";  //base URL of DataAPI with trailing slash
    protected static $data_api_stub_emp = "employee/%s"; //path to query employee info
    protected static $data_api_stub_stu = "student/%s"; //path to query student info
    protected static $data_api_stub_auth = "auth/login";    //path for login/authentication
    protected static $data_api_clientid = "";   //client ID for DataAPI auth
    protected static $data_api_clientkey = "";  //client key for DataAPI auth
    protected static $data_api_token_option_name = "gfedf_dataapi_token";   //name to use for WP option to use to save token
    protected static $data_api_sslverify = false;   //specify whether ssl is verified in call to DataAPI, should prob be true for production environment

    //get functions for config items
    public static function get_restrict_attr() {
        return self::$restrict_attr;
    }
    
    public static function get_restrict_fail_redirect_attr() {
        return self::$restrict_fail_redirect_attr;
    }

    public static function get_default_redirect_url() {
        return self::$default_redirect_url;
    }

    public static function get_data_api_url(){
        return self::$data_api_url;
    }

    public static function get_data_api_stub_emp(){
        return self::$data_api_stub_emp;
    }

    public static function get_data_api_stub_stu(){
        return self::$data_api_stub_stu;
    }

    public static function get_data_api_stub_auth(){
        return self::$data_api_stub_auth;
    }

    public static function get_data_api_clientid(){
        return self::$data_api_clientid;
    }

    public static function get_data_api_clientkey(){
        return self::$data_api_clientkey;
    }

    public static function get_data_api_token_option_name(){
        return self::$data_api_token_option_name;
    }

    public static function get_data_api_sslverify(){
        return self::$data_api_sslverify;
    }
}