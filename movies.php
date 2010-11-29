<?php
/*
Plugin Name: Movies
Description: HTML5 Video (on supported browsers), Flash fallback, CSS-skin'd player, hAudio Micro-formats, attach images to Videos (when used with Shuffle)
Author: Scott Taylor
Version: 0.3
Author URI: http://tsunamiorigami.com
*/

if (!class_exists('getID3')) {
	require_once('getid3/getid3/getid3.php');
}

define('SECURE', false);

function video_get_ogg_object($id) {
	$ogg = '';
	if (function_exists('shuffle_by_mime_type')):
		$oggs =& get_video($id);
		if (is_array($oggs) && count($oggs) > 0) {
			foreach ($oggs as $o):
				if ($o->post_mime_type === 'video/ogg') {
					$ogg = $o;
					break;				
				}
			endforeach;
		}
		unset($oggs);
	endif;
	return $ogg;
}

function video_get_webm_object($id) {
	$webm = '';
	if (function_exists('shuffle_by_mime_type')):
		$webms =& get_video($id);
		if (is_array($webms) && count($webms) > 0) {
			foreach ($webms as $w):
				if ($w->post_mime_type === 'video/webm') {
					$webm = $w;
					break;				
				}
			endforeach;
		}
		unset($webms);
	endif;
	return $webm;
}

function video_get_ogg($id) {
	$ogg = '';
	
	$obj =& video_get_ogg_object($id);
	if (!empty($obj)) {
		$ogg = $obj->guid;
	}
	
	return $ogg;
}

function video_get_webm($id) {
	$webm = '';
	
	$obj =& video_get_webm_object($id);
	if (!empty($obj)) {
		$webm = $obj->guid;
	}
	
	return $webm;
}

function video_get_poster($id) {
	$image = '';
	if (function_exists('shuffle_by_mime_type')):
		$images =& get_images($id);
		if (is_array($images) && count($images) > 0) {
			$image = $images[0]->guid;
		}
		unset($images);
	endif;
	return $image;
}

function video_flash_object($source = '', $w = 0, $h = 0, $image = '') { ?>

	<object class="vjs-flash-fallback" width="<?= $w ?>" height="<?= $h ?>" type="application/x-shockwave-flash"
	        data="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf">
	    <param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf" />
	    <param name="allowfullscreen" value="true" />
	    <param name="wmode" value="transparent" />
	    <param name="flashvars" value='config={"playlist":["<?php if (!empty($image)) { echo $image; } ?>", {"autoPlay": false, "url": "<?= $source ?>"}]}' />
	</object>
<?php    
}

function videojs_embed(&$post, &$info) {
	if ($post->post_mime_type === 'video/mp4'):	
		$mp4 = $post->guid;
		$image = video_get_poster($post->ID);
		$ogg = video_get_ogg($post->ID);	
		$webm = video_get_webm($post->ID);
		$w = $info['width'];
		$h = $info['height'];
?>
	
	<video id="video-playlist" class="video-js player" width="<?= $w ?>" height="<?= $h ?>" preload="preload" controls="controls" <?php if (!empty($image)): ?>poster="<?= $image ?>"<?php endif ?>>
	    <source type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"' src="<?= $mp4 ?>"/>
	    <?php if (!empty($ogg)): ?><source type='video/ogg; codecs="theora, vorbis"' src="<?= $ogg ?>"/><?php endif ?>
		<?php if (!empty($webm)): ?><source type='video/webm; codecs="vp8, vorbis"' src="<?= $webm ?>"/><?php endif ?>
		<?php video_flash_object($mp4, $w, $h, $image); ?>
	</video>	    
<?php
	endif;
}

function video_get_src($url) {
	if (SECURE) {
		$url = base64_encode($url);	
	}

	return $url;
}

function video_enclosure(&$post, &$info) {
	$mime = $post->post_mime_type;
	$source = $post->guid;
	$title = apply_filters('the_title', $post->post_title);
	$attr = apply_filters('the_title_attribute', $post->post_title);	
?>
		<a rel="enclosure" type="<?= $mime ?>" title="Permalink for <?= $attr ?>" href="<?= video_get_src($source) ?>"><?= $title ?> (<span class="width"><?= $info['width'] ?></span> x <span class="height"><?= $info['height'] ?></span>)</a>
<?php
}

