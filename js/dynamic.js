"use strict";

/*globals $, jQuery, window, document */

(function ($) {
	var videos;

	function writeVideo(ctx) {
		var markup,
            W = ctx.find('.width').eq(0).text() || 640,
            H = ctx.find('.height').eq(0).text() || 480,
            img = ctx.find('.photo').attr('src') || '',
            mp4 = ctx.find('a[type="video/mp4"]').attr('href'),
            ogv = ctx.find('a[type="video/ogg"]').attr('href');

		if (mp4.indexOf('.mp4') === -1) {
			video = Base64.decode(mp4);
		}

		if (ogv.indexOf('.ogv') === -1) {
			ogv = Base64.decode(ogv);
		}

		markup = ['<video class="video-js player" width="', W, '" height="', H, '" preload ', img ? 'poster="' + img + '"' : '', ' controls>',
		  	'<source type=\'video/mp4; codecs="avc1.42E01E, mp4a.40.2"\' src="', mp4, '">',
		  	'<source type=\'video/ogg; codecs="theora, vorbis"\' src="', ogv, '">',
		  	'<object class="vjs-flash-fallback" width="', W, '" height="', H, '" type="application/x-shockwave-flash" data="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf">',
		  	'<param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf" />',
		    '<param name="allowfullscreen" value="true" />',
            '<param name="wmode" value="transparent" />',    
		    '<param name="flashvars" value=\'config={"playlist":["' + img + '", {"autoPlay": false, "url": "' + mp4 + '"}]}\' />',
		'</object>',
		'</video>'].join('');

		ctx.closest('.videos-wrapper').find('.video-js-box').html(markup);
	}

	function selectVideo() {
		var elem = $(this);

		writeVideo(elem);
        VideoJS.setupAllWhenReady();        
		return false;
	}

	$(document).ready(function () {
		videos = $('.hMedia');

		if (videos.length > 1) {
			videos.click(selectVideo);
            videos.eq(0).click();
		} else {
            VideoJS.setupAllWhenReady();
		}
	});
}(jQuery));