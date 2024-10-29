<?php
/*
 * Plugin Name: Anteraja
 * Plugin URI: https://wordpress.org/plugins/anteraja
 * Description: Integrated shipping plugin with Anteraja shipping company.
 * Version: 2.0
 * Author: www.epeken.com
 * Author URI: https://www.epeken.com
 *
 * */
if(!defined('ABSPATH')) exit;
add_action('admin_notices', 'epkn_antr_warning');
function epkn_antr_warning() {
 if(!is_admin())
	 return;
  if(!is_plugin_active('epeken-all-kurir/epeken_courier.php')) {
	  echo '<div class="notice notice-warning">
		   <p><strong>Warning!!! </strong>
		    Plugin <strong>Epeken All Kurir</strong> belum terinstal di 
 		    website Kakak sehingga Plugin <strong>Epeken Anteraja</strong> tidak berfungsi. 
		    Plugin Epeken All Kurir wajib diinstal bersama dengan plugin Epeken Anteraja. 
		    Terima kasih.</p></div>';
  }
  //if(false){
  if(epkn_antr_wcfm_active() || epkn_antr_wcvendors_active()){
  	echo '<div class="notice notice-warning">
		<p><strong>Warning</strong> Mohon maaf, Plugin marketplace terdeteksi aktif di website Kakak. Plugin Anteraja belum support konsep marketplace dengan WCFM Marketplace. Kami sarankan menggunakan plugin marketplace Dokan. Plugin Anteraja baru mendukung marketplace dengan plugin Dokan.</p></div>';
  }
}

function epkn_antr_wcfm_active() {
  return in_array('wc-multivendor-marketplace/wc-multivendor-marketplace.php', 
		apply_filters( 'active_plugins', get_option( 'active_plugins'))); #|| 
}

function epkn_antr_dokan_lite_active() {
   return in_array('dokan-lite/dokan.php', 
		apply_filters( 'active_plugins', get_option( 'active_plugins'))) ; #|| 
}

function epkn_antr_dokan_pro_active() {
   return in_array('dokan-pro/dokan.php', 
		apply_filters( 'active_plugins', get_option( 'active_plugins'))) ;#|| 
}

function epkn_antr_dokan_active() {
   return (epkn_antr_dokan_lite_active() || epkn_antr_dokan_pro_active());
}

function epkn_antr_wcvendors_active() {
  return in_array('wc-vendors/class-wc-vendors.php', 
		apply_filters( 'active_plugins', get_option( 'active_plugins'))) ;#|| 
}

function epkn_antr_multivendorx_active() {
  return in_array('dc-woocommerce-multi-vendor/dc_product_vendor.php', 
		apply_filters('active_plugins', get_option('active_plugins')));
}

function epkn_antr_is_multi_vendor() {
  /* Multi Vendor only support dokan, for now */
  if(epkn_antr_dokan_active() || 
   epkn_antr_multivendorx_active()) {
   return true;
  }else { 
   return false;
  }
}

function epkn_antr_epeken_all_kurir_active() {
  return in_array('epeken-all-kurir/epeken_courier.php', 
		apply_filters( 'active_plugins', get_option( 'active_plugins'))); # || 
}

