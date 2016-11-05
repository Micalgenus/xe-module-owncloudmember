
(function($) {
	$(function() {
		
		$("#able_module").on("change", function() {
			if ($(this)[0].checked === true) {
				$("section").not(".able_module_setting").show();
			} else {
				$("section").not(".able_module_setting").hide();
			}
		}).triggerHandler("change");
		
	});
	
} (jQuery));
