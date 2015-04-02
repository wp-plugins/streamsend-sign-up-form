<?php

class Form_Setup {

    public static $key = 'streamsend_form_setup';
    public static $formid = 1;
    public static $extra = "";

    public static $account_settings = array();
    public static $settings = array();


    function fixKey(){
        self::$formid = isset( $_REQUEST['formid'] ) ? $_REQUEST['formid'] : 1;
        $extra = self::$formid>1?"_".self::$formid:"";
        self::$key = 'streamsend_form_setup' . $extra;
    }
    function __construct() {

        self::$settings = $this->get_settings_options();
        self::fixKey();

        self::$account_settings = (array) get_option( Account_Information::$key );

        
        add_action( 'admin_init', array( &$this, 'register_settings' ) );
    }

    public static function get_settings_options() {

        // load the settings from the database
        self::fixKey();
        $settings_options = (array) get_option( self::$key );

        // merge with defaults
        $settings_options = array_merge( self::get_settings_defaults(), $settings_options );

        return $settings_options;

    }

    public static function get_settings_defaults() {
        
        $defaults = array(
            'form_size' => 'medium',
            'submit_txt' => 'Subscribe',
            'confirmation_msg' => 'Thanks for subscribing!',
            'send_confirmation_email' => '1',
            'confirmation_email_sender_name' => '&nbsp;',
            'confirmation_email_sender' => '&nbsp;',
            'confirmation_email_subject' => '&nbsp;',
            'confirmation_email_msg' => '&nbsp;',
            'opt_in_message' => 'I would like to opt-in to the following:',
            'captcha' => 0,
            'group_active' => '',
            'defgroup_active' => '',
            'field_active' => array(),
            'widfield_active' => array(),
            'field_required' => array(),
            'widfield_required' => array(),
            'border_width' => '1',
            'border_color' => 'CCC',
            'border_type' => 'solid',
            'txt_color' => '000',
            'bg_color' => 'FFF',
            'submit_txt_color' => 'FFF',
            'submit_bg_color' => '000',
            'submit_border_width' => '1',
            'submit_border_color' => '555',
            'submit_border_type' => 'solid',
            'submit_hover_txt_color' => '000',
            'submit_hover_border_width' => '1',
            'submit_hover_bg_color' => 'FFF',
            'submit_hover_border_color' => '555',
            'submit_hover_border_type' => 'solid'
        );
        return $defaults;
    }

