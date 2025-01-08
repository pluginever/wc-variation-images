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
		mainImageLink.attr("src", newSrc);
		mainImageLink.attr("href", newSrc);
		mainImageLink.data("zoom-image", newSrc);
	});

	function load_slider() {
		var swiper = new Swiper(".mySwiper", {
			loop: true,
			spaceBetween: 10,
			slidesPerView: 4,
			freeMode: true,
			watchSlidesProgress: true,
		});
		var swiper2 = new Swiper(".mySwiper2", {
			loop: true,
			spaceBetween: 10,
			autoplay: {
				delay: 2500,
				disableOnInteraction: false,
			},
			navigation: {
				nextEl: ".swiper-button-next",
				prevEl: ".swiper-button-prev",
			},
			thumbs: {
				swiper: swiper,
			},
		});

		$("[data-fancybox='gallery']").fancybox({
			loop: true,
			protect: true,
			buttons: [
				'slideShow',
				'fullScreen',
				'thumbs',
				'close'
			],
		});

		$('.product-image').elevateZoom({
			zoomType: "lens",
			lensShape: "round",
			lensSize: 200,
			scrollZoom: true,
			cursor: "crosshair",
		});
	}
	load_slider();

})(window, document, jQuery);
