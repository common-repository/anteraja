<?php
if(!defined('ABSPATH')) exit;
class Anteraja extends WC_Shipping_Method{
	
	public $error_msg;
	public $logger;
  public $is_disc_applicable;
  public $real_discount;

 public function __construct(){
  $this -> logger = new WC_Logger();
  $this -> id = 'anteraja';
  $this -> title = 'Anteraja';
  $this -> method_title = 'Anteraja';
  $this -> method_description = __('Anteraja shipping method');
  $this -> enabled = 'yes';
  $this -> is_disc_applicable = false;
  $this -> real_discount = 0;
  $this -> init_form_fields();
  $this -> init_settings();
  
 }
 public function writelog($str){
   $logger = $this -> logger;
   $logger -> add($this -> id, $str);
 }
 public function init_form_fields(){
     $this -> form_fields  = array(
     'enabled' => array(
 	'title'   => __('Enable/Disable','woocommerce'),
	'type'    => 'checkbox',
	'label'   => __('Enable this shipping method','woocommerce'),
	'default' => 'yes',
     ),
    'pilihan_origin' => array(
       'type' => 'pilihan_origin'
     ),
    'pilihan_layanan' => array(
    	'type' => 'pilihan_layanan'
    ),
    'user_credential' => array(
  	'type' => 'user_credential'
    ),
    'shipper_info' => array(
    	'type' => 'shipper_info'
    )
  );
  add_action('woocommerce_update_options_shipping_'.$this->id,array(&$this, 'process_admin_options'));
  add_action('woocommerce_update_options_shipping_methods',array(&$this, 'process_admin_options'));					
  add_action('woocommerce_update_options_shipping_'.$this->id, array( &$this, 'process_update_data_tarif' ) );
  add_action('admin_enqueue_scripts', array(&$this,'admin_enqueue_scripts'));
  add_action('woocommerce_checkout_update_order_meta', array(&$this, 'action_on_order_created'));
 }

