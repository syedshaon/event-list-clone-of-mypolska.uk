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
             <div class="events-list-mashi">
                <div class="calendar" data-element="calendar">
                  <div class="calendar__header">
                    <div class="calendar__nav">
                      <a href="#" class="calendar__nav-prev">&lt;</a>
                      <div class="calendar__nav-current">October 2025</div>
                      <a href="#" class="calendar__nav-next">&gt;</a>
                    </div>
                    <div class="calendar__day-names">
                      <div class="calendar__day-name">PN</div>
                      <div class="calendar__day-name">WT</div>
                      <div class="calendar__day-name">WED</div>
                      <div class="calendar__day-name">CZ</div>
                      <div class="calendar__day-name">PT</div>
                      <div class="calendar__day-name">SO</div>
                      <div class="calendar__day-name">N</div>
                    </div>
                  </div>

                  <div class="calendar__days">
                    <!-- JS will fill day cells here -->
                  </div>

                  <button data-category="all"  class="calendar__button elm-see-all-btn">See all</button>
                </div>

                <h2 class="all-events-title all-events-title--small all-events-title--border all-events-title--no-mt">List of events</h2>
                <div id="events-list"><!-- event list injected here --></div>
              </div>

   
        </div>
    </div>
    <?php wp_reset_postdata();

    return ob_get_clean();
}


add_shortcode('events_list', 'elm_render_events_list');
