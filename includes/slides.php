<?php
/**
 * Slides (top banner carousel) data layer + renderer.
 * Independent from the photo wall groups/images to avoid breaking existing logic.
 */

if (!defined('ABSPATH')) exit;

/**
 * Option names used by the slide module (isolated from photo_wall_data).
 */
define('WP_PHOTO_WALL_SLIDES_OPTION', 'photo_wall_slides');
define('WP_PHOTO_WALL_SLIDES_ENABLED_OPTION', 'photo_wall_slides_enabled');
define('WP_PHOTO_WALL_SLIDES_INTERVAL_OPTION', 'photo_wall_slides_interval');
define('WP_PHOTO_WALL_SLIDES_LINK_OPTION', 'photo_wall_slides_link');

/**
 * Sanitize a single slide entry.
 *
 * @param array $slide  Raw slide data (type / id / url / full).
 * @return array|null   Cleaned slide or null when invalid.
 */
function wp_photo_wall_sanitize_slide($slide) {
    if (!is_array($slide)) {
        return null;
    }

    $type = isset($slide['type']) ? sanitize_key($slide['type']) : '';
    $url  = isset($slide['url']) ? esc_url_raw(trim($slide['url'])) : '';

    if (!in_array($type, array('local', 'external'), true)) {
        return null;
    }
    if (empty($url)) {
        return null;
    }

    // Local slides carry a numeric attachment id, external slides use a fixed marker.
    $id = 'external';
    if ($type === 'local') {
        $id = isset($slide['id']) ? (int) $slide['id'] : 0;
        if ($id <= 0) {
            return null;
        }
    }

    // Full-size URL used by the lightbox (defaults to the display url).
    $full = isset($slide['full']) ? esc_url_raw(trim($slide['full'])) : '';
    if (empty($full)) {
        $full = $url;
    }

    return array(
        'type' => $type,
        'id'   => $id,
        'url'  => $url,
        'full' => $full,
    );
}

/**
 * Save the ordered list of slides submitted from the admin form.
 *
 * @param array $raw  Raw list of slide entries.
 * @return bool
 */
function wp_photo_wall_save_slides($raw) {
    $slides = array();

    if (is_array($raw)) {
        foreach ($raw as $entry) {
            $clean = wp_photo_wall_sanitize_slide($entry);
            if ($clean !== null) {
                $slides[] = $clean;
            }
        }
    }

    update_option(WP_PHOTO_WALL_SLIDES_OPTION, $slides, false);
    return true;
}

/**
 * Read the saved slides, enriching local slides with thumbnail URLs.
 *
 * @return array
 */
function wp_photo_wall_get_slides() {
    $slides = get_option(WP_PHOTO_WALL_SLIDES_OPTION, array());
    if (!is_array($slides)) {
        return array();
    }

    $out = array();
    foreach ($slides as $slide) {
        if (!is_array($slide) || empty($slide['url'])) {
            continue;
        }
        if ($slide['type'] === 'local' && !empty($slide['id'])) {
            $thumb = wp_get_attachment_image_url((int) $slide['id'], 'large');
            if ($thumb) {
                $slide['url'] = $thumb;
            }
            if (empty($slide['full'])) {
                $full = wp_get_attachment_image_url((int) $slide['id'], 'full');
                $slide['full'] = $full ? $full : $slide['url'];
            }
        }
        $out[] = $slide;
    }
    return $out;
}

/**
 * Whether the top slider is enabled.
 *
 * @return bool
 */
function wp_photo_wall_slides_enabled() {
    return (bool) get_option(WP_PHOTO_WALL_SLIDES_ENABLED_OPTION, false);
}

/**
 * Carousel interval in milliseconds.
 *
 * @return int
 */
function wp_photo_wall_slides_interval() {
    $seconds = (int) get_option(WP_PHOTO_WALL_SLIDES_INTERVAL_OPTION, 5);
    if ($seconds < 2) {
        $seconds = 2;
    }
    return $seconds * 1000;
}

/**
 * Whether slides should open the lightbox when clicked.
 *
 * @return bool
 */
function wp_photo_wall_slides_link() {
    return (bool) get_option(WP_PHOTO_WALL_SLIDES_LINK_OPTION, true);
}

/**
 * Render the top slider markup for a single photo wall instance.
 *
 * @param string $instance_id  Unique id of the current photo wall instance.
 * @return string
 */
function wp_photo_wall_render_slider($instance_id) {
    if (!wp_photo_wall_slides_enabled()) {
        return '';
    }

    $slides = wp_photo_wall_get_slides();
    if (empty($slides)) {
        return '';
    }

    $link = wp_photo_wall_slides_link();
    $interval = wp_photo_wall_slides_interval();

    $slides_html = '';
    foreach ($slides as $index => $slide) {
        $url   = esc_url($slide['url']);
        $full  = esc_url($slide['full']);
        $cls   = $index === 0 ? ' is-active' : '';

        if ($link) {
            // Use <button> instead of <a href> so third-party lightboxes that
            // hook a[href$="*.jpg"] etc. cannot hijack the click.
            $slides_html .= sprintf(
                '<div class="wp-pw-slide%s" data-index="%d">' .
                    '<button type="button" class="wp-photo-wall-lightbox-trigger wp-photo-wall-no-third-party-lightbox" data-full-url="%s" data-no-lightbox="1" aria-haspopup="dialog">' .
                        '<img src="%s" alt="" decoding="async" loading="%s">' .
                    '</button>' .
                '</div>',
                $cls,
                $index,
                esc_attr($full),
                $url,
                $index === 0 ? 'eager' : 'lazy'
            );
        } else {
            $slides_html .= sprintf(
                '<div class="wp-pw-slide%s" data-index="%d">' .
                    '<span class="wp-pw-slide-image" aria-hidden="true">' .
                        '<img src="%s" alt="" decoding="async" loading="%s">' .
                    '</span>' .
                '</div>',
                $cls,
                $index,
                $url,
                $index === 0 ? 'eager' : 'lazy'
            );
        }
    }

    $dots = '';
    foreach ($slides as $index => $slide) {
        $cls = $index === 0 ? ' is-active' : '';
        $dots .= sprintf('<button type="button" class="wp-pw-dot%s" data-index="%d" aria-label="%d"></button>',
            $cls, $index, $index + 1);
    }

    $prev = wp_photo_wall_text('previous') ?: 'Previous';
    $next = wp_photo_wall_text('next') ?: 'Next';

    return sprintf(
        '<div class="wp-photo-wall-slider" data-wp-pw-instance="%s" data-interval="%d">' .
            '<div class="wp-pw-track">%s</div>' .
            '<button type="button" class="wp-pw-nav wp-pw-prev" aria-label="%s">&#8249;</button>' .
            '<button type="button" class="wp-pw-nav wp-pw-next" aria-label="%s">&#8250;</button>' .
            '<div class="wp-pw-dots">%s</div>' .
        '</div>',
        esc_attr($instance_id),
        (int) $interval,
        $slides_html,
        esc_attr($prev),
        esc_attr($next),
        $dots
    );
}
