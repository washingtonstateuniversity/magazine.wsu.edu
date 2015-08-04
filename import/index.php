<?php

die( 'remove this line in local :)' );

$wsm_db = mysqli_init();
mysqli_real_connect( $wsm_db, DB_HOST, DB_USER, DB_PASSWORD, 'wsuwp', null, null, 0 );
mysqli_set_charset( $wsm_db, 'latin1' );

function import_magazine_issues() {
	global $wpdb, $wsm_db;

	$get_magazines = mysqli_query( $wsm_db, "SELECT * FROM magazine" );
	$magazine_data = array();

	while ( $issue = mysqli_fetch_assoc( $get_magazines ) ) {
		$magazine_id = absint( $issue['magazine_id'] );
		$magazine_year = absint( $issue['year'] );
		$magazine_month = sanitize_text_field( $issue['month'] );
		$magazine_season = sanitize_text_field( $issue['season'] );
		$magazine_issue_theme = sanitize_text_field( $issue['issue'] );
		$magazine_volume = absint( $issue['volume'] );
		$magazine_issue_number = absint( $issue['issue_number'] );
		$magazine_thumbnail_image_id = absint( $issue['image'] );

		$existing_magazine_id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_magazine_id' AND meta_value = $magazine_id" );
		if ( $existing_magazine_id ) {
			$magazine_term = get_term_by( 'name', $magazine_season . ' ' . $magazine_year, 'wsu_magazine_issue' );
			$magazine_data[ $magazine_id ] = $magazine_term->term_id;
			continue;
		}

		$thumbnail_query = mysqli_query( $wsm_db, "SELECT file_name FROM article_photo WHERE article_photo_id = $magazine_thumbnail_image_id" );
		$thumbnail = mysqli_fetch_object( $thumbnail_query );
		$magazine_thumbnail = sanitize_file_name( $thumbnail->file_name );

		$new_magazine['post_type'] = 'wsu_magazine_issue';
		$new_magazine['post_status'] = 'publish';
		$new_magazine['post_title'] = $magazine_season . ' ' . $magazine_year;
		$new_magazine['post_date'] = $issue['date_created'];
		$new_magazine['post_modified'] = $issue['date_updated'];

		// Insert the magazine issue.
		$new_magazine_id = wp_insert_post( $new_magazine );

		// Insert the magazine issue term.
		$new_magazine_term_id = wp_insert_term( $magazine_season . ' ' . $magazine_year, 'wsu_magazine_issue' );

		// Maintain a relationship between magazine ID and the new taxonomy. We'll use this with articles.
		$magazine_data[ $magazine_id ] = $new_magazine_term_id;

		// Assign the term to the issue.
		wp_set_object_terms( $new_magazine_id, $new_magazine_term_id, 'wsu_magazine_issue' );

		// Store post meta attached to the issue.
		update_post_meta( $new_magazine_id, '_magazine_id', $magazine_id );
		update_post_meta( $new_magazine_id, '_magazine_year', $magazine_year );
		update_post_meta( $new_magazine_id, '_magazine_month', $magazine_month );
		update_post_meta( $new_magazine_id, '_magazine_season', $magazine_season );
		update_post_meta( $new_magazine_id, '_magazine_issue_theme', $magazine_issue_theme );
		update_post_meta( $new_magazine_id, '_magazine_volume', $magazine_volume );
		update_post_meta( $new_magazine_id, '_magazine_issue_number', $magazine_issue_number );
		update_post_meta( $new_magazine_id, '_magazine_thumbnail_id', $magazine_thumbnail_image_id );
		update_post_meta( $new_magazine_id, '_magazine_thumbnail_file', $magazine_thumbnail );

		echo $new_magazine['post_title'] . ' inserted<br>';
	}

	return $magazine_data;
}