    function register_settings() {
        self::fixKey();
        register_setting( self::$key, self::$key, array( &$this, 'sanitize_form_setup_settings' ) );

        if( isset( self::$account_settings['num_forms']  ) )
        {
            $possforms = explode( ",", self::$account_settings['num_forms'] );
            $matched = false;
            foreach( $possforms as $p=>$val )
            {
                if( $val == self::$formid )
                {
                    $matched = true;
                    break;
                }
            }
            if(!$matched && self::$formid > 1 )
            {
                wp_redirect( admin_url( 'options-general.php?page=streamsend_plugin_options' ) );
                return;

            }
        }
        
        
            // begin settings section
        add_settings_section( 'section_groups', 'FormID '.self::$formid.' <span style="font-size:10px">Shortcode: [streamsend form="'.self::$formid.'"]</span>', array( &$this, 'section_groups_desc' ), self::$key );
        
            // check to see if logged in to streamsend
        if ( isset( self::$account_settings["logged_in"] ) && self::$account_settings["logged_in"] == 'true' ) {
            
            add_settings_field( 'groups', 'Select Opt-In lists that will be displayed on the form.<br><p style="font-size:10px;">Select the StreamSend opt-in lists to display below your opt-in message here.  These lists will appear below the opt-in message with a checkbox and the form will feed into these lists. If you are not including an opt-in message do not select any lists here.</p><i style="font-size:10px">Select multiple using ctrl/cmd</i>', array( &$this, 'field_groups' ), self::$key, 'section_groups' );
            add_settings_field( 'defgroups', 'Select Hidden Lists  <br><p style="font-size:10px;">Select the lists you would like your form to feed into.  If you have selected lists in the "opt-in" section above, do not repeat them here. Only include additional lists. Any lists you select here will not appear on the form.</p><i style="font-size:10px">Select multiple using ctrl/cmd</i>', array( &$this, 'field_defgroups' ), self::$key, 'section_groups' );

                add_settings_field( 'widfields', 'Select Fields To Display in Widget  <br><p style="font-size:10px;">The widget will display the form in the sidebar.</p><i style="font-size:10px">Select multiple using ctrl/cmd</i>', array( &$this, 'field_widfields' ), self::$key, 'section_groups' );

                add_settings_field( 'widfields_required', 'Select Fields To Be Required in Widget  <br><i style="font-size:10px">Select multiple using ctrl/cmd</i>', array( &$this, 'widfield_required' ), self::$key, 'section_groups' );

            add_settings_field( 'fields', 'Select Fields To Display in Pages/Posts<br><i style="font-size:10px">Select multiple using ctrl/cmd</i>', array( &$this, 'field_fields' ), self::$key, 'section_groups' );
            
                add_settings_field( 'fields_required', 'Select Fields To Be Required<br><i style="font-size:10px">Select multiple using ctrl/cmd</i>', array( &$this, 'field_required' ), self::$key, 'section_groups' );

            add_settings_field( 'captcha', 'Display Captcha In Form?', array( &$this, 'field_captcha' ), self::$key, 'section_groups' );
            add_settings_field( 'opt_in_message', 'Opt In Message', array( &$this, 'field_opt_in_message' ), self::$key, 'section_groups' );
        add_settings_field( 'form_id', '', array( &$this, 'field_formid' ), self::$key, 'section_groups' );
            
        }
        
        add_settings_section( 'section_form_size', 'Select Form Size', array( &$this, 'section_form_size_desc' ), self::$key );
        
        add_settings_field( 'form_size', 'Form Size', array( &$this, 'field_form_size' ), self::$key, 'section_form_size' );
        
        add_settings_section( 'section_form_placeholders', 'Form Display', array( &$this, 'section_form_placeholders_desc' ), self::$key );
        
        add_settings_field( 'submit_button_text', 'Submit Button Text', array( &$this, 'field_submit_txt' ), self::$key, 'section_form_placeholders' );
        add_settings_field( 'confirmation_msg', 'Confirmation Message<br/><i style="font-size:10px">This message will appear after someone fills out the form.</i>', array( &$this, 'field_confirmation_msg' ), self::$key, 'section_form_placeholders' );
        
        add_settings_section( 'section_confirmation_email', 'Confirmation Email', array( &$this, 'section_confirmation_email_desc' ), self::$key );
        add_settings_field( 'send_confirmation_email', 'Send Confirmation Email?', array( &$this, 'field_send_confirmation_email' ), self::$key, 'section_confirmation_email' );
        add_settings_field( 'confirmation_email_sender_name', 'Confirmation Email Sender Name<br/><i style="font-size:10px;">(Your name or company name)</i>', array( &$this, 'field_confirmation_email_sender_name' ), self::$key, 'section_confirmation_email' );
        add_settings_field( 'confirmation_email_sender', 'Confirmation Email Sender Email Address<br/><i style="font-size:10px;">(Must be a valid email address)</i>', array( &$this, 'field_confirmation_email_sender' ), self::$key, 'section_confirmation_email' );
        add_settings_field( 'confirmation_email_subject', 'Confirmation Email Subject Line', array( &$this, 'field_confirmation_email_subject' ), self::$key, 'section_confirmation_email' );
        add_settings_field( 'confirmation_email_textarea', 'Confirmation Email Message', array( &$this, 'field_confirmation_email_textarea' ), self::$key, 'section_confirmation_email' );

// begin form custom area
        add_settings_section( 'section_form_fields_custom', 'Form Fields Customization<br/><i style="font-size:10px;">You can customize the look of your form below.</i>', array( &$this, 'section_form_fields_custom_desc' ), self::$key );

        add_settings_field( 'border_width', 'Border Width', array( &$this, 'field_border_width' ), self::$key, 'section_form_fields_custom' );
        add_settings_field( 'border_color', 'Border Color', array( &$this, 'field_border_color' ), self::$key, 'section_form_fields_custom' );
        add_settings_field( 'border_type', 'Border Type', array( &$this, 'field_border_type' ), self::$key, 'section_form_fields_custom' );
        add_settings_field( 'txt_color', 'Text Color', array( &$this, 'field_txt_color' ), self::$key, 'section_form_fields_custom' );
        add_settings_field( 'bg_color', 'Background Color', array( &$this, 'field_bg_color' ), self::$key, 'section_form_fields_custom' );

        add_settings_section( 'section_submit_custom', 'Submit Button Customization<br/><i style="font-size:10px;">You can customize the look of the submit button below.</i>', array( &$this, 'section_submit_desc' ), self::$key );

        add_settings_field( 'submit_txt_color', 'Submit Button Text Color', array( &$this, 'field_submit_txt_color' ), self::$key, 'section_submit_custom' );
        add_settings_field( 'submit_bg_color', 'Submit Button Background Color', array( &$this, 'field_submit_bg_color' ), self::$key, 'section_submit_custom' );

        add_settings_field( 'submit_border_width', 'Submit Button Border Width', array( &$this, 'field_submit_border_width' ), self::$key, 'section_submit_custom' );
        add_settings_field( 'submit_border_color', 'Submit Button Border Color', array( &$this, 'field_submit_border_color' ), self::$key, 'section_submit_custom' );
        add_settings_field( 'submit_border_type', 'Submit Button Border Type', array( &$this, 'field_submit_border_type' ), self::$key, 'section_submit_custom' );

        add_settings_section( 'section_submit_hover_custom', 'Submit Button Hover State Customization<br/><i style="font-size:10px;">You can customize the hover state of the submit button below.</i>', array( &$this, 'section_submit_hover_desc' ), self::$key );

        add_settings_field( 'submit_hover_txt_color', 'Submit Button Hover Text Color', array( &$this, 'field_submit_hover_txt_color' ), self::$key, 'section_submit_hover_custom' );
        add_settings_field( 'submit_hover_bg_color', 'Submit Button Hover Background Color', array( &$this, 'field_submit_hover_bg_color' ), self::$key, 'section_submit_hover_custom' );

        add_settings_field( 'submit_hover_border_width', 'Submit Button Hover Border Width', array( &$this, 'field_submit_hover_border_width' ), self::$key, 'section_submit_hover_custom' );
        add_settings_field( 'submit_hover_border_color', 'Submit Button Hover Border Color', array( &$this, 'field_submit_hover_border_color' ), self::$key, 'section_submit_hover_custom' );
        add_settings_field( 'submit_hover_border_type', 'Submit Button Hover Border Type', array( &$this, 'field_submit_hover_border_type' ), self::$key, 'section_submit_hover_custom' );

        
    }

