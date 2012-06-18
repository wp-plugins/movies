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
        $source = wp_get_attachment_url( $post->ID, 'full' );
        $title = apply_filters( 'the_title', $post->post_title );
        $attr = apply_filters( 'the_title_attribute', $post->post_title );	
    ?>
        <a rel="enclosure" type="<?php esc_attr_e( $post->post_mime_type ) ?>" title="<?php esc_attr_e( $attr ) ?>" href="<?php echo esc_url( $source ) ?>"><?php echo $title ?> 
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
    
    function embed( $post ) {
    if ( 'video/mp4' === $post->post_mime_type ):	
        $mp4 = wp_get_attachment_url( $post->ID );
        $image = $this->get_poster( $post->ID );
        $ogg = $this->get_child_object( $post->ID, 'video/ogg' );	
        $webm = $this->get_child_object( $post->ID, 'video/webm' );
    ?>
        <video width="<?php echo $w ?>" height="<?php echo $h ?>" poster="<?php echo $image ?>" controls="controls" src="<?php echo $mp4 ?>" preload="none">
            <source type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"' src="<?php echo $mp4 ?>"/>
            <?php if ( !empty( $ogg ) ): 
                ?><source type='video/ogg; codecs="theora, vorbis"' src="<?php echo $ogg ?>"/><?php endif ?>
            <?php if ( !empty( $webm ) ): 
                ?><source type='video/webm; codecs="vp8, vorbis"' src="<?php echo $webm ?>"/><?php endif ?>	
        </video>
    <?php
    endif;
    }
    
    function formatted_item( &$post, &$info ) {
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
            $this->enclosure( $post, $info );
            $ogg = $this->get_child_object( $post->ID, 'video/ogg' );
            if ( !empty( $ogg ) )
                $this->enclosure( $ogg, $info );
            $webm = $this->get_child_object( $post->ID, 'video/webm' );
            if ( !empty( $webm ) )
                $this->enclosure( $webm, $info );
        ?> 	
            <p><?php echo $description ?></p>    	    
        </div>
    <?php
    }
    
    function the_videos( $id = 0 ) {

        $videos = get_posts( array(
            'post_parent'    => empty( $id ) ? get_the_ID() : $id,
            'post_mime_type' => 'video',
            'order'       	 => 'ASC',
            'orderby'     	 => 'menu_order',
            'post_type'   	 => 'attachment',
            'post_status' 	 => 'inherit',
            'numberposts' 	 => -1	
        ) );

    if ( is_array( $videos ) ): ?>
    <div class="videos-wrapper" data-library="me-js">	
        <?php if ( 1 === count( $videos ) ): ?>
        <div class="video-js-box" id="video-js-box">
            <div class="vjs-no-video"></div>
                <?php $this->videojs_embed( reset( $videos ) ) ?>
            </div>
            <?php 
            $this->formatted_item( reset( $videos ) ); 
        else: 
        /**
        * Embed code will be loaded via JavaScript
        *
        */
        ?>
        <div class="video-js-box" id="video-js-box"><div class="vjs-no-video"></div></div>
            <div class="videos">
            <?php 
            foreach ( $videos as $video )	
                $this->formatted_item( $video ); 
            ?>
            </div>	
        <?php endif ?>
    </div>	
    <?php endif;
    }
}
$_movies_plugin = new Movies();
$_movies_plugin->init();