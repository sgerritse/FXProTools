<?php
/**
 * ----------------------------
 * Fxprotools - Cusom Functions
 * ----------------------------
 * All custom functions
 */

define('SKIP_PASSWORD_CHECKPOINT', false);

function get_user_checklist()
{
    $checklist = get_user_meta(get_current_user_id(), '_onboard_checklist', true);
    return is_array($checklist) ? $checklist : register_user_checklist(get_current_user_id());
}

function get_checklist_next_step_url()
{
    $checklist = get_user_checklist();
    foreach($checklist as $key => $value){
        if( empty($value) ){
            switch($key){
                case 'verified_email': return home_url() . '/verify-email/';
                case 'verified_profile': return home_url() . '/my-account/';
                case 'scheduled_webinar': return home_url() . '/coaching/';
                case 'accessed_products': return home_url() . '/access-products/';
                case 'got_shirt': return home_url() . '/free-shirt/';
                case 'shared_video': return home_url() . '/share-video/';
                case 'referred_friend': return home_url() . '/refer-a-friend/';
            }
        }
    }
    return '#';
}

function resend_email_verification()
{
    if( get_current_user_id() > 0){
        send_email_verification(get_current_user_id());
    }
}

function verify_email_address($verification_code)
{
    if( get_current_user_id() > 0)
    {
        $user = get_user_by('id', get_current_user_id() );
        $secret = "fxprotools-";
        $hash = MD5( $secret . $user->data->user_email);
        if($hash == $verification_code)
        {
            $checklist = get_user_checklist();
            $checklist['verified_email'] = true;
            update_user_meta( get_current_user_id(), '_onboard_checklist', $checklist );
            return true;
        } else{
            return false;
        }
    } else {
        return false;
    }
}


function random_checkout_time_elapsed(  $full = false)
{
    $now = new DateTime;
    $ago = new DateTime;
    $ago->modify("-" .  mt_rand(15, 3600) . " seconds");
    $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}



function get_customer_orders($user_id)
{
    $order_statuses = array('wc-on-hold', 'wc-processing', 'wc-completed', 'wc-pending', 'wc-cancelled', 'wc-refunded', 'wc-failed');
    $customer_user_id = $user_id;

    $customer_orders=get_posts( array(
            'meta_key' => '_customer_user',
            'meta_value' => $customer_user_id,
            'post_type' => 'shop_order',
            'post_status' => $order_statuses,
            'numberposts' => -1
    ) );
    return $customer_orders;
}

function get_order_columns()
{
    $my_orders_columns = apply_filters( 'woocommerce_my_account_my_orders_columns', array(
        'order-number'  => __( 'Order', 'woocommerce' ),
        'order-date'    => __( 'Date', 'woocommerce' ),
        'order-status'  => __( 'Status', 'woocommerce' ),
        'order-total'   => __( 'Total', 'woocommerce' ),
        'order-actions' => '&nbsp;',
    ) );
    return $my_orders_columns;
}

function get_purchased_items($user_id)
{
    $customer_orders = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $user_id,
        'post_type'   => wc_get_order_types( 'view-orders' ),
        'post_status' => array_keys( wc_get_order_statuses() ),
    ) ) );
    return $customer_orders;
}




/* -------------------------
    Actions and Filters
 --------------------------*/

add_action('wp', 'enforce_page_access');
function enforce_page_access()
{
    global $post;
    if( !isset($post) ) return;
    $slug = $post->post_name;
    $guest_allowed_pages = ThemeSettings::GUEST_ALLOWED_PAGES;

    if( is_user_logged_in() ) {
	    // only allow 'password-checkpoint' to be accessed by imported users that hasn't updated their password yet
	    if ( is_page( 'password-checkpoint' ) && ! has_imported_user_update_password() ) {
		    return 0;
	    }
	    // if the page being visited is not for public, and the user hasn't changed their password yet
	    if ( ! in_array( $slug, array_merge($guest_allowed_pages, [ 'my-account', 'inbox' ]) ) && ! has_imported_user_update_password() ) {
		    wp_redirect( '/password-checkpoint' );
		    exit;
	    }
	    // if the page being accessed are for logged out user or for users that has not updated their password yet, go to dashboard
	    if ( is_page( 'log-out-notice' ) || ( is_page( 'password-checkpoint' ) && has_imported_user_update_password() ) ) {
		    wp_redirect('/dashboard');
		    exit;
	    }
        return 0;
    }
    if( !is_product() && !is_cart() && !is_checkout() && !is_shop() && !is_404() && !is_front_page() ) {
        if( !in_array($slug, $guest_allowed_pages) ){
	        $args = [ 'redirect_to' => $slug ];
	        wp_redirect( home_url() . '/login/?' . http_build_query( $args ) );
	        exit;
        }
    }
}

