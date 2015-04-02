<?php

include_once('admin/class-ss-settings.php');
include_once('widget/class-ss-widget.php');
include_once('shortcode/class-ss-shortcode.php');
include_once('class-ss-api.php');
include_once('class-ss-form.php');

class Streamsend {
    function __construct() {

        $streamsend_settings = new Streamsend_Settings();

        // Add shortcode support for widgets
        add_filter('widget_text', 'do_shortcode');


    }



} // end Class Streamsend


