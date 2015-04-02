<?php

class Streamsend_API {

	/**
     * PROPERTIES
     */
    private $_login_id;
    private $ss_audience = 1;

	private $_APIkey;
	
	private $_headers;

	// base URL for API requests w/ trailing slash
    // gd php. $this->self self::$this
	const REQUEST_URL_BASE = 'https://app.streamsend.com/';

	// THE CONSTRUCTOR
	public function __construct( $_login_id, $_APIkey ) {

		// on construction, pass in public and private API keys, assign them to class properties, 
		$this->_login_id = $_login_id;
		$this->_APIkey = $_APIkey;
		
	} // end __construct()
	
	// THE DESTRUCTOR
	public function __destruct() {} // end destruct()

	/**
     * METHODS
     */

     function init_api(){
         # Customized variables ####################################
             # Streamsend API Key
             
             
             $login_id = $this->_login_id;
         $key      = $this->_APIkey;
         
         # initialize a new curl session
             $headers = array(
                 'Accept: application/xml',       # any data returned should be XML
                 'Content-Type: application/xml'  # any data we send will be XML
                 # include any additional headers here
                              );
         $ch = curl_init();
         
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_USERPWD, "$login_id:$key");
         
         return $ch;
     }

// XML Entity Mandatory Escape Characters
function xmlentities($string) {
	return str_replace ( array ( '&', '"', "'", '<', '>', '?' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string );
}

function xml_add_tagval($tag, $value){
	$xml = "<$tag>" . $this->xmlentities($value) . "</$tag>";
	return $xml;
}

        /**
     * list_groups
     * path: GET /#login_id/groups
     * get a basic listing of all active member groups for a single account.
     * @return array|mixed|string|\WP_Error : array
     */
	public function list_groups() {

 	$ch = $this->init_api();
	$audience_url = "https://app.streamsend.com/audiences/1/lists.xml";
	curl_setopt($ch, CURLOPT_URL, $audience_url);

	# execute the session and receive its response
  	$xml_response = curl_exec($ch);
  	$xml_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 	 # return all options into an associative array
  	$info = curl_getinfo($ch);

         // should probaly check if there's an error
     $groups = simplexml_load_string($xml_response);
     if( $groups->error )
         return false;
     $response = array();
     
     if( isset( $groups ) )
     {
         foreach( $groups->list as $group ) {
                 // format the response as an array of key value pairs, member_group_id => group_name
             $response[ (string)$group->id ] = (string)$group->name;
         }
     }
		
     return $response;
		
	} // end list_groups



	public function list_fields() {

        $ss_audience= $this->ss_audience;
 	$ch = $this->init_api();
	$audience_url = "https://app.streamsend.com/audiences/$ss_audience/fields.xml";
	curl_setopt($ch, CURLOPT_URL, $audience_url);

	# execute the session and receive its response
  	$xml_response = curl_exec($ch);
  	$xml_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 	 # return all options into an associative array
  	$info = curl_getinfo($ch);

         // should probaly check if there's an error
     $fields = simplexml_load_string($xml_response);
//     echo( $xml_response );
     $response = array();
     
     if( isset( $fields ) )
     {
         foreach( $fields->field as $field ) {
             $response[ (string)$field->slug ] = (string)$field->name;
         }
     }
     return $response;
		
	} // end list_fields


    
    public function person_find($ch, $email){
        $ss_audience= $this->ss_audience;
        
        $person_get_url = "https://app.streamsend.com/audiences/$ss_audience/people.xml?email_address=$email";
        curl_setopt($ch, CURLOPT_URL, $person_get_url);
        
//        # execute the session and receive its response
        $xml_response = curl_exec($ch);
        $xml_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        #Return the id of the person
        preg_match('/<id.*?>(.+?)<\/id>/', $xml_response, $matches);
        if(count($matches) > 1){
//            echo( "returning id $matches[1] <br><br>" );
            return $matches[1];
        } else {
            return null;
        }
    }

    function person_update($ch, $p_id, $fields, $data){
  
        $ss_audience= $this->ss_audience;
  
	$person_xml = "<person>";
	foreach($fields as $postVar => $ssVar){
		if(isset($data[$postVar])){ $person_xml .= $this->xml_add_tagval($ssVar,$data[$postVar]); }
		if(isset($data["fields"][$postVar])){ $person_xml .= $this->xml_add_tagval($ssVar,$data["fields"][$postVar]); }
	}

  	$person_xml .= "<activate>true</activate>" . 
    "<deliver-activation>false</deliver-activation>" . 
    "<deliver-welcome>false</deliver-welcome>" . 
    "</person>";	  

	$person_update_url = "https://app.streamsend.com/audiences/$ss_audience/people/$p_id";

	curl_setopt($ch, CURLOPT_URL, $person_update_url);

	# Post the xml
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $person_xml);

//	# execute the session and receive its response
	$xml_response = curl_exec($ch);
	$xml_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$xml_sent = htmlentities($person_xml);	

    return array( $xml_response, $xml_status, $xml_sent );

}


    function person_subscribe($ch, $id, $list_id){
        $ss_audience= $this->ss_audience;
        
        $person_subscribe_url = "https://app.streamsend.com/audiences/$ss_audience/memberships.xml";
        $subscribe_xml = "<membership><list-id>$list_id</list-id><person-id>$id</person-id></membership>";
        
        curl_setopt($ch, CURLOPT_URL, $person_subscribe_url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $subscribe_xml);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        
//        # execute the session and receive its response
            $xml_response = curl_exec($ch);
        $xml_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array( $xml_response, $xml_status );
    }



    function person_create($ch, $fields, $data){
        $ss_audience= $this->ss_audience;
	
	$person_xml = "<person>";
	foreach($fields as $postVar => $ssVar){
		if(isset($data[$postVar])){ $person_xml .= $this->xml_add_tagval($ssVar,$data[$postVar]); }
		if(isset($data["fields"][$postVar])){ $person_xml .= $this->xml_add_tagval($ssVar,$data["fields"][$postVar]); }
	}

	$person_xml .= "<activate>true</activate>" . 
    "<deliver-activation>false</deliver-activation>" . 
    "<deliver-welcome>false</deliver-welcome>" . 
    "</person>";

	$person_create_url = "https://app.streamsend.com/audiences/$ss_audience/people.xml";
	curl_setopt($ch, CURLOPT_URL, $person_create_url);

	# Post the xml
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $person_xml);

