<?php
/**
 * Bing wallpaper settings block (admin).
 * Self-contained panel rendered as the "Bing Wallpaper" tab.
 */
if (!defined('ABSPATH')) exit;

$bing_enabled = wp_photo_wall_bing_enabled() ? 1 : 0;
$bing_api     = get_option(WP_PHOTO_WALL_BING_API_OPTION, WP_PHOTO_WALL_BING_DEFAULT_API);
$bing_title   = wp_photo_wall_bing_title();
$bing_order   = wp_photo_wall_bing_order();
$bing_items   = wp_photo_wall_bing_apply_order(wp_photo_wall_bing_fetch());

$bing_cron_enabled = wp_photo_wall_bing_cron_enabled();
$bing_cron_hour    = wp_photo_wall_bing_cron_hour();
$bing_cron_next    = wp_next_scheduled(WP_PHOTO_WALL_BING_CRON_HOOK);
$bing_cron_next_label = $bing_cron_next
    ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $bing_cron_next)
    : wp_photo_wall_text('bing_cron_not_scheduled');

$bing_download_url = wp_photo_wall_bing_download_url();
// Empty when untouched, so the placeholder (default label) shows instead.
$bing_download_text = (string) get_option(WP_PHOTO_WALL_BING_DOWNLOAD_TEXT_OPTION, '');
?>

