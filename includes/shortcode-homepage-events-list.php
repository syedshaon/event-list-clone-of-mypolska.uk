<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [events_list]
 * Renders the list of events
 */

/**
 * Render events list (archive or shortcode)
 */
function elm_render_homepage_events_list($atts = array()) {
    // Start output buffering
    ob_start();

    // Default attributes
    $atts = shortcode_atts(array(
        'month'          => date('m'),    // current month by default
        'year'           => date('Y'),    // current year by default
        'posts_per_page' => -1,
    ), $atts, 'events_list');

    // Query events
    $args = array(
        'post_type'      => 'events',
        'posts_per_page' => intval($atts['posts_per_page']),
        'meta_key'       => '_elm_event_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => '_elm_event_date',
                'value'   => array(
                    $atts['year'] . '-' . $atts['month'] . '-01',
                    $atts['year'] . '-' . $atts['month'] . '-31'
                ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE',
            ),
        ),
    );

    $query = new WP_Query($args);
    ?>

    <div class="elm_homepage_archive-events">
          
         
           
                

                <div class="elm_homepage_events-container">
                       <h2 class="elm_homepage_all-events-title">Nadchodzące imprezy i wydarzenia</h2>
                  <div id="events_homepage_list">
                  <!-- event list injected here -->
                  </div>

                </div>
           
             

   
        
    </div>
    <?php wp_reset_postdata();

    return ob_get_clean();
}


add_shortcode('elm_homepage_events_list', 'elm_render_homepage_events_list');