//	# execute the session and receive its response
	$xml_response = curl_exec($ch);
	$xml_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$xml_sent = htmlentities($person_xml);	
    $ch2 = $this->init_api();
    $i = 0;
    $p_id = 0;
    while( !$p_id && $i < 5 )
    {
            // this is dumb, but streamsend seems to not find the person immediately
        $p_id = $this->person_find($ch2, $data["email"]);
        $i++;
    }
 		curl_close($ch2);
    
    return $p_id;
}

    
	public function import_single_member( $data, $extrafields = array() ) {
		
            // encode the data, get it ready for transport.
        $email = $data["email"];
        $ch = $this->init_api();
        $p_id = $this->person_find($ch, $email);
        $fields = array(
            'email'=>'email_address',
//            'firstname'=>'first_name',
//            'lastname'=>'last_name',
            // 'company_name' => 'company',
            // 'phone' => 'phone-number',

                        );

        foreach( $extrafields as $val )
        {
            $fields[$val] = $val;
        }

//        # Create/Update The Person
        if( strcmp($p_id,'') == 0 ){
            $p_id = $this->person_create($ch, $fields, $data);
        } else {
            $this->person_update($ch, $p_id, $fields, $data);
        }
        
//        # Subscribe Them

        if( is_array( $data["group_ids"] ) )
        {
            foreach( $data["group_ids"]  as $list_id )
            {
                    // add to appropriate lists
                if( $list_id )
                {
                    $response = $this->person_subscribe($ch, $p_id, $list_id);
                    $myresponse = $response[1];
                }
            }
        }

        if( !isset( $myresponse ) )
        {
            if( $p_id )
                $myresponse = "201";
            else
                $myresponse = "error";
        }
 		curl_close($ch);
        $response_object = new stdClass();
        if( $myresponse == "201" || $myresponse == 422 ) // 422 is "already in list
        {
                // added okay
            $response_object->member_id = $p_id;
            $response_object->status = 'success';
        }
        else
        {
            $response_object->status = 'error' . print_r( $response, true );
        }
        return $response_object;
	} // end import_single_member()


function get_audience($ch){
        /// not used yet
	$audience_url = "http://app.streamsend.com/audiences.xml";
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	# execute the session and receive its response
  	$GLOBALS['xml_response'] = curl_exec($ch);
  	$GLOBALS['xml_status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

 	 # return all options into an associative array
  	$info = curl_getinfo($ch);
  	//print "audience reponse=" . $GLOBALS['xml_response'] . "<br/>\n";
  	//print "audience status=" . $GLOBALS['xml_status'] . "<br/>\n";
}



    
	/**
     * get_member_detail
     * path: GET /#login_id/members/#member_id
     * description: Get detailed information on a particular member, including all custom fields.
     * returns: A single member if one exists.
     * params: deleted (boolean) � Accepts True or 1. Optional flag to include deleted members.
     * raises: Http404 if no member is found.
     * @param $member_id
     * @return array|mixed|string|\WP_Error
     */
	public function get_member_detail( $member_id ) {
        $ss_audience= $this->ss_audience;

        $ch = $this->init_api();
        
        $person_get_url = "https://app.streamsend.com/audiences/$ss_audience/people/$member_id";
        curl_setopt($ch, CURLOPT_URL, $person_get_url);
        
        $xml_response = curl_exec($ch);
//        echo( $xml_response );
        $details = simplexml_load_string($xml_response);

        $arr = array();
        $attrs = $details->children();
        foreach( $attrs as $key=> $val )
        {
//            echo( "$key : $val<br>" );
            if( (string)$val )
            {
                $key = (string)$key;
                $key = str_replace( "-", "_", $key );
                $arr[(string)$key] = (string)$val;
            }
        }
        $arr["email"] = $arr["email_address"];
//        print_r( $arr );
        $object = json_decode(json_encode($arr), FALSE);
        
        return $object;
		
	} // end get_member_detail() 

} // end class Streamsend_API