    function section_groups_desc() {

        if ( isset( self::$account_settings["logged_in"] ) && self::$account_settings['logged_in'] == 'true' ) {
            echo '';
        } else {
            echo 'Once you&apos;ve entered your account information and saved the changes, then you can choose from the available lists to assign new members to.';
        }
    }

    function section_form_fields_custom_desc() {  }

    function field_border_width() { ?>
        <input id="streamsend_border_width"
           type="text"
           size="2"
           name="<?php echo self::$key; ?>[border_width]"
           value="<?php echo esc_attr( self::$settings['border_width'] ); ?>"
        /> px (enter 0 for no border.)
    <?php }

    function field_border_color() { ?>
        # <input id="streamsend_border_color"
             type="text"
             size="6"
             name="<?php echo self::$key; ?>[border_color]"
             value="<?php echo esc_attr( self::$settings['border_color'] ); ?>"
        />
    <?php }

    function field_border_type() {
        $border_types = array( 'none', 'dashed', 'dotted', 'double', 'groove', 'inset', 'outset', 'ridge', 'solid' );
        echo '<select id="streamsend_border_type" name="' . self::$key . '[border_type]">';
        foreach ( $border_types as $border_type ) {
            echo '<option value="' . $border_type . '"';
            if ( self::$settings['border_type'] == $border_type ) { echo "selected"; }
            echo '>'; echo $border_type . '</option>';
        }
        echo '</select>';
    }

    function field_txt_color() { ?>
        # <input id="streamsend_txt_color"
             type="text"
             size="6"
             name="<?php echo self::$key; ?>[txt_color]"
             value="<?php echo esc_attr( self::$settings['txt_color'] ); ?>"
        />
    <?php }
    function field_bg_color() { ?>
        # <input id="streamsend_bg_color"
             type="text"
             size="6"
             name="<?php echo self::$key; ?>[bg_color]"
             value="<?php echo esc_attr( self::$settings['bg_color'] ); ?>"
        />
    <?php }

    function section_submit_desc() {  }

