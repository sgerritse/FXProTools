<?php 
	/**
	 * Plugin Name: EPS-Affiliates
	 * Plugin URI: https://epixelmlmsoftware.com
	 * Description: Affiliate plans and configurations of Epixelmlm for WordPress
	 * Author: EPIXEL SOLUTIONS
	 * Author URI: https://epixelsolutions.com
	 * Version: 2.1.2
	 * Text Domain: eps-affiliates
	 * Domain Path: languages
	 *
	 * EPS-Affiliates is contains over all functionalities of the site which uses the 
	 * matrix plan.
	 *
	 * @package EPS-Affiliates
	 * @category Core
	 * @author < pratheesh@epixelsolutions.com >
	 * @version 1.0
 */

	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) exit;



	if ( ! class_exists( 'Eps_affiliates' ) ) :
	/**
	 * -------------------------------------------------------------------------
	 * Main Eps_affiliates Class
	 *
	 * SINGLETON OBJECT
	 * -------------------------------------------------------------------------
	 *
	 * @since 1.0
	 *
	*/
	final class Eps_affiliates {
		/**
		 * -------------------------------------------------------------------------
		 * Eps_affiliates instance.
		 * -------------------------------------------------------------------------
		 *
		 * @access private
		 * @since  1.0
		 * @var    Eps_plan The one true Eps_plan
		 *
		*/
			private static $instance;

		/**
		 * -------------------------------------------------------------------------
		 * Eps_affiliates Version.
		 * -------------------------------------------------------------------------
		 *
		 * @access private
		 * @since  1.0
		 * @var    string
		 *
		*/
			private $version = '1.0';

		/**
		 * -------------------------------------------------------------------------
		 * Eps_affiliates Plan instance Variable.
		 * -------------------------------------------------------------------------
		 *
		 * @access private
		 * @since  1.0
		 * @var    string
		 *
		*/
			private $afl_plan;
		/**
		 * -------------------------------------------------------------------------
		 * The capabilities (Permissions) class instance variable.
		 * -------------------------------------------------------------------------
		 *
		 * @access public
		 * @since  1.0
		 * @var    Eps_affiliates_Capabilities
		 *
	 	*/
			public $capabilities;
		/**
		 * -------------------------------------------------------------------------
		 * Database migation version
		 * -------------------------------------------------------------------------
		 *
		 * @access public
		 * @since  1.0
		 *
	 	*/
			public $db_version = '1.0';
			
		/**
		 * -------------------------------------------------------------------------
		 * 	Main Eps_affiliates Instance
		 * -------------------------------------------------------------------------
		 *
		 * Insures that only one instance of Eps_affiliates exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0
		 * @static
		 * @staticvar array $instance
		 * @uses Eps_affiliates::setup_globals() Setup the globals needed
		 * @uses Eps_affiliates::includes() Include the required files
		 * @uses Eps_affiliates::setup_actions() Setup the hooks and actions
		 * @uses Eps_affiliates::updater() Setup the plugin updater
		 * @return Eps_affiliates
		 *
	  */
			public static function instance() {
				if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Eps_affiliates ) ) {
					self::$instance = new Eps_affiliates;

					if( version_compare( PHP_VERSION, '5.3', '<' ) ) {

						add_action( 'admin_notices', array( 'Eps_affiliates', 'below_php_version_notice' ) );

						return self::$instance;

					}

					self::$instance->setup_constants();
					self::$instance->includes();

					add_action( 'plugins_loaded', array( self::$instance, 'setup_objects' ), -1 );
					add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
				}
				return self::$instance;
			}
		/**
		 * -------------------------------------------------------------------------
		 * Throw error on object clone
		 * -------------------------------------------------------------------------
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 *
	 */
			public function __clone() {
				// Cloning instances of the class is forbidden
				_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'eps-affiliates' ), '1.0' );
			}
		/**
		 * -------------------------------------------------------------------------
		 * Disable unserializing of the class
		 * -------------------------------------------------------------------------
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 *
	 	*/
			public function __wakeup() {
				// Unserializing instances of the class is forbidden
				_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'eps-affiliates' ), '1.0' );
			}
	 	/**
		 * -------------------------------------------------------------------------
		 * Show a warning to sites running PHP < 5.3
		 * -------------------------------------------------------------------------
		 * @static
		 * @access private
		 * @since 1.0
		 * @return void
	 	*/
			public static function below_php_version_notice() {
				echo '<div class="error"><p>' . __( 'Your version of PHP is below the minimum version of PHP required by Eps_affiliates. Please contact your host and request that your version be upgraded to 5.3 or later.', 'eps-affiliates' ) . '</p></div>';
			}

		/**
		 * -------------------------------------------------------------------------
	 	 * Setup plugin constants
		 * -------------------------------------------------------------------------
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 *
	 	*/
			private function setup_constants() {
				// Plugin version
				if ( ! defined( 'EPSAFFILIATE_VERSION' ) ) {
					define( 'EPSAFFILIATE_VERSION', $this->version );
				}

				// data base migation version
				if ( ! defined( 'EPSAFFILIATE_DB_VERSION' ) ) {
					define( 'EPSAFFILIATE_DB_VERSION', $this->db_version );
				}

				// Plugin Folder Path
				if ( ! defined( 'EPSAFFILIATE_PLUGIN_DIR' ) ) {
					define( 'EPSAFFILIATE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
				}

				// Plugin Folder URL
				if ( ! defined( 'EPSAFFILIATE_PLUGIN_URL' ) ) {
					define( 'EPSAFFILIATE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
				}

				// Plugin Root File
				if ( ! defined( 'EPSAFFILIATE_PLUGIN_FILE' ) ) {
					define( 'EPSAFFILIATE_PLUGIN_FILE', __FILE__ );
				}

				// Plugin Assets path
				if ( ! defined( 'EPSAFFILIATE_PLUGIN_ASSETS' ) ) {
					define( 'EPSAFFILIATE_PLUGIN_ASSETS', plugin_dir_url('eps-affiliates/assets/css',__FILE__));
				}

				// Make sure CAL_GREGORIAN is defined.
				if ( ! defined( 'CAL_GREGORIAN' ) ) {
					define( 'CAL_GREGORIAN', 1 );
				}
			}
		
		/**
		 * -------------------------------------------------------------------------
		 * Include required files for eps-affiliates plans
		 * -------------------------------------------------------------------------
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 *
	 	*/
			private function includes() {
				//common functions callback
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/class.common.php';

				//system permissions 
				require_once EPSAFFILIATE_PLUGIN_DIR . 'eps-permissions.php';
				
				//all the hooks
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/action-hooks.php';
				
				//query variables
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/class-query.php';

				//required conditions when install
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/install.php';

				//required conditions when un-install
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/un-install.php';
				
				//route
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/class-route-url.php';
				
				// save details to table @ user registers
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/class-user-register.php';

				//afl dashboard menus registration
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/class-dashboard-menus.php';
				
				//permissions
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/class-capabilities.php';
				
				//admin menus
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/admin/class-menu.php';

				/* ------Here comes all the admin menu callback functions : Begin ------------*/
				//Menu callbacks for advanced configuration
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/admin/menu_callback/menu-advanced-conf.php';
				// Menu callback for compensation plan
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/admin/menu_callback/menu-compensation-plan-conf.php';
				// Menu callback for roles and permission settings
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/admin/menu_callback/menu-roles-nd-permission-conf.php';
<<<<<<< HEAD
				//Menu callback add business system memebers
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/admin/menu_callback/menu-business-system-members.php';
=======
>>>>>>> a3eb117dca110ed02010bf0895e5c78cdae5e735

				// Menu callback for rank configuration 

				/* ------ Here comes all the member menu callback : Begin  -----------------*/
				//add new member
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/member/menu_callback/menu-add-new-member-callback.php';
				
				/* ------ Here comes all the member menu callback : End  -------------------*/
				

					// Menu callback for rank configuration 
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/admin/menu_callback/menu-rank-conf.php';
				// Menu callback for Pool Bonus configuration 
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/admin/menu_callback/menu-pool-bonus-conf.php';

				//common files callback
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/class.common.php';
				/* ------Here comes all the menu callback functions : End ------------*/

				//install tables
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/class.tables.php';

				//eps-afl-dashboard menus templates
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/eps-template-hooks.php';
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/eps-template-functions.php';

				//page function 
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/eps-page-functions.php';
				//ajax callbacks
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/eps-ajax-callbacks.php';
<<<<<<< HEAD
				//member registration
				require_once EPSAFFILIATE_PLUGIN_DIR . 'inc/class-eps-affiliates-registration.php';
				
					
=======
				
>>>>>>> a3eb117dca110ed02010bf0895e5c78cdae5e735



			}
		/**
		 * -------------------------------------------------------------------------
		 * Setup all objects
		 * -------------------------------------------------------------------------
		 *
		 * @access public
		 * @since 1.0
		 * @return void
		 *
	 	*/
			public function setup_objects() {
				self::$instance->capabilities   = new Eps_affiliates_Capabilities;
			}
		/**
		 * -------------------------------------------------------------------------
		 * Loads the plugin language files
		 * -------------------------------------------------------------------------
		 *
		 * @access public
		 * @since 1.0
		 * @return void
		 *
	 	*/
		 	public function load_textdomain() {

		 	}

	}
endif; // End if class_exists check

/**
	* -------------------------------------------------------------------------
	* The main function responsible for returning the one true Eps_affiliates
 	* Instance to functions everywhere.
 	*
 	* Use this function like you would a global variable, except without needing
 	* to declare the global.
 	*
 	* Example: <?php $eps_affiliate = eps_affiliate(); ?>
 	*
 	* @since 1.0
 	* @return Eps_affiliates The one true Eps_affiliates Instance
 	*
*/
function eps_affiliate() {
	return Eps_affiliates::instance();
}
/*
 * -------------------------------------------------------------------------
 * Custom print function
 * -------------------------------------------------------------------------
*/
	function pr($data = array(), $ex = FALSE){
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		if ($ex){
			exit();
		}
	}
eps_affiliate();
?>