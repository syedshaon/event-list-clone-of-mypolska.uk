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






// Include shortcode file
require_once plugin_dir_path(__FILE__) . 'includes/shortcode-events-list.php';



/**
 * Force plugin templates for Events single and archive
 */


function elm_events_force_plugin_templates($template) {
    if (is_singular('events')) {
        return plugin_dir_path(__FILE__) . 'templates/single-events.php';
    }

    if (is_post_type_archive('events')) {
        return plugin_dir_path(__FILE__) . 'templates/archive-events.php';
    }

    return $template;
}
add_filter('template_include', 'elm_events_force_plugin_templates', 99);


// Load plugin CSS only for Events
 function elm_enqueue_event_scripts() {

    // Enqueue FullCalendar and Moment
    wp_enqueue_script('moment-js', plugin_dir_url(__FILE__) . 'assets/js/moment.js', ['jquery'], null, true);
    wp_enqueue_script('fullcalendar-js', plugin_dir_url(__FILE__) . 'assets/js/fullcalendar.js', ['jquery'], null, true);
    wp_enqueue_style('fullcalendar-css', plugin_dir_url(__FILE__) . 'assets/css/fullcalendar.css');

    // Enqueue main custom script (this is where filtering + calendar logic will live)
    wp_enqueue_script(
        'events-list-mashi-frontend-js',
        plugin_dir_url(__FILE__) . 'assets/js/events.js',
        ['jquery', 'moment-js', 'fullcalendar-js'],
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/events.js'),
        true
    );

    // Enqueue plugin CSS
    wp_enqueue_style(
        'elm-events-style',
        plugin_dir_url(__FILE__) . 'assets/css/events.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/events.css')
    );

    // Fetch events for frontend JS
    $events = [];
    $query = new WP_Query([
        'post_type'      => 'events',
        'posts_per_page' => -1,
    ]);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $cats = wp_get_post_terms(get_the_ID(), 'category', ['fields' => 'slugs']);
            $events[] = [
                'id'        => get_the_ID(),
                'title'     => get_the_title(),
                'date'      => get_post_meta(get_the_ID(), '_elm_event_date', true),
                'time'      => get_post_meta(get_the_ID(), '_elm_event_time', true),
                'location'  => get_post_meta(get_the_ID(), '_elm_event_location', true),
                'excerpt'   => get_the_excerpt(),
                'categories'=> $cats,
                'link'      => get_permalink(),
            ];
        }
    }
    wp_reset_postdata();

    // Localize events data
    wp_localize_script('events-list-mashi-frontend-js', 'elmEventsData', [
        'events' => $events,
    ]);
}
add_action('wp_enqueue_scripts', 'elm_enqueue_event_scripts');



add_action('wp_enqueue_scripts', 'elm_enqueue_event_scripts');



 