if(epkn_antr_epeken_all_kurir_active()){
//This plugin will only work if Epeken All Kurir is active. Multi vendor only with dokan.
//Epeken All Kurir plugin can be downloaded from https://wordpress.org/plugins/epeken-all-kurir/

if(epkn_antr_wcfm_active() || epkn_antr_wcvendors_active()){
   return;
}

function epkn_antr_data_server() {
        $server = get_option('epeken_data_server');
        if(empty($server))
                $server = 'http://103.252.101.131';
        return $server;
}

$epkn_antr_data_server = epkn_antr_data_server();
define('EPEKEN_ANTERAJA_SERVER_URL', $epkn_antr_data_server);	
$epkn_antr_plugin = plugin_basename( __FILE__ );
$epkn_antr_api_end_point = 'anteraja.php';
$epkn_antr_api_rate_url = EPEKEN_ANTERAJA_SERVER_URL.'/api/'.$epkn_antr_api_end_point.'/rate/';
$epkn_antr_api_setuser = EPEKEN_ANTERAJA_SERVER_URL.'/api/'.$epkn_antr_api_end_point.'/setuser';
$epkn_antr_api_book = EPEKEN_ANTERAJA_SERVER_URL.'/api/'.$epkn_antr_api_end_point.'/book';
$epkn_antr_api_cncl = EPEKEN_ANTERAJA_SERVER_URL.'/api/'.$epkn_antr_api_end_point.'/cancel/';
$epeken_antr_api_tracking = EPEKEN_ANTERAJA_SERVER_URL.'/api/'.$epkn_antr_api_end_point.'/tracking';
define('EPEKEN_API_ANTERAJA_RATE_URL', $epkn_antr_api_rate_url);
define('EPEKEN_API_ANTERAJA_CREDENTIAL', $epkn_antr_api_setuser);
define('EPEKEN_API_ANTERAJA_BOOK', $epkn_antr_api_book);
define('EPEKEN_API_ANTERAJA_CANCEL', $epkn_antr_api_cncl);
define('EPEKEN_API_ANTERAJA_TRACKING',$epeken_antr_api_tracking);
$epkn_antr_plugin_dir = plugin_dir_path(__FILE__);
define('EPEKEN_ANTERAJA_DIR_PATH',$epkn_antr_plugin_dir);
include_once('includes/services.php');

function epkn_antr_check_license() {	
	$current_screen = get_current_screen();
	if($current_screen -> base === 'settings_page_epeken-anteraja/epeken-anteraja') {
		return;
	}
	$license = sanitize_text_field(get_option('epeken_wcjne_license_key'));
	$activation_menu = get_admin_url(null, 'options-general.php?page=epeken-all-kurir/epeken_courier.php', null);
	if(empty($license)) {
	?><div class="notice notice-error"><p><strong>Plugin Epeken Anteraja</strong> Anda <strong>belum berlisensi</strong>. 
	Plugin Epeken Anteraja membutuhkan licensi plugin Epeken All Kurir. Jika Kakak belum punya licensi, silakan <a href="https://www.epeken.com/shop/epeken-all-kurir-license/" target="_blank">beli di sini</a>. 
	Jika Kakak sudah memiliki nomor licensi, silakan langsung saja aktivasi <a href="<?php echo esc_html($activation_menu); ?>">di sini</a>.</p></div> 
        <?php
	}
	if(epkn_antr_is_anteraja_setting_page()){
	echo '<div class="notice notice-warning settings-error is-dismissible"><p>
	   Basepath, Access Key Id, Secret Access Key dan Prefix diperlukan untuk melanjutkan menggunakan 
	   layanan kurir Anteraja secara online. Untuk mendapatkannya, Kakak perlu terdaftar di sistem Anteraja.
     <a href="https://anteraja.id/id/partner-with-us/register" target="_blank">Daftar di sini sekarang, Gratis !!!</a>.
     <!-- Kirimkan email ke <strong>support@epeken.com</strong> untuk mendapatkan informasi - informasi koneksi Toko Online Kakak dengan Sistem Anteraja. --></p></div>';
	}
}
add_action('admin_notices', 'epkn_antr_check_license');

add_action('woocommerce_shipping_init', 'epkn_antr_init');
function epkn_antr_init(){
 if(!class_exists('Anteraja')){
	 include_once('class/shipping.php');
 }
}

add_filter('woocommerce_shipping_methods', 'epkn_antr_add_anteraja');
function epkn_antr_add_anteraja($methods){
	$methods[] = 'Anteraja'; 
	return $methods;
}

function epkn_antr_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=wc-settings&tab=shipping&section=anteraja">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
    $lic_link = '<a href="options-general.php?page=epeken-all-kurir%2Fepeken_courier.php">'.__('License').'</a>';
    array_push($links,$lic_link);
    return $links;
  }
add_filter( "plugin_action_links_$epkn_antr_plugin", 'epkn_antr_add_settings_link' );
add_action('admin_enqueue_scripts','epkn_antr_admin_scripts');
add_action('admin_enqueue_scripts','epkn_antr_admin_edit_order');
add_action('dokan_enqueue_scripts', 'epkn_antr_admin_scripts');
add_action('wp_enqueue_scripts','epkn_antr_dokan_dashboard_edit_order');

function epkn_antr_is_dokan_store_settings() {
  global $wp;
  if (epkn_antr_dokan_active() && isset( $wp->query_vars['settings'] ) && $wp->query_vars['settings'] == 'store')
    return true;
  else
    return false;
}

function epkn_antr_admin_scripts($hook) {
 if (epkn_antr_is_anteraja_setting_page() 
	 || epkn_antr_is_edit_user() 
	 || epkn_antr_is_dokan_store_settings()
	) {
   wp_enqueue_script('admin-anteraja',plugin_dir_url(__FILE__).'assets/js/admin.js',array('jquery'), 1.1, true);
   wp_localize_script('admin-anteraja','Admin_Anteraja', array(
	 'ajaxurl' => admin_url('admin-ajax.php'),
	 'nextNonce' => wp_create_nonce('admin-anteraja')
   ));
 }
}

