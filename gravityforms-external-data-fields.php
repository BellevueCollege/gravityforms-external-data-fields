<?php
/*
Plugin Name: Gravity Forms External Data Fields
Plugin URI: https://github.com/BellevueCollege/gravityforms-external-data-fields
Description: Extend Gravity Forms with Bellevue College data api data
Author: Bellevue College Integration Team
Version: 3.0.0
Author URI: http://www.bellevuecollege.edu
GitHub Plugin URI: bellevuecollege/gravityforms-external-data-fields
*/

defined ( 'ABSPATH' ) OR exit;

require_once( plugin_dir_path( __FILE__ ) . 'classes/config.php' );
require_once( "classes/shortcode.php" );
require_once( "classes/studentData.php" );
require_once( "classes/employeeData.php" );
//error_reporting ( E_ALL ^ E_NOTICE ); // Report all errors except E_NOTICE

class GFEDF {

    const STATUS_OK = 'ok';

    protected $studentdata;
    protected $empdata;
    protected $shortcode;

    /**
     * Aggregated external data lookup status for the current request.
     * null | ok | not_found | unavailable
     *
     * @var string|null
     */
    protected $externalDataStatus = null;

    /**
     * Whether the current page shortcode restricts access to employees.
     *
     * @var bool
     */
    protected $requiresEmployeeAccess = false;

    /**
     * Dynamic population parameter names provided by this plugin.
     *
     * @var array
     */
    protected $externalDataParameters = array(
        'bc_sid',
        'bc_first_name',
        'bc_last_name',
        'bc_email',
        'bc_dayphone',
        'bc_evephone',
    );

    function __construct() {
        $this->studentdata = new StudentData();
        $this->empdata = null;
        $this->shortcode = new GFEDF_Shortcode();

        // This action needs to run AFTER the user has been authenticated
        //don't run this if user is on admin/backend page
        if ( !is_admin() ) {
            add_action ( 'wp', array( $this, 'after_auth_action' ), 10 );
        }

        //autofill function filters
        add_filter( 'gform_field_value_bc_sid', array( $this, 'populate_bc_sid' ) );
        add_filter( 'gform_field_value_bc_first_name', array( $this, 'populate_bc_first_name' ) );
        add_filter( 'gform_field_value_bc_last_name', array( $this, 'populate_bc_last_name' ) );
        add_filter( 'gform_field_value_bc_email', array( $this, 'populate_bc_email' ) );
        add_filter( 'gform_field_value_bc_dayphone', array( $this, 'populate_bc_dayphone' ) );
        add_filter( 'gform_field_value_bc_evephone', array( $this, 'populate_bc_evephone' ) );

        add_filter( 'gform_get_form_filter', array( $this, 'maybe_block_form_display' ), 10, 2 );
        add_filter( 'gform_validation', array( $this, 'maybe_block_form_submission' ) );
        add_filter( 'allowed_redirect_hosts', array( $this, 'allow_bellevue_redirect_host' ), 10, 2 );

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
        return "<a href='" . esc_url( $redirect_url ) . "' title='Log in' $class_attr>" . $content_val . "</a>";
    }

