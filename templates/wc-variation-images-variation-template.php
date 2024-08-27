<?php

defined( 'ABSPATH' ) || exit;
?>
<script type="text/html" id="tmpl-wc-variation-images">
	<li class="wc-variation-images-image-info">
		<input type="hidden" name="wc_variation_images_image_variation_thumb[{{data.variation_id}}][]"
				value="{{data.image_id}}">
		<img src="{{data.image_url}}">
		<span class="wc-variation-images-remove-image dashicons dashicons-dismiss"></span>
	</li>
</script>
