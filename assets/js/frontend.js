/**
 * WC Variation Images Frontend Script
 * Handles the dynamic loading of variation-specific images in the WooCommerce product gallery.
 *
 * @author      PluginEver
 * @copyright   2025 PluginEver
 * @license     GPLv2+
 */

/*jslint browser: true */
/*global jQuery:false */

(function (window, document, $) {
	'use strict';

	// Selectors and constants
	const VARIATION_INPUT_SELECTOR = 'input.variation_id';
	const VARIATION_FORM_SELECTOR = 'form.variations_form';
	const VARIATION_SELECT_SELECTOR = 'table.variations select';
	const GALLERY_SELECTOR = '.woocommerce-product-gallery';
	const GALLERY_IMAGE_SELECTOR = '.woocommerce-product-gallery__image';

	/**
	 * Event: On variation select blur
	 * Handles updating the product gallery with variation-specific images.
	 */
	$(VARIATION_FORM_SELECTOR).on('blur', VARIATION_SELECT_SELECTOR, function () {
		const productId = $(VARIATION_FORM_SELECTOR).data('product_id');
		const variationId = $(VARIATION_INPUT_SELECTOR).val();
		const $gallery = $(GALLERY_SELECTOR);
		const $galleryImage = $(GALLERY_IMAGE_SELECTOR);

		// Cache current gallery dimensions
		const galleryWidth = $galleryImage.outerWidth();
		const galleryHeight = $galleryImage.outerHeight();

		// Ensure product ID is valid
		if (productId > 0) {
			const $productElement = $(`#product-${productId}`);

			$.ajax({
				url: WC_VARIATION_IMAGES.ajaxurl,
				type: 'POST',
				data: {
					action: 'wc_variation_images_load_variation_images',
					product_id: productId,
					variation_id: variationId,
					nonce: WC_VARIATION_IMAGES.nonce
				},
				beforeSend: function () {
					// Optionally, add a loader if required
					//$productElement.addClass('loading');
				},
				success: function (response) {
					// Check for successful response
					if (response && response.data && response.data.images) {
						const $galleryParent = $gallery.parent();

						// Remove old gallery and prepend new gallery images
						$gallery.remove();
						$galleryParent.prepend(response.data.images);

						// Adjust gallery image dimensions for smooth transition
						$(GALLERY_IMAGE_SELECTOR).width(galleryWidth).height(galleryHeight);
						$('.woocommerce-product-gallery__wrapper').height(galleryHeight);

						// Reinitialize WooCommerce gallery functionality
						$(GALLERY_SELECTOR).each(function () {
							$(this).wc_product_gallery();
						});
						const mainImage = $(".main-image");
						const mainImageLink = $("#image-link");
						const firstThumbnail = $(".thumbnail").first();
						mainImage.attr("src", firstThumbnail.attr('src'));
						mainImageLink.attr("href", firstThumbnail.attr('src'));
						// $(".wc-variation-images-gallery").css( 'height', '100%');
						load_slider();
						if ( 'no' === WC_VARIATION_IMAGES.i18n.hide_image_zoom ) {
							zoomFunction();
							dataZoom();
						}
					}
				},
				error: function () {
					console.error('Failed to load variation images.');
				},
				complete: function () {
					// Remove loader after request completes
					//$productElement.removeClass('loading');
				}
			});
		}
	});

	$(document).on("click", ".thumbnail", function () {
		var mainImage = $("#main-image");
		const mainImageLink = $("#image-link");
		var newSrc = $(this).attr("src");
		mainImage.attr("src", newSrc);
		mainImageLink.attr("href", newSrc);
		mainImageLink.data("zoom-image", newSrc);
		$('.wcvi-image-zoom').css({ 'background-image': 'url(' + $(this).attr('src') + ')' })
	});

	function load_slider() {
		var slider_settings = JSON.parse( WC_VARIATION_IMAGES.i18n.slider_data );
		var enable_slider = "yes" === slider_settings.enable_slider ? { delay: slider_settings.slider_delay, disableOnInteraction: false } : "false";
		var navigation = "no" === slider_settings.hide_navigation ? false : {
			nextEl: ".swiper-button-next",
			prevEl: ".swiper-button-prev",
		};
		var swiper = new Swiper(".mySwiper", {
			loop: "no" === slider_settings.slider_loop ? false : true,
			spaceBetween: slider_settings.items_space ? slider_settings.items_space : 4,
			slidesPerView: slider_settings.items_per_page ? slider_settings.items_per_page : 4,
			freeMode: true,
			watchSlidesProgress: true,
		});
		var swiper2 = new Swiper(".mySwiper2", {
			loop: "no" === slider_settings.slider_loop ? false : true,
			navigation: navigation,
			autoplay: enable_slider,
			thumbs: {
				swiper: swiper,
			},
		});

		if( "no" === slider_settings.hide_navigation ) {
			$(".swiper-button-next").hide();
			$(".swiper-button-prev").hide();
		}

		var lightbox_data = JSON.parse( WC_VARIATION_IMAGES.i18n.lightbox_data );
		$("[data-fancybox='gallery']").fancybox({
			loop: true,
			protect: true,
			buttons: [
				"zoom",
				lightbox_data.slideShow,
				lightbox_data.fullScreen,
				lightbox_data.share,
				"close"
			],
			zoom: {
				enabled: true,
				duration: 300
			},
			thumbs: {
				autoStart: true,
				axis: "x",
			},
		});
	}

	function zoomFunction() {
		$('.selected-image')
			.on('mouseover', function () {
				$(this).children('.wcvi-image-zoom').css({
					'transform': 'scale(' + $(this).attr('data-scale') + ')',
					'transition': 'all .5s'
				});
			})
			.on('mouseout', function () {
				$(this).children('.wcvi-image-zoom').css({ 'transform': 'scale(1)', 'transition': 'all .5s' });
			})
			.on('mousemove', function (e) {
				$(this).children('.wcvi-image-zoom').css({
					'transform-origin': ((e.pageX - $(this).offset().left) / $(this).width()) * 100 + '% ' + ((e.pageY - $(this).offset().top) / $(this).height()) * 100 + '%', 'transition': 'transform 1s ease-in'
				});
			})
			.each(function () {
				var icon = $(this).find('img').data('type');
				var photoLength = $(this).find('.wcvi-image-zoom').length;
				if (photoLength === 0 && !icon) {
					$(this).append('<div class="wcvi-image-zoom"></div>');
				}
				$(this).children('.wcvi-image-zoom').css({ 'background-image': 'url(' + $(this).find('img').attr('src') + ')' });
			});
	}

	function dataZoom() {
		$('.selected-image').on('mouseenter mouseleave', function () {
			$(this).attr('data-scale', '1.5');
			var img = $(this).find('img').attr('src');
			$(this).attr('data-image', img);
		});
	}

	load_slider();
	if ( 'no' === WC_VARIATION_IMAGES.i18n.hide_image_zoom ) {
		zoomFunction();
		dataZoom();
	}

})(window, document, jQuery);