    ###########################################
    // Filter functions to pre-populate fields
    ###########################################
    // To use: add the string after gform_field_value_
    // Ex. bc_sid or bc_first_name
    // Add desired string to "Allow field to be populated automatically" field in advanced tab of the Gravity Forms Form Editor
    function populate_bc_sid( $value ) {
        debug_log ( "(= gform_field_value_bc_sid =) Setting SID..." );

        $bc_sid = '';
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

        $bc_first_name = '';
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

        $bc_last_name = '';
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

        $bc_email = '';
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

        $bc_dayphone = '';
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

    /**
     * Whether the form uses any plugin external-data population parameters.
     *
     * @param array $form Gravity Forms form object.
     * @return bool
     */
    protected function form_uses_external_data_fields( $form ) {
        $fields = rgar( $form, 'fields' );
        if ( empty( $fields ) || ! is_array( $fields ) ) {
            return false;
        }

        foreach ( $fields as $field ) {
            if ( ! empty( $field->inputName ) && in_array( $field->inputName, $this->externalDataParameters, true ) ) {
                return true;
            }

            if ( empty( $field->inputs ) || ! is_array( $field->inputs ) ) {
                continue;
            }

            foreach ( $field->inputs as $input ) {
                $input_name = rgar( $input, 'name' );
                if ( ! empty( $input_name ) && in_array( $input_name, $this->externalDataParameters, true ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Whether post content embeds a Gravity Forms shortcode or block.
     *
     * @param string $content Post content.
     * @return bool
     */
    protected function content_has_gravity_form( $content ) {
        return has_shortcode( $content, 'gravityform' )
            || has_shortcode( $content, 'gravityforms' )
            || has_block( 'gravityforms/form', $content );
    }

    /**
     * Form ID from a gravityforms/form block tree.
     *
     * @param array $blocks Parsed blocks from parse_blocks().
     * @return int
     */
    protected function find_gravity_forms_block_form_id( $blocks ) {
        foreach ( $blocks as $block ) {
            $block_name = isset( $block['blockName'] ) ? $block['blockName'] : '';
            if ( $block_name === 'gravityforms/form' && ! empty( $block['attrs']['formId'] ) ) {
                return absint( $block['attrs']['formId'] );
            }

            if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
                $inner_form_id = $this->find_gravity_forms_block_form_id( $block['innerBlocks'] );
                if ( $inner_form_id ) {
                    return $inner_form_id;
                }
            }
        }

        return 0;
    }

    /**
     * Resolve the embedded Gravity Forms form ID from shortcode or block.
     *
     * @param string $content Post content.
     * @return int
     */
    protected function get_embedded_form_id( $content ) {
        $form_id = $this->shortcode->get_attribute_value( 'id' );
        if ( ! empty( $form_id ) ) {
            return absint( $form_id );
        }

        if ( ! has_block( 'gravityforms/form', $content ) ) {
            return 0;
        }

        return $this->find_gravity_forms_block_form_id( parse_blocks( $content ) );
    }

    /**
     * Whether this page needs a Data API lookup for the current request.
     *
     * @param string $content Post content.
     * @return bool
     */
    protected function page_needs_external_data_lookup( $content ) {
        if ( $this->requiresEmployeeAccess ) {
            return true;
        }

        $form_id = $this->get_embedded_form_id( $content );
        if ( empty( $form_id ) || ! class_exists( 'GFAPI' ) ) {
            return false;
        }

        $form = GFAPI::get_form( $form_id );
        if ( empty( $form ) || is_wp_error( $form ) ) {
            return false;
        }

        return $this->form_uses_external_data_fields( $form );
    }

    /**
     * Aggregate student/employee lookup results into a single status.
     *
     * @return string ok|not_found|unavailable
     */
    protected function aggregate_external_data_status() {
        $has_employee = $this->empdata && $this->empdata->is_employee();

        if ( $this->studentdata->has_student_record() || $has_employee ) {
            return self::STATUS_OK;
        }

        $employee_unavailable = $this->empdata && ! $this->empdata->is_lookup_available();
        if ( ! $this->studentdata->is_lookup_available() || $employee_unavailable ) {
            return DataApi::STATUS_UNAVAILABLE;
        }

        return DataApi::STATUS_NOT_FOUND;
    }

    /**
     * Human-readable message for the current blocking status.
     *
     * @return string
     */
    protected function get_external_data_error_message() {
        $service_desk_link = '<a href="https://www.bellevuecollege.edu/servicedesk">Bellevue College ITS Service Desk</a>';

        if ( $this->externalDataStatus === DataApi::STATUS_UNAVAILABLE ) {
            return "We are temporarily unable to load your information from college systems, so this form cannot be loaded. Please try again later. If the problem continues, contact the {$service_desk_link}.";
        }

        return "We could not find a matching student or employee record for your account, so this form cannot be loaded. If you believe this is an error, please contact the {$service_desk_link}.";
    }

    /**
     * Whether dependent forms should be blocked for the current request.
     *
     * @return bool
     */
    protected function should_block_external_data_form() {
        // Fail closed: employee-only pages require a confirmed employee match.
        if ( $this->requiresEmployeeAccess ) {
            $is_employee = $this->empdata && $this->empdata->is_employee();
            if ( ! $is_employee ) {
                return true;
            }
        }

        return $this->externalDataStatus === DataApi::STATUS_NOT_FOUND
            || $this->externalDataStatus === DataApi::STATUS_UNAVAILABLE;
    }

    /**
     * Replace form markup when external data is missing or unavailable.
     *
     * @param string $form_string Generated form HTML.
     * @param array  $form        Form object.
     * @return string
     */
    function maybe_block_form_display( $form_string, $form ) {
        if ( ! $this->should_block_external_data_form() ) {
            return $form_string;
        }

        if ( ! $this->requiresEmployeeAccess && ! $this->form_uses_external_data_fields( $form ) ) {
            return $form_string;
        }

        // Reuse Gravity Forms validation styling for a familiar error presentation.
        $message = wp_kses(
            $this->get_external_data_error_message(),
            array( 'a' => array( 'href' => true ) )
        );

        return "<div class='validation_error alert alert-danger'>" . $message . '</div>';
    }

    /**
     * Reject submissions when external data is missing or unavailable.
     *
     * @param array $validation_result Gravity Forms validation result.
     * @return array
     */
    function maybe_block_form_submission( $validation_result ) {
        $form = $validation_result['form'];

        if ( ! $this->should_block_external_data_form() ) {
            return $validation_result;
        }

        if ( ! $this->requiresEmployeeAccess && ! $this->form_uses_external_data_fields( $form ) ) {
            return $validation_result;
        }

        $validation_result['is_valid'] = false;

        // Standard Gravity Forms pattern: mark a field failed so the form-level error is shown.
        if ( ! empty( $form['fields'][0] ) ) {
            $form['fields'][0]->failed_validation  = true;
            $form['fields'][0]->validation_message = wp_kses(
                $this->get_external_data_error_message(),
                array( 'a' => array( 'href' => true ) )
            );
        }

        $validation_result['form'] = $form;
        return $validation_result;
    }

    /**
     * Allow bellevuecollege.edu and its subdomains for wp_safe_redirect.
     *
     * @param string[] $hosts Allowed hosts.
     * @param string   $host  Host being redirected to.
     * @return string[]
     */
    public function allow_bellevue_redirect_host( $hosts, $host ) {
        if ( preg_match( '/(^|\.)bellevuecollege\.edu$/i', $host ) ) {
            $hosts[] = $host;
        }
        return $hosts;
    }

    /*******
    * Check if user authenticated, then gather data for user.
    * Also checks if 'allow' is set to restrict type of user and redirects as necessary
    * This function must be in 'wp' action so that global WP variables (i.e. $post) are available
    *******/
    function after_auth_action () {
        
        debug_log ( "(wp) GF External Data Fields plugin :: after_auth_action" );

        global $post;
        if ( empty( $post ) || empty( $post->post_content ) ) {
            return;
        }

        $content = $post->post_content;

        if ( ! $this->content_has_gravity_form( $content ) ) {
            return;
        }

        $this->shortcode->get_shortcode_from_content( $content );

        $restrict_attr = GFEDF_Config::get_restrict_attr();
        $restrict_value = $this->shortcode->get_attribute_value( $restrict_attr );
        $this->requiresEmployeeAccess = ( ! empty( $restrict_value ) && strtolower( $restrict_value ) === 'employees' );

        if ( ! is_user_logged_in() ) {
            return;
        }

        // Skip remote Data API calls when the page does not use this plugin's features.
        if ( ! $this->page_needs_external_data_lookup( $content ) ) {
            return;
        }

        $current_user = wp_get_current_user();
        $username = $current_user->user_login;
        //$username = 'student.test@bellevuecollege.edu'; // DEBUGGING ONLY -- TEST ACCOUNT USERNAME
        $student_only_domain = '@my.bellevuecollege.edu';
        $is_student_only_account = substr_compare(
            $username,
            $student_only_domain,
            -strlen( $student_only_domain ),
            strlen( $student_only_domain ),
            true
        ) === 0;

        // Always query students first; my.bellevuecollege.edu accounts are student-only.
        $this->studentdata = new StudentData( $username );

        $isEmp = false;
        if ( ! $is_student_only_account ) {
            $this->empdata = new EmployeeData( $username );
            $isEmp = $this->empdata->is_employee();
        }

        $this->externalDataStatus = $this->aggregate_external_data_status();
        debug_log( "GFEDF :: aggregated external data status: " . $this->externalDataStatus );

        /*
         * Limited access for employees based on shortcode attribute.
         * Redirect only when the lookup completed and the user is not an employee.
         * During an API outage, fail closed via maybe_block_* instead of redirecting.
         */
        if ( $this->requiresEmployeeAccess && ! $isEmp
            && $this->externalDataStatus !== DataApi::STATUS_UNAVAILABLE ) {
            $redirect_url = $this->shortcode->get_attribute_value( GFEDF_Config::get_restrict_fail_redirect_attr() );
            if ( empty( $redirect_url ) ) {
                $redirect_url = GFEDF_Config::get_default_redirect_url();
            }
            if ( ! empty( $redirect_url ) ) {
                wp_safe_redirect( $redirect_url );
                exit;
            }
        }
    }
}

if ( class_exists( 'GFEDF' ) ) {
    //instantiate class
    $gfedf_obj = new GFEDF();
}
