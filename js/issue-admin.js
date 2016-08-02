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
		load_issue_articles( window.wsm_issue.items );
	}

	sortable_layout();
	/**
	 * Use jQuery UI Sortable to add sorting functionality to issue articles.
	 */
	function sortable_layout() {
		var article_parent;
		$('.wsuwp-spine-builder-column').sortable( {
			connectWith: '.wsuwp-spine-builder-column',
			handle: '.ttfmake-sortable-handle',
			opacity: 0.6,
			placeholder: 'wsm-item-placeholder',
			start: function(event, ui) {
				article_parent = $(ui.item).parent();
			},
			stop: function(event, ui) {
				var existing_article = ui.item.siblings('.issue-article'),
					builder_stage    = ui.item.closest('#ttfmake-stage'),
					staging_area     = ui.item.closest('#issue-articles');
				if ( existing_article && builder_stage.length ) {
					$( existing_article ).appendTo(article_parent);
				}
				if ( staging_area.length ) {
					ui.item.find('.handlediv').removeClass('wsuwp-toggle-closed');
					ui.item.find('.wsm-article-body').css('display', '');
				}
				process_sorted_data();
			},
		} );
	}

	/**
	 * Process an existing list of issue items and add them to the front end view of the issue build.
	 *
	 * @param raw_data
	 */
	function load_issue_articles( raw_data ) {
		var data = '';

		// Append the results to the existing build of items.
		$.each( raw_data, function( index, val ) {
			var headline = val.headline ? val.headline : val.title,
				fallback = val.headline ? '' : val.title; // Web Extras don't have the headline meta field, so provide the title.
				classes  = ' season-' + $( '#issue_label_slug' ).val().split('-')[0],
				bg_id    = val.bg_id,
				bg_url   = val.bg_url,
				bg_full  = val.bg_full,
				bg_sizes = val.bg_sizes,
				bg_style = bg_url ? ' style=" background-image: url(' + bg_url + ');"' : '';

				if ( 'Web Extra' === val.section ) {
					classes += ' web-extra';
				}

				if ( '' !== bg_style ) {
					classes += ' has-featured-img';
				}

			data += '<div id="issue-article-' + val.id + '" class="issue-article" ' +
				'data-headline="' + fallback + '" ' +
				'data-subtitle="" ' +
				'data-background-id="' + bg_id + '" ' +
				'data-background-position="" ' +
				'data-background-image="' + bg_url + '" ' +
				'data-background-image-full="' + bg_full + '" ' +
				'data-background-sizes="' + bg_sizes + '" ' +
				'data-background-size="thumbnail">' +
				'<div class="ttfmake-sortable-handle" title="Drag-and-drop this article into place">' +
					'<a href="#" class="spine-builder-column-configure"><span>Configure this column</span></a>' +
					'<a href="#" class="wsuwp-column-toggle" title="Click to toggle"><div class="handlediv"></div></a>' +
					'<div class="wsuwp-builder-column-title">' + val.title + '</div>' +
				'</div>' +
				'<div class="wsm-article-body wsuwp-column-content' + classes + '"' + bg_style + '>' +
					'<div class="home-headline-head-wrapper">' +
						'<h2>' + headline + '</h2>' +
						'<div class="article-section">' + val.section + '</div>' +
						'<div class="home-subtitle">' + val.subtitle + '</div>' +
					'</div>' +
				'</div>' +
			'</div>';
		} );

		$issue_articles.html( data );

		sortable_layout();
	}

	/**
	 * As issue articles are sorted, process their associate information.
	 */
	function process_sorted_data() {
		var new_val = '';

		// Articles added to the Page Builder interface.
		placed_articles = $( '#ttfmake-stage' ).find( '.wsuwp-spine-builder-column' );

		$.each( placed_articles, function() {
			var column  = $(this),
				article = column.children( '.issue-article' );

			if ( article.length ) {
				var new_val     = article[0].id.replace( 'issue-article-', '' ),
					headline    = article.data( 'headline' ),
					subtitle    = article.data( 'subtitle' ),
					bg_id       = article.data( 'background-id' ),
					bg_url      = article.data( 'background-image' ),
					bg_sizes    = article.data( 'background-sizes' ),
					bg_size     = article.data( 'background-size' ),
					position    = article.data( 'background-position' ),
					size_select = column.find( '.spine-builder-column-background-size' );

				// Always set Post ID and Headline values.
				column.children( '.wsuwp-column-post-id' ).val( new_val );
				column.find( '.spine-builder-column-headline' ).val( headline );

				// "First Words" articles don't display a subtitle.
				if ( column.closest( '.ttfmake-section' ).hasClass('ttfmake-section-wsuwpsingle') ) {
					column.find( '.spine-builder-column-subtitle' ).val( '' );
				} else {
					column.find( '.spine-builder-column-subtitle' ).val( subtitle );
				}

				// Set the background value and update the HTML if needed.
				column.find( '.spine-builder-column-background-id' ).val( bg_id );
				if ( bg_url.length ) {
					column.find('.spine-builder-column-set-background-image').html('<img src="' + bg_url + '" />').
						next('.spine-builder-column-remove-background-image').show();
				} else {
					column.find('.spine-builder-column-set-background-image').html('Set background image').
						next('.spine-builder-column-remove-background-image').hide();
				}

				// Set the background size options.
				size_select.find('option:gt(0)').remove();
				if ( bg_sizes.length ) {
					var sizes       = bg_sizes.split(',');

					$.each( sizes, function(index, value) {
						var size   = value.split(':'),
							option = '<option value="' + size[0] + '">' + size[1] + '</option>';

						size_select.append(option);
					} );
				}
				if ( bg_size.length ) {
					size_select.val(bg_size);
				}

				// Set background position value if not in a "Secondary Articles" section.
				if ( column.closest( '.ttfmake-section' ).hasClass('ttfmake-section-wsuwpsecondary') ) {
					column.find( '.spine-builder-column-background-position' ).val( '' );
					article.find( '.wsm-article-body' ).css('background-position', '' );
				} else {
					column.find( '.spine-builder-column-background-position' ).val( position );
				}
			} else {
				column.find( '.wsm-article-meta' ).val( '' );
				column.find('.spine-builder-column-set-background-image').html('Set background image').
					next('.spine-builder-column-remove-background-image').hide();
			}
		} );

		// Articles in the staging area.
		staged_articles = $issue_articles.sortable( 'toArray' );

		$.each( staged_articles, function( index, val ) {
			new_val = val.replace( 'issue-article-', '' );
			staged_articles[index] = new_val;
		} );

		$( '#issue-staged-articles' ).val( staged_articles );

	}

	// Load articles associated with the selected issue label into the staging area.
	$( '#load-issue-articles' ).on( 'click', function( e ) {
		e.preventDefault();

		var issue_label = $( '#issue_label_slug' ).val();

		// Copy selected issue label into default Issue Label field.
		$( '#new-tag-wsu_mag_issue_tax' ).val( issue_label );

		// Cache the issue build area for future use.
		var data = {
			action: 'set_issue_articles',
			issue_label: issue_label,
			nonce: wsm_issue.nonce
		};

		// Make the ajax call
		$.post( window.ajaxurl, data, function( response ) {
			var data = '',
				response_data = $.parseJSON( response );

			load_issue_articles( response_data );
			process_sorted_data();
		} );
	} );

	// Make sure newly-added Page Builder elements are made sortable.
	$( '.ttfmake-menu-list' ).on( 'click', '.ttfmake-menu-list-item', function() {
		$oneApp.on( 'afterSectionViewAdded', function() {
			sortable_layout();
		});
	});

	// Apply user-added information to its respective article.
	$( '#ttfmake-stage' ).on('change', '.wsm-article-meta', function() {
		var input = $(this),
			article = input.closest( '.wsuwp-spine-builder-column' ).find( '.issue-article' );
			value   = input.val();

		if ( input.hasClass('spine-builder-column-headline') ) {
			article.data('headline', value ).
				find( 'h2' ).html( value );
		} else if ( input.hasClass('spine-builder-column-subtitle') ) {
			article.data('subtitle', value ).
				find( '.home-subtitle' ).html( value );
		} else if ( input.hasClass('spine-builder-column-background-size') ) {
			article.data('background-size', value );
			if ( article.data('background-image').length ) {
				var new_bg = article.data('background-image-full');
				if ( '' !== value )  {
					var image_path = new_bg.substring(0, new_bg.lastIndexOf('.')),
						size       = input.find('option:selected').text(),
						dimensions = '-' + size.substring(size.lastIndexOf('(') + 1, size.lastIndexOf(')')),
						ext        = new_bg.substring(new_bg.lastIndexOf('.'), new_bg.length);

					new_bg = image_path + dimensions + ext;
				}
				article.data('background-image', new_bg).
					find( '.wsm-article-body' ).css('background-image', 'url(' + new_bg + ')');
			}
		} else if ( input.hasClass('spine-builder-column-background-position') ) {
			article.data('background-position', value ).
				find( '.wsm-article-body' ).
				css('background-position', value.replace(/-/g, ' ') );
		}
	});

}( jQuery, window ));

