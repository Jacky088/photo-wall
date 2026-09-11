<?php
/**
 * AJAX Handlers
 * 
 * @package WP_Photo_Wall
 */

if (!defined('ABSPATH')) exit;

/**
 * Handle AJAX Load More Images (with nonce verification)
 */
function wp_photo_wall_ajax_load_more()
{
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_photo_wall_frontend_nonce')) {
        wp_send_json_error('Security check failed.');
    }

    $page = isset($_POST['page']) ? max(1, min(10000, intval($_POST['page']))) : 1;
    $per_page = 12;

    // Reuse the shared data layer so grouping/sorting stays in sync with the
    // initial shortcode render. Uncategorized items are excluded by
    // wp_photo_wall_get_visible_items().
    $all_items = wp_photo_wall_get_visible_items();

    if (empty($all_items)) {
        wp_send_json_error(wp_photo_wall_text('no_images_found'));
    }

    $total_images = count($all_items);
    $total_pages = (int) ceil($total_images / $per_page);
    $offset = ($page - 1) * $per_page;
    $current_batch = array_slice($all_items, $offset, $per_page);

    if (empty($current_batch)) {
        wp_send_json_success(array(
            'html' => '',
            'has_more' => false
        ));
    }

    ob_start();

    $last_gid = null;
    $open_container = false;

    foreach ($current_batch as $item_index => $item) {
        $current_gid = $item['group_id'];

        if ($current_gid !== $last_gid) {
            if ($open_container) {
                echo '</div></div>';
            }
            echo '<div class="wp-photo-wall-group-section" data-group-id="' . esc_attr($current_gid) . '">';
            echo '<h3 class="wp-photo-wall-group-title">' . esc_html($item['group_name']) . '</h3>';
            echo '<div class="wp-photo-wall-group-grid">';
            $open_container = true;
            $last_gid = $current_gid;
        }

        $full_url = '';
        $thumb_url = '';
        $attachment_id = 0;

        if (isset($item['type']) && $item['type'] === 'external') {
            $full_url = $item['url'];
            $thumb_url = $item['url'];
        } elseif (isset($item['type']) && $item['type'] === 'local') {
            $id = $item['id'];
            $attachment_id = intval($id);
            $full_image_src = wp_get_attachment_image_src($id, 'full');
            $grid_image_src = wp_get_attachment_image_src($id, 'medium_large');
            if ($full_image_src && $grid_image_src) {
                $full_url = $full_image_src[0];
                $thumb_url = $grid_image_src[0];
            }
        }

        if ($full_url && $thumb_url) {
            wp_photo_wall_render_image($thumb_url, $full_url, 12 + $item_index, $attachment_id);
        }
    }

    if ($open_container) {
        echo '</div></div>';
    }

    $html = ob_get_clean();
    $has_more = $page < $total_pages;

    wp_send_json_success(array(
        'html' => $html,
        'has_more' => $has_more
    ));
}
add_action('wp_ajax_wp_photo_wall_load_more', 'wp_photo_wall_ajax_load_more');
add_action('wp_ajax_nopriv_wp_photo_wall_load_more', 'wp_photo_wall_ajax_load_more');
