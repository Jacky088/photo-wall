<?php
/**
 * Bing wallpaper module.
 *
 * Pulls the newest wallpapers from a configurable (Bing compatible) API,
 * caches the result and exposes them as a virtual photo wall group that is
 * always appended after the last custom group on the frontend.
 *
 * The module is intentionally isolated: it never touches photo_wall_data /
 * photo_wall_groups, so enabling or disabling it cannot break existing walls.
 *
 * @package WP_Photo_Wall
 */

if (!defined('ABSPATH')) exit;

define('WP_PHOTO_WALL_BING_ENABLED_OPTION', 'photo_wall_bing_enabled');
define('WP_PHOTO_WALL_BING_API_OPTION', 'photo_wall_bing_api');
define('WP_PHOTO_WALL_BING_TITLE_OPTION', 'photo_wall_bing_title');
define('WP_PHOTO_WALL_BING_ORDER_OPTION', 'photo_wall_bing_order');
define('WP_PHOTO_WALL_BING_UPDATED_OPTION', 'photo_wall_bing_updated');
define('WP_PHOTO_WALL_BING_CRON_ENABLED_OPTION', 'photo_wall_bing_cron_enabled');
define('WP_PHOTO_WALL_BING_CRON_HOUR_OPTION', 'photo_wall_bing_cron_hour');
define('WP_PHOTO_WALL_BING_CRON_LAST_OPTION', 'photo_wall_bing_cron_last');

/** Download button shown right below the wallpapers (empty = hidden). */
define('WP_PHOTO_WALL_BING_DOWNLOAD_URL_OPTION', 'photo_wall_bing_download_url');
define('WP_PHOTO_WALL_BING_DOWNLOAD_TEXT_OPTION', 'photo_wall_bing_download_text');

/** Action fired once per day to pull the newest wallpapers. */
define('WP_PHOTO_WALL_BING_CRON_HOOK', 'wp_photo_wall_bing_daily_pull');

/** Default pull time: 01:00 in the site timezone. */
define('WP_PHOTO_WALL_BING_CRON_DEFAULT_HOUR', 1);

/** Virtual group id used when rendering the wallpapers on the frontend. */
define('WP_PHOTO_WALL_BING_GROUP_ID', 'bing_wallpaper');

/** How many wallpapers are kept (the latest 7 days). */
define('WP_PHOTO_WALL_BING_COUNT', 7);

/** How long a successful fetch is cached. */
define('WP_PHOTO_WALL_BING_CACHE_TTL', 6 * HOUR_IN_SECONDS);

/** How long a failed fetch is remembered (avoids hammering a broken API). */
define('WP_PHOTO_WALL_BING_FAIL_TTL', 5 * MINUTE_IN_SECONDS);

/**
 * Default endpoint: the official Bing homepage image archive.
 * idx=0&n=7 -> the newest 7 days, newest first. No key required.
 */
define('WP_PHOTO_WALL_BING_DEFAULT_API', 'https://www.bing.com/HPImageArchive.aspx?format=js&idx=0&n=7&mkt=zh-CN');

/** Whether the wallpaper group is shown on the frontend. */
function wp_photo_wall_bing_enabled()
{
    return get_option(WP_PHOTO_WALL_BING_ENABLED_OPTION, '1') !== '0';
}

/** Stored (sanitized) API endpoint, falling back to the default one. */
function wp_photo_wall_bing_api()
{
    $api = get_option(WP_PHOTO_WALL_BING_API_OPTION, '');
    return $api ? $api : WP_PHOTO_WALL_BING_DEFAULT_API;
}

/** Group title used on the frontend. */
function wp_photo_wall_bing_title()
{
    $title = get_option(WP_PHOTO_WALL_BING_TITLE_OPTION, '');
    return $title !== '' && $title !== false ? $title : wp_photo_wall_text('bing_group_default');
}

/** Saved manual order (list of item keys). Empty = natural (date) order. */
function wp_photo_wall_bing_order()
{
    $order = get_option(WP_PHOTO_WALL_BING_ORDER_OPTION, array());
    return is_array($order) ? array_values(array_filter(array_map('strval', $order), 'strlen')) : array();
}