<div class="card wp-photo-wall-admin-card wp-photo-wall-bing-card">
    <h2><?php echo esc_html(wp_photo_wall_text('bing_title')); ?></h2>
    <p class="description"><?php echo esc_html(sprintf(wp_photo_wall_text('bing_desc'), WP_PHOTO_WALL_BING_COUNT)); ?></p>

    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><?php echo esc_html(wp_photo_wall_text('bing_enable')); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="photo_wall_bing_enabled" value="1"
                            <?php checked(1, $bing_enabled); ?>>
                        <?php echo esc_html(wp_photo_wall_text('bing_enable_label')); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html(wp_photo_wall_text('bing_api')); ?></th>
                <td>
                    <input type="url" name="photo_wall_bing_api" id="photo_wall_bing_api" class="regular-text code"
                        value="<?php echo esc_attr($bing_api); ?>"
                        placeholder="<?php echo esc_attr(WP_PHOTO_WALL_BING_DEFAULT_API); ?>">
                    <p class="description"><?php echo esc_html(wp_photo_wall_text('bing_api_desc')); ?></p>

                    <details class="wp-pw-bing-hint">
                        <summary><?php echo esc_html(wp_photo_wall_text('bing_api_hint_toggle')); ?></summary>
                        <div class="wp-pw-bing-hint-body">
                            <p><?php echo esc_html(wp_photo_wall_text('bing_api_hint_intro')); ?></p>

                            <h4><?php echo esc_html(wp_photo_wall_text('bing_api_hint_container')); ?></h4>
                            <p><?php echo esc_html(wp_photo_wall_text('bing_api_hint_container_desc')); ?></p>

                            <h4><?php echo esc_html(wp_photo_wall_text('bing_api_hint_fields')); ?></h4>
                            <ul>
                                <li><code>url</code> — <?php echo esc_html(wp_photo_wall_text('bing_api_hint_field_url')); ?></li>
                                <li><code>urlbase</code> — <?php echo esc_html(wp_photo_wall_text('bing_api_hint_field_urlbase')); ?></li>
                                <li><code>startdate</code> — <?php echo esc_html(wp_photo_wall_text('bing_api_hint_field_date')); ?></li>
                                <li><code>title</code> — <?php echo esc_html(wp_photo_wall_text('bing_api_hint_field_title')); ?></li>
                                <li><code>copyright</code> — <?php echo esc_html(wp_photo_wall_text('bing_api_hint_field_copyright')); ?></li>
                            </ul>

                            <?php
                            $bing_notes = preg_split('/\r\n|\r|\n/', wp_photo_wall_text('bing_api_hint_notes'));
                            $bing_notes = array_values(array_filter(array_map('trim', $bing_notes), 'strlen'));
                            ?>
                            <h4><?php echo esc_html(sprintf(array_shift($bing_notes), WP_PHOTO_WALL_BING_COUNT)); ?></h4>
                            <ul>
                                <?php foreach ($bing_notes as $note) : ?>
                                    <li><?php echo esc_html(sprintf($note, WP_PHOTO_WALL_BING_COUNT)); ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <h4><?php echo esc_html(wp_photo_wall_text('bing_api_hint_example')); ?></h4>
                            <pre class="wp-pw-bing-hint-code">{
  "images": [
    {
      "startdate": "20260915",
      "url": "/th?id=OHR.Example_1920x1080.jpg&amp;rf=LaDigue_1920x1080.jpg&amp;pid=hp",
      "urlbase": "/th?id=OHR.Example",
      "copyright": "示例壁纸说明 (© Example)",
      "title": "示例标题"
    }
  ]
}</pre>
                            <p class="description">
                                <?php echo esc_html(wp_photo_wall_text('bing_api_default_hint')); ?>
                            </p>
                            <pre class="wp-pw-bing-hint-code"><?php echo esc_html(WP_PHOTO_WALL_BING_DEFAULT_API); ?></pre>
                        </div>
                    </details>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html(wp_photo_wall_text('bing_group_name')); ?></th>
                <td>
                    <input type="text" name="photo_wall_bing_title" id="photo_wall_bing_title" class="regular-text"
                        maxlength="80" value="<?php echo esc_attr($bing_title); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html(wp_photo_wall_text('bing_cron_enable')); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="photo_wall_bing_cron_enabled" value="1"
                            <?php checked(1, $bing_cron_enabled ? 1 : 0); ?>>
                        <?php echo esc_html(wp_photo_wall_text('bing_cron_enable_label')); ?>
                    </label>
                    <p class="description" style="margin-top:6px;">
                        <?php echo esc_html(wp_photo_wall_text('bing_cron_desc')); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html(wp_photo_wall_text('bing_cron_hour')); ?></th>
                <td>
                    <select name="photo_wall_bing_cron_hour" id="photo_wall_bing_cron_hour">
                        <?php for ($h = 0; $h <= 23; $h++) : ?>
                            <option value="<?php echo esc_attr($h); ?>" <?php selected($h, $bing_cron_hour); ?>>
                                <?php echo esc_html(sprintf('%02d:00', $h)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <p class="description">
                        <?php echo esc_html(sprintf(wp_photo_wall_text('bing_cron_next'), $bing_cron_next_label)); ?>
                        <br>
                        <?php echo esc_html(sprintf(wp_photo_wall_text('bing_cron_last'), wp_photo_wall_bing_cron_last_run())); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th colspan="2" scope="row" class="wp-photo-wall-section-head">
                    <h3><?php echo esc_html(wp_photo_wall_text('bing_download_section')); ?></h3>
                    <p class="description"><?php echo esc_html(wp_photo_wall_text('bing_download_section_desc')); ?></p>
                </th>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html(wp_photo_wall_text('bing_download_url')); ?></th>
                <td>
                    <input type="url" name="photo_wall_bing_download_url" id="photo_wall_bing_download_url"
                        class="regular-text code" value="<?php echo esc_attr($bing_download_url); ?>" placeholder="https://">
                    <p class="description"><?php echo esc_html(wp_photo_wall_text('bing_download_url_desc')); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html(wp_photo_wall_text('bing_download_text')); ?></th>
                <td>
                    <input type="text" name="photo_wall_bing_download_text" id="photo_wall_bing_download_text"
                        class="regular-text" maxlength="60" value="<?php echo esc_attr($bing_download_text); ?>"
                        placeholder="<?php echo esc_attr(wp_photo_wall_text('bing_download_default')); ?>">
                    <p class="description"><?php echo esc_html(wp_photo_wall_text('bing_download_text_desc')); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="wp-pw-bing-toolbar">
        <button type="button" class="button" id="wp-photo-wall-bing-refresh">
            <?php echo esc_html(wp_photo_wall_text('bing_refresh')); ?>
        </button>
        <button type="button" class="button" id="wp-photo-wall-bing-reset">
            <?php echo esc_html(wp_photo_wall_text('bing_reset_order')); ?>
        </button>
        <span class="wp-pw-bing-status" id="wp-photo-wall-bing-status" aria-live="polite">
            <?php echo esc_html(sprintf(wp_photo_wall_text('bing_last_update'), wp_photo_wall_bing_last_updated())); ?>
        </span>
    </div>

    <ul class="wp-pw-bing-list" id="wp-photo-wall-bing-list">
        <?php if (empty($bing_items)) : ?>
            <li class="wp-pw-bing-empty"><?php echo esc_html(wp_photo_wall_text('bing_empty')); ?></li>
        <?php else : ?>
            <?php foreach ($bing_items as $item) :
                $label = $item['date'] ? $item['date'] : '';
                if ($item['title']) {
                    $label = $label ? $label . ' · ' . $item['title'] : $item['title'];
                }
                ?>
                <li class="wp-pw-bing-item" data-key="<?php echo esc_attr($item['key']); ?>">
                    <span class="wp-pw-bing-handle" title="<?php echo esc_attr(wp_photo_wall_text('drag')); ?>">&#8942;&#8942;</span>
                    <img class="wp-pw-bing-thumb" src="<?php echo esc_url($item['thumb']); ?>" alt=""
                        data-full="<?php echo esc_attr($item['full']); ?>" decoding="async" loading="lazy">
                    <span class="wp-pw-bing-meta"><?php echo esc_html($label); ?></span>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <p class="description"><?php echo esc_html(wp_photo_wall_text('bing_order_hint')); ?></p>

    <input type="hidden" name="photo_wall_bing_order" id="photo_wall_bing_order"
        value="<?php echo esc_attr(wp_json_encode($bing_order)); ?>">

    <div class="wp-photo-wall-save-area">
        <input type="submit" name="submit" class="button button-primary wp-photo-wall-save-btn"
            value="<?php echo esc_attr(wp_photo_wall_text('save_changes')); ?>">
    </div>
</div>
