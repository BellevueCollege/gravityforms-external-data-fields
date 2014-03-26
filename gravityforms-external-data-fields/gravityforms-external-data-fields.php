<?php
/*
Plugin Name: Gravity Forms External Data Fields
Plugin URI: http://www.bellevuecollege.edu
Description: Extend Gravity Forms with Bellevue College form field data
Author: Bellevue College Technology Development and Communications
Version: 0.1
Author URI: http://www.bellevuecollege.edu
*/

require_once("gravityforms-external-data-fields-config.php");
require_once("studentData.php");

$gfedf_studentdata = new studentData('');

// Change the location where uploaded files are stored.
add_filter("gform_upload_path", "change_upload_path", 10, 2);
function change_upload_path($path_info, $form_id){

    $path_info["path"] = $GLOBALS["file_upload_path"];
    $path_info["url"] = $GLOBALS["file_upload_url"];
    return $path_info;
}

// Stylesheet - loaded the WordPress way
	wp_enqueue_script( "gravityforms-external-data-fields-style", plugins_url( "/css/style.css", __FILE__));


// Make Gravity Forms fields Read-only
	function gfedf_disable_input_fields() {
		wp_register_script('gravityforms-disable-fields', plugins_url('/js/gravityforms-disable-fields.js', __FILE__), array('jquery'),'', true);
		wp_enqueue_script('gravityforms-disable-fields');
	}

	add_action( 'wp_enqueue_scripts', 'gfedf_disable_input_fields' );


#########################
// Pre-populate Fields
#########################
	// To use: add the string after gform_field_value_
	// Ex. bc_sid or bc_first_name
	// Add desired string to "Allow field to be populated automattically" field in advanced tab of the Gravity Forms Form Editor

	// SID
	add_filter('gform_field_value_bc_sid', 'populate_bc_sid');
		function populate_bc_sid($value){
		global $gfedf_studentdata;
		$bc_sid = $gfedf_studentdata->getStudentID();
		return $bc_sid;
	}

	// First Name
	add_filter('gform_field_value_bc_first_name', 'populate_bc_first_name');
		function populate_bc_first_name($value){
		global $gfedf_studentdata;
		$bc_first_name = $gfedf_studentdata->getFirstName();
		return $bc_first_name;
	}

	// First Name
	add_filter('gform_field_value_bc_last_name', 'populate_bc_last_name');
		function populate_bc_last_name($value){
		global $gfedf_studentdata;
		$bc_last_name = $gfedf_studentdata->getLastName();
		return $bc_last_name;
	}

	// BC Email
	add_filter('gform_field_value_bc_email', 'populate_bc_email');
		function populate_bc_email($value){
		global $gfedf_studentdata;
		$bc_email = $gfedf_studentdata->getEmailAddress();
		return $bc_email;
	}

	// Day Phone
	add_filter('gform_field_value_bc_dayphone', 'populate_bc_dayphone');
		function populate_bc_dayphone($value){
		global $gfedf_studentdata;
		$bc_dayphone = $gfedf_studentdata->getDaytimePhone();
		return $bc_dayphone;
	}

	// Evening Phone
	add_filter('gform_field_value_bc_evephone', 'populate_bc_evephone');
		function populate_bc_evephone($value){
		global $gfedf_studentdata;
		$bc_evephone = $gfedf_studentdata->getEveningPhone();
		return $bc_evephone;
	}