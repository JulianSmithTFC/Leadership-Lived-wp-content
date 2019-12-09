<?php
if ( ! defined( 'ECS_MAX_QUERY_LIMIT' ) )
	define( 'ECS_MAX_QUERY_LIMIT', 500 );

function doing_ecs_ajax_get_calendar_events() {
    return defined( 'DOING_AJAX' ) and DOING_AJAX and isset( $_POST['action'] ) and 'ecs_calendar_events' === $_POST['action'];
}

/**
 * Fetch the events and pass to the calendar
 */
function ajax_ecs_get_calendar_events() {
	global $events_calendar_shortcode;
	$args = array_map( 'sanitize_text_field', $_POST );
	if ( isset( $args['limit'] ) and intval( $args['limit'] ) > ECS_MAX_QUERY_LIMIT )
		$args['limit'] = ECS_MAX_QUERY_LIMIT;
	wp_send_json( array( 'success' => true, 'html' => $events_calendar_shortcode->ecs_fetch_events( $args ) ) );
}

add_action( 'wp_ajax_ecs_calendar_events', 'ajax_ecs_get_calendar_events' );
add_action( 'wp_ajax_nopriv_ecs_calendar_events', 'ajax_ecs_get_calendar_events' );
