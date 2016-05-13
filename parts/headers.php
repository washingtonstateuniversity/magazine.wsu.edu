<?php

/**
 * Retrieve an array of values to be used in the header.
 *
 * site_name
 * site_tagline
 * page_title
 * post_title
 * section_title
 * subsection_title
 * posts_page_title
 * sup_header_default
 * sub_header_default
 * sup_header_alternate
 * sub_header_alternate
 */
$spine_main_header_values = spine_get_main_header();

if ( spine_get_option( 'main_header_show' ) == 'true' ) :

	$featured_image_position = get_post_meta( get_the_ID(), '_featured_image_position', true );

	if ( ! $featured_image_position || $featured_image_position !== sanitize_html_class( $featured_image_position ) ) {
		$featured_image_position = 'background-position-center';
	}
?>
	<header class="main-header <?php echo $featured_image_position; ?>">
		<div class="header-group hgroup">
			<h1 class="h1-header" data-sitename="<?php echo $spine_main_header_values['site_name']; ?>" data-pagetitle="<?php echo $spine_main_header_values['page_title']; ?>" data-posttitle="<?php echo $spine_main_header_values['post_title']; ?>" data-default="<?php echo esc_html($spine_main_header_values['sub_header_default']); ?>" data-alternate="<?php echo esc_html($spine_main_header_values['sub_header_alternate']); ?>"><?php echo strip_tags( $spine_main_header_values['sub_header_default'], '<a>' ); ?></h1>

			<?php if ( is_singular() ) : ?>
			<cite class="article-author" role="author">by <?php coauthors_posts_links(); ?></cite>
			<?php endif; ?>

			<?php if ( is_singular() && spine_has_featured_image() && get_post( get_post_thumbnail_id() )->post_excerpt ) : ?>
			<?php $image_data = wp_get_attachment_image_src( get_post_thumbnail_id(), 'spine-xlarge_size' ); ?>
			<a class="image-information"
				href="<?php echo esc_url( spine_get_featured_image_src() ); ?>"
				title="<?php echo esc_attr( get_post( get_post_thumbnail_id() )->post_excerpt ); ?>">
				<svg viewBox="0 0 11 11" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
					<circle cx="5.5" cy="5.5" r="5.5"/>
					<path d="m5.38,1.92a1,1 0 1 1 -1,1a1,1 0 0 1 1,-1zm1.43,6.56l-2.58,0l0,-1.17l0.46,0l0,-1.77l-0.5,0l0,-1.17l2.16,0l0,2.94l0.46,0l0,1.17z"/>
				</svg>
			</a>
			<?php endif; ?>

		</div>
	</header>
<?php

get_template_part( 'parts/social-share-bar' );

endif;
