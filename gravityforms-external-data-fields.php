<?php
/*
Plugin Name: Gravity Forms External Data Fields
Plugin URI: https://github.com/BellevueCollege/gravityforms-external-data-fields
Description: Extend Gravity Forms with Bellevue College data api data
Author: Bellevue College Integration Team
Version: 1.3.0.2
Author URI: http://www.bellevuecollege.edu
GitHub Plugin URI: bellevuecollege/gravityforms-external-data-fields
*/

defined ( 'ABSPATH' ) OR exit;

require_once( "config.php" );
require_once( "classes/shortcode.php" );
require_once( "classes/studentData.php" );
require_once( "classes/employeeData.php" );
//error_reporting ( E_ALL ^ E_NOTICE ); // Report all errors except E_NOTICE

class GFEDF {
    
    protected $studentdata; 
    protected $empdata;
    protected $shortcode;

    function __construct() {
        $this->studentdata = new StudentData();
        $this->empdata = null;
        $this->shortcode = new GFEDF_Shortcode();

        // This action needs to run AFTER the user has been authenticated
        add_action ( 'wp', array( $this, 'after_auth_action' ), 10 );

        //autofill function filters
        add_filter( 'gform_field_value_bc_sid', array( $this, 'populate_bc_sid' ) );
        add_filter( 'gform_field_value_bc_first_name', array( $this, 'populate_bc_first_name' ) );
        add_filter( 'gform_field_value_bc_last_name', array( $this, 'populate_bc_last_name' ) );
        add_filter( 'gform_field_value_bc_email', array( $this, 'populate_bc_email' ) );
        add_filter( 'gform_field_value_bc_dayphone', array( $this, 'populate_bc_dayphone' ) );
        add_filter( 'gform_field_value_bc_evephone', array( $this, 'populate_bc_evephone' ) );

        //shortcode filters
        add_shortcode( 'gfedf_redirect_to_login', array( $this, 'gfedf_login_redirect' ) );
    }

    // Add shortcode for redirect to login
    // allows attribute 'class' that will be used to style returned element
    function gfedf_login_redirect( $atts , $content = null ) {
        $redirect_url = wp_login_url( get_permalink() );

        $attrs = shortcode_atts( array( 'class' => '' ), $atts );

        $class_attr = "";
        $content_val = "Log in";
        if ( !empty( $attrs["class"] ) ){
            $class_attr = "class='" . esc_attr( $attrs["class"] ) . "'";
        }
        if ( !empty( $content ) ) {
            $content_val = esc_html( $content ); 
        }
        return "<a href='$redirect_url' title='Log in' $class_attr>" . $content_val . "</a>";
    }

    ###########################################
    // Filter functions to pre-populate fields
    ###########################################
    // To use: add the string after gform_field_value_
    // Ex. bc_sid or bc_first_name
    // Add desired string to "Allow field to be populated automatically" field in advanced tab of the Gravity Forms Form Editor
    function populate_bc_sid( $value ) {
        debug_log ( "(= gform_field_value_bc_sid =) Setting SID..." );

        if ( $this->studentdata->has_student_record() )
            $bc_sid = $this->studentdata->get_student_id();
        else {
            if ( $this->empdata != null )
                $bc_sid = $this->empdata->get_sid();
        }

        debug_log( "...'$bc_sid'" );
        return $bc_sid;
    }

    // First name
    function populate_bc_first_name( $value ) {
        debug_log ( "(= gform_field_value_bc_first_name =) Setting first name..." );

        if ( $this->studentdata->has_student_record() )
            $bc_first_name = $this->studentdata->get_first_name();
        else {
            if ( $this->empdata != null )
                $bc_first_name = $this->empdata->get_first_name();
        }

        debug_log( "...'$bc_first_name'" );
        return $bc_first_name;
    }

