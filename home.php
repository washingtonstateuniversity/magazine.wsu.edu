<?php

get_header();

get_template_part( 'parts/featured-images' );
?>
<main>

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'parts/site-header' ); ?>
		<?php get_template_part( 'parts/headers' ); ?>

		<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php

			/**
			 * `the_content` is fired on builder template pages while it is saved
			 * rather than while it is output in order for some advanced tags to
			 * survive the process and to avoid autop issues.
			 */
			remove_filter( 'the_content', 'wpautop', 10 );
			the_content();
			add_filter( 'the_content', 'wpautop', 10 );

			?>
		</div><!-- #post -->

	<?php endwhile; endif; ?>

	<footer class="main-footer">
		<nav class="magazine-nav">
			<a href="https://magazine.wsu.edu">Current Issue</a>
			<a href="https://magazine.wsu.edu/about">About <em>WSM</em></a>
			<a href="https://magazine.wsu.edu/advertising">Advertising</a>
			<a href="https://magazine.wsu.edu/contact">Contact</a>
			<a href="http://wsm.wsu.edu/ourstory/index.php?title=Main_Page">Our Story</a>
		</nav>
		<div class="wsu-logo"><img src="https://repo.wsu.edu/spine/develop/marks/wsu-signature-horizontal.svg" alt="Washington State University"></div>
		<nav class="wsu-nav">
			<a href="https://wsu.edu">WSU Home</a>
			<a href="https://news.wsu.edu">News</a>
			<a href="http://alumni.wsu.edu">Alumni/Friends</a>
			<a href="http://wsucougars.com">Athletics</a>
			<a href="https://foundation.wsu.edu">Give to WSU</a>
		</nav>
	</footer>

	<?php get_template_part( 'parts/footers' ); ?>

</main>
<!--/#page-->

<?php get_footer(); ?>