/** Stored download link for the button below the wallpapers. Empty = hidden. */
function wp_photo_wall_bing_download_url()
{
    $url = get_option(WP_PHOTO_WALL_BING_DOWNLOAD_URL_OPTION, '');
    return is_string($url) ? trim($url) : '';
}

/** Stored button label, falling back to the default one. */
function wp_photo_wall_bing_download_text()
{
    $text = get_option(WP_PHOTO_WALL_BING_DOWNLOAD_TEXT_OPTION, '');
    $text = is_string($text) ? trim($text) : '';
    return $text !== '' ? $text : wp_photo_wall_text('bing_download_default');
}

/**
 * Markup of the download button shown below the wallpapers.
 *
 * The button belongs to the Bing wallpaper group only: it stays hidden when
 * that group is disabled, empty, or when no link has been configured.
 *
 * @param bool $pending  True when the current batch does not reach the Bing
 *                       group yet. The button is then printed hidden and the
 *                       frontend script reveals it below that group once the
 *                       group has been rendered.
 * @return string        Anchor markup, or an empty string when nothing should show.
 */
function wp_photo_wall_bing_download_button($pending = false)
{
    if (!wp_photo_wall_bing_enabled()) return '';
    if (!wp_photo_wall_bing_get_items()) return '';

    $url = wp_photo_wall_bing_download_url();
    if ($url === '') return '';

    $class = 'wp-photo-wall-bing-download';
    if ($pending) {
        $class .= ' wp-photo-wall-bing-download-pending';
    }

    return sprintf(
        '<a href="%1$s" class="%4$s" data-group-id="%3$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
        esc_url($url),
        esc_html(wp_photo_wall_bing_download_text()),
        esc_attr(WP_PHOTO_WALL_BING_GROUP_ID),
        esc_attr($class)
    );
}

/** Human readable "last updated" label. */
function wp_photo_wall_bing_last_updated()
{
    $time = (int) get_option(WP_PHOTO_WALL_BING_UPDATED_OPTION, 0);
    if (!$time) return wp_photo_wall_text('bing_never');

    return date_i18n(
        get_option('date_format') . ' ' . get_option('time_format'),
        $time
    );
}

/** Whether the daily automatic pull is enabled. */
function wp_photo_wall_bing_cron_enabled()
{
    return get_option(WP_PHOTO_WALL_BING_CRON_ENABLED_OPTION, '1') !== '0';
}

/** Hour of the day (site timezone) the daily pull runs at. */
function wp_photo_wall_bing_cron_hour()
{
    $hour = (int) get_option(WP_PHOTO_WALL_BING_CRON_HOUR_OPTION, WP_PHOTO_WALL_BING_CRON_DEFAULT_HOUR);
    if ($hour < 0 || $hour > 23) return WP_PHOTO_WALL_BING_CRON_DEFAULT_HOUR;
    return $hour;
}

/** Human readable label for the last automatic pull. */
function wp_photo_wall_bing_cron_last_run()
{
    $last = get_option(WP_PHOTO_WALL_BING_CRON_LAST_OPTION, array());
    if (!is_array($last) || empty($last['time'])) return wp_photo_wall_text('bing_never');

    $label = date_i18n(
        get_option('date_format') . ' ' . get_option('time_format'),
        (int) $last['time']
    );

    if (isset($last['count'])) {
        $label .= ' · ' . sprintf(wp_photo_wall_text('bing_updated'), (int) $last['count']);
    }

    return $label;
}

/**
 * Next run as a UTC timestamp for the given hour in the site timezone.
 *
 * @param int|null $hour  0-23, defaults to the stored hour.
 * @return int            Unix timestamp (UTC).
 */
function wp_photo_wall_bing_cron_next_timestamp($hour = null)
{
    if ($hour === null) $hour = wp_photo_wall_bing_cron_hour();
    $hour = (int) $hour;
    if ($hour < 0) $hour = 0;
    if ($hour > 23) $hour = 23;

    try {
        $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(date_default_timezone_get());
        $now = new DateTime('now', $tz);
        $next = new DateTime('now', $tz);
        $next->setTime($hour, 0, 0);

        // Already past that hour today? Run tomorrow instead.
        if ($next->getTimestamp() <= $now->getTimestamp()) {
            $next->modify('+1 day');
        }

        return $next->getTimestamp();
    } catch (Exception $e) {
        return time() + DAY_IN_SECONDS;
    }
}

