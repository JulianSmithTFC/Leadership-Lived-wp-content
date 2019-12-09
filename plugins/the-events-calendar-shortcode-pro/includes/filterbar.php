<?php
/**
 * Output the filterbar PHP, along with the CSS if it hasn't been already
 *
 * @param $atts
 * @param $design_count
 *
 * @return string
 */
function ecs_maybe_add_filterbar_output( $atts, $design_count ) {
    $output = '';
    if ( Events_Calendar_Shortcode::isValid( $atts['filterbar'] ) ) {
        if ( ! wp_script_is( 'tecs-filter-bar-calendar' ) ) {
            wp_enqueue_script( 'tecs-filter-bar-calendar' );
            wp_enqueue_script( 'tecs-select2' );
            ob_start();
            include( trailingslashit( dirname( TECS_PLUGIN_FILE ) ) . 'includes/designs/assets/css/filterbar.min.css' );
            include( trailingslashit( dirname( TECS_PLUGIN_FILE ) ) . 'includes/designs/assets/css/select2.min.css' );
            $output .= '<style>';
            $output .= ob_get_clean();
            $output .= '</style>';
        }

        ob_start();
        // NOTE: $design_count used in the filterbar template
        include( trailingslashit( dirname( TECS_PLUGIN_FILE ) ) . 'templates/filterbar.php' );
        $output .= ob_get_clean();
    }
    return $output;
}

function ecs_maybe_alter_atts_for_filterbar( $atts ) {
    if ( Events_Calendar_Shortcode::isValid( $atts['filterbar'] ) ) {
        if ( ! isset( $atts['ecs_page'] ) ) {
            $atts['ecs_page'] = 1;
        }
        $atts['ajaxurl'] = admin_url( 'admin-ajax.php' );
        $atts['action'] = 'ecs_calendar_events';
    }
    return $atts;
}

function ecs_maybe_add_event_settings_script_output( $atts, $design_count ) {
    $output = '';
    if ( Events_Calendar_Shortcode::isValid( $atts['filterbar'] ) ) {
        $output .= '<script type="text/javascript">var tecEventSettings = tecEventSettings || {}; tecEventSettings.' . $atts['design'] . ' = tecEventSettings.' . $atts['design'] . ' || {};';
        $output .= "tecEventSettings." . $atts['design'] . "['" . $design_count . "'] = " . json_encode( $atts ) . ";";
        $output .= '</script>';
    }
    return $output;
}