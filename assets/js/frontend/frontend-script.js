/**
 * WC Variation Images frontend
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */

/*jslint browser: true */
/*global jQuery:false */
jQuery(document).ready(function ($) {
	'use strict';
	$.wc_variation_images = {
		init() {
			const inputVariation = 'input.variation_id',
				formVariation = 'form.variations_form',
				selectVal = 'table.variations select';

			$(formVariation).on('blur', selectVal, function () {
				const productId = $(formVariation).data('product_id');
				const variationID = $(inputVariation).val();
				const gallerySelector = $('.woocommerce-product-gallery');
				const galleryImage = $('.woocommerce-product-gallery__image');
				const width = galleryImage.outerWidth();
				const height = galleryImage.outerHeight();
				if (productId > 0) {
					const productElement = $('#product-' + productId);
					//productElement.addClass('loader');
					$.ajax({
						url: WC_VARIATION_IMAGES.ajaxurl,
						type: 'post',
						data: {
							action: 'wc_variation_images_load_variation_images',
							product_id: productId,
							variation_id: variationID,
							nonce: WC_VARIATION_IMAGES.nonce,
						},
						success(res) {
							const gParent = gallerySelector.parent();
							gallerySelector.remove();

							gParent.prepend(res.data.images);
							//set height width for fixed scroll
							galleryImage.width(width).height(height);
							$('.woocommerce-product-gallery__wrapper').height(
								height
							);

							//render gallery after load all image
							$('.woocommerce-product-gallery').each(function () {
								$(this).wc_product_gallery();
							});

							//productElement.removeClass('loader');
						},
						error() {
							productElement.removeClass('loader');
						},
					});
				}
			});
		},
	};
	$.wc_variation_images.init();
});
