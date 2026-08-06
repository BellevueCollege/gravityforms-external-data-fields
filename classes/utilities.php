<?php

defined( 'ABSPATH' ) || exit;

// Disable to turn off debug logging
define ( "ENABLE_DEBUG_LOG", false );

/**
* @param $message
*/
function debug_log( $message ) {
    if ( ENABLE_DEBUG_LOG ) {
        $date = new DateTime();
        error_log( site_name () . ": [" . $date->format ( 'Y-m-d H:i:s' ) . "] " . $message );
    }
}

/**
* @return string
*/
function site_name() {

    if ( function_exists ( "is_multisite" ) && is_multisite () ) {
        $current_site = get_current_site ();
        return trim ( $current_site->path, "/" );
    }
    else {
        // TODO: Identify the current blog by some string
        return "";
    }
}

/**
* @param $class
*/
/*function class_autoloader( $class ) {
    $classFile = dirname ( __FILE__ ) . "/" . $class . ".php";

    if ( file_exists ( $classFile ) ) {
        include_once ( $classFile );
    }
    else {
        $error = "Failed to load '" . $classFile . "'";
        debug_log ( $error );
        error_log ( $error );
    }
}*/

/**
* @param $class
* @param $constant
* @return bool
*/
/*function class_has_constant( $class, $constant ) {
    $class = new ReflectionClass( $class );
    return $class->hasConstant ( $constant );
}*/