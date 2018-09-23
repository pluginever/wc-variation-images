/**
 * WC Variation Images Admin
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */

/*jslint browser: true */
/*global jQuery:false */
jQuery(document).ready(function ($, window, document, undefined) {
	'use strict';
	$.wc_variation_images = {
		init: function () {
			this.show_variation_images();
			$('.woocommerce_variable_attributes').on('click', '.add-variation-images', this.add_variation_images);
		},
		show_variation_images: function () {
			var $data = {
				action: 'wc_variation_images_load_variation_images',
				nonce: wpwvi.nonce,
				variation_ids: $.wc_variation_images.get_variation_ids()
			};
			console.log($.wc_variation_images.get_variation_ids());
			if ($.wc_variation_images.get_variation_ids().length) {
				$.post(wpwvi.ajaxurl, $data, function (response) {
					if (response.success !== true) {
						return;
					}

					for (var id in response.data.images) {
						if (response.data.images.hasOwnProperty(id)) {
							var html = '<h4 class="wc-additional-variation-images-title">' + wpwvi.variation_image_title + ' <a href="#" class="wc-additional-variations-images-tip" data-tip="' + wpwvi.admin_tip_message + '">[?]</a></h4>' + response.data.images[id] + '<a href="#" class="add-variation-images">' +
								wpwvi.add_variation_image_text + '</a>';
						}

						if (!$('#variable_product_options .woocommerce_variation a.upload_image_button[rel="' + id + '"]').parents('.upload_image').find('a.wc-variation-images-tip').length) {

							$('#variable_product_options .woocommerce_variation a.upload_image_button[rel="' + id + '"]').after(html);
						}

					}

					$('.wc-variation-images-tip').tipTip({
						'attribute': 'data-tip',
						'fadeIn': 50,
						'fadeOut': 50
					});

					//shortable on pro version

				});
			}
		},
		add_variation_images: function () {
			var id = $(this).parents('.upload_image').find('a.upload_image_button').prop('rel'),
				thumbs = $(this).parents('.upload_image').find('ul.wc-variation-images-list'),
				frame;

			frame = wp.media.frames.frame = wp.media({

				title: wpwvi.admin_media_title,

				button: {
					text: wpwvi.admin_media_add_image_text
				},

				// only images
				library: {
					type: 'image'
				},

				multiple: true
			});

			// after a file has been selected
			frame.on('select', function () {
				var selection = frame.state().get('selection');
				selection.map(function (attachment) {
					attachment = attachment.toJSON();
					if (attachment.id) {
						var url = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

						thumbs.append('<li><a href="#" class="wc-variation-images-thumb" data-id="' + attachment.id + '"><img src="' + url + '" width="40" height="40" /><span class="overlay"></span></a></li>');
					}
				});

				// make sure attachments are link to the variation post id instead of parent post id
				wp.media.model.settings.post.id = id;
				$.wc_variation_images.update_variation_selection(id);
			});

			frame.open();
			return false;
		},
		update_variation_selection:function(variation_id){
			console.log('update_variation_selection');
			console.log(variation_id);
			if ( variation_id.length <= 0 ) {
				return;
			}
			var order = [],
				container = $( '#variable_product_options .woocommerce_variation a.upload_image_button[rel="' + variation_id + '"]' ).parent( '.upload_image' );
			if ( container.find( 'ul.wc-variation-images-list li' ).length ) {
				$.each( container.find( 'ul.wc-variation-images-list li' ), function() {
					console.log(this);
					order.push( $( this ).find( 'a.wc-variation-images-thumb' ).data( 'id' ) );
				});

				container.find( 'input.wc-variation-images-thumbs-save' ).val( order );
			}else{
				container.find( 'input.wc-variation-images-thumbs-save' ).val( '' );
			}
			console.log(container.find( 'input.wc-variation-images-thumbs-save' ).val());
			order.join( ',' );

			// just to trigger a change so the save button enables
			$( '#variable_product_options' ).find( 'input' ).eq( 0 ).change();

			// add proper update class so WC knows to trigger a save for specific variation
			container.parents( '.woocommerce_variation' ).eq( 0 ).addClass( 'variation-needs-update' );

		},
		get_variation_ids: function () {
			var ids = [];

			$.each($('#variable_product_options .woocommerce_variation'), function () {
				ids.push($(this).find('.upload_image .upload_image_button').prop('rel'));
			});

			return ids;
		},

	};
	$.wc_variation_images.init();
	$('body').on('woocommerce_variations_added woocommerce_variations_loaded', function () {
		$.wc_variation_images.init();
	});

});
