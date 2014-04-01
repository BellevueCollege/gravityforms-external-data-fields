<?php
/*
Plugin Name: Gravity Forms External Data Fields
Plugin URI: http://www.bellevuecollege.edu
Description: Extend Gravity Forms with Bellevue College form field data
Author: Bellevue College Technology Development and Communications
Version: 0.0.0.2
Author URI: http://www.bellevuecollege.edu
*/

require_once("gravityforms-external-data-fields-config.php");
require_once("studentData.php");
require_once("requireAuthentication.php");
requireAuthentication::setup(array("gravityform","gravityforms"));

error_reporting(E_ALL ^ E_NOTICE); // Report all errors except E_NOTICE


// This function will update the default path and url of the file storage location

add_filter("gform_upload_path", "change_upload_path", 10, 2);
function change_upload_path($path_info, $form_id){
    if(defined('gf_external_data_fields_config::FILE_UPLOAD_PATH') && defined('gf_external_data_fields_config::FILE_UPLOAD_PATH'))
    {
        //Check for trailing slash
        $path = gf_external_data_fields_config::FILE_UPLOAD_PATH;
        $url = gf_external_data_fields_config::FILE_UPLOAD_URL;
        $path .= (substr($path, -1) == '/' ? '' : '/');
        $url .= (substr($url, -1) == '/' ? '' : '/');
        $path_info["path"] =  $path ;
        $path_info["url"] =  $url ;
    }

    return $path_info;
}

// Stylesheet - loaded the WordPress way
function gfedf_load_stylesheets() {
  wp_enqueue_script( "gravityforms-external-data-fields-style", plugins_url( "/css/style.css", __FILE__));
}
add_action( 'wp_enqueue_scripts', 'gfedf_load_stylesheets' );

// Make Gravity Forms fields Read-only
function gfedf_disable_input_fields() {
  wp_register_script('gravityforms-disable-fields', plugins_url('/js/gravityforms-disable-fields.js', __FILE__), array('jquery'),'', true);
  wp_enqueue_script('gravityforms-disable-fields');
}
add_action( 'wp_enqueue_scripts', 'gfedf_disable_input_fields' );

$gfedf_studentdata = new studentData();
function gfedf_get_student_data()
{
  debug_log("(wp) Getting student data...");

  global $gfedf_studentdata;
  $username = requireAuthentication::getCurrentUser();
  debug_log("Current user is '$username'");
  $gfedf_studentdata = new studentData($username);
  debug_log("StudentData:\n".print_r($gfedf_studentdata, true));
}
// This action needs to run AFTER the user has been identified by the requireAuthentication class
add_action("wp", "gfedf_get_student_data", 20);

#########################
// Pre-populate Fields
#########################
	// To use: add the string after gform_field_value_
	// Ex. bc_sid or bc_first_name
	// Add desired string to "Allow field to be populated automattically" field in advanced tab of the Gravity Forms Form Editor

	// SID
	add_filter('gform_field_value_bc_sid', 'populate_bc_sid');
	function populate_bc_sid($value){
    debug_log("(= gform_field_value_bc_sid =) Setting SID...");

    global $gfedf_studentdata;
    $bc_sid = $gfedf_studentdata->getStudentID();
    debug_log("...'$bc_sid'");
		return $bc_sid;
	}

	// First Name
	add_filter('gform_field_value_bc_first_name', 'populate_bc_first_name');
	function populate_bc_first_name($value){
    debug_log("(= gform_field_value_bc_first_name =) Setting first name...");

    global $gfedf_studentdata;
		$bc_first_name = $gfedf_studentdata->getFirstName();
    debug_log("...'$bc_first_name'");
		return $bc_first_name;
	}

	// First Name
	add_filter('gform_field_value_bc_last_name', 'populate_bc_last_name');
	function populate_bc_last_name($value){
    debug_log("(= gform_field_value_bc_last_name =) Setting last name...");

    global $gfedf_studentdata;
		$bc_last_name = $gfedf_studentdata->getLastName();
    debug_log("...'$bc_last_name'");
		return $bc_last_name;
	}

	// BC Email
	add_filter('gform_field_value_bc_email', 'populate_bc_email');
	function populate_bc_email($value){
    debug_log("(= gform_field_value_bc_email =) Setting e-mail...");

    global $gfedf_studentdata;
		$bc_email = $gfedf_studentdata->getEmailAddress();
    debug_log("...'$bc_email'");
		return $bc_email;
	}

	// Day Phone
	add_filter('gform_field_value_bc_dayphone', 'populate_bc_dayphone');
	function populate_bc_dayphone($value){
    debug_log("(= gform_field_value_bc_dayphone =) Setting daytime phone...");

    global $gfedf_studentdata;
		$bc_dayphone = $gfedf_studentdata->getDaytimePhone();
    debug_log("...'$bc_dayphone'");
		return $bc_dayphone;
	}

	// Evening Phone
	add_filter('gform_field_value_bc_evephone', 'populate_bc_evephone');
	function populate_bc_evephone($value){
    debug_log("(= gform_field_value_bc_evephone =) Setting evening phone...");

    global $gfedf_studentdata;
		$bc_evephone = $gfedf_studentdata->getEveningPhone();
    debug_log("...'$bc_evephone'");
		return $bc_evephone;
	}


// This function edits the notification message if the authentication field is not present in the form.

add_filter('gform_notification', 'edit_notification_message', 10, 3);

function edit_notification_message($notification, $form, $entry)
{
    $is_auth = auth_field($form,true);
    if(empty($is_auth))// means auth field is not present in the form, so lets add authentication information in the email
    {
        $is_verified_text = populate_auth_field();
        $notification['message'] = $is_verified_text .  $notification['message'];
    }
    return $notification;

}

// This function adds text to the auth field based on whether the user is logged in or not.

add_filter("gform_pre_render", "pre_populate_fields");
function pre_populate_fields($form)
{
   $updated_form = auth_field($form);
    if($updated_form)
        return $updated_form;
    return $form;
}
// This function checks if the auth field exists. It will update the default value for the auth field if the parameter $if_exists is not true
// This function serves the output for pre-rendering the form and also just checking if the auth field exists in the form
function auth_field(&$form,$if_exists = null)
{
    if(defined('gf_external_data_fields_config::IS_AUTH'))
    {
        foreach($form['fields'] as &$field)
        {
            if($field['inputName'] == gf_external_data_fields_config::IS_AUTH)
            {
               if(!$if_exists)
                   $field["defaultValue"] = populate_auth_field();
                return $form;
            }
        }
    }
    return false;
}

function populate_auth_field()
{

    $text =  defined('gf_external_data_fields_config::IS_NOT_VERIFIED_MESSAGE')? gf_external_data_fields_config::IS_NOT_VERIFIED_MESSAGE : "Not Authenticated";
    if(defined('requireAuthentication::SESSION_USERNAME') && !empty($_SESSION[requireAuthentication::SESSION_USERNAME]))
        $text = defined('gf_external_data_fields_config::IS_VERIFIED_MESSAGE') ? gf_external_data_fields_config::IS_VERIFIED_MESSAGE : "Authenticated";

    return $text;
}