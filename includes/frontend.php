<?php
/**
 * Frontend rendering - shortcode, image rendering, enqueue
 * 
 * @package WP_Photo_Wall
 */

if (!defined('ABSPATH')) exit;

/**
 * Enqueue Frontend Scripts and Styles
 */
function wp_photo_wall_public_enqueue()
{
    wp_enqueue_style(
        'wp-photo-wall-css',
        WP_PHOTO_WALL_URL . 'public/gallery-style.css',
        array(),
        WP_PHOTO_WALL_VERSION
    );

    wp_enqueue_script(
        'wp-photo-wall-js',
        WP_PHOTO_WALL_URL . 'public/gallery-script.js',
        array('jquery'),
        WP_PHOTO_WALL_VERSION,
        true
    );

    // Pass configuration and labels to frontend
    wp_localize_script('wp-photo-wall-js', 'wp_photo_wall_config', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'enable_lightbox' => get_option('wp_photo_wall_enable_lightbox', '1'),
        'nonce' => wp_create_nonce('wp_photo_wall_frontend_nonce'),
        'labels' => array(
            'image_load_error' => wp_photo_wall_text('image_load_error'),
        )
    ));
}

/**
 * Render a frontend gallery image with stable loading attributes.
 */
function wp_photo_wall_render_image($thumb_url, $full_url, $item_index, $attachment_id = 0)
{
    $loading = $item_index < 12 ? 'eager' : 'lazy';
    $fetch_priority = $item_index < 4 ? 'high' : 'auto';

    $image_attrs = array(
        'alt' => '',
        'loading' => $loading,
        'fetchpriority' => $fetch_priority,
        'decoding' => 'async',
        'sizes' => '(min-width: 900px) 25vw, (min-width: 600px) 33vw, 50vw',
        'oncontextmenu' => 'return false;',
    );

    echo '<div class="wp-photo-wall-item">';
    echo '<div class="wp-photo-wall-item-inner">';
    echo '<div class="wp-photo-wall-lightbox-trigger" data-full-url="' . esc_url($full_url) . '">';
    echo '<div class="wp-photo-wall-shield"></div>';
    if ($attachment_id > 0) {
        echo wp_get_attachment_image($attachment_id, 'medium_large', false, $image_attrs);
    } else {
        $img_attrs = 'src="' . esc_url($thumb_url) . '" ';
        $img_attrs .= 'alt="" ';
        $img_attrs .= 'loading="' . esc_attr($loading) . '" ';
        $img_attrs .= 'fetchpriority="' . esc_attr($fetch_priority) . '" ';
        $img_attrs .= 'decoding="async" ';
        $img_attrs .= 'sizes="' . esc_attr($image_attrs['sizes']) . '" ';
        $img_attrs .= 'oncontextmenu="return false;" ';
        $img_attrs .= 'referrerpolicy="no-referrer"';
        echo '<img ' . $img_attrs . ' />';
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

/**
 * Register Shortcode
 */
function wp_photo_wall_shortcode()
{
    wp_photo_wall_public_enqueue();
    ob_start();
    require WP_PHOTO_WALL_PATH . 'public/shortcode.php';
    return ob_get_clean();
}
add_shortcode('photo_wall', 'wp_photo_wall_shortcode');