function import_magazine_articles() {
	global $wpdb, $wsm_db;

	$magazine_data = import_magazine_issues();

	$get_articles = mysqli_query( $wsm_db, "SELECT * FROM article" );

	while( $article = mysqli_fetch_assoc( $get_articles ) ) {
		$new_post = array();

		$article_id = absint( $article['article_id'] ); // store as post meta, original ID.
		$magazine_id = absint( $article['magazine_id'] );

		$existing_post_id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_magazine_article_id' AND meta_value= $article_id" );

		if ( $existing_post_id ) {
			if ( 1 == $article['research'] ) {
				wp_set_object_terms( $existing_post_id, 'Research', 'category' );
			}

			// Set the site category of article.
			wp_set_object_terms( $existing_post_id, 'Article', 'category', true );
			wp_remove_object_terms( $existing_post_id, 'Uncategorized', 'category' );

			wp_set_object_terms( $existing_post_id, $magazine_data[ $magazine_id ], 'wsu_magazine_issue' );
			continue;
		}

		$new_post['post_type'] = 'post';
		$new_post['post_status'] = 'publish';
		$new_post['post_title'] = $article['title'];
		$new_post['post_excerpt'] = $article['summary'];
		$new_post['post_date'] = $article['date_created'];
		$new_post['post_modified'] = $article['date_updated'];

		// Strip new lines in post content to avoid random line breaks.
		$new_post['post_content'] = str_replace( "\r\n", "\n", $article['content'] );
		$new_post['post_content'] = str_replace( "\n", ' ', $new_post['post_content'] );

		$new_post['post_content'] .= '<div class="article-sidebar">' . $article['sidebar'] . '</div>';
		$new_post['post_content'] .= $article['media'];
		$new_post['post_content'] .= '<div class="article-endcontent">' . $article['end_content'] . '</div>';

		$post_id = wp_insert_post( $new_post );

		// If the research flag is set, assign the category.
		if ( 1 == $article['research'] ) {
			wp_set_object_terms( $post_id, 'Research', 'category', true );
		}

		// Set the site category of article.
		wp_set_object_terms( $post_id, 'Article', 'category', true );

		// Assign the magazine issue term.
		if ( isset( $magazine_data[ $magazine_id ] ) ) {
			wp_set_object_terms( $post_id, $magazine_data[ $magazine_id ], 'wsu_magazine_issue' );
		}

		// Post meta to store.
		$article_file_name = sanitize_text_field( $article['file_name'] ); // store as post meta.
		$article_sidebar = wp_kses_post( $article['sidebar'] );
		$article_media_content = wp_kses_post( $article['media'] );
		$article_end_content = wp_kses_post( $article['end_content'] );

		update_post_meta( $post_id, '_magazine_article_id', $article_id );
		update_post_meta( $post_id, '_magazine_file_name', $article_file_name );
		update_post_meta( $post_id, '_magazine_sidebar_content', $article_sidebar );
		update_post_meta( $post_id, '_magazine_media_content', $article_media_content );
		update_post_meta( $post_id, '_magazine_end_content', $article_end_content );

		// Add each of the author IDs. These will match up later with actual authors.
		$author_query = mysqli_query( $wsm_db, "SELECT article_author_id FROM article_author_as WHERE article_id = $article_id" );
		while ( $article_author = mysqli_fetch_assoc( $author_query ) ) {
			add_post_meta( $post_id, '_magazine_author_id', absint( $article_author['article_author_id'] ) );
		}
	}
}

