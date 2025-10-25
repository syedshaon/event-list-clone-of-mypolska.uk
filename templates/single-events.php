<?php
/**
 * Template for single Event post
 * File: templates/single-events.php
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

get_header();

// Start the Loop
if (have_posts()) :
    while (have_posts()) : the_post();

        // Custom fields (if you’re using ACF or manual meta fields)
        $event_date     = get_post_meta(get_the_ID(), '_elm_event_date', true);
        $event_location = get_post_meta(get_the_ID(), '_elm_event_location', true);
        $event_time     = get_post_meta(get_the_ID(), '_elm_event_time', true);
        ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class('single-event'); ?>>
            <header class="event-header">
                <h1 class="event-title"><?php the_title(); ?></h1>

                <div class="event-meta">
                    <?php if ($event_date) : ?>
                        <p><strong>Date:</strong> <?php echo esc_html(date_i18n('F j, Y', strtotime($event_date))); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($event_time) : ?>
                        <p><strong>Time:</strong> <?php echo esc_html($event_time); ?></p>
                    <?php endif; ?>

                    <?php if ($event_location) : ?>
                        <p><strong>Location:</strong> <?php echo esc_html($event_location); ?></p>
                    <?php endif; ?>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="event-thumbnail">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <div class="event-content">
                <?php the_content(); ?>
            </div>

            <footer class="event-footer">
                <div class="event-navigation">
                    <div class="prev-event"><?php previous_post_link('%link', '← Previous Event'); ?></div>
                    <div class="next-event"><?php next_post_link('%link', 'Next Event →'); ?></div>
                </div>
            </footer>
        </article>

    <?php endwhile;
else :
    echo '<p>No event found.</p>';
endif;

get_footer();
