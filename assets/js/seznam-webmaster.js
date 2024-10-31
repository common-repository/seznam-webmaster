jQuery(document).ready( function($) {
	$('#meta_tag').on('change', function() {
		var metatag = $(this).val().trim();
		var regex = /(?:^(\w+)$|content="(\w+)")/;
		var pieces = metatag.match(regex);
		
		if (pieces) {
			var metatag_value = pieces[1] ? pieces[1] : pieces[2];
			$(this).val(metatag_value);
		} else if (metatag !== "") {
			alert('Kód metatagu musí být alfanumerický řetězec.');
		}
	});
	
	$('.seznam-webmaster-metatag-image-toggle').on('click', function(e) {
		$('.seznam-webmaster-metatag-image').toggle();
	});
	
	$('#api_key').on('change', function() {
		var api_key = $(this).val().trim();
		var regex = /(?:^(\w+)$)/;
		var pieces = api_key.match(regex);
		
		if (pieces) {
			var metatag_value = pieces[1];
			$(this).val(metatag_value);
		} else if (api_key !== "") {
			alert('Kód API musí být alfanumerický řetězec.');
		}
	});
});