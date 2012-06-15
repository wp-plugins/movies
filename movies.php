<?php
/*
Plugin Name: Movies
Description: HTML5 Video (on supported browsers), Flash fallback, CSS-skin'd player, hMedia Micro-formats, attach images to Videos (when used with Shuffle)
Author: Scott Taylor
Version: 0.8
Author URI: http://scotty-t.com
*/

exit( "IN DEVELOPMENT, DON'T USE ME" );

class Movies {
	function init() {
		add_filter( 'upload_mimes',     array( $this, 'upload_mimes' ) );
		add_action( 'wp_print_scripts', array( $this, 'scripts' ) );
		add_action( 'wp_print_styles',  array( $this, 'styles' ) );
		add_shortcode( 'movies',        array( $this, 'shortcode' ) );
	}
	
	function upload_mimes( $mimes ) {
		$mimes['ogv'] = 'video/ogg';
		$mimes['webm'] = 'video/webm';
		return $mimes;	
	}
	
	function scripts() {
		if ( !is_admin() ) {
			$js = WP_PLUGIN_URL . '/movies/js';
		
            wp_register_script( 'mejs', $js . '/mediaelement-and-player.min.js', array( 'jquery' ) );	
            wp_register_script( 'movies', $js . '/dynamic.js', array( 'mejs' ) );
		
			wp_enqueue_script( 'movies' );
		}		
	}
	
	function styles() {
		if ( !is_admin() ) {
			$css = WP_PLUGIN_URL . '/movies/css';

            wp_enqueue_style( 'media-element', $css . '/mediaelementplayer.min.css' );
            wp_enqueue_style( 'movies', $css . '/video.css', array( 'video-js' ) );

            if ( is_file( STYLESHEETPATH . '/video.css' ) )
                wp_enqueue_style( 'movies-user', get_stylesheet_directory_uri() . '/video.css', array( 'movies' ) );		
		}		
	}
	
	function shortcode( $atts, $content = null ) {
	     ob_start();
	     the_videos();
	     return ob_get_clean();
	}
    
    /**
     * Template Tags
     *
     */
    function get_child_object( $id, $mime = '' ) {
        $atts = get_posts( array(
            'post_parent'   => $id,
            'post_mime_type'=> $mime,
            'post_type'     => 'attachment',
            'post_status'   => 'inherit'
        ) );
        
        if ( !empty( $atts ) )
            return reset( $atts );
    }

    function get_ogg( $id ) {
        $obj = $this->get_child_object( $id, 'video/ogg' );
        if ( !empty( $obj ) )
            return wp_get_attachment_url( $obj->ID );
    }

    function get_webm( $id ) {
        $obj = $this->get_child_object( $id, 'video/webm' );
        if ( !empty( $obj ) )
            return wp_get_attachment_url( $obj->ID );
    }
    
    function get_poster( $id ) {
        $obj = $this->get_child_object( $id, 'image' );
        if ( !empty( $obj ) )
            return wp_get_attachment_url( $obj->ID, 'full' );
    }
    
    function enclosure( $post, $info ) {
        $mime = $post->post_mime_type;
        $source = $post->guid;
        $title = apply_filters( 'the_title', $post->post_title );
        $attr = apply_filters( 'the_title_attribute', $post->post_title );	
    ?>
        <a rel="enclosure" type="<?php esc_attr_e( $mime ) ?>" title="<?php esc_attr_e( $attr ) ?>" href="<?php echo esc_url( $source ) ?>"><?php echo $title ?> 
            (<span class="width"><?php echo $info['width'] ?></span> x <span class="height"><?php echo $info['height'] ?></span>)</a>
    <?php
    }
    
    function the_flash_video() {
        $video = get_posts( array(
            'post_parent'    => get_the_id(),
            'post_mime_type' => 'video',
            'order'       	 => 'ASC',
            'orderby'     	 => 'menu_order',
            'post_type'   	 => 'attachment',
            'post_status' 	 => 'inherit',
            'numberposts' 	 => 1	
        ) );

        if ( !empty( $video ) ) {
            $flash = reset( $video );
            $img = $this->get_poster( $flash->ID );

            $this->flash_object( wp_get_attachment_url( $flash->ID ), $img );	
        }
       
    }
    
