(function ($) {

	function toggle_excerpt() {
		$('.halves:not(.secondary-articles)').on('click', '.home-headline-head-wrapper', function (e) {
			if ( $(window).width() < 792 ) {
				e.preventDefault();
				e.stopImmediatePropagation();
				$(this).toggleClass('open');
			}
		});
	}

	$(window).on('load', toggle_excerpt);

}(jQuery));
