<?php
/**
 * Data operations - sanitization, retrieval, storage
 * 
 * @package WP_Photo_Wall
 */

if (!defined('ABSPATH')) exit;

/** Normalize groups submitted by the admin. */
function wp_photo_wall_sanitize_groups($groups)
{
    if (!is_array($groups)) return array();
    $clean = array();
    $seen = array();
    foreach (array_slice($groups, 0, 100) as $group) {
        if (!is_array($group)) continue;
        $id = isset($group['id']) ? sanitize_key($group['id']) : '';
        $name = isset($group['name']) ? sanitize_text_field($group['name']) : '';
        if (!$id || $id === 'uncategorized' || !$name || isset($seen[$id])) continue;
        $clean[] = array('id' => $id, 'name' => wp_html_excerpt($name, 80, ''));
        $seen[$id] = true;
    }
    return $clean;
}

/** Normalize gallery items and keep only fields the plugin understands. */
function wp_photo_wall_sanitize_items($items, $groups)
{
    if (!is_array($items)) return array();
    $valid_groups = array_fill_keys(wp_list_pluck($groups, 'id'), true);
    $clean = array();
    foreach (array_slice($items, 0, 5000) as $item) {
        if (is_numeric($item)) $item = array('type' => 'local', 'id' => $item);
        if (!is_array($item)) continue;
        $group_id = isset($item['group_id']) ? sanitize_key($item['group_id']) : 'uncategorized';
        if ($group_id !== 'uncategorized' && !isset($valid_groups[$group_id])) $group_id = 'uncategorized';
        if (isset($item['type']) && $item['type'] === 'local') {
            $id = isset($item['id']) ? absint($item['id']) : 0;
            if ($id && wp_attachment_is_image($id)) $clean[] = array('type' => 'local', 'id' => $id, 'group_id' => $group_id);
        } elseif (isset($item['type']) && $item['type'] === 'external') {
            $url = isset($item['url']) ? esc_url_raw($item['url'], array('http', 'https')) : '';
            if ($url && strlen($url) <= 2048 && wp_http_validate_url($url)) $clean[] = array('type' => 'external', 'url' => $url, 'group_id' => $group_id);
        }
    }
    return $clean;
}

function wp_photo_wall_get_groups()
{
    $groups = json_decode((string) get_option('photo_wall_groups', '[]'), true);
    return wp_photo_wall_sanitize_groups($groups);
}

/**
 * Get sanitized items. Pass an already-loaded groups list to avoid fetching
 * groups a second time (e.g. inside get_visible_items()).
 *
 * @param array|null $groups  Sanitized groups from wp_photo_wall_get_groups().
 * @return array
 */
function wp_photo_wall_get_items($groups = null)
{
    if ($groups === null) {
        $groups = wp_photo_wall_get_groups();
    }
    $items = json_decode((string) get_option('photo_wall_data', '[]'), true);
    if (!is_array($items) || !$items) {
        $items = array_filter(array_map('absint', explode(',', (string) get_option('photo_wall_ids', ''))));
    }
    return wp_photo_wall_sanitize_items($items, $groups);
}

/**
 * Get items with thumbnail URLs pre-resolved (eliminates N+1 fetches on admin).
 */
function wp_photo_wall_get_items_with_thumbnails()
{
    $items = wp_photo_wall_get_items();
    foreach ($items as &$item) {
        if ($item['type'] === 'local') {
            $thumb = wp_get_attachment_image_url($item['id'], 'thumbnail');
            if (!$thumb) {
                $thumb = wp_get_attachment_image_url($item['id'], 'medium');
            }
            $item['thumb_url'] = $thumb ? $thumb : '';
        }
    }
    unset($item);
    return $items;
}

/** Return visible items in group order. Uncategorized is intentionally hidden. */
function wp_photo_wall_get_visible_items()
{
    $groups = wp_photo_wall_get_groups();
    $items = wp_photo_wall_get_items($groups);
    $by_group = array();
    foreach ($items as $item) $by_group[$item['group_id']][] = $item;
    $visible = array();
    foreach ($groups as $group) {
        foreach (isset($by_group[$group['id']]) ? $by_group[$group['id']] : array() as $item) {
            $item['group_name'] = $group['name'];
            $visible[] = $item;
        }
    }
    return $visible;
}

/**
 * Collect local (media library) attachment IDs from an items list.
 *
 * @param array $items  Sanitized or raw items (each may be a numeric id or an array).
 * @return int[]        Unique, non-zero attachment IDs.
 */
function wp_photo_wall_collect_local_ids($items)
{
    $ids = array();
    if (!is_array($items)) return $ids;

    foreach ($items as $item) {
        // Legacy form: a bare numeric attachment id.
        if (is_numeric($item)) {
            $id = (int) $item;
            if ($id > 0) $ids[] = $id;
            continue;
        }
        if (!is_array($item)) continue;
        if (!isset($item['type']) || $item['type'] !== 'local') continue;
        if (empty($item['id'])) continue;
        $ids[] = (int) $item['id'];
    }

    return array_values(array_unique(array_filter($ids, function ($id) {
        return $id > 0;
    })));
}

/**
 * Collect local attachment IDs currently stored in the top banner carousel.
 *
 * @return int[]
 */
function wp_photo_wall_get_slides_local_ids()
{
    $option = defined('WP_PHOTO_WALL_SLIDES_OPTION') ? WP_PHOTO_WALL_SLIDES_OPTION : 'photo_wall_slides';
    $slides = get_option($option, array());
    if (!is_array($slides)) return array();

    $ids = array();
    foreach ($slides as $slide) {
        if (is_array($slide) && isset($slide['type']) && $slide['type'] === 'local' && !empty($slide['id'])) {
            $ids[] = (int) $slide['id'];
        }
    }
    return array_values(array_unique(array_filter($ids)));
}

/**
 * Permanently delete media library attachments that the plugin no longer
 * references anywhere (neither the photo wall nor the top banner carousel).
 *
 * This is the single source of truth for orphan prevention: pass the union of
 * attachment IDs the plugin tracked before a change and the union it tracks
 * after; anything that dropped out is deleted from the media library.
 *
 * @param array $old_ids  IDs referenced before the change (wall + slides).
 * @param array $new_ids  IDs referenced after the change (wall + slides).
 * @return int            Number of attachments actually deleted.
 */
function wp_photo_wall_delete_orphaned_attachments($old_ids, $new_ids)
{
    $old = array_map('intval', (array) $old_ids);
    $new = array_map('intval', (array) $new_ids);

    $orphans = array_diff($old, $new);
    if (empty($orphans)) return 0;

    $deleted = 0;
    foreach ($orphans as $id) {
        $id = (int) $id;
        if ($id <= 0) continue;
        // Only touch real image attachments we actually control.
        if (!wp_attachment_is_image($id)) continue;
        if (!current_user_can('delete_post', $id)) continue;
        if (wp_delete_attachment($id, true)) {
            $deleted++;
        }
    }
    return $deleted;
}
