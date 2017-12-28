<?php

use Intercom\IntercomClient;
use Intercom\IntercomUsers;
use Intercom\IntercomLeads;
use Intercom\IntercomEvents;
use Intercom\Model\CPS_Intercom_Model;

use GuzzleHttp\Exception\GuzzleException;

class CPS_Intercom {

	const ACCESS_TOKEN = 'dG9rOmUxMzMyODcyX2UxMGRfNDZmOF84ZjM5XzY4MTc1MWJiNTBmNzoxOjA=';
	const SECRET_KEY = 'l_-sHsUYbgK3VTBs9AoKgG7kBc1fMAT7fnEgIt1A';
	const HASH = 'sha256';
	const INTERCOM_ID_USER_META = '_intercom_user_id';
	const EVENT_REGISTER_USER = 'register-user';
	const EVENT_UPDATE_PROFILE = 'update-profile';
	const UID_TEMPLATE = '%s?%s';
	const INTERCOM_SWITCH_PAGE = '/intercom-switch';

	/** @var array */
	private $user_roles = [
		'administrator',
		'editor',
		'author',
		'contributor',
		'shop_manager',
		'group_leader',
		'business_admin',
		'business_director',
	];

	/** @var array */
	private $lead_roles = [
		'subscriber',
		'customer',
		'holding_member',
		'afl_member',
		'afl_customer',
	];

	/** @var IntercomClient */
	private $client;

	/**
	 * CPS_Intercom constructor.
	 */
	public function __construct() {
		$this->client = new IntercomClient( self::ACCESS_TOKEN, null );
//		add_action( 'user_register', [ $this, 'intercom_add_user' ] );
		add_action( 'profile_update', [ $this, 'intercom_update_user' ] );
		add_action( 'delete_user', [ $this, 'intercom_delete_user' ] );
	}

	/**
	 * @param $user
	 *
	 * @return false|null|string
	 */
	public static function get_user_intercom_HMAC( $user ) {
		if ( $user ) {
			return hash_hmac(
				self::HASH, // hash function
				$user->user_email,
				self::SECRET_KEY
			);
		}
		return null;
	}

	/**
	 * Creates an intercom account
	 *
	 * @param $user_id int
	 */
	public function intercom_add_user( $user_id ) {
		if ( ! empty( $_POST ) ) {
			/**
			 * @var $role string
			 */
			extract( $_POST );
			if ( in_array( $role, $this->user_roles ) ) {
				$user_data = $this->generate_data( 'user', $user_id );
				$intercomUser = $this->create_user( $user_data );
				if ($intercomUser) {
					add_user_meta( $user_id, self::INTERCOM_ID_USER_META, $intercomUser->id );
					$this->create_event( self::EVENT_REGISTER_USER, $user_id );
				}
				return;
			}

			if ( in_array( $role, $this->lead_roles ) ) {
				$lead = new IntercomLeads( $this->client );

				$lead_data = $this->generate_data( 'lead' );
				try {
					$lead->create( $lead_data );
				} catch ( GuzzleException $e ) {
					error_log( $e->getMessage() );
				}
				$this->create_event( 'register-lead', $user_id );
				return;
			}
		}
	}

	/**
	 * @param $user_id int
	 */
	public function intercom_update_user( $user_id ) {
		$user_data = get_userdata( $user_id );
		$user_meta = $this->flatten_user_meta( $user_id );
		$user_onboard_checklist = $this->get_onboard_checklist( $user_id );
		$user_info = array_merge( (array) $user_data->data, $user_meta, $user_onboard_checklist );

		$intercom_data = $this->arrange_intercom_data( $user_info );
		$intercomUser = $this->create_user( $intercom_data );

		if ( ! isset( $user_meta[ self::INTERCOM_ID_USER_META ] ) ) {
			add_user_meta( $user_id, self::INTERCOM_ID_USER_META, $intercomUser->id );
		}

		$this->create_event( self::EVENT_UPDATE_PROFILE, $user_id );
	}

	public function intercom_delete_user( $user_id ) {
		// use intercom ID instead of user ID to delete only those who have intercom account
		$intercom_user_id = get_user_meta( $user_id, self::INTERCOM_ID_USER_META, true );
		if ( ! empty( $intercom_user_id ) ) {
			$this->delete_user( $intercom_user_id );
		}
	}

	/**
	 * @param array $data
	 *
	 * @return IntercomUsers/void
	 */
	private function create_user( array $data ) {
		$user = new IntercomUsers( $this->client );
		try {
			/** @var IntercomUsers */
			return $user->create( $data );
		} catch ( GuzzleException $e ) {
			error_log( $e->getMessage() );
		}
	}

	/**
	 * @param $id
	 *
	 * @return IntercomUsers/void
	 */
	private function delete_user( $id ) {
		$user = new IntercomUsers( $this->client );
		try {
			/** @var IntercomUsers */
			return $user->deleteUser( $id );
		} catch ( GuzzleException $e ) {
			error_log( $e->getMessage() );
		}
	}

	/**
	 * @param $event_name
	 * @param $user_id
	 */
	private function create_event( $event_name, $user_id ) {
		$event = new IntercomEvents( $this->client );
		try {
			$event->create( [
				CPS_Intercom_Model::KEY_EVENT_NAME => $event_name,
				CPS_Intercom_Model::KEY_CREATED_AT => strtotime( "now" ),
				CPS_Intercom_Model::KEY_USER_ID    => $user_id,
			] );
		} catch ( GuzzleException $e ) {
			error_log( $e->getMessage() );
		}
	}

