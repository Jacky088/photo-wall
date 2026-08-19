<?php
/**
 * AJAX Handlers
 * 
 * @package WP_Photo_Wall
 */

if (!defined('ABSPATH')) exit;

/**
 * Handle AJAX Image Deletion
 */
function wp_photo_wall_ajax_delete_image()
{
    check_ajax_referer('wp_photo_wall_delete_nonce', 'security');

    if (!current_user_can('delete_posts')) {
        wp_send_json_error(wp_photo_wall_text('permission_denied'));
    }

    // Rate limiting
    $transient_key = 'wp_photo_wall_delete_limit_' . get_current_user_id();
    $delete_count = get_transient($transient_key);
    if ($delete_count !== false && $delete_count > 50) {
        wp_send_json_error('Rate limit exceeded. Please wait a moment.');
    }

    $attachment_ids = array();

    if (isset($_POST['attachment_id'])) {
        $attachment_ids[] = intval($_POST['attachment_id']);
    }

    if (isset($_POST['attachment_ids']) && is_array($_POST['attachment_ids'])) {
        $attachment_ids = array_merge($attachment_ids, array_map('intval', $_POST['attachment_ids']));
    }

    if (count($attachment_ids) > 100) {
        wp_send_json_error('Too many items. Please delete in smaller batches (max 100).');
    }

    if (empty($attachment_ids)) {
        wp_send_json_error(wp_photo_wall_text('no_images_selected'));
    }

    $deleted_count = 0;
    foreach ($attachment_ids as $id) {
        if ($id > 0 && current_user_can('delete_post', $id)) {
            if (wp_delete_attachment($id, true)) {
                $deleted_count++;
            }
        }
    }

    set_transient($transient_key, ($delete_count ? $delete_count : 0) + $deleted_count, 60);

    if ($deleted_count > 0) {
        $json_data = get_option('photo_wall_data', '');
        $data_array = array();
        if (!empty($json_data)) {
            $decoded = json_decode($json_data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data_array = $decoded;
            }
        }

        $new_data = array();
        foreach ($data_array as $item) {
            $keep = true;
            if (isset($item['type']) && $item['type'] === 'local') {
                if (in_array($item['id'], $attachment_ids)) {
                    $keep = false;
                }
            }
            if ($keep) {
                $new_data[] = $item;
            }
        }

        update_option('photo_wall_data', wp_json_encode($new_data), false);

        $legacy_ids = array();
        foreach ($new_data as $item) {
            if (isset($item['type']) && $item['type'] === 'local') {
                $legacy_ids[] = $item['id'];
            }
        }
        update_option('photo_wall_ids', implode(',', $legacy_ids), false);

        $msg = sprintf(wp_photo_wall_text('success_delete'), $deleted_count);
        wp_send_json_success($msg);
    } else {
        wp_send_json_error(wp_photo_wall_text('failed_delete'));
    }
}
add_action('wp_ajax_delete_photo_wall_image', 'wp_photo_wall_ajax_delete_image');

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