/**
 * (Re)schedule the daily event so it matches the current settings.
 * Safe to call repeatedly: it is a no-op when the event is already aligned.
 */
function wp_photo_wall_bing_cron_schedule()
{
    $hook = WP_PHOTO_WALL_BING_CRON_HOOK;
    $next = wp_next_scheduled($hook);

    if (!wp_photo_wall_bing_cron_enabled()) {
        if ($next) wp_unschedule_event($next, $hook);
        return;
    }

    $wanted = wp_photo_wall_bing_cron_next_timestamp();

    // Keep the existing event when it is already at (or within a minute of)
    // the configured time, otherwise re-align it.
    if ($next && abs($next - $wanted) <= 60) return;

    if ($next) wp_unschedule_event($next, $hook);
    wp_schedule_event($wanted, 'daily', $hook);
}

/** Remove the daily event (used on deactivation). */
function wp_photo_wall_bing_cron_unschedule()
{
    wp_clear_scheduled_hook(WP_PHOTO_WALL_BING_CRON_HOOK);
}

/** Cron callback: force a fresh pull and remember when it happened. */
function wp_photo_wall_bing_cron_run()
{
    $items = wp_photo_wall_bing_fetch(true);

    update_option(WP_PHOTO_WALL_BING_CRON_LAST_OPTION, array(
        'time' => time(),
        'count' => count($items),
    ), false);

    // Re-align after a timezone / hour change.
    wp_photo_wall_bing_cron_schedule();
}
add_action(WP_PHOTO_WALL_BING_CRON_HOOK, 'wp_photo_wall_bing_cron_run');

/**
 * Self-healing: make sure the event exists (and is aligned) even if the site
 * was updated without reactivating the plugin, or the cron array was wiped.
 */
function wp_photo_wall_bing_cron_maybe_schedule()
{
    if (!wp_photo_wall_bing_cron_enabled()) return;
    if (wp_next_scheduled(WP_PHOTO_WALL_BING_CRON_HOOK)) {
        // Scheduled but misaligned (timezone / hour changed) -> realign.
        if (abs(wp_next_scheduled(WP_PHOTO_WALL_BING_CRON_HOOK) - wp_photo_wall_bing_cron_next_timestamp()) > 60) {
            wp_photo_wall_bing_cron_schedule();
        }
        return;
    }
    wp_photo_wall_bing_cron_schedule();
}
add_action('init', 'wp_photo_wall_bing_cron_maybe_schedule');

/** Plugin activation: arm the daily pull. */
function wp_photo_wall_bing_cron_activate()
{
    wp_photo_wall_bing_cron_schedule();
}

/** Plugin deactivation: never leave a dangling cron behind. */
function wp_photo_wall_bing_cron_deactivate()
{
    wp_photo_wall_bing_cron_unschedule();
}

/** Sanitize the API endpoint submitted from the admin. */
function wp_photo_wall_bing_sanitize_api($url)
{
    $url = trim((string) $url);
    if ($url === '') return WP_PHOTO_WALL_BING_DEFAULT_API;

    $url = esc_url_raw($url, array('http', 'https'));
    if (!$url || !wp_http_validate_url($url)) return WP_PHOTO_WALL_BING_DEFAULT_API;

    return $url;
}

/** Sanitize the frontend group title. */
function wp_photo_wall_bing_sanitize_title($title)
{
    $title = sanitize_text_field((string) $title);
    if ($title === '') $title = wp_photo_wall_text('bing_group_default');
    return wp_html_excerpt($title, 80, '');
}

/** Sanitize the download link (empty string hides the button). */
function wp_photo_wall_bing_sanitize_download_url($url)
{
    $url = trim((string) $url);
    if ($url === '') return '';

    return esc_url_raw($url, array('http', 'https'));
}

/** Sanitize the download button label. */
function wp_photo_wall_bing_sanitize_download_text($text)
{
    return wp_html_excerpt(sanitize_text_field((string) $text), 60, '');
}

