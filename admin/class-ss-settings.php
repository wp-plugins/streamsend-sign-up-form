<?php

include_once('class-account-information.php');
include_once('class-form-setup.php');
include_once('class-form-custom.php');
include_once('class-help.php');

class StreamSend_Settings {

    private static $_plugin_options_key = 'streamsend_plugin_options';
    private $_plugin_settings_tabs = array();

    function __construct() {

        // make sure the admin menu gets hooked up in the admin menu
        add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );

        // add action link
        add_filter( 'plugin_action_links', array( &$this, 'add_action_links' ), 10, 2 );

        // Register settings
        $this->register_settings();

        // Add admin stylesheet
        // add_action( 'admin_init', array( &$this, 'admin_stylesheet_init' ) );

    }


    function register_settings() {

        $this->_plugin_settings_tabs[ Account_Information::$key ]   = 'API Information';
//        $this->_plugin_settings_tabs[ Form_Setup::$key ]            = 'Form Setup';
//        $this->_plugin_settings_tabs[ Form_Custom::$key ]           = 'Form Display';
//        $this->_plugin_settings_tabs[ Help::$key ]                  = 'Help';

        $account_information = new Account_Information();
        $form_setup = new Form_Setup();
//        $form_custom = new Form_Custom();
//        $help = new Help();

    }

    function admin_stylesheet_init() {
        // Register our stylesheet.
        // wp_register_style( 'streamsend-form-styles', plugins_url('class-style.php', __FILE__) );
        // wp_enqueue_style( 'streamsend-form-styles' );
    }

    /**
     * Add action links to installed plugins page
     * @param $links
     * @param $file
     * @return array
     */
    function add_action_links($links, $file) {
        static $this_plugin;
        if (!$this_plugin) $this_plugin = STREAMSEND_FILE;
        if ($file == $this_plugin) {
            /**
             * The "page" query string value must be equal to the slug
             * of the Settings admin page we defined earlier,
             * the $_plugin_options_key property of this class which in
             * this case equals "streamsend_plugin_options".
             */
            $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . self::$_plugin_options_key . '">Settings</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    /*
      * Called during admin_menu, adds an options
      * page under Settings called StreamSend Settings, rendered
      * using the plugin_options_page method.
      */
    function add_admin_menus() {
        // add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback );
        add_options_page( 'StreamSend', 'StreamSend', 'manage_options', self::$_plugin_options_key, array( &$this, 'plugin_options_page' ) );

        // enqueue stylesheet for form preview only on our plugin settings page, not entire admin area.
        // Using registered $menu_slug from add_options_page handle to hook stylesheet loading
        // no love this time. get_option() not available. should've gone w/ the value object
        // add_action( 'admin_print_styles-' . self::$_plugin_options_key, 'streamsend-form-styles' );

    }

    /*
      * Plugin Options page rendering goes here, checks
      * for active tab and replaces key with the related
      * settings key. Uses the plugin_options_tabs method
      * to render the tabs.
      */
    function plugin_options_page() {
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : Account_Information::$key;
        $tabkey = $tab;
        $formid = isset( $_REQUEST['formid'] ) ? $_REQUEST['formid'] : 1;
        $tabkey .= $formid>1?"_$formid":"";
        ?>
        <div class="wrap">
            <?php $this->plugin_options_tabs(); ?>
            <form method="post" action="options.php">
                <?php
                wp_nonce_field( 'update-options' );
                settings_fields( $tabkey );
                do_settings_sections( $tabkey );
                // don't do the submit button on the help tab...
                if ( $tab !== 'streamsend_help' ) {
                    echo '<p class="submit">';
                    submit_button( 'Save', 'primary', $tabkey . '[submit]', false, array( 'id' => 'submit' ) );
                    echo '&nbsp;';
                    echo '&nbsp;';
                    submit_button( 'Reset', 'primary', $tabkey . '[reset]', false, array( 'id' => 'reset' ) );

                    $settings_options = (array) get_option( Account_Information::$key );
                    if( $tab == "streamsend_account_information" && isset( $settings_options["login_id"]) && isset( $settings_options["APIkey"] ) )
{
                    echo '&nbsp;';
                    echo '&nbsp;';
                    submit_button( 'Add New Form', 'primary', $tabkey . '[addnew]', false, array( 'id' => 'addnewform' ) );
}
                    else if( $tab == "streamsend_form_setup" ) {
                    echo '&nbsp;';
                    echo '&nbsp;';
                    submit_button( 'Delete This Form', 'primary', $tabkey . '[delthis]', false, array( 'id' => 'delthisform') );
					echo '<br/><br/><i style="font-size:10px;">If this form is embedded in the sidebar of your site and you would like to delete this form, please disassociate the sidebar widget from this form first.</i>';
                    }
echo '</p>';
                }
		if( $tab == "streamsend_form_setup" )
{
                    echo '<p class="submit">';
                    echo( '        <input type="text" name="'. $tabkey .'[test_recipient]" value="" /> ' ); 

                    submit_button( 'Send Test Confirmation Email', 'primary', $tabkey . '[sendtest]', false, array( 'id' => 'sendtest' ) );
					echo '<br/><br/><i style="font-size:10px;">If you have configured a confirmation email for this form, you can test it by entering your email address above and clicking “Send Test Confirmation Email"</i>';
                    echo '</p>';
					
}
                ?>
            </form>
            <?php if ( $tab !== 'streamsend_help' ) {
                echo '
                <h3>DISPLAYING THE FORM ON YOUR SITE</h3>
                <p>
                    To insert form 1 as a <strong>widget</strong> on your sidebar, go to Appearance -> Widgets and then move
                    the "StreamSend Subscription Form" to the widget area where you want the form to appear.
                </p>
                <p>
                    To insert the form as a <strong>shortcode</strong> within your site, insert [streamsend form=FORMID] within your text editor (replacing FORMID with the Id of your form: eg 1) where you want the form to appear.
                </p>
            ';
            } ?>
        </div>
    <?php }

    /*
      * Renders our tabs in the plugin options page,
      * walks through the object's tabs array and prints
      * them one by one. Provides the heading for the
      * plugin_options_page method.
      */


    function plugin_options_tabs() {
        $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : Account_Information::$key;
        $current_formid = isset( $_REQUEST['formid'] ) ? $_REQUEST['formid'] : 1;
        $extra = $current_formid>1?"_".$current_formid:"";
        echo '<h2 class="nav-tab-wrapper">';
        echo '<span style="padding-right:10px">StreamSend</span>';
        foreach ( $this->_plugin_settings_tabs as $tab_key => $tab_caption ) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . self::$_plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
        }
        $possforms = array( 1 );
        $settings_options = (array) get_option( Account_Information::$key );
        if( isset( $settings_options['num_forms'] ) )
        {
            $possforms = explode( ",", $settings_options['num_forms'] );
        }

        foreach( $possforms as $i )
        {
            $active = $i == $current_formid && $current_tab.$extra == Form_Setup::$key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . self::$_plugin_options_key . '&tab=streamsend_form_setup&formid='.$i.'">Form ' . $i . ' Setup</a>';
            
        }
        
        
        echo '</h2>';
    }


}
