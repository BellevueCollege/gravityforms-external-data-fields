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
error_reporting(E_ALL ^ E_NOTICE); // Report all errors except E_NOTICE


// This function will update the default path and url of the file storage location

add_filter("gform_upload_path", "change_upload_path", 10, 2);
function change_upload_path($path_info, $form_id){

    $path_info["path"] = defined(gf_external_data_fields_config::FILE_UPLOAD_PATH)? gf_external_data_fields_config::FILE_UPLOAD_PATH : "";
    $path_info["url"] = defined(gf_external_data_fields_config::FILE_UPLOAD_URL) ? gf_external_data_fields_config::FILE_UPLOAD_URL : "";
    return $path_info;
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
    if(defined('gf_external_data_fields_config::SESSION_USERNAME') && !empty($_SESSION[gf_external_data_fields_config::SESSION_USERNAME]))
        $text = defined('gf_external_data_fields_config::IS_VERIFIED_MESSAGE') ? gf_external_data_fields_config::IS_VERIFIED_MESSAGE : "Authenticated";

    return $text;
}