    function field_submit_txt_color() { ?>
        # <input id="streamsend_submit_txt_color"
             type="text"
             size="6"
             name="<?php echo self::$key; ?>[submit_txt_color]"
             value="<?php echo esc_attr( self::$settings['submit_txt_color'] ); ?>"
        />
    <?php }

    function field_submit_bg_color() { ?>
        # <input id="streamsend_submit_bg_color" type="text"
             size="6"
             name="<?php echo self::$key; ?>[submit_bg_color]"
             value="<?php echo esc_attr( self::$settings['submit_bg_color'] ); ?>"
        />
    <?php }

    function field_submit_border_width() { ?>
        <input id="streamsend_submit_border_width"
           type="text"
           size="2"
           name="<?php echo self::$key; ?>[submit_border_width]"
           value="<?php echo esc_attr( self::$settings['submit_border_width'] ); ?>"
        /> px (enter 0 for no border.)
    <?php }

    function field_submit_border_color() { ?>
        # <input id="streamsend_submit_border_color"
             type="text"
             size="6"
             name="<?php echo self::$key; ?>[submit_border_color]"
             value="<?php echo esc_attr( self::$settings['submit_border_color'] ); ?>"
        />
    <?php }

    function field_submit_border_type() {
        $border_types = array( 'none', 'dashed', 'dotted', 'double', 'groove', 'inset', 'outset', 'ridge', 'solid' );
        echo '<select name="' . (string)self::$key . '[submit_border_type]">';
        foreach ( $border_types as $border_type ) {
            echo '<option value="' . $border_type . '"';
            if ( self::$settings['submit_border_type'] == $border_type ) { echo "selected"; }
            echo '>';
            echo $border_type . '</option>';
        }
        echo '</select>';
    }

    function section_submit_hover_desc() {  }

    function field_submit_hover_txt_color() { ?>
        # <input id="streamsend_submit_hover_text"
             type="text"
             size="6"
             name="<?php echo self::$key; ?>[submit_hover_txt_color]"
             value="<?php echo esc_attr( self::$settings['submit_hover_txt_color'] ); ?>"
        />
    <?php }

    function field_submit_hover_bg_color() { ?>
        # <input id="streamsend_submit_hover_bg_color"
             type="text"
             size="6"
             name="<?php echo self::$key; ?>[submit_hover_bg_color]"
             value="<?php echo esc_attr( self::$settings['submit_hover_bg_color'] ); ?>"
        />
    <?php }

    function field_submit_hover_border_width() { ?>
        <input id="streamsend_submit_hover_border_width"
           type="text"
           size="2"
           name="<?php echo self::$key; ?>[submit_hover_border_width]"
           value="<?php echo esc_attr( self::$settings['submit_hover_border_width'] ); ?>"
        /> px (enter 0 for no border.)
    <?php }

    function field_submit_hover_border_color() { ?>
        # <input id="streamsend_submit_hover_boder_color"
             type="text"
             size="6"
             name="<?php echo self::$key; ?>[submit_hover_border_color]"
             value="<?php echo esc_attr( self::$settings['submit_hover_border_color'] ); ?>"
        />
    <?php }

    function field_submit_hover_border_type() {
        $border_types = array( 'none', 'dashed', 'dotted', 'double', 'groove', 'inset', 'outset', 'ridge', 'solid' );
        echo '<select name="' . (string)self::$key . '[submit_hover_border_type]">';
        foreach ( $border_types as $border_type ) {
            echo '<option value="' . $border_type . '"';
            if ( self::$settings['submit_hover_border_type'] == $border_type ) { echo "selected"; }
            echo '>';
            echo $border_type . '</option>';
        }
        echo '</select>';
    }
    function field_opt_in_message( $args ) {
        ?>

If you would like to display an opt-in message at the bottom of your form, place the text in the field below. If you do not want an opt-in message to appear, leave the field blank.       <Br> <input id="opt_in_message"
           type="text"
           size="80"
           name="<?php echo self::$key; ?>[opt_in_message]"
           value="<?php echo esc_attr( self::$settings['opt_in_message'] ); ?>"
            />
    <?php }

