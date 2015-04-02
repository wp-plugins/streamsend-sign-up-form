<?php

include_once( STREAMSEND_PATH . '/class-ss-form.php' );

// Register shortcode [streamsend]
add_shortcode( 'streamsend', 'streamsend_form_shortcode' );

function streamsend_form_shortcode( $args ) {
    // call the dynamic stylesheet
    $streamsend_style = new Streamsend_Style();
    // dump it in the footer.
    add_action( 'wp_footer', array( $streamsend_style, 'output' ), 10 );

    $streamsend_form = new Streamsend_Form();

    $returned = $streamsend_form->generate_form( false, $args );
	
	return $returned;

}