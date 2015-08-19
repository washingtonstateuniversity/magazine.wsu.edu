<?php
	//this is where the top site header content will be populated
?>

	<header class="site-header">
		<div class="site-header-issue sh-item">
			<?php echo esc_html( magazine_get_issue_name() ); ?>
		</div>
		<nav class="site-header-nav sh-item">
			<a href="https://stage.magazine.wsu.edu/e-magazine/">eMag</a>
			<a href="https://stage.magazine.wsu.edu/video/">Media</a>
			<a href="#">Search</a>
			<a href="https://stage.magazine.wsu.edu/past-issues/">Issues</a>
			<a href="#">Connect</a>
		</nav>
		<div class="site-header-logo sh-item"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/WSM_logo.svg' ); ?>" alt="Washington State Magazine"></div>
	</header>