add_filter('login_redirect', 'customer_login_redirect');
function customer_login_redirect( $redirect_to, $request = '', $user = '' ){
	if ( ! empty( $_POST['redirect_to'] ) ) {
		return home_url( $_POST['redirect_to'] );
	}
    return home_url('dashboard');
}

function has_imported_user_update_password( $user = null ) {
	if ( SKIP_PASSWORD_CHECKPOINT ) {
		return true;
	}

	if ( ! isset( $user ) ) {
		$user = wp_get_current_user();
	}
	$checkpoint_roles = [ 'holding_member', 'afl_member' ];
	foreach ( $checkpoint_roles as $checkpoint_role ) {
		if ( in_array( $checkpoint_role, (array) $user->roles ) ) {
			return get_user_meta( $user->ID, '_imported_user_password_changed', false );
		}
	}

	return true;
}


add_action('init', 'course_category_rewrite');
function course_category_rewrite()
{
    add_rewrite_rule('course-category/([^/]*)/?','index.php?category_slug=$matches[1]&course_category=1','top');
}

add_action('template_redirect', 'course_category_template');
function course_category_template()
{
    if ( get_query_var( 'category_slug' ) ) {
        add_filter( 'template_include', function() {
            return get_template_directory() . '/sfwd-course-category.php';
        });
    }
}

add_filter('query_vars', 'course_category_vars');
function course_category_vars( $vars )
{
    $vars[] = 'course_category';
    $vars[] = 'category_slug';
    return $vars;
}

add_action('user_register', 'register_user_checklist');
function register_user_checklist($user_id)
{
    $checklist = array(
        'verified_email' 	=> false,
        'verified_profile'	=> false,
        'scheduled_webinar'	=> false,
        'accessed_products' => false,
        'got_shirt'			=> false,
        'shared_video'		=> false,
        'referred_friend'	=> false,
    );
    add_user_meta( $user_id, '_onboard_checklist', $checklist);
}

add_action('user_register', 'send_email_verification');
function send_email_verification($user_id)
{
    $user = get_user_by('id', $user_id);
    $secret = "fxprotools-";
    $hash = MD5( $secret . $user->data->user_email);
    $to =  $user->data->user_email;
    $subject = 'Please verify your Email Address';
    $message = "Click <a href='" . home_url() . '/verify-email/?code=' . $hash . "' target='_blank'>here</a> to verify your email address.";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail( $to, $subject, $message, $headers );
}

add_action('user_register', 'register_affiliate');
function register_affiliate($user_id)
{
    $data = array('user_id' => $user_id, 'notes' => 'affiliate added via fxprotools');
    $affiliate_id = affwp_add_affiliate($data);
}

add_action('affwp_notify_on_approval', 'disable_affiliate_welcome_email');
function disable_affiliate_welcome_email()
{
    return false;
}

add_action('wp', 'track_user_history');
function track_user_history()
{
    if( is_user_logged_in() ){
        //delete_user_meta(get_current_user_id(), "track_user_history");
        $track_user_history = get_user_meta( get_current_user_id(), "track_user_history", true );
        $track_user_history = $track_user_history  ? $track_user_history : array();
        $link = '<a href="'. get_the_permalink() .'">' . get_the_permalink() . '</a>';
        if( isset($_POST['user_login']) ){
            $link = $link . " " . get_the_author_meta('first_name', get_current_user_id()) . " " . get_the_author_meta('last_name', get_current_user_id()) . " changed his username to " . $_POST['user_login'];
        }
        $data = array(
            'time' => date("Y-m-d h:i:sa"),
            'link' => $link,
            'title' => get_the_title()
        );
        array_push($track_user_history, $data);
        update_user_meta(get_current_user_id(), 'track_user_history', $track_user_history);
    }

}

