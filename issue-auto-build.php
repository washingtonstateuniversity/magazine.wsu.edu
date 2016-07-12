<?php
// Auto build a magazine issue from available articles.
$wsu_magazine_section = WSU_Magazine_Section();
?>
<main class="automatic-issue">

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'parts/headers' ); ?>

		<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php
			$magazine_issue_terms = get_the_terms( get_the_ID(), WSU_Magazine_Issue()->taxonomy_slug );
			if ( 1 <= count( $magazine_issue_terms ) ) {
				$magazine_issue = $magazine_issue_terms[0]->slug;
				$magazine_articles = get_posts( array(
					'posts_per_page' => 100,
					'tax_query'      => array(
						array(
							'taxonomy' => WSU_Magazine_Issue()->taxonomy_slug,
							'field' => 'slug',
							'terms' => $magazine_issue,
						),
					),
				) );

				$column_class = array( 'one', 'two' );
				$column = 0;
				foreach ( $magazine_articles as $article ) {
					if ( 0 === $column ) {
						echo '<section class="row halves secondary-articles">';
					}

					$thumbnail_id = get_post_thumbnail_id( $article->ID );
					$thumbnail_src = false;
					if ( $thumbnail_id ) {
						$thumbnail = wp_get_attachment_image_src( $thumbnail_id );
						if ( is_array( $thumbnail ) ) {
							$thumbnail_src = $thumbnail[0];
						}
					}

					if ( $thumbnail_src ) {
						$background_image = 'style="background-image: url(' . esc_url( $thumbnail_src ) . ');"';
						$extra_class = '';
					} else {
						$background_image = '';
						$extra_class = ' no-thumbnail';
					}
					?>
					<div class="column <?php echo $column_class[ $column ]; ?>">
						<a class="home-link-wrap wsu-home-palette-text-default " href="<?php echo esc_url( get_the_permalink( $article->ID ) ); ?>">
							<div <?php echo $background_image; ?> class="home-headline headline-has-background<?php echo $extra_class; ?>">
								<div>
									<div class="home-headline-head-wrapper">
										<h2><?php echo strip_tags( get_the_title( $article->ID ), '<em><strong>' ); ?></h2>
										<div class="article-section"><?php echo esc_html( $wsu_magazine_section->get_article_section( $article->ID ) ); ?></div>
										<div class="home-subtitle"></div>
									</div>
								</div>
							</div>
						</a>
					</div>
					<?php
					if ( 1 === $column ) {
						echo '</section>';
					}
					$column = $column === 0 ? 1 : 0; // Alternate columns
				}
			}
			?>
		</div>

	<?php endwhile; endif; ?>

	<?php get_template_part( 'parts/footers' ); ?>

</main>
