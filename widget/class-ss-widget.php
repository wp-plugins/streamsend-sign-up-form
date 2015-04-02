<?php


include_once( STREAMSEND_PATH . '/class-ss-style.php');

class Streamsend_Widget extends WP_Widget {

    private $_form_setup_settings_key = 'streamsend_form_setup';
    private $possforms = 1;

	// process the widget (the constructor)
	function __construct() {

        // get plugin options for form settings
        $settings_options = (array) get_option( Account_Information::$key );
        if( isset( $settings_options['num_forms'] ) )
            $this->possforms = $settings_options['num_forms'];

        $this->form_setup_settings = (array) get_option( $this->_form_setup_settings_key );

        // setup widget ops
		$widget_ops = array(
			'classname' => 'streamsend-widget',
			'description' => 'Displays an email subscription form for Streamsend'
		);
		
		$this->WP_Widget( 'streamsend-widget', 'Streamsend Subscription Form', $widget_ops );
		
		// check to see if widget is being used
		if ( is_active_widget(false, false, $this->id_base) ) {
			
            $streamsend_style = new Streamsend_Style();
            add_action( 'wp_head', array( $streamsend_style, 'output_widget' ), 10 );


        } // end if
        
	} // end __construct

	// displays the widget form in the admin dashboard, Appearance -> Widgets
	function form($instance) {
	
		$defaults = array(
			'title' => '',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$title = $instance['title'];
		$possforms = explode( ",", $this->possforms );
if( !isset( $instance["form"] ) )
    $instance["form"] = 1;
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wp-emem'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" /></p>
		<p><label for="<?php echo $this->get_field_id('form'); ?>"><?php _e('Form', 'wp-emem'); ?>:</label>
		<select class="widefat" id="<?php echo $this->get_field_id('form'); ?>" name="<?php echo $this->get_field_name('form'); ?>">
<?php foreach( $possforms as $i ) { ?>
<option <?php if( $instance['form'] == $i ) echo ( "SELECTED" ); ?> value='<?php echo $i;?>'><?php echo $i;?></option>
<? } ?>
</select>
</p>
		
	<?php } // end form
		
	// processes widget options to save
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;
		
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['form'] = strip_tags( $new_instance['form'] );
		
		return $instance;
		
	} // end update
	
	// display the widget
	function widget($args, $instance) {
		
		extract($args);
		
		// generate widget markup
		echo $before_widget;
		
		// load up the widget settings
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		// check if there's a title, and display it.
		if (!empty($instance['title']))
			echo $before_title . apply_filters('widget_title', $title) . $after_title;

        // instantiate form class, pass in plugin settings
	$streamsend_form = new Streamsend_Form();
        echo $streamsend_form->generate_form( true, array( "form"=>$instance["form"] ));

		// end of widget output
		echo $after_widget;
		
	} // end widget

} // end class Streamsend_Widget

// use widgets_init action hook to execute custom function
add_action( 'widgets_init', 'streamsend_register_widgets' );

function streamsend_register_widgets() {
	register_widget( 'streamsend_widget' );
}

