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

		</div>
	</header>
<?php

get_template_part( 'parts/social-share-bar' );

endif;