    function field_groups($args) {
        $groups = self::$account_settings['groups'];
        // groups dropdown
        echo '<select id="streamsend_groups" name="' . self::$key . '[group_active][]" multiple  style="width:200px">';

        if( isset( $groups ) )
        foreach ( $groups as $group_key => $group_value ) {
            echo '<option value="' . $group_key . '"';
            if ( is_array(self::$settings['group_active']) &&in_array( $group_key, self::$settings['group_active']) ) { echo "selected"; }
            echo '>' . $group_value . '</option>';
        }

        echo '</select>';

        // refresh button
//        echo '<input style="margin-left: 20px;" type="submit" name="streamsend_account_information[refresh]" id="refresh" class="button-secondary" value="Refresh Lists" />';
    }

    function field_captcha( $args) {

        if( !isset( self::$settings['captcha'] ) )
            self::$settings['captcha'] = 0;

        // groups dropdown
        echo '<select id="streamsend_captcha" name="' . self::$key . '[captcha]" >';

        $groups = array( 0=>"No", 1=>"Yes" );
        foreach ( $groups as $group_key => $group_value ) {
            echo '<option value="' . $group_key . '" ';
            if ( $group_key == self::$settings['captcha'] ) { echo "selected"; }
            echo '>' . $group_value . '</option>';
        }

        echo '</select>';

    }

    function field_defgroups( $args) {

        $groups = self::$account_settings['groups'];

        // groups dropdown
        echo '<select id="streamsend_defgroups" name="' . self::$key . '[defgroup_active][]" multiple  style="width:200px">';

        if( isset( $groups ) )
            foreach ( $groups as $group_key => $group_value ) {
                echo '<option value="' . $group_key . '"';
                if ( is_array(self::$settings['defgroup_active']) &&in_array( $group_key, self::$settings['defgroup_active']) ) { echo "selected"; }
                echo '>' . $group_value . '</option>';
            }
        
        echo '</select>';

        // refresh button
//        echo '<input style="margin-left: 20px;" type="submit" name="streamsend_account_information[refresh]" id="refresh" class="button-secondary" value="Refresh Lists" />';
    }

    function field_fields( $args) {

        $fields = self::$account_settings['fields'];

        // fields dropdown
        echo '<select multiple id="streamsend_fields" name="' . self::$key . '[field_active][]" style="width:200px">';

        
        if( isset( $fields ) )
        foreach ( $fields as $group_key => $group_value ) {
            echo '<option value="' . $group_key . '"';
            if ( is_array(self::$settings['field_active']) && in_array( $group_key, self::$settings['field_active']) ) { echo "selected"; }
            echo '>' . $group_value . '</option>';
        }

        echo '</select>';

        // refresh button
//        echo '<input style="margin-left: 20px;" type="submit" name="streamsend_account_information[refresh]" id="refresh" class="button-secondary" value="Refresh Fields" />';
    }

    function field_widfields() {

        $fields = self::$account_settings['fields'];

        // fields dropdown
        echo '<select multiple id="streamsend_fields" name="' . self::$key . '[widfield_active][]" style="width:200px">';

        
        if( isset( $fields ) )
        foreach ( $fields as $group_key => $group_value ) {
            echo '<option value="' . $group_key . '"';
            if ( is_array(self::$settings['widfield_active']) && in_array( $group_key, self::$settings['widfield_active']) ) { echo "selected"; }
            echo '>' . $group_value . '</option>';
        }

        echo '</select>';

        // refresh button
//        echo '<input style="margin-left: 20px;" type="submit" name="streamsend_account_information[refresh]" id="refresh" class="button-secondary" value="Refresh Fields" />';
    }

    function field_required( $args) {

        $fields = self::$account_settings['fields'];

        // fields dropdown
        echo '<select multiple id="streamsend_fields" name="' . self::$key . '[field_required][]" style="width:200px">';

        
        if( isset( $fields ) )
        foreach ( $fields as $group_key => $group_value ) {
            echo '<option value="' . $group_key . '"';
            if ( is_array(self::$settings['field_required']) && in_array( $group_key, self::$settings['field_required']) ) { echo "selected"; }
            echo '>' . $group_value . '</option>';
        }

        echo '</select>';

        // refresh button
    }