function import_magazine_reviews() {
	global $wpdb, $wsm_db;

	$review_medium = array(
		1 => 'Book',
		2 => 'Music',
		3 => 'DVD',
		4 => 'Movie',
		6 => 'Website',
		7 => 'Software',
	);

	$review_type = array(
		1 => 'Student',
		2 => 'Faculty',
		3 => 'Alumni',
		4 => 'Staff',
		5 => 'WSU Press',
	);

	$magazine_data = import_magazine_issues();

	$get_reviews = mysqli_query( $wsm_db, "SELECT * FROM review" );

	while( $review = mysqli_fetch_assoc( $get_reviews ) ) {
		$new_post = array();

		$review_id = absint( $review['review_id'] ); // store as post meta, original ID.
		$magazine_id = absint( $review['magazine_id'] );
		$medium_id = absint( $review['medium_id'] );
		$type_id = absint( $review['type_id'] );

		$existing_post_id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_magazine_review_id' AND meta_value= $review_id" );

		if ( $existing_post_id ) {
			// Set the site category of article.
			wp_set_object_terms( $existing_post_id, 'Review', 'category', true );

			if ( isset( $review_medium[ $medium_id ] ) ) {
				wp_set_object_terms( $existing_post_id, $review_medium[ $medium_id ], 'category', true );
			}
			if ( isset( $review_type[ $type_id ] ) ) {
				wp_set_object_terms( $existing_post_id, $review_type[ $type_id ], 'category', true );
			}

			wp_remove_object_terms( $existing_post_id, 'Uncategorized', 'category' );

			if ( isset( $magazine_data[ $magazine_id ] ) ) {
				wp_set_object_terms( $existing_post_id, $magazine_data[ $magazine_id ], 'wsu_magazine_issue' );
			}
			continue;
		}

		$new_post['post_type'] = 'post';
		$new_post['post_status'] = 'publish';
		$new_post['post_title'] = $review['title'];
		$new_post['post_date'] = $review['date_created'];
		$new_post['post_modified'] = $review['date_updated'];

		// Strip new lines in post content to avoid random line breaks.
		$new_post['post_content'] = str_replace( "\r\n", "\n", $review['content'] );
		$new_post['post_content'] = str_replace( "\n", ' ', $new_post['post_content'] );

		$new_post['post_content'] .= '<div class="review-creator">' . $review['creator'] . '</div>';
		$new_post['post_content'] .= '<div class="review-publisher">' . $review['publisher'] . '</div>';
		$new_post['post_content'] .= '<div class="review-location">' . $review['pub_loco'] . '</div>';
		$new_post['post_content'] .= '<div class="review-date">' . $review['pub_date'] . '</div>';
		$new_post['post_content'] .= '<div class="review-isbn">' . $review['isbn'] . '</div>';
		$new_post['post_content'] .= $review['media'];

		$post_id = wp_insert_post( $new_post );

		// Set the site category of review.
		wp_set_object_terms( $post_id, 'Review', 'category', true );

		if ( isset( $review_medium[ $medium_id ] ) ) {
			wp_set_object_terms( $post_id, $review_medium[ $medium_id ], 'category', true );
		}
		if ( isset( $review_type[ $type_id ] ) ) {
			wp_set_object_terms( $post_id, $review_type[ $type_id ], 'category', true );
		}

		// Assign the magazine issue term.
		if ( isset( $magazine_data[ $magazine_id ] ) ) {
			wp_set_object_terms( $post_id, $magazine_data[ $magazine_id ], 'wsu_magazine_issue' );
		}

		// Post meta to store.
		$review_file_name = sanitize_text_field( $review['file_name'] ); // store as post meta.
		$review_publisher = sanitize_text_field( $review['publisher'] );
		$review_creator = sanitize_text_field( $review['creator'] );
		$review_location = sanitize_text_field( $review['pub_loco'] );
		$review_pub_date = sanitize_text_field( $review['pub_date'] );
		$review_isbn = sanitize_text_field( $review['isbn'] );
		$review_media_content = wp_kses_post( $review['media'] );

		update_post_meta( $post_id, '_magazine_review_id', $review_id );
		update_post_meta( $post_id, '_magazine_file_name', $review_file_name );
		update_post_meta( $post_id, '_magazine_review_publisher', $review_publisher );
		update_post_meta( $post_id, '_magazine_review_creator', $review_creator );
		update_post_meta( $post_id, '_magazine_review_location', $review_location );
		update_post_meta( $post_id, '_magazine_review_date', $review_pub_date );
		update_post_meta( $post_id, '_magazine_review_isbn', $review_isbn );
		update_post_meta( $post_id, '_magazine_media_content', $review_media_content );

		// Add each of the author IDs. These will match up later with actual authors.
		$author_query = mysqli_query( $wsm_db, "SELECT article_author_id FROM review_author_as WHERE review_id = $review_id" );
		while ( $review_author = mysqli_fetch_assoc( $author_query ) ) {
			add_post_meta( $post_id, '_magazine_author_id', absint( $review_author['article_author_id'] ) );
		}
	}
}

