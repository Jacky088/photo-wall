<?php
/**
 * Plugin Name: 照片墙插件
 * Description: A minimalist, Apple-inspired photo wall plugin with admin management.
 * Version: 2.1.0
 * Author: 木木
 * Text Domain: wp-photo-wall
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_PHOTO_WALL_PATH', plugin_dir_path(__FILE__));
define('WP_PHOTO_WALL_URL', plugin_dir_url(__FILE__));
define('WP_PHOTO_WALL_VERSION', '2.1.0');

// Load modules
require_once WP_PHOTO_WALL_PATH . 'includes/i18n.php';
require_once WP_PHOTO_WALL_PATH . 'includes/data.php';
require_once WP_PHOTO_WALL_PATH . 'includes/ajax.php';
require_once WP_PHOTO_WALL_PATH . 'includes/frontend.php';
require_once WP_PHOTO_WALL_PATH . 'includes/slides.php';

/**
 * Enqueue Admin Scripts and Styles
 */
function wp_photo_wall_admin_enqueue($hook)
{
    if ('toplevel_page_wp-photo-wall' !== $hook) {
        return;
    }

    wp_enqueue_media();

    wp_enqueue_script(
        'wp-photo-wall-admin',
        WP_PHOTO_WALL_URL . 'admin/admin-script.js',
        array('jquery', 'jquery-ui-sortable'),
        WP_PHOTO_WALL_VERSION,
        true
    );

    wp_localize_script('wp-photo-wall-admin', 'wp_photo_wall_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_photo_wall_delete_nonce'),
        'labels' => array(
            'local' => wp_photo_wall_text('from_media_library'),
            'external' => wp_photo_wall_text('from_link'),
            'clear_confirm' => wp_photo_wall_text('clear_confirm'),
            'clear_all_confirm_text' => wp_photo_wall_text('clear_all_confirm_text'),
            'remove_confirm' => wp_photo_wall_text('remove_confirm'),
            'delete_server_confirm' => wp_photo_wall_text('delete_server_confirm'),
            'preview' => wp_photo_wall_text('preview_area'),
            'checking' => wp_photo_wall_text('checking'),
            'invalid_url' => wp_photo_wall_text('invalid_url'),
            'delete_group_confirm' => wp_photo_wall_text('delete_group_confirm'),
            'group_not_empty' => wp_photo_wall_text('group_not_empty'),
            'ajax_error' => wp_photo_wall_text('ajax_error'),
            'done' => wp_photo_wall_text('done'),
            'delete_group' => wp_photo_wall_text('delete_group'),
            'uncategorized' => wp_photo_wall_text('uncategorized'),
            'move_to_group' => wp_photo_wall_text('move_to_group'),
            'select_target_group' => wp_photo_wall_text('select_target_group'),
            'uncategorized_warning' => wp_photo_wall_text('uncategorized_warning'),
            'moved_count' => wp_photo_wall_text('moved_count'),
            'no_group_selected' => wp_photo_wall_text('no_group_selected'),
            'move_confirm' => wp_photo_wall_text('move_confirm'),
            'save_reminder' => wp_photo_wall_text('save_reminder'),
            'unsaved_changes' => wp_photo_wall_text('unsaved_changes'),
            'move_selected' => wp_photo_wall_text('move_selected'),
            'drag' => wp_photo_wall_text('drag'),
            'remove' => wp_photo_wall_text('remove'),
            'slides_add_local' => wp_photo_wall_text('slides_add_local'),
            'slides_add_external' => wp_photo_wall_text('slides_add_external'),
            'add_to_wall' => wp_photo_wall_text('add_to_wall'),
            'image_url' => wp_photo_wall_text('image_url'),
        )
    ));

    wp_enqueue_style(
        'wp-photo-wall-admin-css',
        WP_PHOTO_WALL_URL . 'admin/admin-style.css',
        array(),
        WP_PHOTO_WALL_VERSION
    );
}
add_action('admin_enqueue_scripts', 'wp_photo_wall_admin_enqueue');

