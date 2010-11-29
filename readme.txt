=== Movies ===
Contributors: wonderboymusic
Tags: media, attachments, admin, video, videos, cms, jquery, manage, music, upload, VideoJS, HTML5
Requires at least: 3.0
Tested up to: 3.0
Stable Tag: 0.3

HTML5 Video (on supported browsers), Flash fallback, CSS-skin'd player, hMedia Micro-formats, attach images to videos (when used with Shuffle), associated Ogg Theora videos with MP4s/H.264 (When used with Shuffle) 

== Description ==

Movies allows you to use simple functions in your theme to display videos you have attached to Posts/Pages/Custom Post Types in your Media Library. Your player is styled 100% with CSS/images (if you want). The video player uses the VideoJS library and your browser's native HTML5 capabilities when available with a fallback to Flash when necessary. Allows you to play video inline on mobile browsers that support HTML5 Video. Video metadata is written to the page using the hMedia micro-format for semantic markup.

You can use this shortcode <code>[movies]</code> or <code>the_movies()</code> or <code>the_videos()</code> in your theme to output your item's attachments.

You may need to add these Mime-Type declarations to <code>httpd.conf</code> or your <code>.htaccess</code> file
<code>
AddType video/ogg .ogv 
AddType video/mp4 .mp4 
AddType video/webm .webm
</code>

Read More here: http://scottctaylor.wordpress.com/2010/11/24/new-plugin-movies/
Follow-up: http://scottctaylor.wordpress.com/2010/11/28/movies-plugin-now-supports-webm/

= 0.3 =
* Support for WebM added when used with [Shuffle](http://wordpress.org/extend/plugins/shuffle/ "Shuffle"), fixes Media Uploader to support WebM 

= 0.2 = 
* Some bug fixes, definitely update

= 0.1 =
* Initial release

== Screenshots ==

1. Using [Shuffle](http://wordpress.org/extend/plugins/shuffle/ "Shuffle"), you associate images and OGV files with MP4 files, all will be loaded automatically into the HTML5 video player
 
2. You can customize the look of your player and playlist by adding a video.css file in your theme's directory

== Upgrade Notice ==

* Update to get the latest bug fixes from ongoing development. 0.2 fixes bugs related to dynamic rendering of Video in Firfox.