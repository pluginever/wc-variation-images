/**
 * WC Variation Images Admin
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */

/*jslint browser: true */
/*global jQuery:false */

window.wc_variation_images = (function(window, document, $, undefined){
	'use strict';

	var inputVariation = 'input.variation_id',
		formVariation = 'form.variations_form',
		selectVal = 'table.variations select';

	// form on "blur" delegated event - AFTER
	$(formVariation).on( 'blur', selectVal, function(){
		var productId = $(formVariation).data('product_id');
		var variationID = $(inputVariation).val();
		if( productId > 0 && variationID > 0) {
			$.ajax({
				url : wpwvi.ajaxurl,
				type : 'post',
				data : {
					action : 'wc_variation_images_load_variation_images',
					product_id : productId,
					variation_id : variationID,
					nonce: wpwvi.nonce,
				},
				success: function (res) {
					$('.wc-inventory-product-add').html(res.data);
				},
				error: function (error) {
					alert('Something happend wrong');
					console.log(error);
				}
			});
		}

	});

})(window, document, jQuery);