function video_formatted_item(&$post, &$info) {
	$title = apply_filters('the_title', $post->post_title);
	$attr = apply_filters('the_title_attribute', $post->post_title);
	$artist =  $post->post_excerpt;
	$img = video_get_poster($post->ID);
	$description = $post->post_content;
?>
	<div class="hMedia">
		<?php if (!empty($img)): ?><img class="photo" src="<?= $img ?>" alt="<?= $attr ?>"/>
		<?php endif ?><span class="fn">&#8220;<?= $title ?>&#8221;</span>
		<span class="contributor">
			<span class="vcard">
				<span class="fn org"><?= $artist ?></span>
			</span>
		</span>	   
	<?php 
		video_enclosure($post, $info);
		$ogg = video_get_ogg_object($post->ID);
		if (!empty($ogg)): video_enclosure($ogg, $info); endif;
		$webm = video_get_webm_object($post->ID);
		if (!empty($webm)): video_enclosure($webm, $info); endif;
	?> 	
		<p><?= $description ?></p>    	    
	</div>
<?php
}

function video_get_id3(&$post) {
	$parts = parse_url($post->guid);	
	$local_file = getcwd() . $parts['path'];		

	$getID3 = new getID3;
	$fileinfo = $getID3->analyze($local_file);
	getid3_lib::CopyTagsToComments($fileinfo);
	
	$info = array();
	$info['width'] = $fileinfo['video']['resolution_x'];
	$info['height'] = $fileinfo['video']['resolution_y'];
	
	return $info;
}

function the_videos() {
if (function_exists('shuffle_by_mime_type')):
	$videos =& get_video(); 
else:
	// this is functionality ported over from Shuffle
	// you should be using Shuffle!!!	
	$videos =& get_posts(array(
		'post_parent'    => get_the_id(),
		'post_mime_type' => 'video',
		'order'       	 => 'ASC',
		'orderby'     	 => 'menu_order',
		'post_type'   	 => 'attachment',
		'post_status' 	 => 'inherit',
		'numberposts' 	 => -1	
	));
endif;
if (is_array($videos)): ?>
<div class="videos-wrapper">	
<?php if (count($videos) === 1): ?>
<div class="video-js-box" id="video-js-box">
	<div class="vjs-no-video"></div>
	<?php
	$info = video_get_id3($videos[0]);
	
	videojs_embed($videos[0], $info);
	?></div>
	<?php 
	video_formatted_item($videos[0], $info); 
	
	unset($info);
	else: 
	/**
	 * Embed code will be loaded via JavaScript
	 *
	 */
	?>
<div class="video-js-box" id="video-js-box"><div class="vjs-no-video"></div></div>
<div class="videos">
	<?php foreach ($videos as $video): 
	$info = video_get_id3($video);	
	video_formatted_item($video, $info);
	unset($info); 
	endforeach; ?>
</div>	
	<?php endif;
	unset($videos); ?>
</div>	
<?php endif;
}

function movies_handler($atts, $content = null ) {
     ob_start();
     the_videos();
     $videos = ob_get_contents();
     ob_end_clean();
     return $videos;
}
add_shortcode('movies', 'movies_handler');

function movies_print_styles() {
	wp_register_style('video-js', WP_PLUGIN_URL . '/movies/css/videoJS-1.1.5-modified.css');
	wp_register_style('movies', WP_PLUGIN_URL . '/movies/css/video.css', array('video-js'));	

	wp_enqueue_style('movies');
	
	if (is_file(STYLESHEETPATH . '/video.css')) {
		wp_enqueue_style('movies-user', STYLESHEETDIR . '/video.css', array('movies'));		
	}
}
add_action('wp_print_styles', 'movies_print_styles');

function movies_print_scripts() {
	wp_register_script('video-js', WP_PLUGIN_URL . '/movies/js/videoJS-1.1.5.js');	
	wp_register_script('movies', WP_PLUGIN_URL . '/movies/js/dynamic.js', array('video-js', 'jquery'));

	if (SECURE) {
		wp_register_script('base64', WP_PLUGIN_URL . '/movies/js/Base64.js');
		wp_enqueue_script('base64');
	}

	wp_enqueue_script('movies');
}
add_action('wp_print_scripts', 'movies_print_scripts');

function movies_upload_mimes($existing = array()) {
	$mimes = array(
		'ogv' => 'video/ogg',
		'webm' => 'video/webm'
	);
	return array_merge($existing, $mimes);
}
add_filter('upload_mimes', 'movies_upload_mimes');

function movies_init() {

}
add_action('init', 'movies_init');
?>