function epkn_antr_admin_edit_order($hook) {
  if (!epkn_antr_is_edit_order())
    return;
  
  wp_enqueue_script('edit-order-anteraja',plugin_dir_url(__FILE__).'assets/js/order.js',array('jquery'),1.0,true);
  wp_localize_script('edit-order-anteraja','Admin_Anteraja', array(
  	'ajaxurl' => admin_url('admin-ajax.php'),
  	'nextNonce' => wp_create_nonce('admin-anteraja') 
  ));
}

function epkn_antr_dokan_dashboard_edit_order($hook) {
 if(!epkn_antr_dokan_active()){
   return;
 }
 if(empty($_GET['order_id'])) {
   return;
 }
 
 wp_enqueue_script('edit-order-anteraja',plugin_dir_url(__FILE__).'assets/js/order.js',array('jquery'),1.0,true);
  wp_localize_script('edit-order-anteraja','Admin_Anteraja', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nextNonce' => wp_create_nonce('admin-anteraja') 
  ));
}

function epkn_antr_is_edit_order() {
  $action = array_key_exists('action', $_GET) ? $_GET['action'] : null;
  return(is_admin() && sanitize_text_field($action) === 'edit');
}

function epkn_antr_is_edit_user() {
  $user_id = array_key_exists('user_id', $_GET) ? $_GET['user_id'] : null;
  return(is_admin() && !empty(sanitize_text_field($user_id)));
}

function epkn_antr_is_anteraja_setting_page() {
	$page = array_key_exists('page', $_GET) ? $_GET['page'] : null;
	$tab =  array_key_exists('tab', $_GET) ? $_GET['tab'] : null;
	$section =  array_key_exists('section', $_GET) ? $_GET['section'] : null;
	return (is_admin() && sanitize_text_field($page) === 'wc-settings' && 
		sanitize_text_field($tab) === 'shipping' && 
		sanitize_text_field($section) === 'anteraja');
}

add_action('woocommerce_checkout_update_order_meta', 'epkn_antr_save_shipping_meta',1);
function epkn_antr_save_shipping_meta($order_id) {
  $order = wc_get_order($order_id); 
  $country_states_array = WC()->countries->get_states();
  if($order -> has_shipping_method('anteraja')) {
    $order_weight = sanitize_text_field($_SESSION['CART_WEIGHT']);     
    if(empty($order_weight)) {
	$order_weight = 0;
	foreach($order -> get_items() as $item_id => $item) {
        $weight =  ($item -> get_product() -> get_weight());
 	if(empty($weight)) { 
		$weight = 1;
 	}else{
        	$weight = $weight * $item -> get_quantity();
	}
	$unit = sanitize_text_field(get_option('woocommerce_weight_unit'));
    	if($unit === 'g')
		 $weight = $weight / 1000;
	$order_weight = $order_weight + $weight;
	}
    }   
    $shipping_method = '';
    foreach ($shipping = $order->get_items( 'shipping' ) as $item_id => $item) {
      $item_data = $item -> get_data();
      $shipping_method = $item_data['name'];
      break;
    } 
    $order_shipping_method = $shipping_method;
    $order_receiver_name = sanitize_text_field(get_post_meta($order_id,'_shipping_first_name',true)).' '.
		                   sanitize_text_field(get_post_meta($order_id,'_shipping_last_name',true));
    $order_receiver_postcode = sanitize_text_field(get_post_meta($order_id, '_shipping_postcode', true));
    $receiver_address = sanitize_text_field(get_post_meta($order_id, '_shipping_address_1', true));
    $receiver_kecamatan = sanitize_text_field(get_post_meta($order_id, '_shipping_address_2', true));
    $receiver_city = sanitize_text_field(get_post_meta($order_id, '_shipping_city', true));
    $receiver_kelurahan = sanitize_text_field($_POST['shipping_address_3']); 
    $receiver_province = sanitize_text_field(get_post_meta($order_id, '_shipping_state', true));
    $receiver_province = $country_states_array['ID'][$receiver_province];
    $order_receiver_phone = sanitize_text_field(get_post_meta($order_id,'_billing_phone',true));
    $order_receiver_email = sanitize_text_field(get_post_meta($order_id,'_billing_email',true));
    
    if(empty($receiver_address) && empty($receiver_city) && empty($receiver_kecamatan) && empty($receiver_province)){
     $order_receiver_name = sanitize_text_field(get_post_meta($order_id,'_billing_first_name',true)).' '.
	                   sanitize_text_field(get_post_meta($order_id,'_billing_last_name',true));
     $order_receiver_postcode = sanitize_text_field(get_post_meta($order_id, '_billing_postcode', true));
     $receiver_address = sanitize_text_field(get_post_meta($order_id, '_billing_address_1', true));
     $receiver_province = sanitize_text_field(get_post_meta($order_id, '_billing_state', true));
     $receiver_province = $country_states_array['ID'][$receiver_province];
     $receiver_kecamatan = sanitize_text_field(get_post_meta($order_id, '_billing_address_2', true));
     $receiver_city = sanitize_text_field(get_post_meta($order_id, '_billing_city', true));
     $receiver_kelurahan = sanitize_text_field($_POST['billing_address_3']); 
    } 

    $order_receiver_address = $receiver_address.','.$receiver_kecamatan.','.$receiver_kelurahan.','.$receiver_city.','.$receiver_province;
    $anteraja_ordermeta = array(
   	'order_weight' => $order_weight,
  	'order_shipping_method' => $order_shipping_method,
	'order_receiver_name' => $order_receiver_name,
	'order_receiver_phone' => $order_receiver_phone,
	'order_receiver_email' => $order_receiver_email,
	'order_receiver_postcode' => $order_receiver_postcode,
	'order_receiver_address' => $order_receiver_address,
	'order_receiver_city' => $receiver_city,
	'order_receiver_kecamatan' => $receiver_kecamatan
    );      
    update_post_meta($order_id,'_anteraja_postmeta',$anteraja_ordermeta); 
  }
}

