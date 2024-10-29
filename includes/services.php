<?php
if(!defined('ABSPATH')) exit;
##################################################
## Shipping Microservices functions to Anteraja ##
##################################################
/*
* Get Anteraja Rate
*/
function epkn_antr_rate($origin_city,$origin_district,$dest_city,$dest_district,$weight){
  $logger = new WC_Logger();
  $logger -> add('anteraja', 'Rate from '.$origin_city.', '.$origin_district.', '.$dest_city.', '. $dest_district.' ,'.$weight);
  $license = sanitize_text_field(get_option('epeken_wcjne_license_key'));
  $origin_city = urlencode($origin_city); 
  $origin_district = urlencode($origin_district);
  $dest_city = urlencode($dest_city);
  $dest_district = urlencode($dest_district);
  $url = EPEKEN_API_ANTERAJA_RATE_URL.$license.'/'.$origin_city.'/';
  $url .= $origin_district.'/'.$dest_city.'/'.$dest_district.'/'.$weight;
  $response=wp_remote_get($url);
  $rate = wp_remote_retrieve_body($response);
  return $rate;
}	

function epkn_antr_cancel($waybillno) {
  $license = sanitize_text_field(get_option('epeken_wcjne_license_key'));
  $url = EPEKEN_API_ANTERAJA_CANCEL.$license.'/'.$waybillno;
  $response = wp_remote_get($url);
  $msg = wp_remote_retrieve_body($response);
  return $msg;
}

function epkn_antr_book($invoice_no,$service_code, $parcel_total_weight,$receiver_name,
  		    	 $receiver_phone,$receiver_email,$receiver_city,$receiver_district,
			 $receiver_address, $receiver_postcode, $items, $declared_value, $use_insurance,
			 $vendor_id = '' //book for specific vendor in multi vendor mode.
			 ) {
 $license = sanitize_text_field(get_option('epeken_wcjne_license_key'));
 $basepath = sanitize_text_field(get_option('epeken_anteraja_basepath'));
 $access_key_id = sanitize_text_field(get_option('epeken_anteraja_access_key_id'));
 $secret_access_key = sanitize_text_field(get_option('epeken_anteraja_secret_access_key'));
 $prefix = sanitize_text_field(get_option('epeken_anteraja_prefix'));

 $shipper_name = substr(sanitize_text_field(get_option('epeken_anteraja_shipper_name')),0,50);
 $shipper_phone = substr(sanitize_text_field(get_option('epeken_anteraja_shipper_phone')),0,16); 
 $shipper_email = substr(sanitize_text_field(sanitize_email(get_option('epeken_anteraja_shipper_email'))),0,50);
 $shipper_city = sanitize_text_field(get_option('epeken_anteraja_kota_asal')); 
 $shipper_district = sanitize_text_field(get_option('epeken_anteraja_kecamatan_asal'));
 $shipper_postcode = substr(sanitize_text_field(get_option('epeken_anteraja_shipper_postcode')),0,5);
 $shipper_address = substr(sanitize_text_field(get_option('epeken_anteraja_shipper_address')),0,256);

 if(!empty($vendor_id) && epkn_antr_is_multi_vendor()) {
    $shipper_name = substr(sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_shipper_name',true)),0,50);
    $shipper_phone = substr(sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_shipper_phone', true)),0,16);
    $shipper_email = substr(sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_shipper_email', true)),0,50);
    $shipper_city = sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_kota_asal', true));
    $shipper_district = sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_kecamatan_asal', true));
    $shipper_postcode = substr(sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_shipper_postcode', true)),0,5);
    $shipper_address = substr(sanitize_text_field(get_user_meta(intval($vendor_id),'vendor_anteraja_shipper_address', true)),0,256);
 }

 $url = EPEKEN_API_ANTERAJA_BOOK;
 $post_data = ['license' => $license,
 	  'invoice_no' => $invoice_no, 'service_code' => $service_code,
	  'parcel_total_weight' => $parcel_total_weight, 'receiver_name'=> substr($receiver_name,0,50), 
 	  'receiver_phone' => $receiver_phone, 'receiver_email' => substr($receiver_email,0,50), 
 	  'receiver_city' => $receiver_city, 'receiver_district' => $receiver_district,
	  'receiver_address' => substr($receiver_address,0,256), 'receiver_postcode' => substr($receiver_postcode,0,5),
	  'shipper_name' => $shipper_name, 'shipper_phone' => $shipper_phone, 'shipper_email' => $shipper_email,
	  'shipper_city' => $shipper_city, 'shipper_district' => $shipper_district, 
	  'shipper_address' => $shipper_address, 'shipper_postcode' => $shipper_postcode, 
	  'items' => $items, 
	  'declared_value' => $declared_value,
	  'use_insurance' => $use_insurance]; 
  $response = wp_remote_post($url, array(
    'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
    'body'        => json_encode($post_data),
    'method'      => 'POST',
    'data_format' => 'body',)
  );
  $content = wp_remote_retrieve_body($response);
  $content_json = json_decode($content, true);
  if($content_json['status'] === 200) {
    update_post_meta($invoice_no, '_anteraja_waybill_no', sanitize_text_field($content_json['content']['waybill_no']));
    update_post_meta($invoice_no, '_anteraja_expect_start',  sanitize_text_field($content_json['content']['expect_start']));
    update_post_meta($invoice_no, '_anteraja_expect_finish', sanitize_text_field($content_json['content']['expect_finish']));
    $order = wc_get_order($invoice_no);
    if($order -> has_status('on-hold'))
     $order -> update_status('processing');
    $order -> add_order_note('Pesanan ini menunggu pickup Satria. Nomor Resi: '.esc_html(sanitize_text_field($content_json['content']['waybill_no'])).
			     '. Expect Start: '. esc_html(sanitize_text_field($content_json['content']['expect_start'])). 
			     '. Expect Finish: '. esc_html(sanitize_text_field($content_json['content']['expect_finish'])));
  }
  return $content;
}
function epkn_antr_save_credential($basepath,$access_key_id,$secret_access_key, $prefix) {
  $license = sanitize_text_field(get_option('epeken_wcjne_license_key'));
  $url = EPEKEN_API_ANTERAJA_CREDENTIAL;
  $post_data = ["license" => $license, 
	"basepath" => $basepath, 
	"access_key_id" => $access_key_id, 
	"secret_access_key" => $secret_access_key, 
	"prefix" => $prefix];

  $return = wp_remote_post($url, array(
    'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
    'body'        => json_encode($post_data),
    'method'      => 'POST',
    'data_format' => 'body',)
  );
  if(is_wp_error($return)) {
    return false;
  }else{
    return true;
  }
}

function epkn_antr_track($awb) {
  $license = sanitize_text_field(get_option('epeken_wcjne_license_key'));
  $url = EPEKEN_API_ANTERAJA_TRACKING;
  $post_data = ["license" => $license, "awb" => $awb];
  $response = wp_remote_post($url, array(
    'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
    'body'        => json_encode($post_data),
    'method'      => 'POST',
    'data_format' => 'body'
  ));
  $content = wp_remote_retrieve_body($response);
  $content_json = json_decode($content, true);
  return $content_json;
}

?>