add_action( 'show_user_profile', 'add_extra_profile_fields' );
add_action( 'edit_user_profile', 'add_extra_profile_fields' );
function add_extra_profile_fields( $user ) { ?>
    <h3>Extra profile information</h3>
    <table class="form-table">
        <tr>
            <th><label for="user_sms_subs">SMS/Text Messaging</label></th>
            <td>
                <select id="user_sms_subs" name="user_sms_subs">
                    <option value="no" <?php if(get_the_author_meta( 'user_sms_subs', $user->ID ) == "no"){echo 'selected';} ?>>no</option>
                    <option value="yes" <?php if(get_the_author_meta( 'user_sms_subs', $user->ID ) == "yes"){echo 'selected';} ?>>yes</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="user_email_subs">Email Updates</label></th>
            <td>
                <select id="user_email_subs" name="user_email_subs">
                    <option value="no" <?php if(get_the_author_meta( 'user_email_subs', $user->ID ) == "no"){echo 'selected';} ?>>no</option>
                    <option value="yes" <?php if(get_the_author_meta( 'user_email_subs', $user->ID ) == "yes"){echo 'selected';} ?>>yes</option>
                </select>
            </td>
        </tr>
    </table>
<?php }

add_action( 'personal_options_update', 'save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_profile_fields' );
function save_extra_profile_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) )
        return false;

    update_usermeta( $user_id, 'user_sms_subs', $_POST['user_sms_subs'] );
    update_usermeta( $user_id, 'user_email_subs', $_POST['user_email_subs'] );
}

add_action("wp_ajax_check_valid_username", "check_valid_username");
add_action("wp_ajax_nopriv_check_valid_username", "check_valid_username");
function check_valid_username()
{
    $new_username = $_REQUEST['new_username'];
    if (validate_username($new_username) && !username_exists($new_username))
    {
        if(strlen($new_username) <= 30 && strlen($new_username) >= 3 && preg_match("/^([[:alnum:]])*$/", $new_username))
        {
            echo "1";
        }
        else
        {
            echo "2";
        }
    }
    else{
        if(!strlen($new_username) <= 30 || !strlen($new_username) >= 3 || !preg_match("/^([[:alnum:]])*$/", $new_username))
        {
            echo "2";
        }
        else
        {
            echo "0";
        }
    }

    wp_die();
}
function sess_start() {
    if (!session_id())
    session_start();
}
add_action('init','sess_start');

/**
 * Use to render metabox page template option #1
 * @param  string $page_element
 */

 // TODO: change the function name to get_mb_pto later on
function get_mb_pto1( $page_element, $pto = 'pto1' ) {
    switch ( $page_element ) {
        case 'main_header_menu':
            return mb_menu_display( rwmb_meta( $pto . '_display_main_header_menu'), rwmb_meta( $pto . '_main_header_menu'), 'fx-nav-options', new Nav_Main_Header_Menu_Walker(), 'Main Header Menu', '' );
            break;
        case 'secondary_header_menu':
            return mb_menu_display( rwmb_meta( $pto . '_display_header_menu'), rwmb_meta( $pto . '_secondary_header_menu'), 'fx-nav-options', new Nav_Secondary_Header_Menu_Walker(), 'Dashboard Secondary Menu', '' );
            break;
        case 'footer_left_menu':
            return mb_menu_display( rwmb_meta( $pto . '_display_footer_menu'), rwmb_meta( $pto . '_footer_menu_fl'), 'footer-nav', '', 'Footer Menu 1', '' );
            break;
        case 'footer_middle_menu':
            return mb_menu_display( rwmb_meta( $pto . '_display_footer_menu'), rwmb_meta( $pto . '_footer_menu_mid'), 'footer-nav', '', 'Footer Menu 2', '' );
            break;
        case 'footer_right_menu':
            return mb_menu_display( rwmb_meta( $pto . '_display_footer_menu'), rwmb_meta( $pto . '_footer_menu_fr'), 'footer-nav', '', 'Footer Menu 3', 'with-log-inout' );
            break;
        case 'video_embed':
            // pto1 and pto2 only has support for video tab
            if ( $pto == 'pto1' ) :
                $video_url              = is_user_fx_customer() ? rwmb_meta( $pto . '_video_url_customer') : rwmb_meta( $pto . '_video_url_distributor') ;
            elseif ( $pto == 'pto2' ) :
                $video_url = rwmb_meta( $pto . '_video_url');
            endif;
            $video_autostart        = rwmb_meta( $pto . '_video_autostart');
            $video_show_controls    = rwmb_meta( $pto . '_video_show_controls');
            $scroll_class           = "";
            $scroll_url             = "";
            $float_class            = "";

            if( count( is_mb_video_scroll( $pto ) ) > 0 ){
                $arr_scroll = is_mb_video_scroll( $pto );
                $scroll_class   = ( !empty( rtrim($arr_scroll[0]) ) ) ? $arr_scroll[0] : '';
                $scroll_url     = ( !empty( rtrim($arr_scroll[1]) ) ) ? $arr_scroll[1] : '';
            }

            if( count( is_mb_video_float( $pto ) ) > 0 ){
                $arr_float = is_mb_video_float( $pto );
                $float_class    = $arr_float[0];
            }

            $html = '<div class="fx-video-container" id="' . $float_class . '" data-ptoaction="' . $scroll_class . '" data-ptoautostart="' . implode(' ', $video_autostart) . '" data-ptoshowcontrols="' . implode(' ', $video_show_controls) . '" data-ptourl="' . $scroll_url . '">';
            $html .= ( !empty($scroll_class) ) ? '' : wp_oembed_get($video_url) ;
            $html .= '</div>';
            
            return $html;
            
            break;
        default:
            # code...
            break;
    }
}


