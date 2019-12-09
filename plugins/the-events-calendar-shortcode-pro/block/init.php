<?php
/**
 * Register the block assets for Pro
 *
 * @since 1.9.0
 */
function ecsp_register_block_assets() {
    wp_enqueue_script(
        'ecs-block-pro',
		plugins_url( 'dist/block.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
        TECS_VERSION
    );

    wp_enqueue_style(
		'ecs-block-pro',
		plugins_url( 'dist/ecs-block.css', dirname( __FILE__ ) ),
		array(),
        TECS_VERSION
    );

    // add image url for design preview
    wp_localize_script(
        'ecs-block-pro',
        'ecsVars',
        array( 'imagesUrl' => plugins_url( 'dist/', dirname( __FILE__ ) ) )
    );

    wp_set_script_translations( 'ecs-block-pro', 'the-events-calendar-shortcode', plugin_dir_path( __FILE__ ) . 'languages' );
}
add_action( 'enqueue_block_editor_assets', 'ecsp_register_block_assets' );

/**
 * Allow for attribute defaults to be set for the pro version
 *
 * @since 1.9.0
 */
function ecsp_attributes( $attributes ) {
    $pro_attributes = array(
        'defaultview'       => array(
            'type'      => 'string',
            'default'   => 'month',
        ),
        'button'            => array(
            'type'      => 'string',
            'default'   => 'false',
        ),
        'description'       => array(
            'type'      => 'string',
            'default'   => 'false',
        ),
        'design' 	=> array(
            'type'      => 'string',
            'default'	=> 'default',
        ),
        'columns'         => array( 'type' => 'number' ),
        'raw_description' => array( 'type' => 'string' ),
    );

    return array_merge( $attributes, $pro_attributes );
}
add_filter( 'ecs_block_attributes', 'ecsp_attributes', 10, 1 );