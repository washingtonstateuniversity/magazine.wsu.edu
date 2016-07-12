<?php

get_header();

get_template_part( 'parts/featured-images' );
?>

	<main>

<?php
get_template_part( 'parts/site-header' );
get_template_part( 'parts/headers' );
get_template_part( 'parts/single-layout', get_post_type() );

get_template_part( 'parts/footers' );
?>
	</main>
	<!--/#page-->

	<?php get_footer(); ?>
