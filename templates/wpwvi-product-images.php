<?php

defined( 'ABSPATH' ) || exit;

global $product;

$product_id   = $product->get_id();
$product_type = $product->get_type();

$columns = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );

$post_thumbnail_id = $product->get_image_id();

$default_sizes  = wp_get_attachment_image_src( $post_thumbnail_id, 'woocommerce_single' );
$default_height = $default_sizes[2];
$default_width  = $default_sizes[1];

$attachment_ids = $product->get_gallery_image_ids();

$has_thumbnail = ( has_post_thumbnail() && ( count( $attachment_ids ) > 0 ) );

$slider_options = array(
	'slidesToShow'   => 1,
	'slidesToScroll' => 1,
	'arrows'         => false,
	'adaptiveHeight' => true,
	"rows"           => 0
);

$wrapper_classes = apply_filters('woocommerce_single_product_image_gallery_classes', array(
	'wpwvi-images',
	'wpwvi-images-columns-' . absint($columns),
	$has_thumbnail ? 'wpwvi-has-product-thumbnail' : ''
));
?>

<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" >
	<div class="loading-slider wpwvi-slider-wrapper rtwpvg-product-type-<?php echo esc_attr($product_type) ?>">
		<div class="wpwvi-container ">
			<div class="wpwvi-slider-wrapper">
				<div class="wpwvi-slider" data-slick="<?php echo wp_json_encode($slider_options); ?>">
					<?php
						if(has_post_thumbnail()){
							$html = wc_get_gallery_image_html( $post_thumbnail_id, true );
						} else{
							$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
							$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'wc-variation-images' ) );
							$html .= '</div>';
						}
						echo $html;
					?>
				</div>
			</div>
			<div class="wpwvi-thumb-wrapper">
				<div class="wpwvi-thumb-list">
					<?php
					if ($has_thumbnail):
						// Main Image
						echo wc_get_gallery_image_html($post_thumbnail_id);

						// additional variation image
						foreach ($attachment_ids as $key => $attachment_id) :
							echo wc_get_gallery_image_html($attachment_id, false);
						endforeach;
					endif;
					?>
				</div>
			</div>
		</div>
	</div>
</div>


