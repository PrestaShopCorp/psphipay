$(document).ready(function() {

	$(document).on('click', '#transactions_dates_range_button', function() {
		$('#datepicker').removeClass('hide');
		return false;
	});

	$('#datepicker').removeClass('hide');
	$('#datepicker-cancel').hide();
});
