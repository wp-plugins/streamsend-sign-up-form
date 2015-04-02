<?php 
if ( !session_id() )
    session_start();
include_once( 'admin/class-account-information.php' );
include_once( 'admin/class-form-setup.php' );

class Streamsend_Form {

    private $form_setup_settings = array();
    private $account_information_settings = array();

    private $status_txt;

    private $streamsend_api;

    function __construct () {

        // get authorization data from plugin options
        $this->account_information_settings = (array) get_option( Account_Information::$key );
        $this->form_setup_settings = (array) get_option( Form_Setup::$key );

        // instantiate new streamsend api class, pass in auth data
        $this->streamsend_api = new Streamsend_API( $this->account_information_settings['login_id'], $this->account_information_settings['APIkey'] );

    }

    // outputs the streamsend form,
    public function generate_form( $widget=false, $args=array() ) {
        global $_SESSION;
//        print_r( $_SESSION );
        // check if the form has been submitted
        $captchabad = false;
        $formtype = ($widget?"widget":"form" );
	$this->status_txt = "";        

        if ( isset($_POST['streamsend_form_submit']) && $formtype == $_POST['streamsend_formtype']  ) {

            // validate form fields, if not valid, return status_txt
            $captcha = $this->form_setup_settings['captcha'];
            if( !$widget && $captcha )
            {
                if (empty($_SESSION['captcha'.$formtype]) || trim(strtolower($_REQUEST['captcha'])) != $_SESSION['captcha'.$formtype]) {
                    $captchabad = true;
                }
                else
                    $captchabad = false;
            }

            if( $captchabad )
            {
                $this->status_txt = 'The word you entered did not match. Please try again. ' . trim(strtolower($_REQUEST['captcha'])) .",". $_SESSION['captcha'.$formtype];
            }

	    $missingrequired = false;
            $required_fields = !$widget?$this->form_setup_settings['field_required']:$this->form_setup_settings['widfield_required'];
	    
	    $args["ss-email"] = isset( $_POST['streamsend_email'])?$_POST['streamsend_email']:"";
	    if( count( $required_fields ) )
		{
		    foreach( $required_fields as $r )
			{
			    if( !$_POST["streamsend-extraval-$r"] )
				{
				$this->status_txt = 'Fields with a * are required.';
				$missingrequired = true;
				}
			    $args["streamsend-extraval-".$r] = $_POST["streamsend-extraval-$r"];
			}
		}
            if ( !is_email($_POST['streamsend_email']) ) {

                $this->status_txt = 'Please enter a valid email address';
            // process the form
            } else if( !$captchabad && !$missingrequired ) {
                // get data from form into transportable array
                $data = $this->process_form_data();
                // put members in appropriate group(s)
//                $data = $this->assign_members_to_groups( $data );
                $data["group_ids"] = array();
                if( isset( $data["fields"]["group_ids"] ) )
                    $data["group_ids"] = $data["fields"]["group_ids"];
                    
                // call streamsend, import_single_member, pass in data
                $response = $this->streamsend_api->import_single_member( $data, $this->form_setup_settings['field_active'] );

                // handle the response,
                // pass in wp_error or returned array from streamsend
                // get back object w/ status
                $handled_response = $this->streamsend_request_response_handler( $response );

                // check to see if the member was added,
//                echo( $handled_response->status . " was the status<br>" );
                if ( $handled_response->status == 'member_added' ) {
                    // verify the member was added
                    $verified_member = $this->streamsend_verify_member( $handled_response );

                    // member successfully added
                    if ( $verified_member->status == 'member_verified' ) {

                        // get custom confirmation message, pass through to form
                        $this->status_txt = $this->form_setup_settings['confirmation_msg'];

                        // check to see if user wants to send out confirmation email
                        if ( $this->form_setup_settings['send_confirmation_email'] == '1' ) {
                            $this->send_confirmation_email($verified_member->email);
                        }
                    }

                    // if a wp_error comes back, pass it through to the status text
                    if ( $verified_member->status == 'wp_error' ) {
                        $this->status_txt = $verified_member->wp_error;
                    }

//                    print_r($handled_response);
//                    print_r($verified_member);

                }

                //
                if ( $handled_response->status == 'member_not_added' ) {

                    $this->status_txt = 'Member Not Added, Member may have already been added. Please Try Again.';

//                    print_r($handled_response);
                }

                if ( $handled_response->status == 'member_failed' ) {
                    $this->status_txt = 'Member is in limbo';
                    print_r($handled_response);
                }

            }

        }


        return $this->output_form( $widget, $args );

    }


