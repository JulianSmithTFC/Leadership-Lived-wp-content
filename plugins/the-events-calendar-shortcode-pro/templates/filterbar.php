<div class="ecs-filterbar">
    <?php foreach ( array_map( 'strtolower', array_map( 'trim', explode( ',', $atts['filterorder'] ) ) ) as $filter ): ?>
        <?php
            $values = array();
            switch ( $filter ) {
                case 'category':
                    $categories = get_categories( array( 'taxonomy' => 'tribe_events_cat' ) );
                    foreach ( $categories as $category ) {
                        $values[$category->slug] = $category->name;
                    }
                    break;
                case 'venue':
                    $venues = get_posts( array( 'post_type' => 'tribe_venue', 'numberposts' => -1 ) );
                    foreach ( $venues as $venue ) {
                        $values[ intval( $venue->ID ) ] = $venue->post_title;
                    }
                    break;
                case 'state':
                    foreach( get_posts( array( 'post_type' => 'tribe_venue', 'numberposts' => -1 ) ) as $venue ) {
                        if ( get_post_meta( $venue->ID, '_VenueStateProvince', true ) ) {
                            $values[get_post_meta( $venue->ID, '_VenueStateProvince', true )] = get_post_meta( $venue->ID, '_VenueStateProvince', true );
                        }
                    }
                    break;
                case 'country':
                    foreach( get_posts( array( 'post_type' => 'tribe_venue', 'numberposts' => -1 ) ) as $venue ) {
                        if ( get_post_meta( $venue->ID, '_VenueCountry', true ) ) {
                            $values[get_post_meta( $venue->ID, '_VenueCountry', true )] = get_post_meta( $venue->ID, '_VenueCountry', true );
                        }
                    }
                    break;
            }
        ?>
        <?php if ( count( $values ) ): ?>
            <?php /** @var int $design_count */ ?>
            <div class="ecs-filterbar-item">
                <label for="ecs-<?php echo esc_attr( $filter ); ?>-select-<?php echo esc_attr( $atts['design'] ) ?>-<?php echo intval( $design_count ) ?>"><?php echo esc_html( $atts['filterbar-' . $filter . '-title'] ) ?></label>
                <select
                        id="ecs-<?php echo esc_attr( $filter ); ?>-select-<?php echo esc_attr( $atts['design'] ) ?>-<?php echo intval( $design_count ) ?>"
                        class="ecs-<?php echo esc_attr( $filter ); ?>-select"
                        data-calendar-id="<?php echo intval( $design_count ) ?>"
                        data-design="<?php echo esc_attr( $atts['design'] ) ?>"
                >
                    <option></option>
                    <?php foreach( $values as $key => $value ): ?>
                        <option value="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $value ) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>