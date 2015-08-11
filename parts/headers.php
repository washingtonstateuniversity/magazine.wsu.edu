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

?>
	<header class="main-header">
		<div class="header-group hgroup">
			<h1 class="h1-header" data-sitename="<?php echo $spine_main_header_values['site_name']; ?>" data-pagetitle="<?php echo $spine_main_header_values['page_title']; ?>" data-posttitle="<?php echo $spine_main_header_values['post_title']; ?>" data-default="<?php echo esc_html($spine_main_header_values['sub_header_default']); ?>" data-alternate="<?php echo esc_html($spine_main_header_values['sub_header_alternate']); ?>"><?php echo strip_tags( $spine_main_header_values['sub_header_default'], '<a>' ); ?></h1>

			<?php if ( is_singular() ) : ?>
			<cite class="article-author" role="author"><?php echo esc_html( magazine_get_author() ); ?></cite>
			<?php endif; ?>

		</div>
	</header>
<?php

get_template_part( 'parts/social-share-bar' );

endif;