    public function process_form_data() {

        // construct data array to send to streamsend, array structure parallels streamsend api data request object
        $form_data = array();
        $form_data['email'] = $_POST['streamsend_email'];
//        if ( isset($_POST['streamsend_firstname']) ) $form_data['fields']['firstname'] = $_POST['streamsend_firstname'];
//        if ( isset($_POST['streamsend_lastname']) ) $form_data['fields']['lastname'] = $_POST['streamsend_lastname'];

        foreach( $_POST as $key=>$val )
        {
            if( strpos( $key, "streamsend-extraval" ) !== false )
            {
                $k = str_replace( "streamsend-extraval-", "", $key );
                $form_data['fields'][$k] = $val;
            }
        }
        
        return $form_data;
    }

    // public function assign_members_to_groups( $data ) {

    //     // assign members to group(s), based on settings
    //     if ( $this->account_information_settings['group_active'] !== '0' ) {

    //         // the api accepts an array of integers.
    //         // pass in the active group id as an integer.
    //         $group_ids = (int)$this->account_information_settings['group_active'];

    //         // for now, we're just passing in one group,
    //         $data['group_ids'] = array($group_ids);

    //     }

    //     // if they're not assigning any members to groups, just pass it on thru
    //     return $data;
    // }


    // handles requests returned from the Streamsend_API class, has to deal w/ WP_Error as well as return objects
    public function streamsend_request_response_handler( $response ) {

        if( $response->status == "success" )
        {
                $response->status = 'member_added';
        }
        else
        {
            $response->status = 'wp_error';
            $response->wp_error = 'Something went wrong! Please try to submit the form again,';
                // get the wordpress error
        }

        return $response;
    }

    public function streamsend_verify_member( $handled_response ) {

        // call get_member_detail to verify the member was added, using their member ID
        $verified_member = $this->streamsend_api->get_member_detail( $handled_response->member_id );

        if( !$verified_member->email ) {

            $verified_member->status = 'wp_error';
            $verified_member->wp_error =  'Something went wrong! Please try to submit the form again,';

        } else {
                $verified_member->status = 'member_verified';
        }
        return $verified_member;
    }



    public function send_confirmation_email( $email ) {

        // build email data
        $to = $email;
        $sender = $this->form_setup_settings['confirmation_email_sender'];
        $sender_name = $this->form_setup_settings['confirmation_email_sender_name'];
        $subject = $this->form_setup_settings['confirmation_email_subject'];
        $message = $this->form_setup_settings['confirmation_email_msg'];
        $headers[] = "From:" . $sender_name . " <$sender>"; // uses site admin's email Settings -> General
        // send it.
        add_filter( "wp_mail_content_type", "set_html_content_type" );
        $mail_return = wp_mail( $to, $subject, $message, $headers );
        remove_filter( "wp_mail_content_type", "set_html_content_type" );
    }

