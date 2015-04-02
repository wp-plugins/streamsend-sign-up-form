<?php


class Help {

    public static $key = 'streamsend_help';

    function __construct() {

        add_action( 'admin_init', array( &$this, 'register_settings' ) );

    }

    function register_settings() {

        // register_setting( $option_group, $option_name, $sanitize_callback );
        register_setting( self::$key, self::$key, array( &$this, 'sanitize_help_settings' ) );

        // add_settings_section( $id, $title, $callback, $page );
        add_settings_section( 'section_help', 'Help and Setup Information', array( &$this, 'section_help_desc' ), self::$key );

    }

    function section_help_desc() { ?>

        <a href="http://mystreamsend.com/login/" target="_blank">Login to the StreamSend Dashboard</a>

        Here's an instructional video on youtube that will have you up and running in no time: <a href="http://www.youtube.com/watch?v=e2vjCk5E4Tg">How to use your StreamSend Plugin</a>

        <h3>ACCOUNT INFORMATION TAB</h3>

        <strong>Account Login Information:</strong>
        <p>
            Click on 'Account & billing' in the upper right hand of your StreamSend dashboard. This will take you to
            your “Manage your account settings” page. In the Account settings section, the forth tab is <strong>API
            key</strong>. Click on Generate new key to create your API key.
        </p>
        <p>
            Once you create the key, you will need to copy your account number, public api key, and private
            api key into the corresponding fields in the plugin.
        </p>
        <p>
            The plugin will now be able to connect your WordPress site to your StreamSend account. You may
            now assign a group to hold the email addresses that you capture from your form.
        </p>

        <h3>FORM SETUP</h3>
        <p>
            <strong>Include fields</strong> are the information that you can capture from users who submit the form.
            This information is captured and then put into the StreamSend group you specified in the account
            information tab.
        </p>
        <p>
            <strong>Form size</strong> includes four default sizes are included to be used on your sidebar widget area.
        </p>
        <p>
            <strong>Form placeholders</strong> is where your default text goes for the fields on the form.
        <p>
            <strong>Give props</strong> is where you can choose whether or not to display the StreamSend logo on your site. The
            default setting is no.
        </p>

        <h3>FORM CUSTOMIZATION</h3>
        <p>
            <strong>Form fields</strong> are the border width, color, border type, text color and background color of the
            individual fields the form.
        </p>
        <p>
            <strong>Submit button</strong> are the settings for the submit button on the form.
        </p>
        <p>
            <strong>Submit button hover state</strong> are the settings for the hover property of the submit button
        </p>

        <h3>DISPLAYING THE FORM ON YOUR SITE</h3>
        <p>
            To insert the form as a <strong>widget</strong> on your sidebar, go to Appearance -> Widgets and then move
            the "StreamSend Subscription Form" to the widget area where you want the form to appear. Choose the number of the form you'd like to appear in the appropriate pulldown.
        </p>
        <p>
                    To insert the form as a <strong>shortcode</strong> within your site, insert [streamsend form=FORMID] within your text editor (replacing FORMID with the Id of your form: eg 1) where you want the form to appear.
        </p>

    <?php }

    function sanitize_help_settings() {
        // nothing to sanitize here folks, move along...
    }

    function donations_form() { ?>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="Q7FRK4XEF8EAS">
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
    <?php }

}
