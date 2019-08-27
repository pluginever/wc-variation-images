jQuery(document).ready(function ($) {
	'use strict';
	$.wc_variotion_images = {

		init: function () {

			$('#woocommerce-product-data').on('woocommerce_variations_loaded', function () {
				$.wc_variotion_images.add_images_box();
			});

			$('#variable_product_options').on('woocommerce_variations_added', function () {
				$.wc_variotion_images.add_images_box();
			});
		},

		add_images_box: function () {
			$('.woocommerce_variation').each(function () {
				var optionsTab = $(this).find('.options');
				var wpwviGallery = $(this).find('.wpwvi-gallery-wrapper');
				wpwviGallery.insertBefore(optionsTab);
			});
		},

		upload_images: function (e) {
			e.preventDefault();
			var variationID = $(this).data('wcvivariation_id');
			if ($.wc_variotion_images.is_cross_upload_limit(variationID)) {
				alert('Upload limit 3 images in free version');
				return false;
			}
			var self = $(this);

			// Create the media frame.
			var file_frame = wp.media.frames.file_frame = wp.media({
				title: wpwvi.variation_image_title,
				button: {
					text: wpwvi.add_variation_image_text
				},
				library: {
					type: ['image', 'video']
				},
				multiple: true
			});

			file_frame.on('select', function () {
				var images = file_frame.state().get('selection').toJSON();
				var image_limit = $('#wpwvi-image-list-' + variationID + 'li').length;
				var total_image = image_limit + images.length;
				if (total_image > 3) {
					alert('Upload limit 3 images in free version');
					return false;
				}
				var html = images.map(function (image) {

					var imageID = image.id;
					var imageUrl = image.sizes.thumbnail.url;
					var template = wp.template('wpwvi-image');

					return template({image_id: imageID, image_url: imageUrl, variation_id: variationID});
				}).join('');
				self.parent().prev().find('.wpwvi-image-list').append(html);
				$.wc_variotion_images.variation_change_trigger(self);
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
			$.wc_variotion_images.variation_change_trigger(self);
			$(this).parent().remove();

		},

		is_cross_upload_limit: function (variationId) {
			var selector = $('#wpwvi-image-list-' + variationId + ' li');
			var length = selector.length;
			return (length >= 3) ? true : false;
		}
	};
	$.wc_variotion_images.init();
	$(document).on('click', '.wpwvi-add-image', $.wc_variotion_images.upload_images);
	$(document).on('click', '.wpwvi-remove-image', $.wc_variotion_images.remove_images);
});
