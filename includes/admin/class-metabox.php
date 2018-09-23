<?php

namespace Pluginever\WCVariationImages\Admin;
class MetaBox {
	
	function __construct() {
		//add metabox to variation box
        add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variable_fields' ), 10,3 );
		
		add_action( 'woocommerce_product_after_variable_attributes_js', array( $this, 'variable_fields_js' ) );
		
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variable_fields' ), 10,1 );
	}
	
	
	
	function variable_fields( $loop, $variation_data, $variation ) {
		//varriable
		$variation_id   = absint( $variation->ID );
		$gallery_images = get_post_meta( $variation_id, 'image_attachment_id', true );
		
		wp_enqueue_media();
	
		?>
			
			<div class='image-preview-wrapper'>
				<img id='image-preview' src='<?php echo wp_get_attachment_url( get_post_meta($variation->ID, 'image_attachment_id', true)  ); ?>' height='100'>
			</div>
			<input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload image' ); ?>" />
			
			<?php
			// Hidden field
			woocommerce_wp_hidden_input(
			array( 
				'id'    => 'image_attachment_id['.$loop.']', 
				'value' => get_post_meta($variation->ID, 'image_attachment_id', true)
				)
			);
			?>
			
		<?php
	
		
		
		//jquery
		$my_saved_attachment_post_id = get_post_meta($variation->ID, 'image_attachment_id', true);
	
		?><script type='text/javascript'>
	
			jQuery( document ).ready( function( $ ) {
	
				// Uploading files
				var file_frame;
				var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
				var set_to_post_id = <?php echo $my_saved_attachment_post_id; ?>; // Set this
	
				jQuery('#upload_image_button').on('click', function( event ){
	
					event.preventDefault();
	
					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						// Set the post ID to what we want
						file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						// Open frame
						file_frame.open();
						return;
					} else {
						// Set the wp.media post id so the uploader grabs the ID we want when initialised
						wp.media.model.settings.post.id = set_to_post_id;
					}
	
					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({
						title: 'Select a image to upload',
						button: {
							text: 'Use this image',
						},
						multiple: true	// Set to true to allow multiple files to be selected
					});
	
					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {
						// We set multiple to false so only get one image from the uploader
						attachment = file_frame.state().get('selection').first().toJSON();
	
						// Do something with attachment.id and/or attachment.url here
						$( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
						$( '#image_attachment_id' ).val( attachment.id );
						alert(attachment.id);
	
						// Restore the main post ID
						wp.media.model.settings.post.id = wp_media_post_id;
					});
	
						// Finally, open the modal
						file_frame.open();
				});
	
				// Restore the main ID when the add media button is pressed
				jQuery( 'a.add_media' ).on( 'click', function() {
					wp.media.model.settings.post.id = wp_media_post_id;
				});
			});
	
		</script>
			
		<?php	
	}
	
	
	
	function variable_fields_js() {
		?>
		<tr>
			<td>
				<?php
				// Hidden field
				woocommerce_wp_hidden_input(
				array( 
					'id'    => '_hidden_field[ + loop + ]', 
					'value' =>  ''
					)
				);
				?>
			</td>
		</tr>
		<?php
	}
	
	
	
	
	function save_variable_fields( $post_id ){
		
		if (isset( $_POST['variable_sku'] ) ) {
		$variable_sku          = $_POST['variable_sku'];
		$variable_post_id      = $_POST['variable_post_id'];
		error_log(print_r($_POST, true));
		// Text Field
		$_text_field = $_POST['image_attachment_id'];
		for ( $i = 0; $i < sizeof( $variable_sku ); $i++ ){
			$variation_id = (int) $variable_post_id[$i];
			if ( isset( $_text_field[$i] ) ) {
				update_post_meta( $variation_id, 'image_attachment_id', stripslashes( $_text_field[$i] ) );
			}
		}
		}
	}
		
	
}
