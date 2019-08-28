/**
 * WC Variation Images admin
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
		init: function () {

			$('#woocommerce-product-data').on('woocommerce_variations_loaded', function () {
				$.wc_variation_images.add_images_box();
			});

			$('#variable_product_options').on('woocommerce_variations_added', function () {
				$.wc_variation_images.add_images_box();
			});
		},

		add_images_box: function () {
			$('.woocommerce_variation').each(function () {
				var optionsTab = $(this).find('.options');
				var wcVariationImagesGallery = $(this).find('.wc-variation-images-gallery-wrapper');
				wcVariationImagesGallery.insertBefore(optionsTab);
			});
		},

		upload_images: function (e) {
			e.preventDefault();
			var variationID = $(this).data('wc_variation_images_variation_id');
			if ($.wc_variation_images.is_cross_upload_limit(variationID)) {
				alert('Upload limit 3 images in free version');
				return false;
			}
			var self = $(this);

			// Create the media frame.
			var file_frame = wp.media.frames.file_frame = wp.media({
				title: WC_VARIATION_IMAGES.variation_image_title,
				button: {
					text: WC_VARIATION_IMAGES.add_variation_image_text
				},
				library: {
					type: ['image', 'video']
				},
				multiple: true
			});

			file_frame.on('select', function () {
				var images = file_frame.state().get('selection').toJSON();
				var image_limit = $('#wc-variation-images-image-list-' + variationID + ' li').length;
				var total_image = image_limit + images.length;
				if (total_image > 3) {
					alert('Upload limit 3 images in free version');
					return false;
				}
				var html = images.map(function (image) {

					var imageID = image.id;
					var imageUrl = image.sizes.thumbnail.url;
					var template = wp.template('wc-variation-images');

					return template({image_id: imageID, image_url: imageUrl, variation_id: variationID});
				}).join('');
				self.parent().prev().find('.wc-variation-images-image-list').append(html);
				$.wc_variation_images.variation_change_trigger(self);
			});
			file_frame.open();
		},

		variation_change_trigger: function (element) {
			element.closest('.woocommerce_variation').addClass('variation-needs-update');
			$('button.cancel-variation-changes, button.save-variation-changes').removeAttr('disabled');
			$('#variable_product_options').trigger('woocommerce_variations_input_changed');
		},

		remove_images: function (e) {
			var self = $(this);
			e.preventDefault();
			$.wc_variation_images.variation_change_trigger(self);
			$(this).parent().remove();

		},

		is_cross_upload_limit: function (variationId) {
			var selector = $('#wc-variation-images-image-list-' + variationId + ' li');
			var length = selector.length;
			return (length >= 3) ? true : false;
		}
	};
	$.wc_variation_images.init();
	$(document).on('click', '.wc-variation-images-add-image', $.wc_variation_images.upload_images);
	$(document).on('click', '.wc-variation-images-remove-image', $.wc_variation_images.remove_images);
});
