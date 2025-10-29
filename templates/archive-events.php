<?php
/**
 * Template for Events Archive
 * This file is loaded by the plugin, not the theme.
 */

if (!defined('ABSPATH')) exit; // Prevent direct access


get_header(); ?>

<main id="primary" class="site-main">
    <?php
    // Render the shortcode output (reuses same layout as [events_list])
    echo do_shortcode('[events_list]');
    ?>
</main>

<?php get_footer(); ?>