    public function output_form( $widget = false, $args = array() ) {

        // output status message

        $formid = isset( $args["form"] )?$args["form"]:1;
        if( $formid > 1 && !isset( $_REQUEST["formid"] ) )
	     $this->form_setup_settings = (array) get_option( Form_Setup::$key  . "_" . $formid );
	     if( !isset( $this->form_setup_settings['form_size'] ) )return;

//        print_r( $this->form_setup_settings);
//        $extra = $formid > 1?"_".$formid:"";
        // output form markup
        $streamsend_form = '<div id="streamsend-form" class="' . $this->form_setup_settings['form_size'] . '">';
        if ( isset($_POST['streamsend_form_submit']) ) {
            $streamsend_form .= '<div class="streamsend-status">' . $this->status_txt . '</div>';
        }

        $formtype = ($widget?"widget":"form" );
        $streamsend_form .= '<div class="streamsend-wrap">';
        $streamsend_form .= '<form id="streamsend-subscription-form" action="' . htmlspecialchars( $_SERVER['REQUEST_URI'] ) . '" method="post" accept-charset="utf-8"><input type="hidden" name="formid" value="'.$formid.'">
<input type="hidden" name="streamsend_formtype" value="'.$formtype.'">';
        $streamsend_form .= '<ul id="streamsend-form-elements">';
        $streamsend_form .= '<li class="streamsend-form-row">';
        $streamsend_form .= '<label class="streamsend-form-label" for="streamsend-email"> Email <span class="streamsend-required">*</span> </label>';
	$defval = isset( $args["ss-email"] )?$args["ss-email"]:"";
        $streamsend_form .= '<input id="streamsend-email" class="streamsend-form-input" type="text" name="streamsend_email" size="30" placeholder="" value="'.$defval.'">';
        $streamsend_form .= '</li>';

        $fields_active = $this->form_setup_settings['field_active'];
        if( $widget )
	        $fields_active = $this->form_setup_settings['widfield_active'];
        $fields_required = $this->form_setup_settings['field_required'];
        if( $widget )
	        $fields_required = $this->form_setup_settings['widfield_required'];
        $fields_display = $this->account_information_settings['fields'];
        if( is_array( $fields_active ) ) {
            foreach( $fields_active as $f )
            {
                $display = $fields_display[$f];
		$req = "";
		if( isset( $fields_required ) && in_array( $f, $fields_required ))
		    {
			$display .= '<span class="streamsend-required">*</span>';
			$req = " required";
		    }
		$defval = isset( $args["streamsend-extraval-".$f] )?$args["streamsend-extraval-".$f]:"";

                $streamsend_form .= '

<li class="streamsend-form-row">';
                $streamsend_form .= '<label class="streamsend-form-label" for="streamsend-extraval-'.$f.'">'.$display.'</label>';
                $streamsend_form .= '<input id="streamsend-extraval-'.$f.'"  class="streamsend-form-input ' . $req . '" type="text" name="streamsend-extraval-'.$f.'"  size="30" placeholder="" value="'.$defval.'">';
                $streamsend_form .= '</li>';
            }
        }

        $defgroups_active = $this->form_setup_settings['defgroup_active'];
        $groups_active = $this->form_setup_settings['group_active'];
        $captcha = $this->form_setup_settings['captcha'];
        $groups_display = $this->account_information_settings['groups'];
        
        if( is_array( $groups_active ) ) {
            $streamsend_form .= "<div id='streamsend-form-list-optin'>" . $this->form_setup_settings['opt_in_message']."</div><div style='clear:both;'></div>";
            foreach( $groups_active as $f )
            {
                $display = $groups_display[$f];
                $streamsend_form .= '<li class="streamsend-form-row">';
                $streamsend_form .= '<input id="streamsend-extraval-'.$f.'"  class="streamsend-form-input-checkbox" type="checkbox" name="streamsend-extraval-group_ids[]"  value='.$f.'>';
                $streamsend_form .= '<label class="streamsend-form-label" for="streamsend-lastname">'.$display.'</label>';
                $streamsend_form .= '</li>';
            }
        }

        if( is_array( $defgroups_active ) ) {
            foreach( $defgroups_active as $f )
            {
                $streamsend_form .= '<input id="streamsend-extraval-'.$f.'"  class="streamsend-form-input" type="hidden" name="streamsend-extraval-group_ids[]"  value='.$f.'>';
            }
        }

	if( $captcha && !$widget )
	{
        $dir = plugin_dir_url( __FILE__ );
        $streamsend_form .= '<li class="streamsend-form-row-big"><table style="border-width:0px"><tr ><td class="captcha-td" style="border-width:0px;"><span class="captcha-text">Please enter the following:</span>';
$streamsend_form .= '</td><td class="captcha-td" style="border-width:0px"><img src="'.$dir.'/captcha/captcha.php?formtype='.$formtype.'" id="captcha" /></td></tr>

<tr><td class="captcha-td" style="border-width:0px">
<!-- CHANGE TEXT LINK -->
<a href="#" onclick="
    document.getElementById(\'captcha\').src=\''.$dir.'captcha/captcha.php?formtype=' . $formtype.'&\'+Math.random();
    document.getElementById(\'captcha-form\').focus();"
    id="change-image"><span class="captcha-text">Not readable? Change text.</span></a>

</td><td style="border-width:0px">
<span class="streamsend-required">*</span> <input type="text" name="captcha" id="captcha-form" autocomplete="off" /></td></tr></table><br/>
';			
	                $streamsend_form .= '</li>';

	}
        
        $streamsend_form .= '<li class="streamsend-form-row streamsend-form-row-last">';
        $streamsend_form .= '<span class="streamsend-form-label-required">';
        $streamsend_form .= '<span class="streamsend-required">*</span> required </span>';
        $streamsend_form .= '<input id="streamsend-form-submit" type="submit" name="streamsend_form_submit" value="' . $this->form_setup_settings['submit_txt'] . '">';
        $streamsend_form .= '</li>';
        $streamsend_form .= '</ul>';
        $streamsend_form .= '</form>';

        $streamsend_form .= '</div><!-- end .streamsend-wrap -->';

        $streamsend_form .= '</div><!-- end #streamsend-form -->';

        return $streamsend_form;

    } // end output_form

} // end Class Streamsend_Form


function set_html_content_type() {
return "text/html";
}