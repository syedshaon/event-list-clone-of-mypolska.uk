<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [events_list]
 * Renders the list of events
 */

/**
 * Render events list (archive or shortcode)
 */
function elm_render_events_list($atts = array()) {
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

    <div class="archive-events">
        
            <div class="page-columns__left">
                <nav aria-label="breadcrumbs" class="rank-math-breadcrumb">
                    <small>
                        <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
                        <span class="separator">/</span>
                        <span class="last">Events</span>
                    </small>
                </nav>
                <h1 class="archive-title">Parties and Events</h1>
            </div>
     

            <!-- Categories List -->
     <div class="categories_list scrolled-box">
          <button  data-category="all" 
            class="categories_list__item categories_list__item--all scrolled-box__element 
            <?php echo (!is_tax() ? 'categories_list__item--active' : ''); ?>">
            All events
          </button>

          <?php 
          $terms = get_terms(array(
              'taxonomy' => 'category',
              'hide_empty' => true,
          ));
          if (!empty($terms) && !is_wp_error($terms)) :
              foreach ($terms as $term) :
                  $active_class = (is_tax('category', $term->slug)) ? 'categories_list__item--active' : '';
                  ?>
                  <button  data-category="<?php echo esc_html($term->slug); ?>" class="categories_list__item scrolled-box__element <?php echo esc_attr($active_class); ?>"  >
                      <?php echo esc_html($term->name); ?>
                  </button>
              <?php 
              endforeach;
          endif; 
          ?>
      </div>


        <div class="grid js-event-day-filter">
            <!-- calendar -->

            <div id="events-list-mashi-calendar"></div>
            <!-- events list -->
             <div id="events-list"></div>

   
        </div>
    </div>
    <?php wp_reset_postdata();

    return ob_get_clean();
}


add_shortcode('events_list', 'elm_render_events_list');