    // Last name
    function populate_bc_last_name( $value ) {
        debug_log ( "(= gform_field_value_bc_last_name =) Setting last name..." );

        if ( $this->studentdata->has_student_record() )
            $bc_last_name = $this->studentdata->get_last_name();
        else {
            if ( $this->empdata != null )
                $bc_last_name = $this->empdata->get_last_name();
        }

        debug_log( "...'$bc_last_name'" );
        return $bc_last_name;
    }

    // BC email
    function populate_bc_email( $value ) {
        debug_log ( "(= gform_field_value_bc_email =) Setting email..." );

        if ( $this->studentdata->has_student_record() )
            $bc_email = $this->studentdata->get_email();
        else {
            if ( $this->empdata != null )
                $bc_email = $this->empdata->get_email();
        }

        debug_log( "...'$bc_email'" );
        return $bc_email;
    }

    // Day phone
    function populate_bc_dayphone( $value ) {
        debug_log ( "(= gform_field_value_bc_dayphone =) Setting daytime phone..." );

        if ( $this->studentdata->has_student_record() )
            $bc_dayphone = $this->studentdata->get_daytime_phone();
        else {
            if ( $this->empdata != null )
                $bc_dayphone = $this->empdata->get_phone();
        }

        debug_log( "...'$bc_dayphone'" );
        return $bc_dayphone;
    }

    // Evening phone
    function populate_bc_evephone( $value ) {
        debug_log ( "(= gform_field_value_bc_evephone =) Setting evening phone..." );

        $bc_evephone = $this->studentdata->get_evening_phone();

        debug_log( "...'$bc_evephone'" );
        return $bc_evephone;
    }

    /*******
    * Check if user authenticated, then gather data for user.
    * Also checks if 'allow' is set to restrict type of user and redirects as necessary
    * This function must be in 'wp' action so that global WP variables (i.e. $post) are available
    *******/
    function after_auth_action () {
        
        debug_log ( "(wp) GF External Data Fields plugin :: after_auth_action" );

        global $post;
        $content = $post->post_content;
        $this->shortcode->get_shortcode_from_content($content);

        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $username = $current_user->user_login;
            //$username = "nicole.swan";
            $this->empdata = new EmployeeData( $username );
            $isEmp = $this->empdata->is_employee();
            //echo "Is employee?: "; var_dump($isEmp);
            //var_dump($this->empdata);
            /*
            *  Limited access for employees or student based on shortcode attribute value.
            */
            $restrict_attr = GFEDF_Config::get_restrict_attr();
            if ( isset( $restrict_attr )  && !empty($this->shortcode) ) {
                //var_dump($this->shortcode);
                $matchedEl = $this->shortcode->get_attribute_value( $restrict_attr );
                //echo "matchedEL: " . $matchedEl;
                if ( isset( $matchedEl ) ) {
                    $matchedVal = strtolower( $matchedEl );
                    if ( isset( $matchedVal ) && !empty( $matchedVal ) ) {
                        switch ( $matchedVal ) {
                            case "employees":
                                //Check if the user is an employee or not
                                //echo "in employees";
                                if ( !$isEmp ) {
                                    //echo "You don't have permission to access this form.";
                                    $redirect_url = $this->shortcode->get_attribute_value( GFEDF_Config::get_restrict_fail_redirect_attr() );
                                    if ( isset( $redirect_url ) && !empty( $redirect_url ) ) {
                                        $sanitize_url = esc_url( $redirect_url );
                                        Header( 'Location:' . $sanitize_url );
                                        exit();
                                    }
                                    else {
                                        //should go to a default url
                                        $default_url = GFEDF_Config::get_default_redirect_url();
                                        if ( isset( $default_url ) && !empty( $default_url ) ) {
                                            $sanitize_url = esc_url( $default_url );
                                            Header ( 'Location:' . $sanitize_url );
                                        }
                                    }
                                    //echo "You don't have permission to view the form.";
                                    exit();

                                }
                                break;

                        }
                    }

                }
            }

            $this->studentdata = new StudentData( $username );
        }
    }
}

if ( class_exists( 'GFEDF' ) ) {
    //instantiate class
    $gfedf_obj = new GFEDF();
}