<?php
/**
 * Plugin Name: Events List Mashi
 * Description: Adds a custom post type "Events" with categories, meta fields (date, time, location), and a shortcode [events_list] to display them.
 * Version: 1.1.0
 * Author: Mashi
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Custom Post Type: Events
 */
function elm_register_events_post_type() {
    $labels = array(
        'name'               => __('Events', 'events-list-mashi'),
        'singular_name'      => __('Event', 'events-list-mashi'),
        'add_new'            => __('Add New Event', 'events-list-mashi'),
        'add_new_item'       => __('Add New Event', 'events-list-mashi'),
        'edit_item'          => __('Edit Event', 'events-list-mashi'),
        'new_item'           => __('New Event', 'events-list-mashi'),
        'view_item'          => __('View Event', 'events-list-mashi'),
        'search_items'       => __('Search Events', 'events-list-mashi'),
        'not_found'          => __('No Events found', 'events-list-mashi'),
        'not_found_in_trash' => __('No Events found in Trash', 'events-list-mashi'),
        'menu_name'          => __('Events', 'events-list-mashi'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_in_menu'       => true,
        'menu_icon'          => 'dashicons-calendar',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'events'),
        'show_in_rest'       => true,
        'taxonomies'         => array('category'),
    );

    register_post_type('events', $args);
}
add_action('init', 'elm_register_events_post_type');

/**
 * Add Meta Boxes (Date, Time, Location)
 */