add_action('add_meta_boxes', 'epkn_antr_metabox_callback');
function epkn_antr_metabox_callback() {
  add_meta_box ( 'anteraja-metabox', __( 'Anteraja', 'woocommerce' ), 'epkn_antr_metabox'
            , 'shop_order', 'side', 'high' );
}

function epkn_antr_metabox() {
  global $post;
  $order_no = $post->ID;
  if(empty(wc_get_order($order_no)) && !is_admin()) {
   $order_no = sanitize_text_field($_GET['order_id']);
  }
  $order = wc_get_order($order_no);

  if(!$order -> has_shipping_method('anteraja')) {
    echo '<p>Tidak Tersedia. Order ini dikirimkan dengan kurir yang lain.</p>'; 
    return;
  }
  if(epkn_antr_is_multi_vendor()){
	   $shipping_items = $order -> get_items('shipping');
	   if(sizeof($shipping_items) > 1){
	     $args = array('post_parent' => $order_no, 'post_type' => 'shop_order');
	     $children = get_children($args);
	     
	     echo '<p>Tidak tersedia. Request pengiriman Anteraja, dapat dilakukan dari suborder:</p>'; 
	     echo '<ol>';
	     foreach($children as $child) {
		$suborder = wc_get_order($child -> ID);
		if($suborder -> has_shipping_method('anteraja'))
		   echo '<li> <a href='.$suborder->get_edit_order_url().'>#'.$child -> ID.'</a></li>';
	     }
	     echo '</ol>';
	     return;
	   }
   }

  if(!empty(sanitize_text_field(get_post_meta($order_no,'_anteraja_waybill_no', true)))) {
    echo '<p><strong>Nomor Resi Anteraja:</strong> <a>'.esc_html(sanitize_text_field(get_post_meta($order_no,'_anteraja_waybill_no',true))).' </a></p>';
    echo '<p>Perkiraan waktu penjemputan: <br>'.esc_html(sanitize_text_field(get_post_meta($order_no,'_anteraja_expect_start',true))).'<br>s.d<br> '.
	    esc_html(sanitize_text_field(get_post_meta($order_no,'_anteraja_expect_finish',true))).'</p>';
    epkn_antr_track_order(wc_get_order($order_no));
    echo '<p><input type="button" class="button save_order button-primary" value="Batalkan Pickup" id="btn_cancelanteraja"/></p>';
    echo '<input type="hidden" id="order_no" value="'.esc_html($order_no).'"/>';
    echo '<input type="hidden" id="waybillno" value="'.esc_html(sanitize_text_field(get_post_meta($order_no,'_anteraja_waybill_no',true))).'"/>';
    return;
  }

  echo '<strong><p style="font-size: 14px">Pengiriman Ke Pelanggan</p></strong>';
  if(empty(sanitize_text_field(get_post_meta($order_no, '_anteraja_postmeta', true)))){
	epkn_antr_save_shipping_meta($order_no);
  }
  $anteraja_ordermeta = get_post_meta($order_no, '_anteraja_postmeta', true); #array of anteraja shipping info of an order.
  echo '<p>';
  echo '<strong>Ditujukan Kepada:</strong> <a>'.esc_html(sanitize_text_field($anteraja_ordermeta['order_receiver_name'])).'</a><br>'."\n";
  echo '<input type="hidden" id="order_receiver_name" value="'.esc_html(sanitize_text_field($anteraja_ordermeta['order_receiver_name'])).'"/>';
  echo '<strong>Telefon:</strong> <a>'.esc_html(sanitize_text_field($anteraja_ordermeta['order_receiver_phone'])).'</a><br>'."\n";
  echo '<input type="hidden" id="order_receiver_phone" value="'.esc_html(sanitize_text_field($anteraja_ordermeta['order_receiver_phone'])).'"/>';
  echo '<strong>Email: </strong> <a>'.esc_html(sanitize_email($anteraja_ordermeta['order_receiver_email'])).'</a><br>'."\n";
  echo '<input type="hidden" id="order_receiver_email" value="'.esc_html(sanitize_email($anteraja_ordermeta['order_receiver_email'])).'"/>';
  $w = sanitize_text_field($anteraja_ordermeta['order_weight']);
  if ($w < 1) 
	   $w = 1;
  echo '<strong>Berat Paket:</strong> <a>'.esc_html($w).'</a> kg<br>'."\n";
  echo '<input type="hidden" id="order_weight" value="'.(esc_html(sanitize_text_field($anteraja_ordermeta['order_weight'])) * 1000).'"/>';
  echo '<strong>Alamat Tujuan:</strong><br><a>'.esc_html(sanitize_text_field($anteraja_ordermeta['order_receiver_address'])).'</a><br>'."\n";
  echo '<input type="hidden" id="order_receiver_address" value="'.esc_html(sanitize_text_field($anteraja_ordermeta['order_receiver_address'])).'"/>';
  echo '<strong>Kodepos:</strong><a>'.esc_html(sanitize_text_field($anteraja_ordermeta['order_receiver_postcode'])).'</a><br>';
  echo '<input type="hidden" id="order_receiver_postcode" value="'.esc_html(sanitize_text_field($anteraja_ordermeta['order_receiver_postcode'])).'"/>';
  $city = str_replace("Kota ", "", sanitize_text_field($anteraja_ordermeta["order_receiver_city"]));
  $city = str_replace("Kabupaten ", "", $city);
  echo '<input type="hidden" id="adm_anteraja_city" value="'.esc_html($city).'"/>';
  echo '<input type="hidden" id="adm_anteraja_kec" value="'.esc_html(sanitize_text_field($anteraja_ordermeta['order_receiver_kecamatan'])).'"/>';

  if(epkn_antr_is_multi_vendor()){
    if(epkn_antr_dokan_active())
    {	  
	  $vendor_id = dokan_get_seller_id_by_order( $order_no );
	  epkn_antr_echo_shipper_info($vendor_id);
    }else if(epkn_antr_multivendorx_active()) {
	  //$vendor_id =  
	  $vendor_id = get_post_meta($order_no, '_vendor_id', true);
 	  epkn_antr_echo_shipper_info($vendor_id);
    }
  }

  echo '<p>Dikirim dengan layanan <br><strong><a>'.esc_html(sanitize_text_field($anteraja_ordermeta['order_shipping_method'])).'</a></strong></p>'."\n"; 
  echo '<input type="hidden" value="'.esc_html($order_no).'" id = "order_id" />';
  $shipping_method = '';
  if(strpos(esc_html(sanitize_text_field($anteraja_ordermeta['order_shipping_method'])),'Same Day') !== false)
    $shipping_method = 'SD';
  else if(strpos(esc_html(sanitize_text_field($anteraja_ordermeta['order_shipping_method'])),'Next Day') !== false) 
    $shipping_method = 'ND';
  else if(strpos(esc_html(sanitize_text_field($anteraja_ordermeta['order_shipping_method'])),'Regular') !== false) 
    $shipping_method = 'REG';
  echo '<input type="hidden" id="order_shipping_method" value="'.esc_html($shipping_method).'"/>';
  echo '</p>';
  echo '<p><input type="button" id="btn_kirim_anteraja" value="Request Pengiriman Satria" class="button save_order button-primary" /></p>';
  echo '<p><input type="button" id="btn_refresh_data_anteraja" value="Refresh Data Pengiriman" class = "button save_order button-primary"/></p>';
}