function is_mb_video_scroll( $pto = 'pto1' ){
    $video_scrolling = implode( ' ', rwmb_meta( $pto . '_video_scrolling') );
    if( !empty( rtrim($video_scrolling) ) && $video_scrolling == 'yes' ) 
        return array('pto--scrolling-video', rwmb_meta( $pto . '_video_url'));
}

function is_mb_video_float( $pto = 'pto1' ){
    $video_floating = implode( ' ', rwmb_meta( $pto . '_video_floating') );
    if( !empty( rtrim($video_floating) ) && $video_floating == 'yes' )
        return array('pto--floating-video');
}

function mb_menu_display( $display, $menu, $menu_class = '', $walker = '', $fallback, $location = '' ) {
    // menu fallback
    $menu_fb = $fallback;
    // check for menu display value
    if( !empty( rtrim( $display ) ) ){
        if( rtrim( $display ) == 'yes' ){
            if( $menu ){
                $term_id = $menu->term_id;
                $params = array(
                    'menu'            => $term_id,
                    'theme_location'  => '',
                    'container'       => false,
                    'container_class' => '',
                    'container_id'    => '',
                    'menu_id'         => $term_id,
                    'menu_class'      => $menu_class,
                    'echo'            => true,
                    'fallback_cb'     => 'wp_page_menu',
                    'before'          => '',
                    'after'           => '',
                    'link_before'     => '',
                    'link_after'      => '',
                    'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'depth'           => 0,
                    'walker'          => $walker,
                    'theme_location'  => $location,
                );
                return wp_nav_menu( $params );
            }else return wp_nav_menu( array('menu' => $menu_fb,'menu_class' => $menu_class, 'walker' => $walker ) );
        }else return;
    }
    // if menu display is default
    else{
        return wp_nav_menu( array('menu' => $menu_fb,'menu_class' => $menu_class, 'walker' => $walker ) );
    }
}

// Menu locations
add_action( 'init', 'register_my_menus' );
function register_my_menus() {
    register_nav_menus(
        array(
            'with-log-inout' => __( 'with Login-Logout' ),
        )
    );
}

// Login / Logout menu
add_filter( 'wp_nav_menu_items', 'add_login_logout_link', 10, 2 );
function add_login_logout_link( $items, $args ) {
    if( $args->theme_location == 'with-log-inout' ){
        ob_start();
        wp_loginout('index.php');
        $loginoutlink = ob_get_contents();
        ob_end_clean();
        $items .= '<li>'. $loginoutlink .'</li>';
        return $items;
    }
    return $items;
}

function get_emails_for_user($statuses, $user_id = null)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $response = get_posts(array(
        'posts_per_page'	=> -1,
        'orderby'			=> 'modified',
        'order'				=> 'DESC',
        'post_type'			=> 'fx_email',
        'meta_key'			=> '_user_' . $user_id . '_state',
        'meta_query'		=> array(
            array(
                'key'       => '_user_' . $user_id . '_state',
                'value'     => $statuses,
                'compare'   => 'IN',
            )
        )
    ));

    return $response;
}