    function widfield_required() {

        $fields = self::$account_settings['fields'];

        // fields dropdown
        echo '<select multiple id="streamsend_fields" name="' . self::$key . '[widfield_required][]" style="width:200px">';

        
        if( isset( $fields ) )
        foreach ( $fields as $group_key => $group_value ) {
            echo '<option value="' . $group_key . '"';
            if ( is_array(self::$settings['widfield_required']) && in_array( $group_key, self::$settings['widfield_required']) ) { echo "selected"; }
            echo '>' . $group_value . '</option>';
        }

        echo '</select>';

        // refresh button
    }


    
    function section_confirmation_email_desc() {
        //echo '<p>Configure the confirmation email</p>';
    }

    function field_send_confirmation_email() { ?>
        <label for="send_confirmation_email_yes">Yes</label>
        <input id="send_confirmation_email_yes"
           type="radio"
           name="<?php echo self::$key; ?>[send_confirmation_email]"
           value="1" <?php checked( '0', ( self::$settings['send_confirmation_email'] ) ); ?>
        />
        <label for="send_confirmation_email_no">No</label>
        <input id="send_confirmation_email_no"
               type="radio"
               name="<?php echo self::$key; ?>[send_confirmation_email]"
               value="0" <?php checked( '1', ( self::$settings['send_confirmation_email'] ) ); ?>
                />
    <?php }

    function field_confirmation_email_subject() { ?>

        <input id="confirmation_email_subject"
           type="text"
           size="80"
           name="<?php echo self::$key; ?>[confirmation_email_subject]"
           value="<?php echo esc_attr( self::$settings['confirmation_email_subject'] ); ?>"
            />
    <?php }
    function field_confirmation_email_sender() { ?>

        <input id="confirmation_email_sender"
           type="text"
           size="80"
           name="<?php echo self::$key; ?>[confirmation_email_sender]"
           value="<?php echo esc_attr( self::$settings['confirmation_email_sender'] ); ?>"
            />
    <?php }

    function field_confirmation_email_sender_name() { ?>

        <input id="confirmation_email_sender_name"
           type="text"
           size="80"
           name="<?php echo self::$key; ?>[confirmation_email_sender_name]"
           value="<?php echo esc_attr( self::$settings['confirmation_email_sender_name'] ); ?>"
            />
    <?php }

    function field_confirmation_email_textarea() {
        $content = isset( self::$settings['confirmation_email_msg'] ) ? esc_attr( self::$settings['confirmation_email_msg'] ) : '';

        $settings = array( 'media_buttons' => false,'quicktags' => false );
        $editor_id = self::$key ."_confirmation_email_msg";
        wp_editor( $content, $editor_id,$settings );
        return;
    }

    function section_form_field_includes_desc() {  }

    function section_form_size_desc() {  }
    function field_form_size() { ?>
        <input id="form_size_x_small"
           type="radio"
           name="<?php echo self::$key; ?>[form_size]"
           value="x-small" <?php checked( 'x-small', ( self::$settings['form_size'] ) ); ?>
        />
        <label for="form_size_x_small">Extra Small ( 200px )</label>
        <br />
        <input id="form_size_small"
           type="radio"
           name="<?php echo self::$key; ?>[form_size]"
           value="small" <?php checked( 'small', ( self::$settings['form_size'] ) ); ?>
        />
        <label for="form_size_small">Small ( 280px )</label>
        <br />
        <input id="form_size_medium"
           type="radio"
           name="<?php echo self::$key; ?>[form_size]"
           value="medium" <?php checked( 'medium', ( self::$settings['form_size'] ) ); ?>
        />
        <label for="form_size_medium">Medium ( 400px )</label>
        <br />
        <input id="form_size_large"
           type="radio"
           name="<?php echo self::$key; ?>[form_size]"
           value="large" <?php checked( 'large', ( self::$settings['form_size'] ) ); ?>
        />
        <label for="form_size_large">Large ( 600px )</label>
    <?php }

    function section_form_placeholders_desc() {  }
    function field_email_placeholder() { ?>
        <input id="streamsend_email_placeholder"
           type="text"
           size="40"
           name="<?php echo self::$key; ?>[email_placeholder]"
           value="<?php echo esc_attr( self::$settings['email_placeholder'] ); ?>"
        />
    <?php }

    function field_submit_txt() { ?>
        <input id="streamsend_submit_txt"
           type="text"
           size="40"
           name="<?php echo self::$key; ?>[submit_txt]"
           value="<?php echo esc_attr( self::$settings['submit_txt'] ); ?>"
        />
    <?php }

