<?php

// If a background image is assigned to the post, attach it as a background to jacket.
if ( spine_has_background_image() ) {
	$background_image_src = spine_get_background_image_src();

	?>
	<style>
		#jacket {
			background-image: url('<?php echo esc_url( $background_image_src ); ?>');
		}

	</style>
	<?php
}

// If a featured image is assigned to the post, output it as a figure with a background image accordingly.
if ( spine_has_featured_image() ) {
	$featured_image_src = spine_get_featured_image_src();

	?>
		<style>
			.main-header {
				background-image: url('<?php echo esc_url( $featured_image_src ); ?>');
			}

		</style>
		<?php
}
