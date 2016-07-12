<?php

spine_load_section_header();

global $ttfmake_section_data, $ttfmake_is_js_template;

if ( in_array( $ttfmake_section_data['section']['id'], array( 'wsuwphalves', 'wsuwpsecondary' ) ) ) {
	$wsuwp_range = 2;
} else {
	$wsuwp_range = 1;
}

// We didn't always treat single as a column layout. Provide a shim for the old data structure.
if ( 'wsuwpsingle' === $ttfmake_section_data['section']['id'] ) {
	if ( ! empty( $ttfmake_section_data['data']['content'] ) ) {
		$ttfmake_section_data['data']['columns'][1]['content'] = $ttfmake_section_data['data']['content'];
	}

	if ( ! empty( $ttfmake_section_data['data']['title'] ) ) {
		$ttfmake_section_data['data']['columns'][1]['title'] = $ttfmake_section_data['data']['title'];
	}
}

$section_name   = ttfmake_get_section_name( $ttfmake_section_data, $ttfmake_is_js_template );
$section_order  = ( ! empty( $ttfmake_section_data['data']['columns-order'] ) ) ? $ttfmake_section_data['data']['columns-order'] : range( 1, $wsuwp_range );

?>
	<div class="wsuwp-spine-halves-stage">
		<?php $j = 1; foreach ( $section_order as $key => $i ) : ?>
			<?php
			$column_name = $section_name . '[columns][' . $i . ']';
			$article_id  = ( isset( $ttfmake_section_data['data']['columns'][ $i ]['post-id'] ) ) ? $ttfmake_section_data['data']['columns'][ $i ]['post-id'] : '';
			$visible     = ( isset( $ttfmake_section_data['data']['columns'][ $i ]['toggle'] ) ) ? $ttfmake_section_data['data']['columns'][ $i ]['toggle'] : 'visible';

			if ( ! in_array( $visible, array( 'visible', 'invisible' ) ) ) {
				$visible = 'visible';
			}

			if ( 'invisible' === $visible ) {
				$column_style = ' display: none;';
				$toggle_class = ' wsuwp-toggle-closed';
			} else {
				$column_style = '';
				$toggle_class = '';
			}
			?>
			<div class="wsuwp-spine-builder-column wsuwp-spine-builder-column-position-<?php echo $j; ?>" data-id="<?php echo $i; ?>">
				<input type="hidden" class="wsuwp-column-visible wsm-article-meta" name="<?php echo $column_name; ?>[toggle]" value="<?php echo $visible; ?>" aria-hidden="true" />
				<input type="hidden" class="wsuwp-column-post-id wsm-article-meta" name="<?php echo $column_name; ?>[post-id]" value="<?php echo $article_id; ?>" aria-hidden="true" />
				<div class="spine-builder-column-overlay">
					<div class="spine-builder-column-overlay-wrapper">
						<div class="spine-builder-overlay-header">
							<div class="spine-builder-overlay-title">Configure Article</div>
							<div class="spine-builder-column-overlay-close">Done</div>
						</div>
						<div class="spine-builder-overlay-body">
							<?php wsm_issue_article_configuration_output( $column_name, $ttfmake_section_data, $j ); ?>
						</div>
					</div>
				</div>

				<?php if ( $article_id ) : ?>
				<?php
					$headline            = ( isset( $ttfmake_section_data['data']['columns'][ $i ]['headline'] ) ) ? $ttfmake_section_data['data']['columns'][ $i ]['headline'] : '';
					$subtitle            = ( isset( $ttfmake_section_data['data']['columns'][ $i ]['subtitle'] ) ) ? $ttfmake_section_data['data']['columns'][ $i ]['subtitle'] : '';
					$background_id       = ( isset( $ttfmake_section_data['data']['columns'][ $i ]['background-id'] ) ) ? $ttfmake_section_data['data']['columns'][ $i ]['background-id'] : '';
					$background_size     = ( isset( $ttfmake_section_data['data']['columns'][ $i ]['background-size'] ) ) ? $ttfmake_section_data['data']['columns'][ $i ]['background-size'] : '';
					$background_image    = ( ! empty( $background_id ) ) ? wp_get_attachment_image_src( $background_id, $background_size )[0] : '';
					$background_full     = ( ! empty( $background_id ) ) ? wp_get_attachment_image_src( $background_id, 'full' )[0] : '';
					$background_position = ( isset( $ttfmake_section_data['data']['columns'][ $i ]['background-position'] ) ) ? $ttfmake_section_data['data']['columns'][ $i ]['background-position'] : '';
					$display_headline    = ( ! empty( $headline ) ) ? $headline : get_post_meta( $article_id, '_wsu_home_headline', true );
					$display_subtitle    = ( ! empty( $subtitle ) ) ? $subtitle : get_post_meta( $article_id, '_wsu_home_subtitle', true );
					$sections            = wp_get_object_terms( $article_id, 'wsu_magazine_section', array( 'fields' => 'names' ) );
					$section             = ( $sections ) ? $sections[0] : '';
					$article_classes     = '';
					$article_styles      = '';

					if ( ! empty( $background_image ) ) {
						$article_classes .= ' has-featured-img';
						$article_styles  .= ' background-image: url(' . esc_url( $background_image ) . ');';
					}

					if ( ! empty( $background_position ) ) {
						$article_styles  .= ' background-position: ' . esc_attr( str_replace( "-", " ", $background_position ) ) . ';';
					}

					if ( ! empty( $section ) && 'Web Extra' === $section ) {
						$article_classes .= ' web-extra';
					}

					$issues = wp_get_object_terms( $article_id, 'wsu_mag_issue_tax' );
					if ( 1 >= count( $issues ) ) {
						$issue = explode( ' ', $issues[0]->name );
						$article_classes .= ' season-' . esc_attr( strtolower( $issue[0] ) );
					}

					$background_sizes = '';
					if ( ! empty( $background_id ) ) {
						$sizes = array( 'thumbnail', 'medium', 'large', 'spine-large_size' );

						foreach ( $sizes as $size ) {
							$image = wp_get_attachment_image_src( $background_id, $size );

							if ( ! empty( $image ) ) {
								$name = ucfirst( $size ) . ' (' . $image[1] . 'x' . $image[2] . ')';
								$background_sizes .= $size . ':' . $name . ',';
							}
						}
						$background_sizes = rtrim( $background_sizes, ',' );
					}
				?>
				<div id="issue-article-<?php echo esc_attr( $article_id ); ?>"
					 class="issue-article"
					 data-headline="<?php echo esc_attr( $headline ); ?>"
					 data-subtitle="<?php echo esc_attr( $subtitle ); ?>"
					 data-background-id="<?php echo esc_attr( $background_id ); ?>"
					 data-background-position="<?php echo esc_attr( $background_position ); ?>"
					 data-background-image="<?php echo esc_url( $background_image ); ?>"
					 data-background-image-full="<?php echo esc_url( $background_full ); ?>"
					 data-background-sizes="<?php echo esc_attr( $background_sizes ); ?>"
					 data-background-size="<?php echo esc_attr( $background_size ); ?>">
					<div class="ttfmake-sortable-handle ui-sortable-handle" title="Drag-and-drop this article into place">
						<a href="#" class="spine-builder-column-configure"><span>Configure this column</span></a>
						<a href="#" class="wsuwp-column-toggle" title="Click to toggle"><div class="handlediv<?php echo $toggle_class; ?>" aria-expanded="true"></div></a>
						<div class="wsuwp-builder-column-title"><?php echo get_the_title( $article_id ); ?></div>
					</div>
					<div class="wsm-article-body wsuwp-column-content<?php echo $article_classes; ?>" style="<?php echo $article_styles; ?>">
						<div class="home-headline-head-wrapper">
							<h2><?php echo esc_html( $display_headline ); ?></h2>
							<div class="article-section"><?php echo esc_html( $section ); ?></div>
							<div class="home-subtitle"><?php echo esc_html( $display_subtitle ); ?></div>
						</div>
					</div>
				</div>
				<?php endif; ?>

			</div>
			<?php
			$j++;
		endforeach; ?>
	</div>

	<div class="clear"></div>
	<div class="spine-builder-overlay">
		<div class="spine-builder-overlay-wrapper">
			<div class="spine-builder-overlay-header">
				<div class="spine-builder-overlay-title">Configure Section</div>
				<div class="spine-builder-overlay-close">Done</div>
			</div>
			<div class="spine-builder-overlay-body">
				<?php
				spine_output_builder_section_label( $section_name, $ttfmake_section_data );
				do_action( 'spine_output_builder_section', $section_name, $ttfmake_section_data, 'columns' );
				?>
			</div>
		</div>
	</div>
	<input type="hidden" value="<?php echo esc_attr( implode( ',', $section_order ) ); ?>" name="<?php echo $section_name; ?>[columns-order]" class="wsuwp-spine-builder-columns-order" />
	<input type="hidden" class="ttfmake-section-state" name="<?php echo $section_name; ?>[state]" value="<?php
	if ( isset( $ttfmake_section_data['data']['state'] ) ) {
		echo esc_attr( $ttfmake_section_data['data']['state'] );
	} else {
		echo 'open';
	} ?>" />
<?php
spine_load_section_footer();