function elm_add_event_meta_boxes() {
    add_meta_box(
        'elm_event_details',
        __('Event Details', 'events-list-mashi'),
        'elm_event_details_callback',
        'events',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'elm_add_event_meta_boxes');

/**
 * Meta Box HTML
 */
function elm_event_details_callback($post) {
    $date = get_post_meta($post->ID, '_elm_event_date', true);
    $time = get_post_meta($post->ID, '_elm_event_time', true);
    $location = get_post_meta($post->ID, '_elm_event_location', true);

    wp_nonce_field('elm_save_event_details', 'elm_event_nonce');
    ?>
    <p>
        <label for="elm_event_date"><strong>Date*</strong></label><br>
        <input type="date" id="elm_event_date" name="elm_event_date" value="<?php echo esc_attr($date); ?>" required>
    </p>
   <p>
    <label for="elm_event_time"><strong>Time</strong></label><br>
    <input type="text" id="elm_event_time" name="elm_event_time" 
           value="<?php echo esc_attr($time); ?>" 
           placeholder="e.g. 11:00 a.m. to 3:00 p.m." 
           style="width:100%;">
</p>
    <p>
        <label for="elm_event_location"><strong>Location</strong></label><br>
        <input type="text" id="elm_event_location" name="elm_event_location" value="<?php echo esc_attr($location); ?>" style="width:100%;">
    </p>
    <?php
}

/**
 * Save Meta Box Data
 */
function elm_save_event_details($post_id) {
    if (!isset($_POST['elm_event_nonce']) || !wp_verify_nonce($_POST['elm_event_nonce'], 'elm_save_event_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['elm_event_date'])) {
        update_post_meta($post_id, '_elm_event_date', sanitize_text_field($_POST['elm_event_date']));
    }

    if (isset($_POST['elm_event_time'])) {
        update_post_meta($post_id, '_elm_event_time', sanitize_text_field($_POST['elm_event_time']));
    }

    if (isset($_POST['elm_event_location'])) {
        update_post_meta($post_id, '_elm_event_location', sanitize_text_field($_POST['elm_event_location']));
    }
}
add_action('save_post', 'elm_save_event_details');





/**
 * Admin notice: remind admin to use [events_list] shortcode
 */
function elm_events_shortcode_admin_notice() {
    // Only show to admins
    if (!current_user_can('manage_options')) {
        return;
    }

    // Only show on dashboard pages (not on post edit screens)
    $screen = get_current_screen();
    if ($screen && $screen->base === 'dashboard') {
        ?>
        <div class="notice notice-info is-dismissible">
            <p><strong>Events List Mashi:</strong>  
            To display your events on the site, add the shortcode  
            <code>[events_list]</code> to any page or post.</p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'elm_events_shortcode_admin_notice');


/**
 * Fallback templates for single and archive Events
 */
function elm_events_fallback_templates($template) {
    if (is_singular('events')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/single-events.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    if (is_post_type_archive('events')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/archive-events.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    return $template;
}
add_filter('template_include', 'elm_events_fallback_templates');



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

    <div class="grid__cell-1-9 grid__cell-l-1-12">
        <div class="page-columns">
            <div class="page-columns__left">
                <nav aria-label="breadcrumbs" class="rank-math-breadcrumb">
                    <p>
                        <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
                        <span class="separator">/</span>
                        <span class="last">Events</span>
                    </p>
                </nav>
                <h1 class="title title--uppercase title--no-mt">Parties and Events</h1>
            </div>
        </div>

        <div class="categories_list scrolled-box">
            <a href="<?php echo esc_url(get_post_type_archive_link('events')); ?>" class="categories_list__item categories_list__item--active categories_list__item--all scrolled-box__element">
                All events
            </a>
            <?php 
            $terms = get_terms(array('taxonomy' => 'category', 'hide_empty' => true));
            if (!empty($terms) && !is_wp_error($terms)) :
                foreach ($terms as $term) : ?>
                    <a class="categories_list__item scrolled-box__element" href="<?php echo esc_url(get_term_link($term)); ?>">
                        <?php echo esc_html($term->name); ?>
                    </a>
                <?php endforeach;
            endif; ?>
        </div>

        <div class="grid js-event-day-filter">
            <div class="grid__cell-1-4 grid__cell-m-1-4 grid__cell-s-1-5">
                <div class="calendar" data-element="calendar">
                    <div class="calendar__header">
                        <div class="calendar__nav">
                            <a href="#" class="calendar__nav-prev">&lt;</a>
                            <div class="calendar__nav-current"><?php echo date('F Y'); ?></div>
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
                        <!-- JS will populate calendar days here -->
                    </div>
                    <a href="#" class="calendar__button">See all</a>
                </div>
            </div>

            <div class="grid__cell-5-12 grid__cell-m-5-12 grid__cell-s-6-12 events">
                <h2 class="title title--small title--border title--no-mt">List of events</h2>
                <div class="horizontal-box-list" data-element="list">
                    <?php if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post(); ?>
                            <div class="horizontal-box-list__item notice-box notice-box--small js-modal" data-day="1">
                                <div class="notice-box__header">
                                    <div class="notice-category">
                                        <?php
                                        $cats = get_the_terms(get_the_ID(), 'category');
                                        if ($cats && !is_wp_error($cats)) :
                                            foreach ($cats as $cat) : ?>
                                                <div class="notice-category__name"><?php echo esc_html($cat->name); ?></div>
                                            <?php endforeach;
                                        endif; ?>
                                    </div>
                                    <a href="<?php the_permalink(); ?>">
                                        <h2 class="title title--medium notice-box__title"><?php the_title(); ?></h2>
                                    </a>
                                </div>
                                <div class="notice-box__image-wrapper">
                                    <?php if (has_post_thumbnail()) the_post_thumbnail('medium', array('class'=>'notice-box__img')); ?>
                                </div>
                                <div class="notice-box__content">
                                    <div class="notice-box__columns">
                                        <div class="notice-box__column-full"><?php the_excerpt(); ?></div>
                                    </div>
                                    <a href="<?php the_permalink(); ?>" class="notice-box__link">Details</a>
                                </div>
                            </div>
                        <?php endwhile;
                    else : ?>
                        <p>No events found.</p>
                    <?php endif; ?>
                </div>

                <div class="pagination-wrapper pagination-wrapper--months">
                    <?php previous_posts_link('&lt; Previous Month'); ?>
                    <?php next_posts_link('Next Month &gt;'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php wp_reset_postdata();

    return ob_get_clean();
}


add_shortcode('events_list', 'elm_render_events_list');