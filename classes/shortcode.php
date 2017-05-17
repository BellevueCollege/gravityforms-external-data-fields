<?php
require_once( dirname( plugin_dir_path( __FILE__ ) ) . "/config.php" );
require_once( "utilities.php" );

/**
    * This class provides a simple wrapper for the standard gravity forms 
    * shortcode string
*/
class GFEDF_Shortcode {
    protected $shortcode_str;   // the shortcode attribute string retrieved from content
    protected $shortcode_names = array('gravityform', 'gravityforms');  //valid gravity forms shortcode names

    /**
    * @param      $_shortcode_str
    */
    function __construct( $_shortcode_str = null ) {
        $this->shortcode_str = $_shortcode_str;
    }

    /**
    * Looks for the given attribute in the shortcode string and returns the value
    * @param $_attribute
    * @return string
    */
    function get_attribute_value( $_attribute ) {
        
        if ( !isset( $_attribute ) || empty( $_attribute ) )
            return null;
        else {
            $regex = "/$_attribute\s*=\s*\"\s*(\S*)\s*\"/i";

            $has_auth_param = preg_match( $regex, $this->shortcode_str, $matches );
            if ( isset( $matches[1] ) )
                return $matches[1];
            else
                return null;
        }
    }

    /**
    * Get the shortcode attribute string given post/page content
    * @param $_content
    */
    function get_shortcode_from_content( $_content ){
        // get standard regex pattern for shortcodes
        $pattern = get_shortcode_regex();
        $matches = array();

        // if we don't already have an array of shortcodes create one
        $codes = is_array( $this->shortcode_names ) ? $this->shortcode_names : array( $this->shortcode_names );

        // check content for each valid
        foreach ( $codes as $code ) {
            $has_shortcode = preg_match_all( '/' . $pattern . '/s', $_content, $matches ) &&     array_key_exists( 2, $matches ) && in_array( $code, $matches[2] );
            if ( $has_shortcode ) {

                if ( !empty( $matches ) ) {
                    $attributes = $matches[3];
                    $this->shortcode_str = $attributes[0];

                    //var_dump($attributes[0]);
                }
            }
        }
    }
}