/**
 * Handle Background Image media modal.
 */
(function ($) {

	'use strict';

	var media_modal;

	$('#ttfmake-stage').on('click', '.spine-builder-column-set-background-image', function (e) {

		e.preventDefault();

		var set_image_link = $(this);

		media_modal = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			multiple: false
		});

		media_modal.on('select', function () {
			var attachment     = media_modal.state().get('selection').first().toJSON(),
				attachment_url = attachment.sizes.hasOwnProperty('medium') ? attachment.sizes.medium.url : attachment.url,
				size_select    = set_image_link.closest('.spine-builder-overlay-body').find('.spine-builder-column-background-size'),
				image_sizes    = '';

			size_select.find('option:gt(0)').remove();

			$.each( attachment.sizes, function( index, val ) {
				if ( 'full' === index && 990 < val.width ) {
					index = 'spine-large_size';
					val['height'] = Math.round(990 * val.height / val.width);
					val['width'] = 990;
				}

				var option_name = index.charAt(0).toUpperCase() + index.slice(1) + ' (' + val.width + 'x' + val.height + ')',
					option      = '<option value="' + index + '">' + option_name + '</option>';

				image_sizes += index + ':' + option_name + ',';

				size_select.append(option);
			} );

			set_image_link.html('<img src="' + attachment_url + '" />').
				prev('.spine-builder-column-background-id').val(attachment.id).trigger('change').
				siblings('.spine-builder-column-remove-background-image').show().
				closest('.wsuwp-spine-builder-column').find('.issue-article').
					data('background-id', attachment.id ).
					data('background-image', attachment_url ).
					data('background-image-full', attachment.url ).
					data('background-sizes', image_sizes.substring(0, image_sizes.length - 1) ).
					find( '.wsm-article-body' ).
					css('background-image', 'url(' + attachment_url + ')').
					addClass( 'has-featured-img' );
		});

		media_modal.open();
	});

	$('#ttfmake-stage').on('click', '.spine-builder-column-remove-background-image', function (e) {
		e.preventDefault();

		var column = $(this).closest('.wsuwp-spine-builder-column');

		$(this).hide()
			.prev('.spine-builder-column-set-background-image').html('Set background image')
			.prev('.spine-builder-column-background-id').val('');

		column.find('.spine-builder-column-background-size').val('').find('option:gt(0)').remove();
		column.find('.spine-builder-column-background-position').val('');
		column.find('.issue-article').
			data('background-id', '' ).
			data('background-position', '' ).
			data('background-image', '' ).
			data('background-image-full', '' ).
			data('background-sizes', '' ).
			data('background-size', '' ).
			find('.wsm-article-body').css('background-image', '');
	});

}(jQuery));
