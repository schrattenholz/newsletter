// JavaScript Schrattenholz/Newsletter

jQuery(document).ready(function() {
	$( "#Form_AnmeldeformularNewsletter #Email" ).on('change focus',function(){
		
	});
	
	$( "#Form_AnmeldeformularNewsletter" ).submit(function( event ) {
		event.preventDefault();
		if (jQuery('#Form_AnmeldeformularNewsletter')[0].checkValidity() === false) {
			
		}else{
			jQuery.ajax({
				url: pageLink+'/HandleNewsletter?'+jQuery(this).serialize(),
				success: function(data) {
					var response=JSON.parse(data);
					var status=response.Status;
					var title=response.Title;
					var message=response.Message;
					var delay=response.Value;
					if(status=='error'){
						$('#toast_error').toast({
							autohide: true,
							delay:delay,
							animation:true
						});
						$('#toast_error .toast-header .content').html(title);
						$('#toast_error .toast-body').html(message);
						$('#toast_error').toast('show');
					}else if(status=='info'){
						$('#toast_info').toast({
							autohide: true,
							delay:delay,
							animation:true
						});
						$('#toast_info .toast-header .content').html(title);
						$('#toast_info .toast-body').html(message);
						$('#toast_info').toast('show');
					}else{
						$('#toast_success').toast({
							autohide: true,
							delay:delay,
							animation:true
						});
						$('#toast_success .toast-header .content').html(title);
						$('#toast_success .toast-body').html(message);
						$('#toast_success').toast('show');
						//window.location=pageLink;
					}
					/*jQuery(this).css("display","none");
					if(error==0){
						jQuery(this).parent().prepend(msg);										
						jQuery(this).parent().find('.msg').delay(delay).fadeTo(200,0,function(){											
							jQuery(this).remove();
							jQuery('#Form_AnmeldeformularNewsletter').fadeTo(200,1);	
						});
					}else{
						jQuery(this).parent().prepend(msg);										
						jQuery(this).parent().find('.msg').delay(delay).fadeTo(200,0,function(){											
							jQuery(this).remove();
							jQuery('#Form_AnmeldeformularNewsletter').fadeTo(200,1);	
						});
					}*/
				}
			});
		}
		return false;
	});
});