/**
 * Create Admin Menu
 */
function wp_photo_wall_menu()
{
    add_menu_page(
        wp_photo_wall_text('photo_wall_settings'),
        wp_photo_wall_text('photo_wall'),
        'manage_options',
        'wp-photo-wall',
        'wp_photo_wall_render_admin_page',
        'dashicons-images-alt2',
        20
    );
}
add_action('admin_menu', 'wp_photo_wall_menu');

/**
 * Add "Settings" link to the plugin action links (停用 | 设置)
 */
function wp_photo_wall_plugin_action_links($links)
{
    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=wp-photo-wall')) . '">' . esc_html(wp_photo_wall_text('settings_link')) . '</a>';
    $links[] = $settings_link;
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_photo_wall_plugin_action_links');

/**
 * Render Admin Page
 */
function wp_photo_wall_render_admin_page()
{
    if (!current_user_can('manage_options')) wp_die(esc_html(wp_photo_wall_text('permission_denied')));

    // Handle form submission
    if (isset($_POST['wp_photo_wall_nonce']) && wp_verify_nonce($_POST['wp_photo_wall_nonce'], 'wp_photo_wall_save')) {
        if (isset($_POST['photo_wall_data'])) {
            $json_data = wp_unslash($_POST['photo_wall_data']);
            $decoded = json_decode($json_data, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $groups_raw = isset($_POST['photo_wall_groups']) ? json_decode(wp_unslash($_POST['photo_wall_groups']), true) : array();
                $validated_groups = wp_photo_wall_sanitize_groups($groups_raw);
                $validated_data = wp_photo_wall_sanitize_items($decoded, $validated_groups);

                update_option('photo_wall_data', wp_json_encode($validated_data));
                update_option('photo_wall_groups', wp_json_encode($validated_groups));

                // Update legacy ID list
                $local_ids = array();
                foreach ($validated_data as $item) {
                    if (isset($item['type']) && $item['type'] === 'local') {
                        $local_ids[] = $item['id'];
                    }
                }
                update_option('photo_wall_ids', implode(',', $local_ids));

                // Save Settings
                if (isset($_POST['submit'])) {
                    $enable_lightbox = isset($_POST['wp_photo_wall_enable_lightbox']) ? '1' : '0';
                    update_option('wp_photo_wall_enable_lightbox', $enable_lightbox);

                    if (isset($_POST['wp_photo_wall_download_link'])) {
                        $download_link = esc_url_raw(wp_unslash($_POST['wp_photo_wall_download_link']), array('http', 'https'));
                        update_option('wp_photo_wall_download_link', $download_link);
                    }
                }

                // Save top banner carousel settings + selected slides.
                if (isset($_POST['submit'])) {
                    update_option(WP_PHOTO_WALL_SLIDES_ENABLED_OPTION, isset($_POST['photo_wall_slides_enabled']) ? '1' : '0');

                    $interval = isset($_POST['photo_wall_slides_interval']) ? (int) $_POST['photo_wall_slides_interval'] : 5;
                    if ($interval < 2) {
                        $interval = 2;
                    } elseif ($interval > 30) {
                        $interval = 30;
                    }
                    update_option(WP_PHOTO_WALL_SLIDES_INTERVAL_OPTION, $interval);

                    update_option(WP_PHOTO_WALL_SLIDES_LINK_OPTION, isset($_POST['photo_wall_slides_link']) ? '1' : '0');

                    if (isset($_POST['photo_wall_slides'])) {
                        $slides_raw = json_decode(wp_unslash($_POST['photo_wall_slides']), true);
                        if (is_array($slides_raw)) {
                            wp_photo_wall_save_slides($slides_raw);
                        }
                    } else {
                        wp_photo_wall_save_slides(array());
                    }
                }

                $msg = wp_photo_wall_text('settings_saved');
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($msg) . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html(wp_photo_wall_text('json_error')) . '</p></div>';
            }
        }
    }

    require_once WP_PHOTO_WALL_PATH . 'admin/admin-page.php';
}