    function field_confirmation_msg() { ?>
        <textarea id="streamsend_confirmation_msg"
              name="<?php echo self::$key; ?>[confirmation_msg]"
              rows="6"
              cols="40" ><?php
        // avoid undefined index by checking for the value 1st, then assigning it nothing if it has not been set.
        $confirmation_msg = isset( self::$settings['confirmation_msg'] ) ? esc_attr( self::$settings['confirmation_msg'] ) : '';
        echo $confirmation_msg;
        ?></textarea>
    <?php
        // someday we're gonna use the native wp_editor, and let them dump html in thar...
        //$args = array("textarea_name" => "streamsend_options[confirmation_msg]");
        //wp_editor( $options['confirmation_msg'], "streamsend_options[confirmation_msg]", $args );
    }

    // Form preview section
    // for version 2.0,
    function field_form_preview() {
        echo '<div style="position: fixed; top: 130px; right: 50px;">';
        $preview_form = new Form( self::$settings );
        echo $preview_form->output();
        echo '</div>';
    }

    function field_formid() { ?>
        <input id="streamsend_formid"
               type="hidden"
               name="formid"
               value="<?php echo esc_attr( self::$formid ); ?>"
        />
    <?php }
    function streamsend_detect_shortcode()
    {
        global $wp_query;
        $posts = $wp_query->get_posts();
        $pages = get_pages();
        foreach ($posts as $post){
            if (  strpos( $post->post_content, 'streamsend form="'. self::$formid . '"' ) !== false  )
            {
                return true;
            }
        }
        foreach ($pages as $post){
            if (  strpos( $post->post_content, 'streamsend form="'. self::$formid . '"' ) !== false  )
            {
                return true;
            }
        }
        return false;
    }
    
