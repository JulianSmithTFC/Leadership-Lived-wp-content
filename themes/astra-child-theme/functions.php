<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'astra-theme-css' ) );
        wp_enqueue_style( 'bootstrap-css', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' );
        wp_enqueue_script('bootstrap-js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        wp_enqueue_style( 'Material-Icons2', '//fonts.googleapis.com/icon?family=Material+Icons' );

        wp_enqueue_style( 'OPENICONIC', '/open-iconic/font/css/open-iconic-bootstrap.css' );
        wp_enqueue_style( 'HOVER', get_template_directory_uri(). '/Hover-master/css/hover-min.css' );
        wp_enqueue_script('youtube-scribe-button', 'https://apis.google.com/js/platform.js');



        wp_enqueue_style( 'Google Font Roboto Slab', '//fonts.googleapis.com/css?family=IBM+Plex+Sans|Roboto+Condensed|Anton|Patua+One|Roboto+Slab|Ubuntu|Source+Sans+Pro' );

}
endif;

/** Add secondary logo for for a specific pages. */
add_filter( 'get_custom_logo', 'custom_secondary_logo_callback' );
/**
 * @link https://codex.wordpress.org/Conditional_Tags
 */
function custom_secondary_logo_callback( $logo ){
    $your_custom_logo = '<div><img src="'.get_field('secondary_logo').'" alt="No Image" width="'.get_field('width').'px"></div>';// Add your custom logo maerkup instead.
    // is_singular() find more conditional tags from here -- https://codex.wordpress.org/Conditional_Tags
    if ( is_page( array('Momentum Mondays', 'Season One' )) ) {
        $logo = $your_custom_logo;
    }
    return $logo;
}


add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 20 );

function my_recent_posts_shortcode($atts){
    $q = new WP_Query(
        array( 'orderby' => 'date', 'posts_per_page' => '15')
    );

    $list = '<div class="recent-posts">';

    while($q->have_posts()) : $q->the_post();


        $list .= '<div class="container" style="font-family: \'IBM Plex Sans\', sans-serif;"><div class="row"><div align="center" class="col-lg-9 col-md-8 col-sm-12 col-xs-12" style="padding: 20px 20px 20px 20px; text-align: left;"><h3><b>'. get_the_title(). '</b></h3><hr align="left" style="background-color:#FAB825; height:1.5px; width:100px;"/>Posted On: <i><span style="color: #888;">' . get_the_date() . '</i></span><br/><br/>' . get_the_excerpt() .  '<div><a href="' . get_permalink() . '" style="color: #FAB825; font-size: 15px;">Read More!</a></div></div></div></div>';

    endwhile;

    wp_reset_query();

    return $list . '</div>';

}

add_shortcode('recent-posts', 'my_recent_posts_shortcode');


// END ENQUEUE PARENT ACTION
?>