function get_users_who_ordered($product_ids, $user_fields = array('user_email'))
{
    global $wpdb;
    $select = [];

    foreach ($user_fields as $field) {
        $select[] = 'users.' . $field . ' as ' . $field;
    }

    $select = implode(', ', $select);
    $ids = implode(',', $product_ids);

    $results = $wpdb->get_results($sql = "SELECT DISTINCT {$select}
        FROM {$wpdb->prefix}woocommerce_order_items as order_items
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        INNER JOIN {$wpdb->prefix}postmeta as user_id ON user_id.post_id = order_items.order_id AND user_id.meta_key = '_customer_user'
        INNER JOIN {$wpdb->users} as users ON users.ID = user_id.meta_value
        INNER JOIN {$wpdb->posts} as posts ON posts.ID = order_items.order_id AND post_type = 'shop_order'
        WHERE order_items.order_item_type = 'line_item'
        AND order_item_meta.meta_key = '_product_id'
        AND order_item_meta.meta_value IN ($ids)
    ");

    return $results;
}

function get_users_with_active_subscriptions($subscription_ids, $user_fields = array('user_email'))
{
    global $wpdb;
    $select = [];

    foreach ($user_fields as $field) {
        $select[] = 'users.' . $field . ' as ' . $field;
    }

    $select = implode(', ', $select);
    $ids = implode(',', $subscription_ids);

    $results = $wpdb->get_results($sql = "SELECT DISTINCT {$select}
        FROM {$wpdb->prefix}woocommerce_order_items as order_items
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        INNER JOIN {$wpdb->prefix}postmeta as user_id ON user_id.post_id = order_items.order_id AND user_id.meta_key = '_customer_user'
        INNER JOIN {$wpdb->users} as users ON users.ID = user_id.meta_value
        INNER JOIN {$wpdb->posts} as posts ON posts.ID = order_items.order_id AND post_status = 'wc-active' AND post_type = 'shop_subscription'
        WHERE order_items.order_item_type = 'line_item'
        AND order_item_meta.meta_key = '_product_id'
        AND order_item_meta.meta_value IN ($ids)
    ");

    return $results;
}

// redirect to custom login page instead of wordpress page
add_action('wp_login_failed', 'custom_redirect_login_failed');
function custom_redirect_login_failed($username) {
    $args = [
        'login' => 'failed',
        'username' => $username
    ];
	if ( isset( $_REQUEST['redirect_to'] ) ) {
		$args['redirect_to'] = urlencode( $_REQUEST['redirect_to'] );
	}
    wp_redirect(get_bloginfo('url') . '/login?' . http_build_query($args));
}

// redirects the user to dashboard if already logged in and went to /login
add_action( 'wp', 'check_if_logged_in' );
function check_if_logged_in() {
    if ( is_user_logged_in() && is_page('login' ) ) {
        wp_redirect( '/dashboard' );
        exit;
    }
}

// redirect to homepage after logging out
add_action('wp_logout','confirm_logout');
function confirm_logout(){
    wp_safe_redirect( '/log-out-notice' );
    exit();
}

// redirect to /login when accessing wp-login.php
add_action('init','redirect_to_login');
function redirect_to_login(){
    global $pagenow;
	if ( 'wp-login.php' == $pagenow && isset($_GET['action']) && $_GET['action'] != 'logout' ) {
		if ( is_user_logged_in() ) {
			wp_redirect( '/dashboard' );
			exit();
		} else {
			// if not submitting login credentials
			if ( empty( $_POST ) ) {
				wp_redirect( '/login' );
				exit();
			}
		}
	}
}

// Button Shortcode
add_shortcode('fx-button', 'fx_shortcode_buton');
function fx_shortcode_buton($atts, $content = null)
{
    // Extract shortcode attributes
    extract(shortcode_atts(array(
        'url'    => '',
        'title'  => '',
        'target' => '',
        'text'   => '',
        'class'  => '',
    ), $atts ));

    $content = $text ? $text : $content;

    if($url){
        $link_attr = array(
            'href'   => esc_url( $url ),
            'title'  => esc_attr( $title ),
            'target' => ('blank' == $target) ? '_blank' : '',
            'class'  => 'btn btn-danger '.$class
        );
        $link_attrs_str = '';
        foreach($link_attr as $key => $val){
            if($val){
                $link_attrs_str .= ' '. $key .'="'. $val .'"';
            }
        }
        return '<a'.$link_attrs_str.'>'.do_shortcode($content).'</a>';
    } else {
        return '<a href="#" class="btn btn-danger">'.do_shortcode($content).'</a>';
    }
}

// redirect to custom logout confirmation page
add_action( 'login_form_logout', 'custom_logout_notice' );
function custom_logout_notice() {
    if ( ! is_user_logged_in() ) {
        wp_redirect( '/login' );
        exit();
    }
    wp_logout();
    exit();
}

/**
* Check if user has active subscription
* @see class Apyc_User
* @param	$user_id	integer		the user id, if its null we get the current user id loged in
* @return boolean	| 	Apyc_User method hasActiveSubscription()
**/
if ( ! function_exists('apyc_has_active_user_subscription')) {
    function apyc_has_active_user_subscription ($user_id = null)  {
        return Apyc_User::get_instance()->hasActiveSubscription($user_id);
    }
}
