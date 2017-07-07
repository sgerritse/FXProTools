<?php
/**
 * ---------------------------------
 * Storefront Child - Theme Settings
 * ---------------------------------
 * Storefront child theme related settings
 */

if(!defined('ABSPATH')){
	exit;
}

if(!class_exists('CoreTheme')){

	class CoreTheme {
		
		public function __construct()
		{
			add_action('wp_enqueue_scripts', array($this, 'enqueue_theme_assets'));
		}

		// Theme Assets
		public function enqueue_theme_assets()
		{
			global $theme_version;
			// Disable loading of jquery on wordpress core
			if(!is_admin()){				
				wp_deregister_script('jquery'); 
				wp_deregister_script('wp-embed');
			}
			
			wp_enqueue_style('theme-style', get_template_directory_uri().'/style.css', $theme_version);
			wp_enqueue_script('theme-scripts',get_template_directory_uri().'/assets/js/bundle.js', $theme_version);
		}

	}

}

return new CoreTheme();