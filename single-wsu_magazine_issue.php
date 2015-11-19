<?php

get_header();

get_template_part( 'parts/featured-images' );

$issue_is_using_builder = get_post_meta( get_the_ID(), '_ttfmake-use-builder', true );

if ( $issue_is_using_builder ) {
	get_template_part( 'issue-page-builder' );
} else {
	get_template_part( 'issue-auto-build' );
}

get_footer();