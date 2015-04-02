<?php

class Account_Information {

    public static $key = 'streamsend_account_information';

    public static $settings = array();

    public static $run = false;
    function __construct() {

        add_action( 'admin_init', array( &$this, 'register_settings' ) );

        self::$settings = $this->get_settings_options();
    }

    public static function get_settings_options() {

        // load the settings from the database
        $settings_options = (array) get_option( self::$key );
//        print_r( $settings_options );
        // merge with defaults
        $settings_options = array_merge( self::get_settings_defaults(), $settings_options );
        return $settings_options;

    }

    public static function get_settings_defaults() {
        $defaults = array(
            'plugin_version' => '1.0.5',
            'login_id' => '',
            'APIkey' => '',
            'num_forms' => 1,
            'logged_in' => 'false',
            'groups' => array(),
            'fields' => array(),
        );

        return $defaults;
    }

    function register_settings() {


        // register_setting( $option_group, $option_name, $sanitize_callback );
        register_setting( self::$key, self::$key, array( &$this, 'sanitize_account_information_settings' ) );

        add_settings_section( 'section_login', 'StreamSend Account Login Information', array( &$this, 'section_login_desc' ), self::$key );

        add_settings_field( 'login_id', 'Login ID', array( &$this, 'field_login_id' ), self::$key, 'section_login' );
        add_settings_field( 'api_key', 'API Key', array( &$this, 'field_api_key' ), self::$key, 'section_login' );
        add_settings_field( 'num_forms', '', array( &$this, 'field_num_forms' ), self::$key, 'section_login' );

        add_settings_field( 'logged_in', '', array( &$this, 'field_logged_in' ), self::$key, 'section_login' );


                // begin settings section
            
                // check to see if logged in to streamsend
            if ( self::$settings['logged_in'] == 'true' ) {
                
                add_settings_field( 'groups', 'Select Opt-In lists that will be displayed on the form.<br><p style="font-size:10px;">Select the StreamSend opt-in lists to display below your opt-in message here.  These lists will appear below the opt-in message with a checkbox and the form will feed into these lists. If you are not including an opt-in message do not select any lists here.</p><i style="font-size:10px">Select multiple using ctrl/cmd</i>', array( &$this, 'field_groups' ), self::$key, 'section_groups' );
                add_settings_field( 'defgroups', 'Select Hidden Lists  <br><p style="font-size:10px;">Select the lists you would like your form to feed into.  If you have selected lists in the "opt-in" section above, do not repeat them here. Only include additional lists. Any lists you select here will not appear on the form.</p><i style="font-size:10px">Select multiple using ctrl/cmd</i>', array( &$this, 'field_defgroups' ), self::$key, 'section_groups' );
                
                add_settings_field( 'fields', 'Select Fields To Display in Pages/Posts<br><i style="font-size:10px">Select multiple using ctrl/cmd</i>', array( &$this, 'field_fields' ), self::$key, 'section_groups' );
                
                add_settings_field( 'captcha', 'Display Captcha In Form?', array( &$this, 'field_captcha' ), self::$key, 'section_groups' );
                add_settings_field( 'opt_in_message', 'Opt In Message', array( &$this, 'field_opt_in_message' ), self::$key, 'section_groups' );
                
            }
                // end settings section
            
    }           


    function section_login_desc() { echo 'To use this plugin you must have an active StreamSend account.  To activate your API key, log into your account, click on "Account" and then click on "Activate" below the API section on the right. '; }
//    function section_optin_desc() { echo 'If you would like to display an opt-in message at the bottom of your form, place the text in the field below. If you do not want an opt-in message to appear, leave the field blank.'; }
    function field_login_id() { ?>
        <input id="streamsend_login_id"
           type="text"
           size="15"
           name="<?php echo self::$key; ?>[login_id]"
           value="<?php echo esc_attr( self::$settings['login_id'] ); ?>"
        />
    <?php }
    function field_api_key() { ?>
        <input id="streamsend_apikey"
           type="text"
           size="20"
           name="<?php echo self::$key; ?>[APIkey]"
           value="<?php echo esc_attr( self::$settings['APIkey'] ); ?>"
        />
    <?php }
    function field_num_forms() { ?>
        <input id="num_forms"
           type="hidden"
           size="3"
           name="<?php echo self::$key; ?>[num_forms]"
           value="<?php echo esc_attr( self::$settings['num_forms'] ); ?>"
        />
    <?php }

    function section_groups_desc() {

        if ( self::$settings['logged_in'] == 'true' ) {
            echo '';
        } else {
            echo 'Once you&apos;ve entered your account information and saved the changes, then you can choose from the available lists to assign new members to.';
        }
    }

