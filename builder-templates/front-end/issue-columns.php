<?php
global $ttfmake_section_data, $ttfmake_sections;

// Default to sidebar right if a section type has not been specified.
$section_type = ( isset( $ttfmake_section_data['section-type'] ) ) ? $ttfmake_section_data['section-type'] : 'wsuwphalves';

if ( 'wsuwpsingle' === $section_type ) {
	$section_layout = 'single';
} else {
	$section_layout = 'halves';
}

// Provide a list matching the number of columns to the selected section type.
$section_type_columns = array(
	'wsuwpsecondary' => 2,
	'wsuwphalves'    => 2,
	'wsuwpsingle'    => 1,
);

// Retrieve data for the column being output.
$data_columns = spine_get_column_data( $ttfmake_section_data, $section_type_columns[ $section_type ] );

// If a child theme or plugin has declared a section ID, we handle that.
// This may be supported in the parent theme one day.
$section_id  = ( isset( $ttfmake_section_data['section-id'] ) ) ? $ttfmake_section_data['section-id'] : '';

// If a section ID is not available for use, we build a default ID.
if ( '' === $section_id ) {
	$section_id = 'builder-section-' . esc_attr( $ttfmake_section_data['id'] );
} else {
	$section_id = sanitize_key( $section_id );
}
?>
	<section id="<?php echo esc_attr( $section_id ); ?>" class="row <?php echo esc_attr( $section_layout ); if ( 'wsuwpsecondary' === $section_type ) { echo ' secondary-articles'; } ?>">
	<?php
	if ( ! empty( $data_columns ) ) {
		// We output the column's number as part of a class and need to track count.
		$column_count = array( 'one', 'two', 'three', 'four' );
		$count = 0;
		foreach ( $data_columns as $column ) {
			$sections = wp_get_object_terms( $column['post-id'], 'wsu_magazine_section', array( 'fields' => 'names' ) );
			$web_extra = ( is_array( $sections ) && isset( $sections[0] ) && 'Web Extra' === $sections[0] ) ? ' web-extra' : '';

			$headline = isset( $column['headline'] ) && ! empty( $column['headline'] );
			$headline_attribute = ( $headline ) ? ' headline="' . esc_html( $column['headline'] ) . '"': '';

			$subtitle = isset( $column['subtitle'] ) && ! empty( $column['subtitle'] );
			$subtitle_attribute = ( $subtitle ) ? ' subtitle="' . esc_html( $column['subtitle'] ) . '"': '';

			$background_size = ( isset( $column['background-size'] ) && ! empty( $column['background-size'] ) ) ? esc_html( $column['background-size'] ) : 'full';

			$background_image = isset( $column['background-id'] ) && ! empty( $column['background-id'] );
			$background_attribute = ( $background_image ) ? ' background="' . esc_url( wp_get_attachment_image_src( $column['background-id'], $background_size )[0] ) . '"': '';

			$background_position = isset( $column['background-position'] ) && ! empty( $column['background-position'] );
			$background_position_attribute = ( $background_position && 'wsuwpsecondary' !== $section_type ) ? ' background_position="' . esc_html( $column['background-position'] ) . '"': '';

			?>
			<div class="column <?php echo $column_count[ $count ]; $count++; echo $web_extra; ?>">

				<?php if ( isset( $column['post-id'] ) && ! empty( $column['post-id'] ) ) : ?>
				[home_headline id="<?php echo $column['post-id']; ?>" wrapper="a"<?php echo $headline_attribute . $subtitle_attribute . $background_attribute . $background_position_attribute; ?>]
				<?php endif; ?>

			</div>
		<?php
		}
	}
	?>
	</section>
<?php