function import_magazine_web_extras() {
	global $wpdb, $wsm_db;

	$magazine_data = import_magazine_issues();

	$get_extras = mysqli_query( $wsm_db, "SELECT * FROM web_extras" );

	while( $extra = mysqli_fetch_assoc( $get_extras ) ) {
		$new_post = array();

		$extra_id = absint( $extra['web_extras_id'] ); // store as post meta, original ID.
		$magazine_id = absint( $extra['magazine_id'] );

		$existing_post_id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_magazine_web_extras_id' AND meta_value= $extra_id" );

		if ( $existing_post_id ) {
			if ( isset( $magazine_data[ $magazine_id ] ) ) {
				wp_set_object_terms( $existing_post_id, $magazine_data[ $magazine_id ], 'wsu_magazine_issue' );
			}
			continue;
		}

		$new_post['post_type'] = 'wsu_magazine_we';
		$new_post['post_status'] = 'publish';
		$new_post['post_title'] = $extra['title'];
		$new_post['post_excerpt'] = $extra['summary'];
		$new_post['post_date'] = $extra['date_created'];
		$new_post['post_modified'] = $extra['date_updated'];

		// Strip new lines in post content to avoid random line breaks.
		$new_post['post_content'] = str_replace( "\r\n", "\n", $extra['content'] );
		$new_post['post_content'] = str_replace( "\n", ' ', $new_post['post_content'] );

		$new_post['post_content'] .= '<div class="article-sidebar">' . $extra['sidebar'] . '</div>';
		$new_post['post_content'] .= $extra['media'];
		$new_post['post_content'] .= '<div class="article-endcontent">' . $extra['end_content'] . '</div>';

		$post_id = wp_insert_post( $new_post );

		// Assign the magazine issue term.
		if ( isset( $magazine_data[ $magazine_id ] ) ) {
			wp_set_object_terms( $post_id, $magazine_data[ $magazine_id ], 'wsu_magazine_issue' );
		}

		// Post meta to store.
		$extra_file_name = sanitize_text_field( $extra['file_name'] ); // store as post meta.
		$extra_sidebar = sanitize_text_field( $extra['sidebar'] );
		$extra_end_content = sanitize_text_field( $extra['end_content'] );
		$extra_media_content = wp_kses_post( $extra['media'] );

		update_post_meta( $post_id, '_magazine_extra_id', $extra_id );
		update_post_meta( $post_id, '_magazine_file_name', $extra_file_name );
		update_post_meta( $post_id, '_magazine_sidebar_content', $extra_sidebar );
		update_post_meta( $post_id, '_magazine_media_content', $extra_media_content );
		update_post_meta( $post_id, '_magazine_end_content', $extra_end_content );

		// Add each of the author IDs. These will match up later with actual authors.
		$author_query = mysqli_query( $wsm_db, "SELECT article_author_id FROM we_author_as WHERE web_extras_id = $extra_id" );
		while ( $extra_author = mysqli_fetch_assoc( $author_query ) ) {
			add_post_meta( $post_id, '_magazine_author_id', absint( $extra_author['article_author_id'] ) );
		}
	}
}

function import_magazine_authors() {
	global $wsm_db;

	$get_authors = mysqli_query( $wsm_db, "SELECT article_author_id, first_name, last_name FROM article_author" );

	while( $author = mysqli_fetch_assoc( $get_authors ) ) {
		$author_id = absint( $author['article_author_id'] );
		$author_first = $author['first_name'];
		$author_last = $author['last_name'];

		$new_post = array();
		$new_post['post_type'] = 'wsu_magazine_author';
		$new_post['post_status'] = 'publish';
		$new_post['post_title'] = $author['first_name'] . ' ' . $author['last_name'];

		$post_id = wp_insert_post( $new_post );

		update_post_meta( $post_id, '_magazine_author_first_name', sanitize_text_field( $author['first_name'] ) );
		update_post_meta( $post_id, '_magazine_author_last_name', sanitize_text_field( $author['last_name'] ) );
		update_post_meta( $post_id, '_magazine_author_id', $author_id );

		echo 'Author added - ' . $author_first . ' ' . $author_last . '<br>';
	}
}

