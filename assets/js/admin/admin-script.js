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
		init() {
			$('#woocommerce-product-data').on(
				'woocommerce_variations_loaded',
				function () {
					$.wc_variation_images.add_images_box();
					$.wc_variation_images.sortable();
				}
			);

			$('#variable_product_options').on(
				'woocommerce_variations_added',
				function () {
					$.wc_variation_images.add_images_box();
					$.wc_variation_images.sortable();
				}
			);
		},

		add_images_box() {
			$('.woocommerce_variation').each(function () {
				const optionsTab = $(this).find('.options');
				const wcVariationImagesGallery = $(this).find(
					'.wc-variation-images-gallery-wrapper'
				);
				wcVariationImagesGallery.insertBefore(optionsTab);
			});
		},
		sortable() {
			$('.woocommerce_variation').each(function () {
				$(this)
					.find('.wc-variation-images-image-list')
					.sortable({
						items: 'li.wc-variation-images-image-info',
						cursor: 'move',
						scrollSensitivity: 40,
						forcePlaceholderSize: true,
						forceHelperSize: false,
						helper: 'clone',
						opacity: 0.75,
						start: function start(event, ui) {
							ui.item.css('background-color', '#f6f6f6');
						},
						stop: function stop(event, ui) {
							ui.item.removeAttr('style');
						},
						update: function update() {
							$.wc_variation_images.update_variation(this);
						},
					});
			});
		},
		update_variation($list) {
			$($list)
				.closest('.woocommerce_variation')
				.addClass('variation-needs-update');
			$('.cancel-variation-changes, .save-variation-changes').removeAttr(
				'disabled'
			);
			$('#variable_product_options').trigger(
				'woocommerce_variations_input_changed'
			);
		},
		upload_images(e) {
			e.preventDefault();
			const variationID = $(this).data(
				'wc_variation_images_variation_id'
			);
			if ($.wc_variation_images.is_cross_upload_limit(variationID)) {
				alert('Upload limit 5 images in free version');
				return false;
			}
			const self = $(this);

			// Create the media frame.
			const file_frame = (wp.media.frames.file_frame = wp.media({
				title: WC_VARIATION_IMAGES.variation_image_title,
				button: {
					text: WC_VARIATION_IMAGES.add_variation_image_text,
				},
				library: {
					type: ['image', 'video'],
				},
				multiple: true,
			}));

			file_frame.on('select', function () {
				const images = file_frame.state().get('selection').toJSON();
				const image_limit = $(
					'#wc-variation-images-image-list-' + variationID + ' li'
				).length;
				const total_image = image_limit + images.length;
				if (total_image > 5) {
					alert('Upload limit 5 images in free version');
					return false;
				}
				const html = images
					.map(function (image) {
						const imageID = image.id;
						const imageUrl = image.url;
						const template = wp.template('wc-variation-images');

						return template({
							image_id: imageID,
							image_url: imageUrl,
							variation_id: variationID,
						});
					})
					.join('');
				self.parent()
					.prev()
					.find('.wc-variation-images-image-list')
					.append(html);
				$.wc_variation_images.variation_change_trigger(self);
			});
			file_frame.open();
		},

		variation_change_trigger(element) {
			element
				.closest('.woocommerce_variation')
				.addClass('variation-needs-update');
			$(
				'button.cancel-variation-changes, button.save-variation-changes'
			).removeAttr('disabled');
			$('#variable_product_options').trigger(
				'woocommerce_variations_input_changed'
			);
		},

		remove_images(e) {
			const self = $(this);
			e.preventDefault();
			$.wc_variation_images.variation_change_trigger(self);
			$(this).parent().remove();
		},

		is_cross_upload_limit(variationId) {
			const selector = $(
				'#wc-variation-images-image-list-' + variationId + ' li'
			);
			const length = selector.length;
			return length >= 5;
		},
	};
	$.wc_variation_images.init();
	$(document).on(
		'click',
		'.wc-variation-images-add-image',
		$.wc_variation_images.upload_images
	);
	$(document).on(
		'click',
		'.wc-variation-images-remove-image',
		$.wc_variation_images.remove_images
	);
});