function epkn_antr_echo_shipper_info($vendor_id){
  echo '<strong><p style="font-size: 14px"><u>Informasi Seller/Vendor</u></p></strong>';
  echo '<strong>Seller Name:</strong> '.sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_shipper_name',true)).'<br>';
  echo '<strong>Seller Address:</strong> '.sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_shipper_address',true)).'<br>';
  echo '<strong>Seller City:</strong> '.sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_kota_asal',true)).'<br>';
  echo '<strong>Seller District:</strong> '.sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_kecamatan_asal',true)).'<br>';
  echo '<strong>Seller Postcode:</strong> '.sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_shipper_postcode',true)).'<br>';
  echo '<strong>Seller Phone:</strong> '.sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_shipper_phone',true)).'<br>';
  echo '<strong>Seller Email:</strong> '.sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_shipper_email',true)).'<br>';
}

function epkn_antr_get_kota() {
  $json = file_get_contents(EPEKEN_ANTERAJA_DIR_PATH.'assets/json/city.json');
   $json_decode = json_decode($json,true);
   $options = '';
   foreach($json_decode["City"] as $option) {
     $options .= '<option value="'.esc_html(sanitize_text_field($option['City'])).'">'.esc_html(sanitize_text_field($option['City'])).'</option>'."\n";
   }
   return $options;
}

