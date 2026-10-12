<?php
/**
 * AJAX Handlers
 * 
 * @package WP_Photo_Wall
 */

if (!defined('ABSPATH')) exit;

/**
 * Handle AJAX "Refresh Bing wallpapers" (admin only).
 *
 * Forces a fresh request against the submitted endpoint so the admin can
 * preview a new API URL before saving it.
 */
function wp_photo_wall_ajax_bing_refresh()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(wp_photo_wall_text('permission_denied'));
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_photo_wall_bing_nonce')) {
        wp_send_json_error('Security check failed.');
    }

    $api = isset($_POST['api'])
        ? wp_photo_wall_bing_sanitize_api(wp_unslash($_POST['api']))
        : wp_photo_wall_bing_api();

    $items = wp_photo_wall_bing_fetch(true, $api);

    wp_send_json_success(array(
        'items' => $items,
        'updated' => sprintf(wp_photo_wall_text('bing_last_update'), wp_photo_wall_bing_last_updated()),
        'message' => sprintf(wp_photo_wall_text('bing_updated'), count($items)),
    ));
}
add_action('wp_ajax_wp_photo_wall_bing_refresh', 'wp_photo_wall_ajax_bing_refresh');

/**
 * Handle AJAX test connection for Bing API or download URL.
 */
function wp_photo_wall_ajax_test_connection()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(wp_photo_wall_text('permission_denied'));
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_photo_wall_bing_nonce')) {
        wp_send_json_error('安全校验失败，请刷新页面重试。');
    }

    $url = isset($_POST['url']) ? esc_url_raw(trim(wp_unslash($_POST['url']))) : '';
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : 'general';

    if (empty($url) || !wp_http_validate_url($url)) {
        wp_send_json_error('请输入有效的 HTTP / HTTPS 链接地址。');
    }

    $response = wp_remote_get($url, array(
        'timeout'     => 10,
        'sslverify'   => false,
        'redirection' => 5,
        'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) WP-Photo-Wall/' . WP_PHOTO_WALL_VERSION,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('连接失败：' . $response->get_error_message());
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 400) {
        wp_send_json_error('目标地址连接异常，HTTP 状态码：' . $code);
    }

    if ($type === 'bing') {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (!is_array($data) || empty($data['images'])) {
            wp_send_json_error('接口连通（HTTP ' . $code . '），但返回数据未包含有效的 images 壁纸数组。');
        }
        $count = count($data['images']);
        wp_send_json_success(array(
            'message' => '连接成功！接口正常解析到 ' . $count . ' 张壁纸（HTTP ' . $code . '）。',
            'count'   => $count,
        ));
    }

    wp_send_json_success(array(
        'message' => '连接成功！目标地址正常可访问（HTTP ' . $code . '）。',
    ));
}
add_action('wp_ajax_wp_photo_wall_test_connection', 'wp_photo_wall_ajax_test_connection');

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
            $thumb_url = !empty($item['thumb']) ? $item['thumb'] : $item['url'];
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