    function flash_object( $source = '', $w = 0, $h = 0, $image = '' ) { 
        $w = $w < 400 ? 400 : $w; 
    ?>
        <object width="<?php echo $w ?>" height="<?php echo $h ?>" type="application/x-shockwave-flash" data="<?php echo WP_PLUGIN_URL ?>/movies/js/flashmediaelement.swf"> 		
            <param name="movie" value="<?php echo WP_PLUGIN_URL ?>/movies/js/flashmediaelement.swf" /> 
            <param name="flashvars" value="controls=true&amp;poster=<?php echo $image ?>&amp;file=<?php echo $source ?>" /> 		
        </object> 
    <?php
    }
    
    function embed( $post, $info ) {
    if ( 'video/mp4' === $post->post_mime_type ):	
        $mp4 = $post->guid;
        $image = video_get_poster( $post->ID );
        $ogg = video_get_ogg( $post->ID );	
        $webm = video_get_webm( $post->ID );
        $w = $info['width'];
        $h = $info['height'];
    ?>
        <video width="<?php echo $w ?>" height="<?php echo $h ?>" poster="<?php echo $image ?>" controls="controls" src="<?php echo $mp4 ?>" preload="none">
            <source type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"' src="<?php echo $mp4 ?>"/>
            <?php if ( !empty( $ogg ) ): ?><source type='video/ogg; codecs="theora, vorbis"' src="<?php echo $ogg ?>"/><?php endif ?>
            <?php if ( !empty( $webm ) ): ?><source type='video/webm; codecs="vp8, vorbis"' src="<?php echo $webm ?>"/><?php endif ?>	
        </video>
    <?php
    endif;
    }
    
    function video_formatted_item( &$post, &$info ) {
        $title = apply_filters( 'the_title', $post->post_title );
        $attr = apply_filters( 'the_title_attribute', $post->post_title );
        $artist =  $post->post_excerpt;
        $img = $this->get_poster( $post->ID );
        $description = $post->post_content;
    ?>
        <div class="hMedia">
            <?php if ( !empty( $img ) ): ?><img class="photo" src="<?php echo $img ?>" alt="<?php echo $attr ?>"/>
            <?php endif ?><span class="fn">&#8220;<?php echo $title ?>&#8221;</span>
            <span class="contributor">
                <span class="vcard">
                    <span class="fn org"><?php echo $artist ?></span>
                </span>
            </span>	   
        <?php 
            video_enclosure( $post, $info );
            $ogg = $this->get_child_object( $post->ID, 'video/ogg' );
            if ( !empty( $ogg ) )
                video_enclosure( $ogg, $info );
            $webm = $this->get_child_object( $post->ID, 'video/webm' );
            if ( !empty( $webm ) )
                video_enclosure( $webm, $info );
        ?> 	
            <p><?php echo $description ?></p>    	    
        </div>
    <?php
    }
    
    function the_videos() {
    if ( function_exists( 'shuffle_by_mime_type' ) ):
        $videos = get_video(); 
    else:
        // this is functionality ported over from Shuffle
        // you should be using Shuffle!!!	
        $videos = get_posts( array(
            'post_parent'    => get_the_id(),
            'post_mime_type' => 'video',
            'order'       	 => 'ASC',
            'orderby'     	 => 'menu_order',
            'post_type'   	 => 'attachment',
            'post_status' 	 => 'inherit',
            'numberposts' 	 => -1	
        ) );
    endif;
    if ( is_array( $videos ) ): ?>
    <div class="videos-wrapper" data-library="<?php echo MEDIA_ELEMENT ? 'me-js' : 'video-js'?>">	
    <?php if ( 1 === count( $videos ) ): ?>
    <div class="video-js-box" id="video-js-box">
        <div class="vjs-no-video"></div>
        <?php
        $info = video_get_id3( $videos[0] );

        videojs_embed( $videos[0], $info );
        ?></div>
        <?php 
        video_formatted_item( $videos[0], $info ); 
        unset( $info );
        else: 
        /**
        * Embed code will be loaded via JavaScript
        *
        */
        ?>
    <div class="video-js-box" id="video-js-box"><div class="vjs-no-video"></div></div>
    <div class="videos">
    <?php 
    foreach ( $videos as $video ): 
        $info = video_get_id3( $video );	
        video_formatted_item( $video, $info );
        unset( $info ); 
    endforeach ?>
    </div>	
    <?php endif;
        unset( $videos ); ?>
    </div>	
    <?php endif;
    }
}
$_movies_plugin = new Movies();
$_movies_plugin->init();