###################################
# Ajax Hooks & Callback Functions #
###################################

add_action('wp_ajax_get_anteraja_kecamatan','epkn_antr_get_kecamatan');
add_action('wp_ajax_nopriv_get_anteraja_kecamatan','epkn_antr_get_kecamatan');
add_action('wp_ajax_request_anteraja_order', 'epkn_antr_get_order_parameters');
add_action('wp_ajax_nopriv_request_anteraja_order', 'epkn_antr_get_order_parameters');
add_action('wp_ajax_refresh_data_anteraja', 'epkn_antr_refresh_data_anteraja');
add_action('wp_ajax_nopriv_refresh_data_anteraja', 'epkn_antr_refresh_data_anteraja');
add_action('wp_ajax_cancel_anteraja_order', 'epkn_antr_cancel_anteraja_order');
add_action('wp_ajax_nopriv_cancel_anteraja_order', 'epkn_antr_cancel_anteraja_order');

function epkn_antr_refresh_data_anteraja() {
  $order_id = sanitize_text_field($_GET['order_id']);
  delete_post_meta($order_id, '_anteraja_postmeta');
}

function epkn_antr_get_kecamatan() {
  $vendor_id = sanitize_text_field($_GET['vendor_id']); //check if multi vendor
  $kota = sanitize_text_field($_GET['kota']);
  $nonce = sanitize_text_field($_GET['nextNonce']);
  if(!wp_verify_nonce($nonce,'admin-anteraja')){
   die('Invalid Invocation');
  }
  $json = file_get_contents(EPEKEN_ANTERAJA_DIR_PATH.'assets/json/kecamatan.json');
  $json_decode = json_decode($json,true);

  $origin_kecamatan = "";
  if(!empty($vendor_id))
   $origin_kecamatan = sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_kecamatan_asal' ,true));
  else
   $origin_kecamatan = sanitize_text_field(get_option('epeken_anteraja_kecamatan_asal'));
 
  echo '<option value="">-- Silakan Pilih --</option>';
  foreach($json_decode['data'] as $data){
    if($data['City'] !== $kota)
	    continue;
    $selected = '';
    if($origin_kecamatan === sanitize_text_field($data['Kecamatan'])) 
	    $selected = 'selected';
    echo '<option value="'.esc_html(sanitize_text_field($data['Kecamatan'])).'" '.$selected.'>'.
	                   esc_html(sanitize_text_field($data['Kecamatan'])).'</option>'."\n"; 
  }
}

function epkn_antr_cancel_anteraja_order() {
   $waybillno = sanitize_text_field($_GET['waybillno']);
   $nonce = sanitize_text_field($_GET['nextNonce']);
   $order_no = sanitize_text_field($_GET['order_no']);
   if(!wp_verify_nonce($nonce,'admin-anteraja')){
      die('Invalid Invocation');
   }
   $msg = epkn_antr_cancel($waybillno);
   $msgaa = json_decode($msg,true);
   if($msgaa['status'] === 200) {
    delete_post_meta($order_no, '_anteraja_waybill_no'); 
    delete_post_meta($order_no, '_anteraja_expect_start');
    delete_post_meta($order_no, '_anteraja_expect_finish');
    $order = wc_get_order($order_no);
    $order -> update_status('wc-cancelled');
    $order -> add_order_note('Order dibatalkan karena pemesanan anteraja dibatalkan. Silakan informasikan pembatalan order kepada pelanggan.');
   }
   echo $msg;
} 

