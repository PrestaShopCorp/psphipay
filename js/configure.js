$(document).ready(function() {
	var pattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	
	$('#install_user_email').on('change keyup', function() {
		var match = pattern.test($(this).val());
		var container = $(this).parent().parent();
		var container_message = container.children('#email_error_message');
		
		if (match == true) {
			if (container_message.length > 0) {
				container_message.hide();
			}
		} else {
			if (container_message.length == 0) {
				container.append('<span class="alert" id="email_error_message">'+email_error_message+'</span>');
			}
			container_message.show();
		}
	});
	
	$('#datepicker').removeClass('hide');
	$('#datepicker-cancel').hide();
});
