"use strict";

/*globals $, jQuery, window, document */

(function ($) {
	var videos;

	function writeVideo(ctx) {
		var markup,
            W = ctx.find('.width').text() || 640,
            H = ctx.find('.height').text() || 480,
            img = ctx.find('.photo').attr('src') || '',
            video = ctx.find('a[rel="enclosure"]').attr('href');

		if (video.indexOf('.mp4') === -1) {
			video = Base64.decode(video);
		}

		markup = ['<video class="video-js player" width="', W, '" height="', H, '" preload ', img ? 'poster="' + img + '"' : '', ' controls>',
		  	'<source type=\'video/mp4; codecs="avc1.42E01E, mp4a.40.2"\' src="', video, '">',
		  	'<source type=\'video/ogg; codecs="theora, vorbis"\' src="', video, '">',
		  	'<object class="vjs-flash-fallback" width="', W, '" height="', H, '" type="application/x-shockwave-flash" data="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf">',
		  	'<param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf" />',
		    '<param name="allowfullscreen" value="true" />',
            '<param name="wmode" value="transparent" />',    
		    '<param name="flashvars" value=\'config={"playlist":["' + img + '", {"autoPlay": false, "url": "' + video + '"}]}\' />',
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