function epkn_antr_get_order_parameters() {
   $vendor_id = '';
   $order_id = sanitize_text_field($_POST['order_id']);
   $nonce = sanitize_text_field($_POST['nextNonce']);
   if(!wp_verify_nonce($nonce,'admin-anteraja')){
    die('Invalid Invocation');
   }
   $order_id = sanitize_text_field($_POST['order_id']);
   $order_receiver_name = sanitize_text_field($_POST['order_receiver_name']);
   $order_receiver_phone = sanitize_text_field($_POST['order_receiver_phone']);
   $order_receiver_email = sanitize_email(sanitize_text_field($_POST['order_receiver_email']));
   $order_weight = sanitize_text_field($_POST['order_weight']);
   if($order_weight < 1000) 
	    $order_weight = 1000;
   $order_receiver_address = sanitize_text_field($_POST['order_receiver_address']);
   $order_receiver_postcode = sanitize_text_field($_POST['order_receiver_postcode']);
   $order_receiver_city = sanitize_text_field($_POST['order_receiver_city']);
   $order_receiver_kecamatan = sanitize_text_field($_POST['order_receiver_kecamatan']);
   $order_shipping_method = sanitize_text_field($_POST['order_shipping_method']);
   $items = array();
   $order = wc_get_order($order_id); 
   foreach($order -> get_items() as $item_id => $item) {
        $weight =  ($item -> get_product() -> get_weight());
 	if(empty($weight)) { 
		$weight = 1;
 	}else{
        	$weight = $weight * $item -> get_quantity();
	}
	$unit = sanitize_text_field(get_option('woocommerce_weight_unit'));
    	if($unit === 'kg')
		$weight = $weight * 1000;

	$d = $item -> get_total();

	if($d < 1000)
	   $d = 1000;

       $anteraja_item = array('item_name' => substr($item->get_name(), 0, 50),
			'item_desc' => substr($item -> get_product() -> get_description(), 0, 32),
			'item_quantity' => $item -> get_quantity(), 
			'declared_value' => $d, 
			'weight' => $weight
 			);
        array_push($items, $anteraja_item);
   }
   $declared_value = $order -> get_subtotal();

   if($declared_value < 1000)  
 	$declared_value = 1000;

   $items = json_encode($items);
   $use_insurance = 'false';

   if(epkn_antr_is_multi_vendor()) {
     if(epkn_antr_dokan_active()) {
      $vendor_id = dokan_get_seller_id_by_order( $order -> ID );
     } else if(epkn_antr_multivendorx_active()) {
      $vendor_id = get_post_meta($order -> ID, '_vendor_id', true);
     }
   }
   
   $returned_message = epkn_antr_book($order_id,$order_shipping_method,$order_weight,$order_receiver_name,
		   $order_receiver_phone,$order_receiver_email,$order_receiver_city,
  		   $order_receiver_kecamatan,$order_receiver_address,
		   $order_receiver_postcode, $items, $declared_value,$use_insurance, $vendor_id); 
   echo $returned_message;
 }

add_action('woocommerce_order_details_after_order_table', 'epkn_antr_track_order');
function epkn_antr_track_order($order) {
  $order_no = $order -> get_id();
  $awb = get_post_meta($order_no, '_anteraja_waybill_no', true);

  if(empty($awb))
    return; //do nothing

  echo "<h4>Riwayat Pengiriman Anteraja</h4>";
  
  if(!is_admin())
    echo "<p align='center'>Nomor Resi: <strong>".$awb."</strong></p>";
  
  $track = epkn_antr_track($awb);
  if($track['status'] === 200){
     echo "<ol>";
     foreach($track['content']['history'] as $history) {
    	$hub_name=$history["hub_name"];
        if(empty($hub_name))
		$hub_name = "N/A";
	echo "<li><strong>Hub Name:</strong> ".$hub_name."<br><strong>Message:</strong> ".$history['message']["id"]."</li>";
     }
     echo "</ol>";
  }
}

add_action('epeken_add_vendor_shipping_item', 'epkn_antr_add_vendor_shipping_item', 10,1);
function epkn_antr_add_vendor_shipping_item($user) {
	$shipping = WC_Shipping::instance();
   	$methods = $shipping -> get_shipping_methods();
	$anteraja = $methods['anteraja'];
	$vendor_id = $user -> ID;
	?>
	<tr>
	<th class="titledesc">
 	Vendor/Seller Origin (Anteraja)	
	</th>
	<td>
	<table><tr><td>
	Kota Asal</td><td><input type="hidden" name="anteraja_vendor_id" id="adm_anteraja_vendor_id" value="<?php echo $vendor_id; ?>"/>
	<select id="adm_anteraja_origin_city" name="anteraja_origin_city">
	<?php echo $anteraja -> get_kota_asal($vendor_id); ?></select></td></tr>
	<tr><td>Kecamatan Asal: </td><td><select id="adm_anteraja_origin_kec" name="anteraja_origin_kecamatan">
	<?php $kota_asal = get_user_meta(intval($vendor_id), 'vendor_anteraja_kota_asal', true); ?>
	<option value="">-- Silakan Pilih --</option><?php echo $anteraja -> get_kecamatan_asal(esc_html($kota_asal), $vendor_id); ?></select></td></tr>
	<?php epkn_antr_generate_shipper_info($vendor_id);?>
	</table>
 	</td> 
	</tr>
	<?php
}

