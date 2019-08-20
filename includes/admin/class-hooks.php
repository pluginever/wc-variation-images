<?php

namespace Pluginever\WCVariationImages\Admin;
class Hooks {

	public function __construct() {
		add_action('woocommerce_product_after_variable_attributes', array($this, 'upload_variation_images'), 10, 3);

	}

	/**
	 * Upload variation images
	 *
	 * since 1.0.0
	 * @param $loop
	 * @param $variation_data
	 * @param $variation
	 *
	 * @return void
	 */
	public function upload_variation_images($loop, $variation_data, $variation){
		$variation_id = absint($variation->ID);
		$variation_images = get_post_meta($variation_id, 'wpwvi_variation_images', true);
		?>
		<div class="form-row form-row-full wpwvi-gallery-wrapper">
			<h4><?php esc_html_e('Variation Images', 'wc-variation-image') ?></h4>
			<div class="wpwvi-image-container">
				<ul id="wpwvi-image-list-<?php echo absint($variation_id); ?>" class="wpwvi-image-list">
					<?php
					if (is_array($variation_images) && !empty($variation_images)) {
						foreach ($variation_images as $image_id):
							$image_arr = wp_get_attachment_image_src($image_id);
							?>
							<li class="wpwvi-image-info">
								<input type="hidden" name="wpwvi_image_variation_thumb[<?php echo $variation_id ?>][]"
								       value="<?php echo $image_id ?>">
								<img src="<?php echo esc_url($image_arr[0]) ?>">
								<span class="wpwvi-remove-image dashicons dashicons-dismiss"></span>
							</li>
						<?php endforeach;
					} ?>
				</ul>
			</div>
			<p class="wpwvi-add-container hide-if-no-js">
				<a href="#" data-wpwvi_variation_id="<?php echo absint($variation->ID) ?>"
				   class="button wpwvi-add-image"><?php _e('Add Variation Images', 'wc-variation-image') ?></a>
			</p>
		</div>
		<?php
	}

}
