<?php

defined('ABSPATH') || exit;
?>
<script type="text/html" id="tmpl-wpwvi-image">
	<li class="wpwvi-image-info">
		<input type="hidden" name="wpwvi_image_variation_thumb[{{data.variation_id}}][]"
		       value="{{data.image_id}}">
		<img src="{{data.image_url}}">
		<span class="wpwvi-remove-image dashicons dashicons-dismiss"></span>
	</li>
</script>