/** Sanitize the manual order (list of item keys). */
function wp_photo_wall_bing_sanitize_order($order)
{
    $clean = array();
    if (!is_array($order)) return $clean;

    foreach (array_slice($order, 0, 30) as $key) {
        $key = sanitize_key((string) $key);
        if ($key !== '' && !in_array($key, $clean, true)) $clean[] = $key;
    }
    return $clean;
}

/** Turn a relative Bing path into an absolute URL using the API host. */
function wp_photo_wall_bing_absolute_url($url, $api)
{
    $url = trim((string) $url);
    if ($url === '') return '';

    if (preg_match('#^https?://#i', $url)) return $url;
    if (strpos($url, '//') === 0) return 'https:' . $url;

    $parts = wp_parse_url($api);
    if (empty($parts['scheme']) || empty($parts['host'])) return '';

    return $parts['scheme'] . '://' . $parts['host'] . '/' . ltrim($url, '/');
}

/**
 * Normalize one raw API entry into a predictable shape.
 *
 * Understands the official Bing payload and stays tolerant enough for custom
 * endpoints that return a plain list of {url, title, copyright, date}.
 *
 * @param array  $raw  Raw entry.
 * @param string $api  Endpoint the entry came from (used to resolve relatives).
 * @return array|null  {key, full, thumb, preview, title, copyright, date}
 */
function wp_photo_wall_bing_normalize_image($raw, $api)
{
    if (!is_array($raw)) return null;

    $url = '';
    foreach (array('url', 'full', 'image', 'src', 'img', 'download_url', 'thumbnail') as $field) {
        if (!empty($raw[$field]) && is_string($raw[$field])) {
            $url = $raw[$field];
            break;
        }
    }
    if ($url === '') return null;

    $full = wp_photo_wall_bing_absolute_url($url, $api);
    if (!$full) return null;

    // Bing sends `url` at 1920x1080 and a resolution-less `urlbase`
    // (/th?id=OHR.Name) that can be turned into any other size.
    $urlbase = isset($raw['urlbase']) && is_string($raw['urlbase']) ? $raw['urlbase'] : '';
    // e.g. /th?id=OHR.Name_1920x1080.jpg&rf=... -> /th?id=OHR.Name
    if ($urlbase === '' && preg_match('#^(https?://[^/]+)?(/th\?id=)([^&#]+?)(?:_\d+x\d+)?\.jpg#i', $url, $m)) {
        $urlbase = $m[2] . $m[3];
    }

    $thumb = '';
    $preview = '';
    if ($urlbase !== '') {
        $thumb = wp_photo_wall_bing_absolute_url($urlbase . '_400x240.jpg', $api);
        $preview = wp_photo_wall_bing_absolute_url($urlbase . '_800x600.jpg', $api);
    }
    if (!$thumb) $thumb = $full;
    if (!$preview) $preview = $full;

    $copyright = '';
    foreach (array('copyright', 'description', 'desc', 'caption') as $field) {
        if (!empty($raw[$field]) && is_string($raw[$field])) {
            $copyright = $raw[$field];
            break;
        }
    }
    $copyright = wp_strip_all_tags(html_entity_decode($copyright, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

    $title = isset($raw['title']) && is_string($raw['title']) ? sanitize_text_field($raw['title']) : '';

    $date = '';
    foreach (array('startdate', 'date', 'enddate') as $field) {
        if (!empty($raw[$field]) && is_string($raw[$field])) {
            $date = preg_replace('/\D/', '', $raw[$field]);
            break;
        }
    }

    return array(
        // Stable identifier: the wallpaper date when available, otherwise the url.
        'key' => $date !== '' ? 'd' . $date : 'u' . md5($full),
        'full' => $full,
        'thumb' => $thumb,
        'preview' => $preview,
        'title' => $title,
        'copyright' => $copyright,
        'date' => $date,
    );
}

/**
 * Find the image list inside a decoded API response.
 *
 * @param mixed $data  Decoded JSON.
 * @return array       Raw entries (may be empty).
 */
function wp_photo_wall_bing_extract_list($data)
{
    if (!is_array($data)) return array();

    foreach (array('images', 'data', 'list', 'results', 'wallpapers') as $field) {
        if (isset($data[$field]) && is_array($data[$field])) {
            return $data[$field];
        }
    }

    // Bare list: [ {...}, {...} ]
    if (isset($data[0])) return $data;

    return array();
}

/**
 * Fetch the wallpapers, using the transient cache unless a refresh is forced.
 *
 * @param bool        $force  Bypass the cache.
 * @param string|null $api    Endpoint to use (defaults to the stored one).
 * @return array              Normalized items, newest first.
 */
function wp_photo_wall_bing_fetch($force = false, $api = null)
{
    static $memory = array();

    if ($api === null || $api === '') {
        $api = wp_photo_wall_bing_api();
    }

    $cache_key = 'wp_photo_wall_bing_' . md5($api);

    if (!$force) {
        if (isset($memory[$cache_key])) return $memory[$cache_key];
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            $memory[$cache_key] = $cached;
            return $cached;
        }
    }

    $response = wp_remote_get($api, array(
        'timeout' => 10,
        'redirection' => 3,
        'headers' => array(
            'Accept' => 'application/json, text/javascript, */*',
        ),
    ));

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 400) {
        // Keep whatever we already have; remember the failure briefly so a slow
        // or broken endpoint cannot slow down every frontend request.
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            $memory[$cache_key] = $cached;
            return $cached;
        }
        set_transient($cache_key, array(), WP_PHOTO_WALL_BING_FAIL_TTL);
        return array();
    }

    $body = trim((string) wp_remote_retrieve_body($response));

    // Some providers wrap the payload in a JSONP callback.
    if ($body !== '' && $body[0] !== '{' && $body[0] !== '[' && preg_match('/\((.*)\)\s*;?\s*$/s', $body, $m)) {
        $body = trim($m[1]);
    }

    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $cached = get_transient($cache_key);
        if (is_array($cached)) return $cached;
        set_transient($cache_key, array(), WP_PHOTO_WALL_BING_FAIL_TTL);
        return array();
    }

    $items = array();
    foreach (array_slice(wp_photo_wall_bing_extract_list($data), 0, WP_PHOTO_WALL_BING_COUNT) as $raw) {
        $item = wp_photo_wall_bing_normalize_image($raw, $api);
        if ($item) $items[] = $item;
    }

    // Newest first (the API already returns that order, but do not trust it).
    usort($items, function ($a, $b) {
        if ($a['date'] === $b['date']) return 0;
        if ($a['date'] === '') return 1;
        if ($b['date'] === '') return -1;
        return strcmp($b['date'], $a['date']);
    });

    set_transient($cache_key, $items, WP_PHOTO_WALL_BING_CACHE_TTL);
    update_option(WP_PHOTO_WALL_BING_UPDATED_OPTION, time(), false);
    $memory[$cache_key] = $items;

    return $items;
}

