(function($){
	adm_kota_on_change = function(){
		$('#adm_anteraja_city').on("change", function(){
			$.get(Admin_Anteraja.ajaxurl,
				{
					action: 'get_anteraja_kecamatan',
					nextNonce: Admin_Anteraja.nextNonce,
					kota: this.value
				}, function(data,status){
					$('#adm_anteraja_kec').empty();
					$('#adm_anteraja_kec').append(data);	
				})
		}
		);
	}
	adm_refresh_data = function(){		
	     $('#btn_refresh_data_anteraja').on("click", function(){
	 	$('#btn_refresh_data_anteraja').val('Mohon menunggu..');
		$('#btn_refresh_data_anteraja').prop('disabled',true);
		var call=$.get(Admin_Anteraja.ajaxurl, {
		    action: 'refresh_data_anteraja',
		    nextNonce: Admin_Anteraja.nextNonce,
		    order_id: $('#order_id').val()
		}, function(data,status){
		    $('#btn_refresh_data_anteraja').val('Merefresh halaman..');
		    location.reload();
		});
      	     });
        }
	adm_cancel_order = function() {
	      $('#btn_cancelanteraja').on("click", function() {
 		 if(!confirm("Yakin ingin membatalkan order pickup Anteraja ?\nMembatalkan order pickup Anteraja akan membatalkan pesanan ini.")){
                     return; // do nothing if no confirm
                 }
	 	 var btn_init_label = $('#btn_cancelanteraja').val();
		 $('#btn_cancelanteraja').val('Mohon menunggu..');
	         $('#btn_cancelanteraja').prop('disabled',true);
 	 	 var call=$.get(Admin_Anteraja.ajaxurl,{
		    action: 'cancel_anteraja_order',
		    nextNonce: Admin_Anteraja.nextNonce,
		    waybillno: $('#waybillno').val(),
		    order_no: $('#order_no').val(),
  	 	  }, function(data,status){
		      if(status === 'success'){
			 data = data.slice(0,-1);
		      }
		      var obj = jQuery.parseJSON(data);
		      if(obj.status !== 200){
			 alert(obj.info);
  			 $('#btn_cancelanteraja').val(btn_init_label);
			 $('#btn_cancelanteraja').prop('disabled',false);
		      }else{
			 $('#btn_cancelanteraja').val('Merefresh halaman..');	
			 location.reload();
		      }
		 }); 
		});
	}
	adm_order = function() {
	      $('#btn_kirim_anteraja').on("click", function(){
		    if(!confirm('Yakin ingin membuat order pickup ke Anteraja ?')){
		     return; // do nothing if no confirm
		    }
		    var btn_init_label = $('#btn_kirim_anteraja').val();
		    $('#btn_kirim_anteraja').val('Mohon menunggu..');
		    $('#btn_kirim_anteraja').prop('disabled',true);
		    var call=$.post(Admin_Anteraja.ajaxurl,
			{
			   action: 'request_anteraja_order',
			   nextNonce: Admin_Anteraja.nextNonce,
 			   order_id: $('#order_id').val(),
			   order_receiver_name: $('#order_receiver_name').val(),
			   order_receiver_phone: $('#order_receiver_phone').val(),
			   order_receiver_email: $('#order_receiver_email').val(),
  			   order_weight: $('#order_weight').val(),
			   order_receiver_address: $('#order_receiver_address').val(),
			   order_receiver_postcode: $('#order_receiver_postcode').val(),
			   order_receiver_city: $('#adm_anteraja_city').val(),
			   order_receiver_kecamatan: $('#adm_anteraja_kec').val(),
			   order_shipping_method: $('#order_shipping_method').val(),
			}, function(data,status){
			   $('#btn_kirim_anteraja').prop('disabled',false);
			   $('#btn_kirim_anteraja').val(btn_init_label);
			   if(status === 'success'){
			     data = data.slice(0,-1);
			   }
			   var obj = jQuery.parseJSON(data);
			   if(obj.status !== 200){
			      alert(obj.info);
			   }else{
			      alert('Order pengiriman Anteraja berhasil dibuat. Nomor Resi Anteraja: ' + obj.content.waybill_no);			      
			      location.reload();
			   }
			}
		    )
		}
	      );
	}
})(jQuery);
jQuery(document).ready(function($){
 adm_order();
 adm_refresh_data();
 adm_cancel_order();
});
