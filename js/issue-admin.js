try{Typekit.load({ async: true });}catch(e){}
/**
 * Handle various features required in creation of issue pages in the admin.
 */
(function( $, window ) {

	/**
	 * Selector cache of the container holding all of the items for an issue.
	 *
	 * @type {*|HTMLElement}
	 */
	var $issue_articles = $( '#issue-articles' );

	if ( window.wsm_issue.items instanceof Array ) {
		load_issue_articles( window.wsm_issue.items, 'staged', null );
	}

	/**
	 * Use jQuery UI Sortable to add sorting functionality to issue articles.
	 */
	function sortable_layout() {
		$('.wsuwp-spine-builder-column').sortable( {
			connectWith: '.wsuwp-spine-builder-column',
			handle: '.ttfmake-sortable-handle',
			opacity: 0.6,
			placeholder: 'wsm-item-placeholder',
		} );
	}

	/**
	 * Process an existing list of issue items and add them to the front end view of the issue build.
	 *
	 * @param raw_data
	 */
	function load_issue_articles( raw_data, context, shortcode ) {
		var data = '';

		// Append the results to the existing build of items.
		$.each( raw_data, function( index, val ) {
			var headline = val.headline,
				bg_image = val.featured;
				position = '';

			if ( 'sorted' === context ) {
				var headline = shortcode['headline'] ? shortcode['headline'] : headline,
					bg_image = shortcode['bg_img'] ? shortcode['bg_img'] : bg_image,
					position = shortcode['bg_pos'] ? '; background-position: ' + shortcode['bg_pos'] : '';
			}

			var title = headline ? headline : title,
				featured_img = bg_image ? ' has-featured-img" style="background-image: url(' + bg_image + ')' + position : '';

			data += '<div id="issue-article-' + val.id + '" class="issue-article" ' +
				'data-headline="' + headline + '" ' +
				'data-background="' + bg_image + '" ' +
				'data-background-position="">' +
				'<div class="ttfmake-sortable-handle" title="Drag-and-drop this article into place">' +
					'<a href="#" class="spine-builder-column-configure"><span>Configure this column</span></a>' +
					'<a href="#" class="wsuwp-column-toggle" title="Click to toggle"><div class="handlediv "></div></a>' +
					'<div class="wsuwp-builder-column-title">' + val.title + '</div>' +
				'</div>' +
				'<div class="wsm-article-body wsuwp-column-content' + featured_img + '">' +
					'<div class="home-headline-head-wrapper">' +
						'<h2>' + headline + '</h2>' +
						'<div class="article-section">' + val.section + '</div>' +
						'<div class="home-subtitle">' + val.subtitle + '</div>' +
					'</div>' +
				'</div>' +
			'</div>';
		} );

		if ( 'staged' === context ) {
			$issue_articles.html( data );
		} else if ( 'sorted' === context ) {
			$( shortcode['column'] ).append( data );
		}


		sortable_layout();
	}

	/**
	 * As issue articles are sorted, process the associated post IDs into something we can pass to the back end.
	 */
	function process_sorted_data() {
		var new_val = '';

		placed_articles = $( '#ttfmake-stage' ).find( '.wsuwp-spine-builder-column' ).toArray();

		$.each( placed_articles, function() {
			var column          = $(this),
				column_id       = column.data( 'id' ),
				section         = column.closest( '.ttfmake-section' ).data( 'id' ),
				existing_editor = 'ttfmakeeditortext' + section + column_id,
				added_editor    = 'ttfmakeeditortextcolumn' + column_id + section,
				editor          = ( $( '#' + existing_editor ).length ) ? tinyMCE.get( existing_editor ) : tinyMCE.get( added_editor ),
				article         = column.children( '.issue-article' );

			if ( article.length ) {
				var new_val     = article[0].id.replace( 'issue-article-', '' );
					headline    = article.data( 'headline' ),
					background  = article.data( 'background' ),
					section     = article.data( 'section' ),
					subtitle    = article.data( 'subtitle' ),
					shortcode   = '[home_headline id="' + new_val + '" headline="' + headline + '" background="' + background + '" background_position="" wrapper="a"]';
				editor.setContent( shortcode );
			} else {
				editor.setContent( '' );
			}
		} );

		// Articles not yet added to the Page Builder interface.
		staged_articles = $issue_articles.sortable( 'toArray' );

		$.each( staged_articles, function( index, val ) {
			new_val = val.replace( /issue-article-/g, '' );
			staged_articles[index] = new_val;
		} );

		$( '#issue-staged-articles' ).val( staged_articles );

	}

	// Load articles associated with the selected issue label into the staging area.
	$( '#load-issue-articles' ).on( 'click', function( e ) {
		e.preventDefault();

		// Copy selected issue label into default Issue Label field.
		$( '#new-tag-wsu_mag_issue_tax' ).val( issue_label );

		var issue_label = $( '#issue_label_slug' ).val();

		// Cache the issue build area for future use.
		var data = {
			action: 'set_issue_articles',
			//post_id: window.wsm_issue.post_id,
			issue_label: issue_label
		};

		// Make the ajax call
		$.post( window.ajaxurl, data, function( response ) {
			var data = '',
				response_data = $.parseJSON( response );

			load_issue_articles( response_data, 'staged', null );
			process_sorted_data();
		} );
	} );

	// Fire an event any time sorting has stopped after a move.
	$( '#issue-articles, .wsuwp-spine-builder-column' ).on( "sortupdate", process_sorted_data );

	// Make sure added Page Builder elements are made sortable.
	$( '.ttfmake-menu-list' ).on( 'click', '.ttfmake-menu-list-item', function() {
		$oneApp.on( 'afterSectionViewAdded', function() {
			sortable_layout();
		});
	});

	// Add user-added section classes to the section body.
	$( '#ttfmake-stage' ).on( 'blur', '.wsuwp-builder-section-classes', function() {
		if ( $(this).val() ) {
			$(this).closest( '.ttfmake-section-body' ).addClass( $(this).val() );
		} else {
			$(this).closest( '.ttfmake-section-body' ).attr( 'class', 'ttfmake-section-body' );
		}
	});

	// Add user-added background images to their respective columns.
	// Note: the column Background Image input does indeed have the '.spine-builder-column-classes' class.
	// This is likely just a copy/paste oversight.
	$( '#ttfmake-stage' ).on( 'blur', '.spine-builder-column-classes', function() {
		if ( $(this).val() ) {
			$(this).closest( '.wsuwp-spine-builder-column' ).find( '.issue-article' ).
			data( 'background', $(this).val() ).
				find( '.wsm-article-body' ).
				css('background-image', '').
				css('background-image', 'url(' + $(this).val() + ')').
				addClass( 'has-featured-img' );
		}
		process_sorted_data();
	});

	function just_testing ( shortcode ) {
		return function( response ) {
			var response_data = $.parseJSON( response );
			load_issue_articles( response_data, 'sorted', shortcode );
		};
	};

	// Apply section classes and 'render' shortcodes on page load.
	$(window).load(function () {
		$( '.wsuwp-builder-section-classes' ).blur();

		// Render shortcodes. Pretty rough.
		for ( i=1; i < tinyMCE.editors.length; i++ ) {
			var editor  = tinyMCE.editors[i],
				content = editor.getContent();

			if ( content ) {
				var id        = editor.id.replace( 'ttfmakeeditortext', '' ),
					section   = 'ttfmake-section-' + id.substr(0, id.length - 1),
					column    = 'wsuwp-spine-builder-column-position-' + id.substr(id.length - 1),
					post_id   = content.match(/id=([^\s]+)/)[1].replace(/"/g, ""),
					headline  = content.match(/headline="(.*?)"/),
					bg_img    = content.match(/background="(.*?)"/),
					bg_pos    = content.match(/background_position="(.*?)"/),
					shortcode = { column: '#' + section + ' .' + column };

					if ( headline ) { shortcode['headline'] = headline[1]; }
					if ( bg_img ) { shortcode['bg_img'] = bg_img[1]; }
					if ( bg_pos ) { shortcode['bg_pos'] = bg_pos[1]; }

				var data = {
					action: 'set_issue_articles',
					post_ids: post_id,
				};

				$.post( window.ajaxurl, data, just_testing( shortcode ) );
			}
		}
	});
}( jQuery, window ));
