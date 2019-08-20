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
		if( productId > 0) {

			var productElement = $(`#product-${productId}`);
			productElement.addClass('loader');
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
					var gallerySelector = $('.woocommerce-product-gallery');
					gallerySelector.replaceWith(res.data.images);
					productElement.removeClass('loader');
					$('.woocommerce-product-gallery').each( function() {
						$( this ).wc_product_gallery();
					} );
				},
				error: function (error) {
					productElement.removeClass('loader');
					console.log(error);
				}
			});
		}


	});

})(window, document, jQuery);
