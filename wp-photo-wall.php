<?php
/**
 * Plugin Name: 图片墙插件
 * Description: A minimalist, Apple-inspired photo wall plugin with admin management.
 * Version: 2.8.0
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
define('WP_PHOTO_WALL_VERSION', '2.8.0');

// Load modules
require_once WP_PHOTO_WALL_PATH . 'includes/i18n.php';
require_once WP_PHOTO_WALL_PATH . 'includes/data.php';
require_once WP_PHOTO_WALL_PATH . 'includes/ajax.php';
require_once WP_PHOTO_WALL_PATH . 'includes/frontend.php';
require_once WP_PHOTO_WALL_PATH . 'includes/slides.php';
require_once WP_PHOTO_WALL_PATH . 'includes/bing.php';

// Arm / disarm the daily Bing wallpaper pull together with the plugin.
register_activation_hook(__FILE__, 'wp_photo_wall_bing_cron_activate');
register_deactivation_hook(__FILE__, 'wp_photo_wall_bing_cron_deactivate');

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
        filemtime(WP_PHOTO_WALL_PATH . 'admin/admin-script.js'),
        true
    );

    wp_localize_script('wp-photo-wall-admin', 'wp_photo_wall_ajax', array(
        'slides_max' => WP_PHOTO_WALL_SLIDES_MAX,
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
            'slides_add_to_carousel' => wp_photo_wall_text('slides_add_to_carousel'),
            'slides_count' => wp_photo_wall_text('slides_count'),
            'slides_limit_reached' => wp_photo_wall_text('slides_limit_reached'),
            'image_url' => wp_photo_wall_text('image_url'),
            'confirm_remove_title' => wp_photo_wall_text('confirm_remove_title'),
            'confirm_perm_delete_title' => wp_photo_wall_text('confirm_perm_delete_title'),
            'confirm_clear_title' => wp_photo_wall_text('confirm_clear_title'),
            'confirm_remove_local' => wp_photo_wall_text('confirm_remove_local'),
            'confirm_remove_external' => wp_photo_wall_text('confirm_remove_external'),
            'confirm_remove_bulk' => wp_photo_wall_text('confirm_remove_bulk'),
            'confirm_remove_slide' => wp_photo_wall_text('confirm_remove_slide'),
            'confirm_perm_delete' => wp_photo_wall_text('confirm_perm_delete'),
            'confirm_clear' => wp_photo_wall_text('confirm_clear'),
            'impact_external' => wp_photo_wall_text('impact_external'),
            'impact_local_delete' => wp_photo_wall_text('impact_local_delete'),
            'impact_perm_delete' => wp_photo_wall_text('impact_perm_delete'),
            'impact_slide_local' => wp_photo_wall_text('impact_slide_local'),
            'impact_clear' => wp_photo_wall_text('impact_clear'),
            'detail_local_count' => wp_photo_wall_text('detail_local_count'),
            'detail_external_count' => wp_photo_wall_text('detail_external_count'),
            'save_to_apply_note' => wp_photo_wall_text('save_to_apply_note'),
            'save_to_apply_note_perm' => wp_photo_wall_text('save_to_apply_note_perm'),
            'confirm_word_hint' => wp_photo_wall_text('confirm_word_hint'),
            'confirm_delete' => wp_photo_wall_text('confirm_delete'),
            'bulk_delete_selected' => wp_photo_wall_text('bulk_delete_selected'),
            'delete_from_media' => wp_photo_wall_text('delete_from_media'),
            'clear_all' => wp_photo_wall_text('clear_all'),
            'cancel' => wp_photo_wall_text('cancel'),
            'bing_refresh' => wp_photo_wall_text('bing_refresh'),
            'bing_refreshing' => wp_photo_wall_text('bing_refreshing'),
            'bing_fetch_failed' => wp_photo_wall_text('bing_fetch_failed'),
            'bing_empty' => wp_photo_wall_text('bing_empty'),
            'bing_updated' => wp_photo_wall_text('bing_updated'),
            'leave_confirm' => wp_photo_wall_text('leave_confirm'),
        ),
        'bing_nonce' => wp_create_nonce('wp_photo_wall_bing_nonce'),
    ));

    wp_enqueue_style(
        'wp-photo-wall-admin-css',
        WP_PHOTO_WALL_URL . 'admin/admin-style.css',
        array(),
        filemtime(WP_PHOTO_WALL_PATH . 'admin/admin-style.css')
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

                update_option('photo_wall_data', wp_json_encode($validated_data), false);
                update_option('photo_wall_groups', wp_json_encode($validated_groups), false);

                // Update legacy ID list
                $local_ids = wp_photo_wall_collect_local_ids($validated_data);
                update_option('photo_wall_ids', implode(',', $local_ids), false);

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

                // Save Bing wallpaper settings.
                if (isset($_POST['submit'])) {
                    update_option(WP_PHOTO_WALL_BING_ENABLED_OPTION, isset($_POST['photo_wall_bing_enabled']) ? '1' : '0');

                    $bing_api = isset($_POST['photo_wall_bing_api'])
                        ? wp_photo_wall_bing_sanitize_api(wp_unslash($_POST['photo_wall_bing_api']))
                        : WP_PHOTO_WALL_BING_DEFAULT_API;
                    update_option(WP_PHOTO_WALL_BING_API_OPTION, $bing_api);

                    $bing_title = isset($_POST['photo_wall_bing_title'])
                        ? wp_photo_wall_bing_sanitize_title(wp_unslash($_POST['photo_wall_bing_title']))
                        : wp_photo_wall_text('bing_group_default');
                    update_option(WP_PHOTO_WALL_BING_TITLE_OPTION, $bing_title);

                    $bing_order_raw = isset($_POST['photo_wall_bing_order'])
                        ? json_decode(wp_unslash($_POST['photo_wall_bing_order']), true)
                        : array();
                    update_option(WP_PHOTO_WALL_BING_ORDER_OPTION, wp_photo_wall_bing_sanitize_order($bing_order_raw));

                    update_option(WP_PHOTO_WALL_BING_CRON_ENABLED_OPTION, isset($_POST['photo_wall_bing_cron_enabled']) ? '1' : '0');

                    $bing_cron_hour = isset($_POST['photo_wall_bing_cron_hour'])
                        ? (int) $_POST['photo_wall_bing_cron_hour']
                        : WP_PHOTO_WALL_BING_CRON_DEFAULT_HOUR;
                    if ($bing_cron_hour < 0) $bing_cron_hour = 0;
                    if ($bing_cron_hour > 23) $bing_cron_hour = 23;
                    update_option(WP_PHOTO_WALL_BING_CRON_HOUR_OPTION, $bing_cron_hour);

                    // Re-arm the daily event so the new time takes effect at once.
                    wp_photo_wall_bing_cron_schedule();

                    $bing_download_url = isset($_POST['photo_wall_bing_download_url'])
                        ? wp_photo_wall_bing_sanitize_download_url(wp_unslash($_POST['photo_wall_bing_download_url']))
                        : '';
                    update_option(WP_PHOTO_WALL_BING_DOWNLOAD_URL_OPTION, $bing_download_url);

                    $bing_download_text = isset($_POST['photo_wall_bing_download_text'])
                        ? wp_photo_wall_bing_sanitize_download_text(wp_unslash($_POST['photo_wall_bing_download_text']))
                        : '';
                    update_option(WP_PHOTO_WALL_BING_DOWNLOAD_TEXT_OPTION, $bing_download_text);
                }

                // What the plugin still references after this save (wall + carousel).
                // Deletion below is skipped for anything in this set so no reference
                // is ever left dangling.
                $new_referenced = array_merge($local_ids, wp_photo_wall_get_slides_local_ids());

                // Explicit deletions queued from the admin (Media Library images the
                // user removed from the wall or the carousel). Applied here, on save,
                // so an unsaved removal never touches the server and the page can be
                // reloaded to revert. Anything still referenced elsewhere is skipped
                // so no reference is left dangling.
                $explicit_deleted_ids = array();
                if (isset($_POST['photo_wall_pending_deletions'])) {
                    $pending_raw = json_decode(wp_unslash($_POST['photo_wall_pending_deletions']), true);
                    if (is_array($pending_raw)) {
                        $candidates = array();
                        foreach (array_slice($pending_raw, 0, 500) as $pending_id) {
                            $pending_id = absint($pending_id);
                            if ($pending_id > 0 && !in_array($pending_id, $new_referenced, true)) {
                                $candidates[] = $pending_id;
                            }
                        }

                        if (!empty($candidates)) {
                            $explicit_deleted_ids = wp_photo_wall_delete_attachments($candidates);
                        }
                    }
                }

                if (!empty($explicit_deleted_ids)) {
                    // Drop any leftover reference to the deleted attachments so the
                    // wall and the carousel never keep a dangling id behind.
                    $local_ids = wp_photo_wall_purge_deleted_ids($validated_data, $explicit_deleted_ids);
                    update_option('photo_wall_data', wp_json_encode($validated_data), false);
                    update_option('photo_wall_ids', implode(',', $local_ids), false);

                    $stored_slides = get_option(WP_PHOTO_WALL_SLIDES_OPTION, array());
                    if (is_array($stored_slides)) {
                        $kept_slides = array();
                        foreach ($stored_slides as $stored_slide) {
                            if (
                                is_array($stored_slide)
                                && isset($stored_slide['type'], $stored_slide['id'])
                                && $stored_slide['type'] === 'local'
                                && in_array((int) $stored_slide['id'], $explicit_deleted_ids, true)
                            ) {
                                continue;
                            }
                            $kept_slides[] = $stored_slide;
                        }
                        if (count($kept_slides) !== count($stored_slides)) {
                            update_option(WP_PHOTO_WALL_SLIDES_OPTION, $kept_slides, false);
                        }
                    }

                    $msg = sprintf(wp_photo_wall_text('settings_saved_deleted'), count($explicit_deleted_ids));
                } else {
                    $msg = wp_photo_wall_text('settings_saved');
                }
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($msg) . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html(wp_photo_wall_text('json_error')) . '</p></div>';
            }
        }
    }

    require_once WP_PHOTO_WALL_PATH . 'admin/admin-page.php';
}
