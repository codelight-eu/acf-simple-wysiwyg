<?php

/*
Plugin Name: Advanced Custom Fields: CL Simple Wysiwyg
Description: Modified ACF Wysiwyg field to be used with ACF.
Version: 1.0.0
Author:
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// check if class already exists
if( !class_exists('acf_plugin_cl_simple_wysiwyg') ) :

class acf_plugin_cl_simple_wysiwyg {


	function __construct() {

		// vars
		$this->settings = array(
			'version'	=> '1.0.0',
			'url'		=> plugin_dir_url( __FILE__ ),
			'path'		=> plugin_dir_path( __FILE__ )
		);

		load_plugin_textdomain( 'acf-cl_simple_wysiwyg', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );

		// include field
		add_action('acf/include_field_types', 	array($this, 'include_field_types')); // v5
		add_action('acf/register_fields', 		array($this, 'include_field_types')); // v4
	}

	function include_field_types( $version = false ) {

		include_once('source/acf-cl_simple_wysiwyg-v5.php');

	}

}

// initialize
new acf_plugin_cl_simple_wysiwyg();

// class_exists check
endif;



?>