function import_article_tags() {
	global $wsm_db, $wpdb;

	$article_ids = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_magazine_article_id'" );

	foreach ( $article_ids as $article ) {
		$post_id = absint( $article->post_id );
		$article_id = absint( $article->meta_value );

		$tags_query = mysqli_query( $wsm_db, "SELECT article_tags.tags FROM article_tags LEFT JOIN article_tags_as
				ON article_tags.tags_id=article_tags_as.tags_id WHERE article_id= $article_id GROUP BY article_tags.tags" );

		$assign_tags = array();
		while ( $tags = mysqli_fetch_assoc( $tags_query ) ) {
			$assign_tags[] = $tags['tags'];
		}
		$assign_tags = implode( ',', $assign_tags );

		wp_set_post_tags( $post_id, $assign_tags );
	}
}

function import_review_tags() {
	global $wsm_db, $wpdb;

	$article_ids = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_magazine_review_id'" );

	foreach ( $article_ids as $article ) {
		$post_id = absint( $article->post_id );
		$article_id = absint( $article->meta_value );

		$tags_query = mysqli_query( $wsm_db, "SELECT article_tags.tags FROM article_tags LEFT JOIN review_tags_as
				ON article_tags.tags_id=review_tags_as.tags_id WHERE review_id= $article_id GROUP BY article_tags.tags" );

		$assign_tags = array();
		while ( $tags = mysqli_fetch_assoc( $tags_query ) ) {
			$assign_tags[] = $tags['tags'];
		}
		$assign_tags = implode( ',', $assign_tags );

		if ( ! empty( $assign_tags ) ) {
			wp_set_post_tags( $post_id, $assign_tags );
		}
	}
}

function import_web_extras_tags() {
	global $wsm_db, $wpdb;

	$article_ids = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_magazine_extra_id'" );

	foreach ( $article_ids as $article ) {
		$post_id = absint( $article->post_id );
		$article_id = absint( $article->meta_value );

		$tags_query = mysqli_query( $wsm_db, "SELECT article_tags.tags FROM article_tags LEFT JOIN we_tags_as
				ON article_tags.tags_id=we_tags_as.tags_id WHERE web_extras_id= $article_id GROUP BY article_tags.tags" );

		$assign_tags = array();
		while ( $tags = mysqli_fetch_assoc( $tags_query ) ) {
			$assign_tags[] = $tags['tags'];
		}
		$assign_tags = implode( ',', $assign_tags );

		if ( ! empty( $assign_tags ) ) {
			wp_set_post_tags( $post_id, $assign_tags );
		}
	}
}

function import_web_extras_associates() {
	global $wsm_db, $wpdb;

	$article_ids = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_magazine_article_id'" );

	foreach ( $article_ids as $article ) {
		$post_id = absint( $article->post_id );
		$article_id = absint( $article->meta_value );

		$extras_query = mysqli_query( $wsm_db, "SELECT web_extras_id FROM web_extras_as WHERE article_id = $article_id" );

		$assign_extras = array();
		while ( $extras = mysqli_fetch_assoc( $extras_query ) ) {
			array_push( $assign_extras, absint( $extras['web_extras_id'] ) );
		}

		if ( ! empty( $assign_extras ) ) {
			update_post_meta( $post_id, '_magazine_article_web_extras', $assign_extras );
		}
	}
}

function import_article_university_categories() {
	global $wsm_db, $wpdb;

	$article_ids = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_magazine_article_id'" );

	foreach ( $article_ids as $article ) {
		$post_id = absint( $article->post_id );
		$article_id = absint( $article->meta_value );

		$tags_query = mysqli_query( $wsm_db, "SELECT article_category.category FROM article_category LEFT JOIN article_category_as
				ON article_category.article_category_id=article_category_as.article_category_id WHERE article_id= $article_id GROUP BY article_category.category" );

		while ( $tags = mysqli_fetch_assoc( $tags_query ) ) {
			$test = get_term_by( 'name', $tags['category'], 'wsuwp_university_category' );
			if ( ! $test ) {
				wp_set_object_terms( $post_id, $tags['category'], 'category', true );
			} else {
				wp_set_object_terms( $post_id, $tags['category'], 'wsuwp_university_category', true );
			}
		}
	}
}

function import_review_university_categories() {
	global $wsm_db, $wpdb;

	$article_ids = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_magazine_review_id'" );

	foreach ( $article_ids as $article ) {
		$post_id = absint( $article->post_id );
		$article_id = absint( $article->meta_value );

		$tags_query = mysqli_query( $wsm_db, "SELECT article_category.category FROM article_category LEFT JOIN review_category_as
				ON article_category.article_category_id=review_category_as.article_category_id WHERE review_id= $article_id GROUP BY article_category.category" );

		while ( $tags = mysqli_fetch_assoc( $tags_query ) ) {
			$test = get_term_by( 'name', $tags['category'], 'wsuwp_university_category' );
			if ( ! $test ) {
				wp_set_object_terms( $post_id, $tags['category'], 'category', true );
			} else {
				wp_set_object_terms( $post_id, $tags['category'], 'wsuwp_university_category', true );
			}
		}
	}
}

function import_web_extra_university_categories() {
	global $wsm_db, $wpdb;

	$article_ids = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_magazine_extra_id'" );

	foreach ( $article_ids as $article ) {
		$post_id = absint( $article->post_id );
		$article_id = absint( $article->meta_value );

		$tags_query = mysqli_query( $wsm_db, "SELECT article_category.category FROM article_category LEFT JOIN we_category_as
				ON article_category.article_category_id=we_category_as.article_category_id WHERE web_extras_id= $article_id GROUP BY article_category.category" );

		while ( $tags = mysqli_fetch_assoc( $tags_query ) ) {
			$test = get_term_by( 'name', $tags['category'], 'wsuwp_university_category' );
			if ( ! $test ) {
				wp_set_object_terms( $post_id, $tags['category'], 'category', true );
			} else {
				wp_set_object_terms( $post_id, $tags['category'], 'wsuwp_university_category', true );
			}
		}
	}
}

function import_magazine_photos() {
	global $wpdb, $wsm_db;

	$get_photos = mysqli_query( $wsm_db, "SELECT * FROM article_photo" );

	while ( $photo = mysqli_fetch_assoc( $get_photos ) ) {
		$new_post = array();

		$photo_id = absint( $photo['article_photo_id'] );

		$existing_post_id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_magazine_article_photo_id' AND meta_value= $photo_id" );

		if ( $existing_post_id ) {
			continue;
		}

		$new_post['post_type'] = 'wsu_magazine_photo';
		$new_post['post_status'] = 'publish';
		$new_post['post_title'] = $photo['image_name'];
		$new_post['post_date'] = $photo['date_created'];
		$new_post['post_modified'] = $photo['date_updated'];

		$post_id = wp_insert_post( $new_post );

		$photo_alt = wp_kses_post( $photo['alt'] );
		$photo_caption = wp_kses_post( $photo['caption'] );
		$photo_filename = sanitize_text_field( $photo['file_name'] );
		$photo_bigbrother = sanitize_text_field( $photo['big_brother'] );

		update_post_meta( $post_id, '_magazine_article_photo_id', $photo_id );
		update_post_meta( $post_id, '_magazine_photo_alt', $photo_alt );
		update_post_meta( $post_id, '_magazine_photo_caption', $photo_caption );
		update_post_meta( $post_id, '_magazine_photo_filename', $photo_filename );
		update_post_meta( $post_id, '_magazine_photo_bigbrother', $photo_bigbrother );
	}
}

function import_magazine_photographer_attribution() {
	global $wpdb, $wsm_db;

	$get_photographers = mysqli_query( $wsm_db, "SELECT article_photographer_as.article_photo_id,article_photographer.first_name, article_photographer.last_name
												FROM article_photographer_as
												LEFT JOIN article_photographer
												ON article_photographer_as.article_photographer_id=article_photographer.article_photographer_id" );

	while ( $photo_record = mysqli_fetch_assoc( $get_photographers ) ) {
		$photo_id = absint( $photo_record['article_photo_id'] );

		$post_id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_magazine_article_photo_id' AND meta_value=$photo_id" );

		if ( $post_id ) {
			update_post_meta( $post_id, '_magazine_photographer_first_name', sanitize_text_field( $photo_record['first_name'] ) );
			update_post_meta( $post_id, '_magazine_photographer_last_name', sanitize_text_field( $photo_record['last_name'] ) );
		}
	}
}

function import_article_photos() {
	global $wpdb, $wsm_db;

	$article_ids = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_magazine_article_id'" );

	foreach ( $article_ids as $article ) {
		$post_id = absint( $article->post_id );
		$article_id = absint( $article->meta_value );

		$photos_query = mysqli_query( $wsm_db, "SELECT article_photo_id FROM article_photo_as WHERE article_id=$article_id" );

		while ( $photos = mysqli_fetch_assoc( $photos_query ) ) {
			$photo_id = absint( $photos['article_photo_id'] );

			if ( 0 < $photo_id ) {
				add_post_meta( $post_id, '_magazine_associated_photo_id', $photo_id, false );
			}
		}
	}
}

function import_review_photos() {
	global $wpdb, $wsm_db;

	$article_ids = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_magazine_review_id'" );

	foreach ( $article_ids as $article ) {
		$post_id = absint( $article->post_id );
		$article_id = absint( $article->meta_value );

		$photos_query = mysqli_query( $wsm_db, "SELECT article_photo_id FROM review_photo_as WHERE review_id=$article_id" );

		while ( $photos = mysqli_fetch_assoc( $photos_query ) ) {
			$photo_id = absint( $photos['article_photo_id'] );

			if ( 0 < $photo_id ) {
				add_post_meta( $post_id, '_magazine_associated_photo_id', $photo_id, false );
			}
		}
	}
}

function import_web_extra_photos() {
	global $wpdb, $wsm_db;

	$article_ids = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_magazine_extra_id'" );

	foreach ( $article_ids as $article ) {
		$post_id = absint( $article->post_id );
		$article_id = absint( $article->meta_value );

		$photos_query = mysqli_query( $wsm_db, "SELECT article_photo_id FROM we_photo_as WHERE web_extras_id=$article_id" );

		while ( $photos = mysqli_fetch_assoc( $photos_query ) ) {
			$photo_id = absint( $photos['article_photo_id'] );

			if ( 0 < $photo_id ) {
				add_post_meta( $post_id, '_magazine_associated_photo_id', $photo_id, false );
			}
		}
	}
}

// Uncomment these lines one at a time to import data from the magazine.

// Import magazine issue content type and taxonomy.
//import_magazine_issues();

// Import magazine articles content type.
//import_magazine_articles();

// Import reviews.
//import_magazine_reviews();

// Import web extras.
//import_magazine_web_extras();

// Import magazine authors.
//import_magazine_authors();

// Assign tags to articles.
//import_article_tags();

// Assign tags to reviews.
//import_review_tags();

// Assign tags to web extras.
//import_web_extras_tags();

// Assign web extras to articles.
//import_web_extras_associates();

// Assign categories to articles.
//import_article_university_categories();

// Assign categories to reviews.
//import_review_university_categories();

// Assign categories to web extras.
//import_web_extra_university_categories();

// Import all magazine photo data.
//import_magazine_photos();

// Assign photographer first and last name to photos.
//import_magazine_photographer_attribution();

// Mirror associations between articles and photos.
//import_article_photos();

// Mirror associations between reviews and photos.
//import_review_photos();

// Mirror associations between web extras and photos.
//import_web_extra_photos();