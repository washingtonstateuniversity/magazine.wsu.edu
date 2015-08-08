<?php

get_header();

get_template_part('parts/featured-images');
?>

	<main>

		<?php
get_template_part('parts/site_header');
get_template_part('parts/headers');

if ( function_exists( 'wsuwp_uc_get_object_type_slugs' ) && in_array( get_post_type(), wsuwp_uc_get_object_type_slugs() ) ) {
	if ( 'wsuwp_uc_person' === get_post_type() ) {
		get_template_part( 'parts/single-layout', 'wsuwp_uc_person' );
	} else {
		get_template_part( 'parts/single-layout', 'university-center' );
	}
} else {
	get_template_part( 'parts/single-layout', get_post_type() );
}
?>

			<footer class="main-footer">
				<section class="row halves pager prevnext gutter pad-ends">
					<div class="column one">
						<?php previous_post_link(); ?>
					</div>
					<div class="column two">
						<?php next_post_link(); ?>
					</div>
				</section>
				<!--pager-->
			</footer>

			<?php get_template_part( 'parts/footers' ); ?>

	</main>
	<!--/#page-->

	<?php get_footer(); ?>