 public function generate_pilihan_origin_html() {  
   ob_start();
   echo '<tr align="left">';
   echo '<th scope="row" class="titledesc">Pilihan Kota<br>dan Kecamatan Asal</th>';
   echo '<td><p>Kota Asal: <select id="adm_anteraja_origin_city" name="origin_city">'.$this->get_kota_asal().'</select></p>';
   $kota_asal = get_option('epeken_anteraja_kota_asal');
   echo '<p>Kecamatan: <select id="adm_anteraja_origin_kec" name="origin_kecamatan"><option value="">-- Silakan Pilih --</option>'.$this->get_kecamatan_asal(esc_html($kota_asal)).'</select><p></td>';
   echo '</tr>';
   return ob_get_clean();
 }
 public function generate_pilihan_layanan_html() {
   ob_start();
   $layanan = get_option('epeken_anteraja_layanan');
   echo '<tr align="left">';
   echo '<th scope="row" class="titledesc">Pilihan Layanan</th>';
   $creg = '';
     if(is_array($layanan) && in_array('Regular', $layanan))
	$creg = 'checked';
   
   echo '<td>
   <table><tr><td>'; 
   echo'</p><input name="layanan_regular" type="checkbox" value="Regular" '.esc_html($creg).' />Regular</p>';
   $csd = '';
   if(is_array($layanan) && in_array('Same Day', $layanan))
	   $csd = 'checked';
   echo '<p><input name="layanan_sameday" type="checkbox" value="Same Day" '.esc_html($csd).' />Same Day</p>';

   $cnd = '';
   if(is_array($layanan) && in_array('Next Day', $layanan))
	   $cnd = 'checked';
   echo '<p><input name="layanan_nextday" type="checkbox" value="Next Day" '.esc_html($cnd).' />Next Day</p>'; 

   $cfd = '';
   if(is_array($layanan) && in_array('Frozen', $layanan))
	$cfd = 'checked';
   echo '<p><input name="layanan_frozen" type="checkbox" value="Frozen" '.esc_html($cfd).' />Frozen</p>'; 

   $ccd = '';
   if(is_array($layanan) && in_array('Cargo', $layanan))
	$ccd = 'checked';
   echo '<p><input name="layanan_cargo" type="checkbox" value="Cargo" '.esc_html($ccd).' />Cargo</p>'; 



   echo '</td>';

   // setting parameter diskon ada di bawah ini.
   $nilai_diskon = sanitize_text_field(get_option('epeken_anteraja_nilai_diskon'));
   $awal_diskon = sanitize_text_field(get_option('epeken_anteraja_awal_diskon'));
   $akhir_diskon = sanitize_text_field(get_option('epeken_anteraja_akhir_diskon'));
   $quota_diskon = sanitize_text_field(get_option('epeken_anteraja_quota_diskon'));
   echo '<td>
   <table style="margin-left: 50px;">
   <tr>
    <th scope="row" class="titledesc">Diskon Ongkir</th>
    <td style="padding: 5px;">
    <p>Nominal Diskon (Rp)<br><input type="number" name="anteraja_nilai_diskon" value="'.$nilai_diskon.'"/></p>
    <p>Berlaku Sejak Tanggal<br><input type="text" id="adm_anteraja_awal_diskon" 
    value="'.$awal_diskon.'" name="anteraja_awal_diskon"></p>
    <p>Berlaku Hingga Tanggal<br><input type="text" id="adm_anteraja_akhir_diskon" 
    value="'.$akhir_diskon.'" name="anteraja_akhir_diskon"></p>
    <p>Quota Diskon (Remaining)<br><input type="number" name="anteraja_quota_diskon" value="'.$quota_diskon.'"><br>
    <em>Angka kuota akan berkurang setelah order/pesanan pelanggan dengan diskon ongkir ter-create.</em></p>
    </td>
   </tr>
   </table>
   </td>';
   echo '</tr>
   </table></tr>';
   return ob_get_clean();
 }
 public function generate_user_credential_html() {
   ob_start();  
   $base_path = get_option('epeken_anteraja_basepath');
   $access_key_id = get_option('epeken_anteraja_access_key_id');
   $secret_access_key = get_option('epeken_anteraja_secret_access_key');
   $prefix = get_option('epeken_anteraja_prefix');
   echo '<tr align="left">';
   echo '<th scope="row" class="titledesc">Anteraja User <br>Credential</th>';
   echo '<td>';
   echo '<p>Basepath <br><input type="text" value = "'.esc_html($base_path).'" name="base_path"/></p>';
   echo '<p>Access Key Id <br><input type="text" value = "'.esc_html($access_key_id).'" name="access_key_id" /></p>';
   echo '<p>Secret Access Key <br><input type="text" value = "'.esc_html($secret_access_key).'" name="secret_access_key" /></p>';
   echo '<p>Prefix <br><input type="text" name="prefix" value = "'.esc_html($prefix).'" /></p>'; 
   echo '</td></tr>';
   return ob_get_clean();
 }
 function generate_shipper_info_html() {
   ob_start();
   $shipper_name = get_option('epeken_anteraja_shipper_name');
   $shipper_phone = get_option('epeken_anteraja_shipper_phone');
   $shipper_email = get_option('epeken_anteraja_shipper_email');
   $shipper_address = get_option('epeken_anteraja_shipper_address');
   $shipper_postcode = get_option('epeken_anteraja_shipper_postcode');
   echo '<tr align="left">';
   echo '<th scope="row" class="titledesc">Informasi Shipper</th><td>';
   echo '<p>Shipper Name <br><input type="text" value="'.esc_html($shipper_name).'" name="shipper_name" /></p>';
   echo '<p>Shipper Phone<br><input type="text" value="'.esc_html($shipper_phone).'" name="shipper_phone" /></p>';
   echo '<p>Shipper Email <br><input type="text" value="'.esc_html($shipper_email).'" name="shipper_email" /></p>';
   echo '<p>Shipper Address <br><input type="text" value="'.esc_html($shipper_address).'" name="shipper_address" /></p>';
   echo '<p>Shipper Postcode<br><input type="text" value="'.esc_html($shipper_postcode).'" name="shipper_postcode" /></p>';
   echo '</td></tr>';
   return ob_get_clean();
 }
 public function get_kecamatan_asal($kota, $vendor_id = null) {
  if(empty($kota)){
    return '';
  }	
  $json = file_get_contents(EPEKEN_ANTERAJA_DIR_PATH.'assets/json/kecamatan.json');
  $json_decode = json_decode($json,true);
  $origin_kecamatan = "";
  if(empty($vendor_id))
   $origin_kecamatan = get_option('epeken_anteraja_kecamatan_asal');
  else
   $origin_kecamatan = get_user_meta(intval($vendor_id), 'vendor_anteraja_kecamatan_asal', true);

  $str = '';
  foreach($json_decode['data'] as $data){
    if($data['City'] !== $kota)
	    continue;
    $selected = '';
    if($origin_kecamatan === $data['Kecamatan']) 
	    $selected = 'selected';
    $str .= '<option value="'.esc_html($data['Kecamatan']).'" '.$selected.'>'.esc_html($data['Kecamatan']).'</option>'."\n"; 
  }
  return $str;
 }
 public function get_kota_asal($user_id = "") {
   $kota_asal = "";
   if(empty($user_id))
    $kota_asal = get_option('epeken_anteraja_kota_asal');
   else
    $kota_asal = get_user_meta(intval($user_id),'vendor_anteraja_kota_asal', true);

   $json = file_get_contents(EPEKEN_ANTERAJA_DIR_PATH.'assets/json/city.json');
   $json_decode = json_decode($json,true);
   $options = '';
   foreach($json_decode["City"] as $option) {
     $selected = '';
     if($option['City'] === $kota_asal)
	     $selected = 'selected';
     $options .= '<option value="'.esc_html($option['City']).'" '.$selected.'>'.esc_html($option['City']).'</option>'."\n";
   }
   return $options;
 }
 public function admin_options() {
   echo '<h2>Anteraja Settings</h2>';
   echo '<table>';
   $this -> generate_settings_html();
   echo '</table>';
 }
 public function is_anteraja_setting_page() {
	 return (is_admin() && sanitize_text_field($_GET['page']) === 'wc-settings' && 
		 sanitize_text_field($_GET['tab']) === 'shipping' && 
		 sanitize_text_field($_GET['section']) === 'anteraja');
 }
 public function validate_settings($params) {
    if(!is_email($params['shipper_email'])) {
	 $this -> error_msg = "Invalid Email";
	    return false;
    }
    if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$params['base_path'])) {
	    $this -> error_msg = "Invalid basepath URL";
	    return false;
    }
    return true;
 }

 public function error_when_save_settings() {
  if(empty($this -> error_msg))
	return;

  echo '<div class="notice notice-error is-dismissible">
         <p>Gagal menyimpan pengaturan. <strong>Pesan Error: '.esc_html($this -> error_msg).'</strong>. Mohon mencoba kembali.</p>
	 </div>'; 

  $this -> error_msg = '';
 }

 public function process_update_data_tarif() { 
  if(!$this -> validate_settings($_POST)) {
	add_action('updated_option', array(&$this,'error_when_save_settings'));	
	return;
  }

  update_option('epeken_anteraja_kota_asal', sanitize_text_field($_POST['origin_city']));
  update_option('epeken_anteraja_kecamatan_asal', sanitize_text_field($_POST['origin_kecamatan']));
  $layanan = array();
  if(array_key_exists('layanan_regular', $_POST) && sanitize_text_field($_POST['layanan_regular']) === 'Regular')
	  array_push($layanan,'Regular');
  if(array_key_exists('layanan_sameday', $_POST) && sanitize_text_field($_POST['layanan_sameday']) === 'Same Day')
	  array_push($layanan,'Same Day');
  if(array_key_exists('layanan_nextday', $_POST) && sanitize_text_field($_POST['layanan_nextday']) === 'Next Day')
	  array_push($layanan,'Next Day');
  if(array_key_exists('layanan_cargo', $_POST) && sanitize_text_field($_POST['layanan_cargo']) === 'Cargo')
	  array_push($layanan,'Cargo');
  if(array_key_exists('layanan_frozen', $_POST) && sanitize_text_field($_POST['layanan_frozen']) === 'Frozen')
	  array_push($layanan,'Frozen');

  update_option('epeken_anteraja_layanan', $layanan);
  update_option('epeken_anteraja_basepath', sanitize_text_field($_POST['base_path']));
  update_option('epeken_anteraja_access_key_id', sanitize_text_field($_POST['access_key_id']));
  update_option('epeken_anteraja_secret_access_key', sanitize_text_field($_POST['secret_access_key']));
  update_option('epeken_anteraja_prefix', sanitize_text_field($_POST['prefix']));
  update_option('epeken_anteraja_shipper_name', sanitize_text_field($_POST['shipper_name']));
  update_option('epeken_anteraja_shipper_phone', sanitize_text_field($_POST['shipper_phone']));
  update_option('epeken_anteraja_shipper_email', sanitize_text_field(sanitize_email($_POST['shipper_email'])));
  update_option('epeken_anteraja_shipper_address', sanitize_text_field($_POST['shipper_address']));
  update_option('epeken_anteraja_shipper_postcode', sanitize_text_field($_POST['shipper_postcode']));
  epkn_antr_save_credential(sanitize_text_field($_POST['base_path']), sanitize_text_field($_POST['access_key_id']),
			   sanitize_text_field($_POST['secret_access_key']), sanitize_text_field($_POST['prefix']));
  update_option('epeken_anteraja_awal_diskon', sanitize_text_field($_POST['anteraja_awal_diskon']));
  update_option('epeken_anteraja_akhir_diskon', sanitize_text_field($_POST['anteraja_akhir_diskon']));
  update_option('epeken_anteraja_nilai_diskon', sanitize_text_field($_POST['anteraja_nilai_diskon']));
  update_option('epeken_anteraja_quota_diskon', sanitize_text_field($_POST['anteraja_quota_diskon']));
 }

 public function get_package_weight($packages) {
	$tweight = 0;
 	foreach($packages['contents'] as $package) {
 		$product_id = $package['product_id'];
		$quantity = $package['quantity'];
		if($package['variation_id'] > 0){
		 $product_id = $package['variation_id'];
		}
		$product = wc_get_product($product_id);
		$weight = $quantity * floatval($product -> get_weight());
		$tweight += $weight;
	}
	return $tweight;
 }

 public function calculate_shipping($package=array()){
   
   $vendor_id = $package['vendor_id'];

   $options = get_option('woocommerce_anteraja_settings');
   if($options['enabled'] !== 'yes')
	   return;
   $shipping = WC_Shipping::instance();
   $methods = $shipping -> get_shipping_methods();
   $epeken_all_kurir = $methods['epeken_courier'];
   $epeken_all_kurir -> get_destination_city_and_kecamatan();
   $shipping_city = urldecode($epeken_all_kurir -> shipping_city);
   $shipping_kecamatan = urldecode($epeken_all_kurir -> shipping_kecamatan);
   
   $origin_city = ''; $origin_kecamatan = '';
   $weight = '';
   $weight_unit = get_option('woocommerce_weight_unit');
   if(empty($vendor_id)) { //single store
    $origin_city = get_option('epeken_anteraja_kota_asal');
    $origin_kecamatan = get_option('epeken_anteraja_kecamatan_asal');
    $weight = $this -> get_package_weight($package);
    if($weight_unit === 'kg')
	   $weight = $weight * 1000; //to gram
   }else{ //multi vendor 
    $origin_city = get_user_meta(intval($vendor_id),'vendor_anteraja_kota_asal', true);
    $origin_kecamatan = get_user_meta(intval($vendor_id),'vendor_anteraja_kecamatan_asal', true);
    $this -> writelog('Calculating shipping cost for vendor '.$vendor_id.';kota asal:'.$origin_city.';kecamatan asal:'.$origin_kecamatan);
    $weight = $package['weight']; //already in gram
   }
   if(intval($weight) === 0)
	    $weight = 1000; //1000 grams

   $rate_cache_key = $origin_city . '-' . $origin_kecamatan  . '-' . $shipping_city . '-' . $shipping_kecamatan . '-' . $weight . '_anteraja';
   $rate_cache_key = preg_replace( '/[^\da-z]/i', '_', $rate_cache_key ); 
   $rates = '';
   if (!empty( WC() -> session -> get ($rate_cache_key))) {
    $rates = WC() -> session -> get ($rate_cache_key);
   } else {
    $rates = epkn_antr_rate($origin_city,$origin_kecamatan,$shipping_city,$shipping_kecamatan,$weight);
    WC() -> session -> set ($rate_cache_key, $rates);
   }
   if(empty($rates))
      return;
   $rates = json_decode($rates,true);
   $layanan = get_option('epeken_anteraja_layanan');

   if(!is_array($rates['result']))
     return;

   $is_discount_applicable = $this -> is_discount_applicable();
   $this -> is_disc_applicable = $is_discount_applicable;
   foreach($rates['result'] as $rate){
	$nama_layanan = str_replace('Anteraja ','',$rate['product_name']);
     if(!in_array($nama_layanan, $layanan))
		   continue;

     
     $t = $rate['product_name'];     
     $t = apply_filters('epeken_anteraja_ganti_label', $t, array('title' => $this -> title, 'product_name' => $rate['product_name']));
     $normalized_rate = array('id' => $this -> id.'_'.$rate['product_code'],
		 'label' => $t.' ('.$rate['etd'].')',
		 'cost' => $rate['rates'],
		 'taxes' => false
	   );
     
     if ($is_discount_applicable) {
      $normalized_rate = $this -> apply_subsidi($normalized_rate); 
     }
     
     $this -> add_rate( $normalized_rate );
   }
   if($is_discount_applicable){
    add_action('woocommerce_review_order_after_shipping',function(){
      echo "<tr><td colspan=2>Selamat !!!, Kakak mendapatkan subsidi ongkir Anteraja sebesar Rp.".
      sanitize_text_field(get_option("epeken_anteraja_nilai_diskon"))
      .". Ongkir Anteraja di atas sudah termasuk diskon.</td></tr>";
    });
   }
 }
 public function admin_enqueue_scripts() {
   wp_enqueue_script('jquery-ui-datepicker');
 }
 public function is_discount_applicable() {
  $quota = sanitize_text_field(get_option('epeken_anteraja_quota_diskon'));
  $quota = intval($quota);   
  if($quota <= 0)
    return false;

   $date = new DateTime("now", new DateTimeZone("Asia/Jakarta"));
   $date = $date -> format('Y-m-d');
   $date = strtotime($date);
   $discount_start = sanitize_text_field(get_option('epeken_anteraja_awal_diskon'));
   $discount_start = strtotime($discount_start);
   $discount_end = sanitize_text_field(get_option('epeken_anteraja_akhir_diskon'));
   $discount_end = strtotime($discount_end);
   if($date >= $discount_start && $date <= $discount_end){
     return true;
   }else{
     return false;
   }

  }
  public function apply_subsidi($rate) {
    $discount_amount = sanitize_text_field(get_option('epeken_anteraja_nilai_diskon'));
    $chosen = WC()->session->get( 'chosen_shipping_methods' )[0];
    if($chosen === $rate['id']) {
     if ($rate['cost'] <= $discount_amount){
       $this -> real_discount = $rate['cost'];
     }else{
       $this -> real_discount = $discount_amount;
     }
    }

    $rate['cost'] = $rate['cost'] - $discount_amount; 
    if($rate['cost'] < 0) {
      $rate['cost'] = 0; 
      $rate['label'] = $rate['label'].' - Bebas Ongkir';
    }
    return $rate;
  }
  public function action_on_order_created($order_id) {
      if($this -> is_disc_applicable){
        $quota = intval(sanitize_text_field(get_option('epeken_anteraja_quota_diskon')));
        $amount = intval(sanitize_text_field(get_option('epeken_anteraja_nilai_diskon')));
        $quota = $quota - 1;
        update_option('epeken_anteraja_quota_diskon',sanitize_text_field($quota));
        update_post_meta($order_id,'_anteraja_dicount', $this -> real_discount);  
        $order = wc_get_order($order_id);
        $order -> add_order_note('Pesanan ini mendapatkan diskon ongkir Anteraja sebesar Rp.'.$this -> real_discount);
        $this -> is_disc_applicable = false;
        $this -> real_discount = 0;
      }
  }
}

/*
//This chunk of code is to rename anteraja label. Thank you.
add_filter('epeken_anteraja_ganti_label', 'epeken_anteraja_label', 10,2);
function epeken_anteraja_label($t,$attribute) {
  $t = $attribute['product_name'];
  return $t;
}
*/

?>
