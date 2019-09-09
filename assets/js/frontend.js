/**
 * WC Variation Images frontend
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */

/*jslint browser: true */
/*global jQuery:false */

window.wc_variation_images = (function (window, document, $, undefined) {
	'use strict';
	$.wc_variation_images = {
		inputVariation: 'input.variation_id',
		formVariation: 'form.variations_form',
		selectVal: 'table.variations select',
		get_images: function () {
			var productId = $($.wc_variation_images.formVariation).data('product_id');
			var variationID = $($.wc_variation_images.inputVariation).val();
			var gallerySelector = $('.woocommerce-product-gallery');
			var galleryImage = $('.woocommerce-product-gallery__image');
			var width = galleryImage.outerWidth();
			var height = galleryImage.outerHeight();
			if (productId > 0) {
				var productElement = $('#product-' + productId);
				productElement.addClass('loader');
				$.ajax({
					url: WC_VARIATION_IMAGES.ajaxurl,
					type: 'post',
					data: {
						action: 'wc_variation_images_load_variation_images',
						product_id: productId,
						variation_id: variationID,
						nonce: WC_VARIATION_IMAGES.nonce
					},
					success: function (res) {
						gallerySelector.replaceWith(res.data.images);

						//set height width for fixed scroll
						galleryImage.width(width).height(height);
						$('.woocommerce-product-gallery__wrapper').width(width).height(height);

						//render gallery after load all image
						$('.woocommerce-product-gallery').each(function () {
							$(this).wc_product_gallery();
						});
						productElement.removeClass('loader');
					},
					error: function () {
						productElement.removeClass('loader');
					}
				});
			}
		}
	};
	$.wc_variation_images.get_images();
	$('form.variations_form').on('blur', 'table.variations select', function () {
		$.wc_variation_images.get_images();
	});
})(window, document, jQuery);
