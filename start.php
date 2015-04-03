<?php
/**
 * Plugin Name: StreamSend for WordPress - Official Plugin
 * Plugin URI: http://streamsend.com
 * Description: The Streamsend plugin allows you to easily add a signup form for your Streamsend list. 
 * Version: 1.0.1
 * Author: Rachel O'Connor
 * Author URI: http://vireo.org
 * Contributors: vireollc
 * License: GPLv2
 *
 */

define( 'STREAMSEND_PATH',     dirname( __FILE__ ) );
define( 'STREAMSEND_URL',      plugins_url( '', __FILE__ ) );

define( 'STREAMSEND_FILE',     plugin_basename( __FILE__ ) );
define( 'STREAMSEND_ASSETS',   STREAMSEND_URL . '/assets' );

// just sos ya know, this can't be called from the main class.
// i don't want to talk about it.
register_activation_hook( __FILE__, array( 'Start', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Start','plugin_deactivation' ) );

include_once( STREAMSEND_PATH . '/class-ss.php' );

include_once('admin/class-account-information.php');
include_once('admin/class-form-setup.php');
include_once('admin/class-form-custom.php');

// instantiate main class
$streamsend = new Streamsend();
class Start {

    private $error_txt;

    function __construct() {

    }

    function plugin_activation() {

        if( version_compare( PHP_VERSION, '5.2.6', '<' ) ) {
            $this->error_txt = 'The Streamsend plugin requires at least PHP 5.2.6.';
        }
        if( version_compare( get_bloginfo( 'version' ), '3.1', '<' ) ) {
            $this->error_txt = 'The Streamsend plugin requires at least WordPress version 3.1.';

        }

        // probably should do some checking before sending this off...
        add_action( 'admin_notices', array( &$this, 'version_require' ) );

        // load default options into database on activation
        // add_option( $option, $value, $depreciated, $autoload );
        add_option( Account_Information::$key, Account_Information::get_settings_defaults(), '', 'yes' );
        add_option( Form_Setup::$key, Form_Setup::get_settings_defaults(), '', 'yes' );
//        add_option( Form_Custom::$key, Form_Custom::get_settings_defaults(), '', 'yes' );

    }

    function plugin_deactivation() {

        // buh-bye!
    }

    function version_require() {
        if( current_user_can( 'manage_options' ) )
            echo '<div class="error"><p>' . $this->error_txt . '</p></div>';
    }

} // end class Start

