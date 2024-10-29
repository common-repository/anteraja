(function($){
	adm_kota_asal_on_change = function(){
		$('#adm_anteraja_origin_city').on("change", function(){
			$.get(Admin_Anteraja.ajaxurl,
				{
					action: 'get_anteraja_kecamatan',
					nextNonce: Admin_Anteraja.nextNonce,
					kota: this.value,
					vendor_id: $('#adm_anteraja_vendor_id').value
				}, function(data,status){
					$('#adm_anteraja_origin_kec').empty();
					$('#adm_anteraja_origin_kec').append(data);	
				})
		}
		);
	}
})(jQuery);
jQuery(document).ready(function($){
 adm_kota_asal_on_change();
 $('#adm_anteraja_origin_city').select2();
 $('#adm_anteraja_origin_kec').select2();
 $('#adm_anteraja_awal_diskon').datepicker({ dateFormat: 'yy-mm-dd' });
 $('#adm_anteraja_akhir_diskon').datepicker({ dateFormat: 'yy-mm-dd' });
});