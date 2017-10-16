<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Woocommerce_Settings' ) ) {

	class Woocommerce_Settings {

		const META_ENABLE_BUY_BUTTON = '_enable_buy_button';
		const META_BUY_BUTTON_URL = '_buy_button_url';
		const META_BUY_BUTTON_TEXT = '_buy_button_text';

		public function __construct() {
			// Filters
			add_filter( 'woocommerce_checkout_fields', array( $this, 'wc_setup_checkout_fields' ) );
			add_filter( 'woocommerce_product_tabs', array( $this, 'wc_remove_product_tabs' ) );
			add_filter( 'woocommerce_product_additional_information_heading', array( $this, 'wc_remove_additional_info_heading' ) );
			add_filter( 'wc_authorize_net_cim_credit_card_payment_form_save_payment_method_checkbox_html', array( $this, 'wc_auth_net_cim_save_payment_method_default_checked' ), 10, 2 );
			add_filter( 'woocommerce_add_to_cart_redirect ', array( $this, 'wc_add_to_cart_redirect' ) );
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'wc_clear_cart' ) );
			add_filter( 'wc_add_to_cart_message_html', array( $this, 'wc_clear_add_to_cart_message' ) );
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'wc_add_buy_button_tab' ) );
			add_filter( 'woocommerce_breadcrumb_defaults', array( $this, 'wc_custom_breadcrumbs' ) );
			add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'wc_archive_custom_cart_button_text' ) );
			add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'wc_archive_custom_cart_button_url' ) );

			// Actions
			add_action( 'woocommerce_thankyou', array( $this, 'wc_after_checkout_redirect' ) );
			add_action( 'template_redirect', array( $this, 'wc_redirect_to_checkout_if_cart' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'wc_buy_button_tab_fields' ) );
			add_action( 'woocommerce_admin_process_product_object', array( $this, 'wc_save_buy_button_tab_fields' ) );
		}

		public function wc_setup_checkout_fields( $fields ) {
			unset( $fields['order']['order_comments'] );
			unset( $fields['billing']['billing_company'] );
			unset( $fields['billing']['billing_address_2'] );

			$fields['billing']['billing_first_name']['priority'] = 1;
			$fields['billing']['billing_last_name']['priority'] = 2;
			$fields['billing']['billing_email']['priority'] = 3;
			$fields['billing']['billing_phone']['priority'] = 4;
			$fields['billing']['billing_state']['priority'] = 5;
			$fields['billing']['billing_address_1']['priority'] = 6;
			$fields['billing']['billing_city']['priority'] = 8;
			$fields['billing']['billing_postcode']['priority'] = 9;
			$fields['billing']['billing_country']['priority'] = 11;
			$fields['email']['priority'] = 3;
			$fields['address_1']['priority'] = 6;
			$fields['address_2']['priority'] = 7;

			return $fields;
		}

		public function wc_remove_product_tabs( $tabs ) {
			unset( $tabs['additional_information'] );

			return $tabs;
		}

		public function wc_remove_additional_info_heading() {
			echo '';
		}

		public function wc_after_checkout_redirect( $order_id ) {
			$order = new WC_Order( $order_id );
			$url = home_url() . '/dashboard';
			if ( $order->get_status() != 'failed' ) {
				wp_redirect( $url );
				exit;
			}
		}

		public function wc_auth_net_cim_save_payment_method_default_checked( $html, $form ) {
			if ( empty( $html ) || $form->tokenization_forced() ) {
				return $html;
			}

			return str_replace( 'type="checkbox"', 'type="checkbox" checked="checked"', $html );
		}

		public function wc_add_to_cart_redirect( $wc_get_cart_url ) {
			return site_url( '#trial-products' );
		}


		public function wc_redirect_to_checkout_if_cart() {
			if ( ! is_cart() ) {
				return;
			}

			global $woocommerce;
			if ( sizeof( WC()->cart->get_cart() ) != 0 ) {
				wp_redirect( $woocommerce->cart->get_checkout_url(), '301' );
				exit;
			} else {
				wp_redirect( site_url( '#trial-products' ), '301' );
				exit;
			}
		}

		public function wc_clear_cart( $cart_item_data ) {
			global $woocommerce;
			$woocommerce->cart->empty_cart();

			return $cart_item_data;
		}

		public function wc_clear_add_to_cart_message( $message ) {
			return '';
		}

		/**
         * Custom breadcrumbs or shop and single page
		 * @return array
		 */
		public function wc_custom_breadcrumbs() {
			$link = is_shop() ? get_permalink( wc_get_page_id( 'shop' ) ) : get_permalink();
			$wrap_before = <<<HTML
<div class="navbar fx-navbar-sub">
    <ul class="fx-nav-options">
        <li class="dashboard">
            <a class="icon icon-share" href="{$link}">&nbsp;</a>
        </li>
HTML;

			$wrap_after = <<<HTML
    </ul>
</div>
HTML;

			return array(
				'delimiter'   => '',
				'wrap_before' => $wrap_before,
				'wrap_after'  => $wrap_after,
				'before'      => '',
				'after'       => '',
				'home'        => _x( 'Home', 'breadcrumb', 'woocommerce' ),
			);
		}

		/**
		 * Adds "Buy Button" tab in product data block
		 *
		 * @param $product_data_tabs
		 *
		 * @return mixed
		 */
		public function wc_add_buy_button_tab( $product_data_tabs ) {
			$product_data_tabs['buy-button'] = array(
				'label'    => __( 'Buy Button', 'woocommerce' ),
				'target'   => 'buy_button_product_data',
				'class'    => array(),
				'priority' => 25,
			);

			return $product_data_tabs;
		}

		public function wc_buy_button_tab_fields() {
			?>
            <div id='buy_button_product_data'
                 class='panel woocommerce_options_panel'> <?php
			?>
            <div class='options_group'> <?php

				// Create enable/disable
				woocommerce_wp_checkbox(
					array(
						'id'          => self::META_ENABLE_BUY_BUTTON,
						'label'       => __( 'Enable/Disable', 'woocommerce' ),
						'description' => __( 'Do you want to enable this custom buy button URL?', 'woocommerce' )
					)
				);

				// create Button URL
				woocommerce_wp_text_input(
					array(
						'id'          => self::META_BUY_BUTTON_URL,
						'label'       => __( 'Button URL', 'woocommerce' ),
						'desc_tip'    => 'true',
						'description' => __( 'link for the button.', 'woocommerce' )
					)
				);

				// Create Button Text
				woocommerce_wp_text_input(
					array(
						'id'          => self::META_BUY_BUTTON_TEXT,
						'label'       => __( 'Button Text', 'woocommerce' ),
						'desc_tip'    => 'true',
						'description' => __( 'text for the button.', 'woocommerce' )
					)
				);
				?> </div>

            </div><?php
		}

		/**
         * Save Buy Button tab fields as product meta
		 * @param $product
		 */
		public function wc_save_buy_button_tab_fields( $product ) {
			// Save enable/disable
			$enable_buy_button = isset( $_POST[ self::META_ENABLE_BUY_BUTTON ] ) ? 'yes' : 'no';
			update_post_meta( $product->get_id(), self::META_ENABLE_BUY_BUTTON, $enable_buy_button );

			// Save Button URL
			$buy_button_url = $_POST[ self::META_BUY_BUTTON_URL ];
			update_post_meta( $product->get_id(), self::META_BUY_BUTTON_URL, $buy_button_url );

			// Save Button Text
			$buy_button_text = $_POST[ self::META_BUY_BUTTON_TEXT ];
			update_post_meta( $product->get_id(), self::META_BUY_BUTTON_TEXT, $buy_button_text );
		}

		/**
         * Change shop/single button if Buy Button is enabled in backend
		 * @param $default
		 *
		 * @return string|void
		 */
		public function wc_archive_custom_cart_button_text( $default ) {
			$enable_custom_button = get_post_meta( get_the_ID(), self::META_ENABLE_BUY_BUTTON );
			if ( ! empty( $enable_custom_button ) && $enable_custom_button[0] == 'yes' ) {
				$buy_button_text = get_post_meta( get_the_ID(), self::META_BUY_BUTTON_TEXT );
				if ( ! empty( $buy_button_text ) ) {
					return __( $buy_button_text[0], 'woocommerce' );
				}
			}

			return $default;
		}

		/**
         * Change shop/single URL if Buy Button is enabled in backend
		 * @param $url
		 *
		 * @return mixed
		 */
		public function wc_archive_custom_cart_button_url( $url ) {
			$enable_custom_button = get_post_meta( get_the_ID(), self::META_ENABLE_BUY_BUTTON );
			if ( ! empty( $enable_custom_button ) && $enable_custom_button[0] == 'yes' ) {
				$buy_button_url = get_post_meta( get_the_ID(), self::META_BUY_BUTTON_URL );

				if ( ! empty( $buy_button_url ) ) {
					return $buy_button_url[0];
				}
			}

			return $url;
		}
	}
}

return new Woocommerce_Settings();