	/**
	 * @param string $type
	 * @param null $user_id
	 *
	 * @return array
	 */
	private function generate_data( $type, $user_id = null ) {
		$data = [];

		/**
		 * @var $email string
		 * @var $first_name string
		 * @var $last_name string
		 */
		extract( $_POST );

		switch ( $type ) {
			case 'user' :
				if ( ! empty( $_POST ) ) {
					$data = [
						CPS_Intercom_Model::KEY_EMAIL        => $email,
						CPS_Intercom_Model::KEY_USER_ID      => $user_id,
						CPS_Intercom_Model::KEY_NAME         => $first_name . ' ' . $last_name,
						CPS_Intercom_Model::KEY_SIGNED_UP_AT => strtotime( "now" ),
					];
				}
				break;
			case 'lead':
				if ( ! empty( $_POST ) ) {
					$data = [
						CPS_Intercom_Model::KEY_EMAIL => $email,
						CPS_Intercom_Model::KEY_NAME  => $first_name . ' ' . $last_name,
					];
				}
				break;
		}

		return $data;
	}

	private function flatten_user_meta( $user_id ) {
		$user_meta = get_user_meta( $user_id );
		$data = [];
		foreach ( $user_meta as $meta_key => $value ) {
			$data[ $meta_key ] = $value[0];
		}

		return $data;
	}

	private function arrange_intercom_data( $data ) {
		return [
			CPS_Intercom_Model::KEY_USER_ID           => $data['ID'],
			CPS_Intercom_Model::KEY_EMAIL             => $data['user_email'],
			CPS_Intercom_Model::KEY_NAME              => $this->get_name( $data ),
			CPS_Intercom_Model::KEY_PHONE             => $data['phone_number'],
			CPS_Intercom_Model::KEY_SIGNED_UP_AT      => strtotime( $data['user_registered'] ),
			CPS_Intercom_Model::KEY_LAST_SEEN_IP      => $this->get_real_IP(),
			CPS_Intercom_Model::KEY_CUSTOM_ATTRIBUTES => $this->get_custom_attributes( $data ),
		];
	}

	private function get_custom_attributes( array $data ) {
		return [
			CPS_Intercom_Model::KEY_UID                         => $this->get_uid( $data ),
			CPS_Intercom_Model::KEY_NICKNAME                    => $data['nickname'],
			CPS_Intercom_Model::KEY_FIRST_NAME                  => $data['first_name'],
			CPS_Intercom_Model::KEY_LAST_NAME                   => $data['last_name'],
			CPS_Intercom_Model::KEY_USER_SMS_SUBS               => $data['user_sms_subs'],
			CPS_Intercom_Model::KEY_USER_EMAIL_SUBS             => $data['user_email_subs'],
			CPS_Intercom_Model::KEY_BILLING_COMPANY             => $data['billing_company'],
			CPS_Intercom_Model::KEY_BILLING_ADDRESS_1           => $data['billing_address_1'],
			CPS_Intercom_Model::KEY_BILLING_ADDRESS_2           => $data['billing_address_2'],
			CPS_Intercom_Model::KEY_BILLING_CITY                => $data['billing_city'],
			CPS_Intercom_Model::KEY_BILLING_STATE               => $data['billing_state'],
			CPS_Intercom_Model::KEY_BILLING_POSTCODE            => $data['billing_postcode'],
			CPS_Intercom_Model::KEY_SHIPPING_COMPANY            => $data['shipping_company'],
			CPS_Intercom_Model::KEY_SHIPPING_ADDRESS_1          => $data['shipping_address_1'],
			CPS_Intercom_Model::KEY_SHIPPING_ADDRESS_2          => $data['shipping_address_2'],
			CPS_Intercom_Model::KEY_SHIPPING_CITY               => $data['shipping_city'],
			CPS_Intercom_Model::KEY_SHIPPING_STATE              => $data['shipping_state'],
			CPS_Intercom_Model::KEY_SHIPPING_POSTCODE           => $data['shipping_postcode'],
			CPS_Intercom_Model::KEY_WEBSITE                     => $data['website'],
			CPS_Intercom_Model::KEY_FACEBOOK                    => $data['facebook'],
			CPS_Intercom_Model::KEY_TWITTER                     => $data['twitter'],
			CPS_Intercom_Model::KEY_GOOGLEPLUS                  => $data['googleplus'],
			CPS_Intercom_Model::KEY_CHECKLIST_VERIFIED_EMAIL    => $data['verified_email'],
			CPS_Intercom_Model::KEY_CHECKLIST_VERIFIED_PROFILE  => $data['verified_profile'],
			CPS_Intercom_Model::KEY_CHECKLIST_SCHEDULED_WEBINAR => $data['scheduled_webinar'],
			CPS_Intercom_Model::KEY_CHECKLIST_ACCESSED_PRODUCTS => $data['accessed_products'],
			CPS_Intercom_Model::KEY_CHECKLIST_GOT_SHIRT         => $data['got_shirt'],
			CPS_Intercom_Model::KEY_CHECKLIST_SHARED_VIDEO      => $data['shared_video'],
			CPS_Intercom_Model::KEY_CHECKLIST_REFERRED_FRIEND   => $data['referred_friend'],
		];
	}

	private function get_uid( $data ) {
		$args = [ CPS_Intercom_Model::KEY_UID => $data['ID'] ];
		return sprintf( self::UID_TEMPLATE, home_url( self::INTERCOM_SWITCH_PAGE ), http_build_query( $args ) );
	}

	private function get_onboard_checklist( $user_id ) {
		return get_user_meta( $user_id, ONBOARD_CHECKLIST_META_KEY, true );
	}

	private function get_name( $data ) {
		return sprintf( '%s %s', $data['first_name'], $data['last_name'] );
	}

	private function get_real_IP() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )   //check ip from share internet
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )   //to check ip is pass from proxy
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
}

return new CPS_Intercom();
