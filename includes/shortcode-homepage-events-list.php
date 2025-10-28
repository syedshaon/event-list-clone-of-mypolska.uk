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

    <div class="elm_events-box">
      <h2 class="elm_title elm_title--smaller elm_title--uppercase elm_title--blue elm_title--no-mt">
        Nadchodzące imprezy i wydarzenia
      </h2>
      <div class="elm_events-box__list elm_events_homepage_list" id="elm_events_homepage_list">
        <!-- events injected here -->
      </div>
      <a href="#" class="elm_events-box__more">
        Przeglądaj wszystkie
        <svg width="36" height="6" viewBox="0 0 36 6" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M36 3L31 0.113249V5.88675L36 3ZM0 3.5H31.5V2.5H0V3.5Z" fill="var(--default-font-color)"></path>
        </svg>
      </a>
    </div>

    <?php wp_reset_postdata();

    return ob_get_clean();
}


add_shortcode('elm_homepage_events_list', 'elm_render_homepage_events_list');