add_action('epeken_save_vendor_shipping_item', 'epkn_antr_save_vendor_shipping_item', 10,1);
function epkn_antr_save_vendor_shipping_item($user_id) { 
	$vendor_kota_asal = (empty($_POST['anteraja_origin_city'])) ? '' : sanitize_text_field($_POST['anteraja_origin_city']);
	$vendor_kecamatan_asal = (empty($_POST['anteraja_origin_kecamatan'])) ? '' : sanitize_text_field($_POST['anteraja_origin_kecamatan']);
	$vendor_shipper_name =  (empty($_POST['shipper_name'])) ? '' : sanitize_text_field($_POST['shipper_name']);
	$vendor_shipper_phone = (empty($_POST['shipper_phone'])) ? '' : sanitize_text_field($_POST['shipper_phone']);
	$vendor_shipper_email = (empty($_POST['shipper_email'])) ? '' : sanitize_text_field($_POST['shipper_email']);
 	$vendor_shipper_address  = (empty($_POST['shipper_address'])) ? '' : sanitize_text_field($_POST['shipper_address']);
	$vendor_shipper_postcode = (empty($_POST['shipper_postcode'])) ? '' : sanitize_text_field($_POST['shipper_postcode']);

	update_user_meta($user_id, 'vendor_anteraja_kota_asal', $vendor_kota_asal);
	update_user_meta($user_id, 'vendor_anteraja_kecamatan_asal', $vendor_kecamatan_asal);
	update_user_meta($user_id, 'vendor_anteraja_shipper_name', $vendor_shipper_name);
	update_user_meta($user_id, 'vendor_anteraja_shipper_phone', $vendor_shipper_phone);
	update_user_meta($user_id, 'vendor_anteraja_shipper_email', $vendor_shipper_email);
	update_user_meta($user_id, 'vendor_anteraja_shipper_address', $vendor_shipper_address);
	update_user_meta($user_id, 'vendor_anteraja_shipper_postcode', $vendor_shipper_postcode);
}

add_action('dokan_order_detail_after_order_items','epkn_antr_metabox_in_vendor',10);
function epkn_antr_metabox_in_vendor($order) {
 ?>
 <div class="dokan-panel dokan-panel-default">
               <div class="dokan-panel-heading"><strong>Anteraja Shipping</strong></div>
               <div class="dokan-panel-body">
 <?php epkn_antr_metabox();?>
  </div></div>
 <?php
}
 
}//End is Epeken All Kurir Active

function epkn_antr_generate_shipper_info($user_id){
   if(!epkn_antr_is_multi_vendor())
	   return;
   $shipper_name = get_user_meta(intval($user_id), 'vendor_anteraja_shipper_name', true);
   $shipper_phone = get_user_meta(intval($user_id), 'vendor_anteraja_shipper_phone', true);
   $shipper_email = get_user_meta(intval($user_id), 'vendor_anteraja_shipper_email', true);
   $shipper_address = get_user_meta(intval($user_id), 'vendor_anteraja_shipper_address', true);
   $shipper_postcode = get_user_meta(intval($user_id), 'vendor_anteraja_shipper_postcode', true);
   if(!epkn_antr_is_multi_vendor())
  	return;
   echo '<tr align="left">';
   echo '<th scope="row" class="titledesc">Informasi Shipper</th><td>';
   echo '<p>Shipper Name <br><input type="text" value="'.esc_html($shipper_name).'" name="shipper_name" /></p>';
   echo '<p>Shipper Phone<br><input type="text" value="'.esc_html($shipper_phone).'" name="shipper_phone" /></p>';
   echo '<p>Shipper Email <br><input type="text" value="'.esc_html($shipper_email).'" name="shipper_email" /></p>';
   echo '<p>Shipper Address <br><input type="text" value="'.esc_html($shipper_address).'" name="shipper_address" /></p>';
   echo '<p>Shipper Postcode<br><input type="text" value="'.esc_html($shipper_postcode).'" name="shipper_postcode" /></p>';
   echo '</td></tr>';
}

?>
