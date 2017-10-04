<?php
require ABSPATH . 'vendor/autoload.php';

if(!class_exists('FX_Sendgrid_Api')){

	class FX_Sendgrid_Api {

		const SENDGRID_API_KEY = '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';
		const SENDGRID_FXPROTOOLS_LIST_ID = '2027971';
		
		public function __construct()
		{
			
		}	
		// Get all contacts of a list
		public function get_contacts($list_id = self::SENDGRID_FXPROTOOLS_LIST_ID)
		{
			$sg = new \SendGrid( self::SENDGRID_API_KEY );
			$query_params = json_decode('{"page": 1, "page_size": 1, }');
			$response = $sg->client->contactdb()->lists()->_($list_id)->recipients()->get(null, $query_params);
			return $response->body();
		}

		public function add_recipient( $recipient = array() ){
			$sg = new \SendGrid( self::SENDGRID_API_KEY );
			$request_body = array( (object) $recipient );
			$response = $sg->client->contactdb()->recipients()->post($request_body);
			$response_body = json_decode($response->body());
			$recipient_id = $response_body->persisted_recipients[0];
			return $recipient_id;
		}

		public function add_recipient_to_list($recipient_id = '', $list_id = self::SENDGRID_FXPROTOOLS_LIST_ID)
		{
			if(!$recipient_id) return;

			$sg = new \SendGrid( self::SENDGRID_API_KEY );
			$request_body = array( $recipient_id );
			$response = $sg->client->contactdb()->lists()->_($list_id)->recipients()->post($request_body);
			return $response->statusCode();
		}
	}
}

//$recipient = array('email' => 'user'.rand().'@gmail.com', "first_name" => 'test', 'last_name' => 'test', 'campaign' => '123' );
//$recipient_id = FX_Sendgrid_Api::add_recipient($recipient);
//FX_Sendgrid_Api::add_recipient_to_list($recipient_id);