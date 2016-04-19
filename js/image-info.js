(function($){

	$( '.image-information' ).on( 'click', function(e) {

		e.preventDefault();

		var img_src = $(this).attr('href'),
			img_width = $(this).data('img-width'),
			img_height = $(this).data('img-height'),
			img_title = $(this).attr('title');

		$( '<div class="wsm-box-overlay wsm-box-close"></div>' ).appendTo( document.body ).fadeIn( 500, function(){
			$( '.wsm-box-overlay' ).html(
				'<div class="wsm-box-content" style="max-width:' + img_width + 'px;">' +
				'<img class="wsm-box-image" src="' + img_src + '" width="' + img_width + '" height="' + img_height + '" />' +
				'<div class="wsm-box-image-title">' +
				'<a class="wsm-box-close" href="#">x</a>' +
				'<p>' + img_title + '</p>' +
				'</div>' +
				'</div>'
			);
		});

	})

	$( document ).on( 'click', '.wsm-box-close', function(e) {

		if ( e.target == this ) {
			e.preventDefault();
			$( '.wsm-box-overlay' ).remove();
		}

	})

})(jQuery);
