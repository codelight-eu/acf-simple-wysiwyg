<?php
/**
 * Plugin Name:       Advanced Custom Fields: Simple Wysiwyg field
 * Plugin URI:        https://codelight.eu
 * Description:       Creates a new ACF Wysiwyg field type with very limited controls - bold, italic, link and colors. Perfect for headings.
 * Version:           2.0
 * Author:            Codelight.eu
 * Author URI:        https://codelight.eu
 * Text Domain:       acf-simple-wysiwyg
 * Domain Path:       /languages
 */

if (!defined('WPINC')) {
    die;
}

add_action('acf/include_field_types', function() {
    define('ACF_SIMPLE_WYSIWYG_URL', plugins_url('', __FILE__));
    require_once('src/ACFSimpleWysiwyg.php');
    $GLOBALS['codelight_acf_simple_wysiwyg'] = new Codelight\ACFSimpleWysiwyg\ACFSimpleWysiwyg();
});

add_action('plugins_loaded', function() {
    load_plugin_textdomain('acf-simple-wysiwyg', false, plugin_basename(dirname(__FILE__)) . '/lang');
});