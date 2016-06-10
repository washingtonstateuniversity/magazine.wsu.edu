try{Typekit.load({ async: true });}catch(e){}

/** Super Simple Slider by @intllgnt **/
(function(e,t,n,r){e.fn.sss=function(r){var i=e.extend({slideShow:false,startOn:0,speed:3500,transition:400,arrows:true},r);return this.each(function(){function y(e){return s.eq(e).height()/o.width()*100+"%"}function b(e){if(!c){c=true;var t=s.eq(e);t.fadeIn(a);s.not(t).fadeOut(a);o.animate({paddingBottom:y(e)},a,function(){c=false});g()}}function w(){l=l===u-1?0:l+1;b(l)}function E(){l=l===0?u-1:l-1;b(l)}var r=e(this),s=r.children().wrapAll('<div class="sss"/>').addClass("ssslide"),o=r.find(".sss"),u=s.length,a=i.transition,f=i.startOn,l=f>u-1?0:f,c=false,h,p,d,v,m,g=i.slideShow?function(){clearTimeout(p);p=setTimeout(w,i.speed)}:e.noop;if(i.arrows){o.append('<div class="sssprev"/>','<div class="sssnext"/>')}m=o.find(".sssnext"),v=o.find(".sssprev");e(t).load(function(){o.css({paddingBottom:y(l)}).click(function(t){h=e(t.target);if(h.is(m)){w()}else if(h.is(v)){E()}});b(l);e(n).keydown(function(e){d=e.keyCode;if(d===39){w()}else if(d===37){E()}})})})}})(jQuery,window,document);

/* rwdImageMaps jQuery plugin v1.5 */
(function(a){a.fn.rwdImageMaps=function(){var c=this;var b=function(){c.each(function(){if(typeof(a(this).attr("usemap"))=="undefined"){return}var e=this,d=a(e);a("<img />").load(function(){var g="width",m="height",n=d.attr(g),j=d.attr(m);if(!n||!j){var o=new Image();o.src=d.attr("src");if(!n){n=o.width}if(!j){j=o.height}}var f=d.width()/100,k=d.height()/100,i=d.attr("usemap").replace("#",""),l="coords";a('map[name="'+i+'"]').find("area").each(function(){var r=a(this);if(!r.data(l)){r.data(l,r.attr(l))}var q=r.data(l).split(","),p=new Array(q.length);for(var h=0;h<p.length;++h){if(h%2===0){p[h]=parseInt(((q[h]/n)*100)*f)}else{p[h]=parseInt(((q[h]/j)*100)*k)}}r.attr(l,p.toString())})}).attr("src",d.attr("src"))})};a(window).resize(b).trigger("resize");return this}})(jQuery);

(function($) {

	$(document).ready(function() {

		// Toggle article sidebars.
		$('.article-sidebar').click(function() {
			$(this).toggleClass('').toggleClass('article-sidebar-open');
		});

		// Call the Super Simple Slider function.
		$('.gallery').sss();

		// Call the rwdImageMaps function.
		$('img[usemap]').rwdImageMaps();

	});

}(jQuery));

// Colorbox 'curtain' effect.
function colorbox_curtain() {
	'use strict';

	jQuery('#cboxOverlay').css({
		'visibility': 'visible',
		'height': 0,
		'opacity': 0,
		'cursor': 'pointer'
	}).animate({
		height: jQuery(document).height(),
		opacity: 0.9
	}, 1200);
}
