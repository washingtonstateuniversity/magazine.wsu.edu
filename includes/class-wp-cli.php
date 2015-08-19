<?php

WP_CLI::add_command( 'wsu-magazine', 'WSU_Magazine_Command' );

class WSU_Magazine_Command extends WP_CLI_Command {

	/**
	 * @subcommand create-guest-authors
	 */
	public function create_guest_authors() {
		global $coauthors_plus;

		$args = array(
			'post_type' => 'wsu_magazine_author',
			'posts_per_page' => 5000,
		);

		$query = new WP_Query( $args );

		while ( $query->have_posts() ) {
			$query->the_post();

			$old_author_name = get_the_title();
			$old_author_slug = explode( "’", $old_author_name );
			$old_author_slug = esc_attr( trim( $old_author_slug[0] ) );
			$old_author_id = get_post_meta( get_the_ID(), '_magazine_author_id', true );

			$args = array(
				'display_name' => $old_author_name,
				'user_login' => sanitize_title( $old_author_slug ),
			);

			$new_post_id = $coauthors_plus->guest_authors->create( $args );

			if ( is_wp_error( $new_post_id ) ) {
				WP_CLI::line( $old_author_name . ' | ' . esc_attr( $old_author_id ) . ' | ' . $new_post_id->get_error_message() );
			} else {
				update_post_meta( $new_post_id, '_magazine_author_id', absint( $old_author_id ) );
				WP_CLI::line( $old_author_name . ' ' . esc_attr( $old_author_id ) . ' ' . $new_post_id );
			}
		}
	}

	/**
	 * @subcommand attach-guest-authors
	 */
	public function attach_guest_authors() {
		global $wpdb, $coauthors_plus;

		$authors = array();

		$all_data = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_magazine_author_id'" );

		foreach( $all_data as $data ) {
			$author = get_post( $data->post_id );

			if ( 'guest-author' !== $author->post_type ) {
				continue;
			}

			$author_article_id = absint( $data->meta_value );

			$authors[ $author_article_id ] = $data->post_id;
		}

		foreach ( $all_data as $data ) {
			$post = get_post( $data->post_id );

			if ( 'post' !== $post->post_type ) {
				continue;
			}

			$author_article_id = absint( $data->meta_value );

			if ( isset( $authors[ $author_article_id ] ) ) {
				$coauthor = $coauthors_plus->get_coauthor_by( 'id', $authors[ $author_article_id ] );
				$coauthors_plus->add_coauthors( $data->post_id, array( $coauthor->user_nicename ), $append = true );
				WP_CLI::line( "Assign " . $authors[ $author_article_id ] . ' to ' . $data->post_id );
			}
		}
	}
}