/**
 * Apply the saved manual order. Wallpapers that are not part of the saved
 * order (i.e. brand new days) are appended in their natural date order.
 *
 * @param array $items  Natural (newest first) items.
 * @return array
 */
function wp_photo_wall_bing_apply_order($items)
{
    $order = wp_photo_wall_bing_order();
    if (!$order || !$items) return $items;

    $map = array();
    foreach ($items as $item) $map[$item['key']] = $item;

    $sorted = array();
    foreach ($order as $key) {
        if (isset($map[$key])) {
            $sorted[] = $map[$key];
            unset($map[$key]);
        }
    }
    foreach ($items as $item) {
        if (isset($map[$item['key']])) $sorted[] = $item;
    }

    return $sorted;
}

/**
 * Ordered wallpapers shaped like regular photo wall items so the shortcode and
 * the "load more" handler can render them with the existing markup.
 *
 * @return array
 */
function wp_photo_wall_bing_get_items()
{
    if (!wp_photo_wall_bing_enabled()) return array();

    $items = wp_photo_wall_bing_apply_order(wp_photo_wall_bing_fetch());
    $group_name = wp_photo_wall_bing_title();
    $out = array();

    foreach ($items as $item) {
        $out[] = array(
            'type' => 'external',
            'url' => $item['full'],
            'thumb' => $item['preview'],
            'title' => $item['title'],
            'group_id' => WP_PHOTO_WALL_BING_GROUP_ID,
            'group_name' => $group_name,
        );
    }

    return $out;
}