    function section_fields_desc() {

        if ( self::$settings['logged_in'] == 'true' ) {
            echo 'Display additional fields ( optional )';
        } else {
            echo 'Once you&apos;ve entered your account information and saved the changes, then you can choose from the available fields to collect.';
        }
    }

    function field_logged_in() { ?>
        <input id="streamsend_logged_in"
               type="hidden"
               name="<?php echo self::$key ?>[logged_in]"
               value="<?php echo esc_attr( self::$settings['logged_in'] ); ?>"
        />
<?php         echo '<input type="submit" name="streamsend_account_information[refresh]" id="refresh" class="button-secondary" value="Refresh Lists and Fields" />'; ?>
    <?php }

    function sanitize_account_information_settings( $input ) {

        // get the current options
        // $valid_input = self::$settings;
        $valid_input = array();

        // check which button was clicked, submit or reset,
        $submit = ( ! empty( $input['submit'] ) ? true : false );
        $reset = ( ! empty( $input['reset'])  ? true : false );
        $refresh = ( ! empty( $input['refresh']) ? true : false );
        $addnewform = ( ! empty( $input['addnew']) ? true : false );
        $manualsave = ( ! empty( $input['manualsave']) ? true : false );
        // if the submit or refresh button was clicked
        if ( $submit || $refresh || $addnewform || $manualsave ) {

            /**
             * validate the account information settings, and add error messages
             * add_settings_error( $setting, $code, $message, $type )
             * $setting here refers to the $id of add_settings_field
             * add_settings_field( $id, $title, $callback, $page, $section, $args );
             */

            // account number

            if( !isset( $input["num_forms"] ) )
                $input["num_forms"] = "1";
            $num_forms = $input["num_forms"];

            // check if it's a number
            if( !isset( $input["login_id"] ) )
                $input["login_id"] = "";
            if( !isset( $valid_input["login_id"] ) )
                $valid_input["login_id"] = "";
            $valid_input['login_id'] = isset($input["login_id"]) && ctype_alnum($input['login_id']) ? $input['login_id'] : $valid_input['login_id'];
            if ( $valid_input['login_id'] != $input['login_id'] ) {
                add_settings_error(
                    'login_id',
                    'streamsend_error',
                    'The Login ID can only contain letters and numbers.',
                    'error'
                );
            };

            //  API key
//            echo( strlen(trim( $input['APIkey'] )) );
            if ( ( strlen($input['APIkey']) == 16 ) && ( ctype_alnum($input['APIkey']) ) ) {
                $valid_input['APIkey'] = $input['APIkey'];
            } else {
                add_settings_error(
                    'api_key',
                    'streamsend_error',
                    'The API Key can only contain letters and numbers, and should be 16 characters long.',
                    'error'
                );
            }
            $valid_input['num_forms'] = $input['num_forms'];
            if( $addnewform )
            {
                $max = 2;
                $expl = explode( ",", $input["num_forms"] );
                foreach( $expl as $i )
                {
                    if( $max <= intval( $i ) )
                    {
                        $max = intval( $i ) + 1;
                    }
                }
                $valid_input['num_forms'] = $input['num_forms'] . ",$max";
//                echo( $input['num_forms'] . ",$max" );
//                exit;
            }
//            exit;
            if( isset( $valid_input["APIkey"] ) && isset( $valid_input["login_id"] )  && $valid_input["APIkey"] && $valid_input["login_id"] )
            {
                    // get group data
                    // instantiate a new StreamSend API class, pass login / auth data to it
                $streamsend_api = new StreamSend_API( $valid_input['login_id'], $valid_input['APIkey']);
                
                    // get the groups for this account
                $groups = $streamsend_api->list_groups();
                $fields = $streamsend_api->list_fields();

            }

                // check if groups returned an error, or an answer
            if ( isset( $groups ) && is_array($groups) ) {

                // if it returns an array, it's got groups back from hooking up w/ streamsend
                $valid_input['logged_in'] = 'true';

                // pass the array of groups into the settings
                $valid_input['groups'] = $groups;
                $valid_input['fields'] = $fields;



            } else {

                // not logged in...
                $valid_input['logged_in'] = 'false';

                // pass thru previous info
                $valid_input['groups'] = self::$settings['groups'];

                $valid_input['fields'] = self::$settings['fields'];

                // the method returns a string / error message otherwise
                add_settings_error(
                    'login_id',
                    'streamsend_error',
                    "Unable to log into the API using those credentials. Please check and try again",
                    'error'
                );


            }

        } elseif ( $reset ) {

            // get defaults
            $default_input = $this->get_settings_defaults();
            // assign to valid input
            $valid_input = $default_input;

        }

        return $valid_input;

    } // end sanitize_account_information_settings


}