    function sanitize_form_setup_settings( $input ) {
        $valid_input = array();
        
        // check which button was clicked, submit or reset,
        $submit = ( ! empty( $input['submit'] ) ? true : false );
        $reset = ( ! empty( $input['reset'])  ? true : false );
        $sendtest = ( ! empty( $input['sendtest'])  ? true : false );
        $delthis = ( ! empty( $input['delthis'])  ? true : false );
        $couldnotremove = false;
        if( $delthis )
        {
                // check if this is used by any posts
            $usedbypost = self::streamsend_detect_shortcode();
            if( $usedbypost )
            {
                $couldnotremove = true;
                add_settings_error(
                    'border_color',
                    'streamsend_error',
                    'This form is used by a post. Please remove before deleting this form. ',
                    'error'
                );
            }
            else
            {
                $settings_options = (array) get_option( Account_Information::$key );
                $possforms = explode( ",", $settings_options['num_forms'] );
                foreach( $possforms as $p=>$val )
                {
                    if( $val == self::$formid )
                    {
                        unset( $possforms[$p] );
                        break;
                    }
                    
                }
                $settings_options["num_forms"] = implode( ",", $possforms );
                $settings_options["manualsave"] = 1;
                update_option( Account_Information::$key, $settings_options );
            }

            
        }
        if ( $submit || $sendtest ) {

            // text inputs
//            $valid_input['include_firstname_lastname']  = $input['include_firstname_lastname'];
            $valid_input['form_size']                   = $input['form_size'];
            // $valid_input['email_placeholder']           = wp_kses( $input['email_placeholder'], '' );
            // $valid_input['firstname_placeholder']       = wp_kses( $input['firstname_placeholder'], '' );
            // $valid_input['lastname_placeholder']        = wp_kses( $input['lastname_placeholder'], '' );
            $valid_input['submit_txt']                  = wp_kses( $input['submit_txt'], '' );
            $valid_input['confirmation_msg']            = wp_kses( $input['confirmation_msg'], '' );
            $valid_input['send_confirmation_email']     = $input['send_confirmation_email'];
            $valid_input['confirmation_email_subject']  = wp_kses( $input['confirmation_email_subject'], '' );
            $valid_input['confirmation_email_sender_name']  = wp_kses( $input['confirmation_email_sender_name'], '' );
            $valid_input['confirmation_email_sender']  = wp_kses( $input['confirmation_email_sender'], '' );
            
//            $valid_input['confirmation_email_msg']      = wp_kses( $input['confirmation_email_msg'], '' );
            $valid_input['confirmation_email_msg']      = wp_kses( $_REQUEST[self::$key."_confirmation_email_msg"], '' );

                // if there is an active group selected, pass it through
	    if( isset( $input['group_active'] ) )
		$valid_input['group_active'] = $input['group_active'];
	    if( isset( $input['defgroup_active'] ) )
		$valid_input['defgroup_active'] = $input['defgroup_active'];
	    if( isset( $input['field_active'] ) )
		$valid_input['field_active'] = $input["field_active"];
	    if( isset( $input['widfield_active'] ) )
                $valid_input['widfield_active'] = $input["widfield_active"];

	    if( isset( $input['field_required'] ) )
		$valid_input['field_required'] = $input["field_required"];
	    if( isset( $input['widfield_required'] ) )
                $valid_input['widfield_required'] = $input["widfield_required"];


            $valid_input['captcha'] = $input["captcha"];
	    $valid_input['opt_in_message']            = wp_kses( $input['opt_in_message'], '' );
	    

            // check all hexadecimal values
            // not checking for a true hex value, not capturing '#'
            // border_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['border_color']) ) {
                $valid_input['border_color'] = $input['border_color'];
            } else {
                add_settings_error(
                    'border_color',
                    'streamsend_error',
                    'The form fields border color is an invalid hexadecimal value',
                    'error'
                );
            }
            // txt_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['txt_color']) ) {
                $valid_input['txt_color'] = $input['txt_color'];
            } else {
                add_settings_error(
                    'txt_color',
                    'streamsend_error',
                    'The form fields text color is an invalid hexadecimal value',
                    'error'
                );
            }
            // bg_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['bg_color']) ) {
                $valid_input['bg_color'] = $input['bg_color'];
            } else {
                add_settings_error(
                    'bg_color',
                    'streamsend_error',
                    'The form fields background color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_txt_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_txt_color']) ) {
                $valid_input['submit_txt_color'] = $input['submit_txt_color'];
            } else {
                add_settings_error(
                    'submit_txt_color',
                    'streamsend_error',
                    'The submit button text color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_bg_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_bg_color']) ) {
                $valid_input['submit_bg_color'] = $input['submit_bg_color'];
            } else {
                add_settings_error(
                    'submit_bg_color',
                    'streamsend_error',
                    'The submit button background color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_border_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_border_color']) ) {
                $valid_input['submit_border_color'] = $input['submit_border_color'];
            } else {
                add_settings_error(
                    'submit_border_color',
                    'streamsend_error',
                    'The submit border color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_hover_txt_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_hover_txt_color']) ) {
                $valid_input['submit_hover_txt_color'] = $input['submit_hover_txt_color'];
            } else {
                add_settings_error(
                    'submit_hover_txt_color',
                    'streamsend_error',
                    'The submit hover text color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_hover_bg_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_hover_bg_color']) ) {
                $valid_input['submit_hover_bg_color'] = $input['submit_hover_bg_color'];
            } else {
                add_settings_error(
                    'submit_hover_bg_color',
                    'streamsend_error',
                    'The submit hover background color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_hover_border_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_hover_border_color']) ) {
                $valid_input['submit_hover_border_color'] = $input['submit_hover_border_color'];
            } else {
                add_settings_error(
                    'submit_hover_border_color',
                    'streamsend_error',
                    'The submit hover border color is an invalid hexadecimal value',
                    'error'
                );
            }

            // validate pixel values,
            $valid_input['border_width'] = (is_numeric($input['border_width']) ? $input['border_width'] : $valid_input['border_width']);
            $valid_input['submit_border_width'] = (is_numeric($input['submit_border_width']) ? $input['submit_border_width'] : $valid_input['submit_border_width']);
            $valid_input['submit_hover_border_width'] = (is_numeric($input['submit_hover_border_width']) ? $input['submit_hover_border_width'] : $valid_input['submit_hover_border_width']);

            // validate select elements, border types
            $valid_input['border_type'] = $input['border_type'];
            $valid_input['submit_border_type'] = $input['submit_border_type'];
            $valid_input['submit_hover_border_type'] = $input['submit_hover_border_type'];
            
        } elseif ( $reset ) {

            // get defaults
            $default_input = $this->get_settings_defaults();
            // assign to valid input
            $valid_input = $default_input;

        }
        if( $sendtest && $input["test_recipient"])
        {
            $streamsend_form = new Streamsend_Form();
            $streamsend_form->send_confirmation_email($input["test_recipient"]);
        }
        return $valid_input;

    }


}
