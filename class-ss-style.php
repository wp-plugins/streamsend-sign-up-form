<?php
class Streamsend_Style {

    private $settings;

    function __construct() {

        $this->settings = (array) get_option( Form_Setup::$key );

    }

    public function output() { ?>

        <style id="streamsend" type="text/css" media="all">
            /**
            * Streamsend Plugin Stylesheet
            */

            /** Basics **/
            #streamsend-subscription-form { width: 100%; }
            ul#streamsend-form-elements { list-style-type: none; margin: 0 ; padding: 0 ; }
            ul#streamsend-form-elements li.streamsend-form-row { list-style-type: none ; width: 90%; height: 50px; margin:0 0 15px 0; display: block; color:#333333; }
            ul#streamsend-form-elements li.streamsend-form-row-big { list-style-type: none; width: 90% ; display: block; }
            ul#streamsend-form-elements .streamsend-form-label { float: left; height: 20px; width: 27% ; }
            ul#streamsend-form-elements .streamsend-form-input { float: right; width: 69% ;}
            ul#streamsend-form-elements .streamsend-form-input-checkbox { width: 9%;margin-top:5px;float:left;}
            ul#streamsend-form-elements .streamsend-form-row-last { clear: both ; }
            ul#streamsend-form-elements .streamsend-required { color: #C00 !important; }
            ul#streamsend-form-elements #streamsend-form-submit { float: right; background:#666666; width:200px; height:40px; }
            ul#streamsend-form-elements .streamsend-form-label-required { width: 40%; }
	    ul#streamsend-form-elements #streamsend-form-list-optin {margin:10px 0 0px 0; float:left;font-weight:bold;}	
            ul#streamsend-form-elements .streamsend-status-msg { width: 90%; margin: 0 auto !important; }
            ul#streamsend-form-elements streamsend-error { width: 90%; margin: 0 auto; color: #C00; }
			ul#streamsend-form-elements .captcha-text { color:#333333; }
			ul#streamsend-form-elements .captcha-td { padding-bottom:25px; }
			ul#streamsend-form-elements table { margin:0; padding:0; }

            #streamsend-form.x-small { width: 200px; }
            #streamsend-form.small { width: 280px; }
            #streamsend-form.medium { width: 400px; }
            #streamsend-form.large { width: 600px; }

            /** Customizable Elements **/
            ul#streamsend-form-elements .streamsend-form-input {
                border: <?php echo $this->settings['border_width'] . 'px ' . $this->settings['border_type'] . ' #' . $this->settings['border_color']; ?>;
                color: #<?php echo $this->settings['txt_color']; ?>;
                background-color: #<?php echo $this->settings['bg_color']; ?>;
            }
            #streamsend-form input[type="submit"] {
                border: <?php echo $this->settings['submit_border_width'] . 'px ' . $this->settings['submit_border_type'] . ' #' . $this->settings['submit_border_color']; ?>;
                color: #<?php echo $this->settings['submit_txt_color']; ?>;
                background-color: #<?php echo $this->settings['submit_bg_color']; ?>;
            }
            #streamsend-form input[type="submit"]:hover {
                border: <?php echo $this->settings['submit_hover_border_width'] . 'px ' . $this->settings['submit_hover_border_type'] . ' #' . $this->settings['submit_hover_border_color']; ?>;
                color: #<?php echo $this->settings['submit_hover_txt_color']; ?>;
                background-color: #<?php echo $this->settings['submit_hover_bg_color']; ?>;
            }

            #streamsend-form.x-small ul#streamsend-form-elements .streamsend-form-input,
            #streamsend-form.x-small ul#streamsend-form-elements .streamsend-form-label { float: left; width: 97%; }

            /* status text */
            #streamsend-form .streamsend-status { width: 100%; margin: 0 auto; color: #ff0000;padding-bottom:10px; }

        </style>

    <?php }
    public function output_widget() { ?>

        <style id="streamsend_widget" type="text/css" media="all">
            /**
            * Streamsend Plugin Stylesheet - Widget
            */

            /** Basics **/
.streamsend-widget 				{list-style-type:none;}			
.streamsend-widget            #streamsend-subscription-form { width: 100%; }
.streamsend-widget            ul#streamsend-form-elements { list-style-type: none; margin: 0; padding: 0; }
.streamsend-widget            ul#streamsend-form-elements li.streamsend-form-row { list-style-type: none; width: 90%; margin: 0; height: auto; display: block; }
.streamsend-widget            ul#streamsend-form-elements .streamsend-form-label { width: 80%; display:inline-block;margin:15px 0 0 0;  }
.streamsend-widget            ul#streamsend-form-elements .streamsend-form-input { float: left; width: 100%;}
.streamsend-widget            ul#streamsend-form-elements .streamsend-form-input-checkbox { float: left; margin: 3px 5px 0 0; width:10%; }
.streamsend-widget            ul#streamsend-form-elements .streamsend-form-row-last { padding-top:15px;clear: both; }
.streamsend-widget            ul#streamsend-form-elements .streamsend-required { color: #C00; }
.streamsend-widget            ul#streamsend-form-elements #streamsend-form-submit { float: right; }
.streamsend-widget            ul#streamsend-form-elements .streamsend-form-label-required { width: 40%; display:none; }

.streamsend-widget            ul#streamsend-form-elements #streamsend-form-list-optin {margin:10px 0 10px 0;}
.streamsend-widget            .streamsend-status-msg { width: 90%; margin: 0 auto; }
.streamsend-widget            .streamsend-error { width: 90%; margin: 0 auto; color: #C00; }


.streamsend-widget            #streamsend-form.x-small { width: 180px; }
.streamsend-widget            #streamsend-form.small { width: 180px; }
.streamsend-widget            #streamsend-form.medium { width: 180px; }
.streamsend-widget            #streamsend-form.large { width: 180px; }

            /** Customizable Elements **/
.streamsend-widget            ul#streamsend-form-elements .streamsend-form-input {
                border: <?php echo $this->settings['border_width'] . 'px ' . $this->settings['border_type'] . ' #' . $this->settings['border_color']; ?>;
                color: #<?php echo $this->settings['txt_color']; ?>;
                background-color: #<?php echo $this->settings['bg_color']; ?>;
            }
.streamsend-widget            #streamsend-form input[type="submit"] {
                border: <?php echo $this->settings['submit_border_width'] . 'px ' . $this->settings['submit_border_type'] . ' #' . $this->settings['submit_border_color']; ?>;
                color: #<?php echo $this->settings['submit_txt_color']; ?>;
                background-color: #<?php echo $this->settings['submit_bg_color']; ?>;
            }
.streamsend-widget            #streamsend-form input[type="submit"]:hover {
                border: <?php echo $this->settings['submit_hover_border_width'] . 'px ' . $this->settings['submit_hover_border_type'] . ' #' . $this->settings['submit_hover_border_color']; ?>;
                color: #<?php echo $this->settings['submit_hover_txt_color']; ?>;
                background-color: #<?php echo $this->settings['submit_hover_bg_color']; ?>;
            }

.streamsend-widget            #streamsend-form.x-small ul#streamsend-form-elements .streamsend-form-input,
.streamsend-widget            #streamsend-form.x-small ul#streamsend-form-elements .streamsend-form-label { float: left; width: 97%; }

            /* status text */
.streamsend-widget            #streamsend-form .streamsend-status { width: 90%; margin: 0 auto; color: #ff0000 }

        </style>

    <?php }

} // end class Style