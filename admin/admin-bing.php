<?php
/**
 * Bing wallpaper settings block (admin).
 * Self-contained panel rendered as the "必应壁纸" tab.
 */
if (!defined('ABSPATH')) exit;

$bing_enabled       = wp_photo_wall_bing_enabled() ? 1 : 0;
$bing_api           = get_option(WP_PHOTO_WALL_BING_API_OPTION, WP_PHOTO_WALL_BING_DEFAULT_API);
$bing_title         = wp_photo_wall_bing_title();
$bing_order         = wp_photo_wall_bing_order();
$bing_items         = wp_photo_wall_bing_apply_order(wp_photo_wall_bing_fetch());

$bing_cron_enabled  = wp_photo_wall_bing_cron_enabled();
$bing_cron_hour     = wp_photo_wall_bing_cron_hour();
$bing_cron_next     = wp_next_scheduled(WP_PHOTO_WALL_BING_CRON_HOOK);
$bing_cron_next_label = $bing_cron_next
    ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $bing_cron_next)
    : wp_photo_wall_text('bing_cron_not_scheduled');

$bing_download_enabled = wp_photo_wall_bing_download_enabled() ? 1 : 0;
$bing_download_url     = wp_photo_wall_bing_download_url();
$bing_download_text    = (string) get_option(WP_PHOTO_WALL_BING_DOWNLOAD_TEXT_OPTION, '');
?>

<div class="wp-pw-bing-page">
    <div class="card wp-pw-card wp-pw-bing-main-card">
            <div class="wp-pw-card-header wp-pw-card-header-flex">
                <div class="wp-pw-header-titles">
                    <h2 class="wp-pw-card-title"><?php echo esc_html(wp_photo_wall_text('bing_title')); ?></h2>
                    <p class="wp-pw-card-desc"><?php echo esc_html(sprintf(wp_photo_wall_text('bing_desc'), WP_PHOTO_WALL_BING_COUNT)); ?></p>
                </div>
                <div class="wp-pw-header-actions">
                    <span class="wp-pw-status-pill <?php echo $bing_enabled ? 'is-active' : ''; ?>" id="wp-pw-bing-status-pill">
                        <?php echo $bing_enabled ? '已启用' : '已关闭'; ?>
                    </span>
                    <label class="wp-pw-switch">
                        <input type="checkbox" name="photo_wall_bing_enabled" id="photo_wall_bing_enabled" value="1"
                            <?php checked(1, $bing_enabled); ?>>
                        <span class="wp-pw-switch-track"></span>
                    </label>
                    <span class="wp-pw-switch-desc">在前端显示必应壁纸分组</span>
                </div>
            </div>

            <div class="wp-pw-form-body">
                <!-- API URL -->
                <div class="wp-pw-row wp-pw-row-aligned">
                    <div class="wp-pw-row-label">
                        <label for="photo_wall_bing_api">API 接口地址</label>
                        <span class="dashicons dashicons-editor-help wp-pw-tooltip-icon" title="可配置官方或自建的必应美图 API"></span>
                    </div>
                    <div class="wp-pw-row-content wp-pw-row-content-stacked">
                        <div class="wp-pw-input-action-group">
                            <input type="url" name="photo_wall_bing_api" id="photo_wall_bing_api" class="wp-pw-input-text code"
                                value="<?php echo esc_attr($bing_api); ?>"
                                placeholder="<?php echo esc_attr(WP_PHOTO_WALL_BING_DEFAULT_API); ?>">
                            <button type="button" class="button wp-pw-btn-outline" id="wp-pw-bing-test-api"><span>测试连接</span></button>
                            <button type="button" class="button wp-pw-btn-outline" id="wp-pw-bing-reset-api"><span>恢复默认</span></button>
                        </div>
                        <p class="wp-pw-field-tip">可填写任意兼容的 JSON 接口，留空则恢复默认地址。结果缓存 6 小时，点击「测试连接」检查是否可用。</p>

                        <details class="wp-pw-details">
                            <summary class="wp-pw-details-summary">&gt; 接口返回格式要求 (点击展开)</summary>
                            <div class="wp-pw-details-content">
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
                                <h4>默认接口</h4>
                                <pre class="wp-pw-code-snippet"><?php echo esc_html(WP_PHOTO_WALL_BING_DEFAULT_API); ?></pre>
                            </div>
                        </details>
                    </div>
                </div>

                <!-- Group Title -->
                <div class="wp-pw-row wp-pw-row-aligned">
                    <div class="wp-pw-row-label">
                        <label for="photo_wall_bing_title"><?php echo esc_html(wp_photo_wall_text('bing_group_name')); ?></label>
                        <span class="dashicons dashicons-editor-help wp-pw-tooltip-icon" title="自定义前台展示的分组标题"></span>
                    </div>
                    <div class="wp-pw-row-content wp-pw-row-content-stacked">
                        <input type="text" name="photo_wall_bing_title" id="photo_wall_bing_title" class="wp-pw-input-text"
                            maxlength="80" value="<?php echo esc_attr($bing_title); ?>" placeholder="<?php echo esc_attr(wp_photo_wall_text('bing_group_default')); ?>">
                        <p class="wp-pw-field-tip">将在前端页面显示，留空则使用默认标题「<?php echo esc_html(wp_photo_wall_text('bing_group_default')); ?>」。</p>
                    </div>
                </div>

                <!-- Auto update / WP-Cron -->
                <div class="wp-pw-row wp-pw-row-aligned">
                    <div class="wp-pw-row-label">
                        <span>自动更新</span>
                    </div>
                    <div class="wp-pw-row-content wp-pw-row-content-stacked">
                        <label class="wp-pw-checkbox-label">
                            <input type="checkbox" name="photo_wall_bing_cron_enabled" id="photo_wall_bing_cron_enabled" value="1"
                                <?php checked(1, $bing_cron_enabled ? 1 : 0); ?>>
                            <span>每天自动获取一次最新壁纸</span>
                        </label>
                        <p class="wp-pw-field-tip">基于 WordPress 定时任务 (WP-Cron)，需要有访问量时才会触发；若需精确执行，请为站点配置系统级 crontab。</p>
                    </div>
                </div>

                <!-- Execution Time -->
                <div class="wp-pw-row wp-pw-row-aligned">
                    <div class="wp-pw-row-label">
                        <label for="photo_wall_bing_cron_hour">执行时间</label>
                    </div>
                    <div class="wp-pw-row-content wp-pw-row-content-stacked">
                        <div class="wp-pw-time-picker">
                            <span class="dashicons dashicons-clock"></span>
                            <select name="photo_wall_bing_cron_hour" id="photo_wall_bing_cron_hour" class="wp-pw-select">
                                <?php for ($h = 0; $h <= 23; $h++) : ?>
                                    <option value="<?php echo esc_attr($h); ?>" <?php selected($h, $bing_cron_hour); ?>>
                                        <?php echo esc_html(sprintf('%02d:00', $h)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <p class="wp-pw-field-tip">
                            下次执行：<?php echo esc_html($bing_cron_next_label); ?> (基于站点时区)<br>
                            上次自动拉取：<?php echo esc_html(wp_photo_wall_bing_cron_last_run()); ?>
                        </p>
                    </div>
                </div>

                <!-- Download Button Section Card -->
                <div class="wp-pw-subcard">
                    <div class="wp-pw-subcard-header">
                        <div class="wp-pw-subcard-title-box">
                            <h3 class="wp-pw-subcard-title">下载按钮</h3>
                            <p class="wp-pw-subcard-desc">仅适用于必应壁纸，开启后将在图片下方显示下载按钮；关闭后将隐藏。</p>
                        </div>
                        <div class="wp-pw-subcard-toggle">
                            <label class="wp-pw-switch">
                                <input type="checkbox" name="photo_wall_bing_download_enabled" id="photo_wall_bing_download_enabled" value="1"
                                    <?php checked(1, $bing_download_enabled); ?>>
                                <span class="wp-pw-switch-track"></span>
                            </label>
                            <span class="wp-pw-switch-text">显示下载按钮</span>
                        </div>
                    </div>

                    <div class="wp-pw-subcard-body" id="wp-pw-bing-download-fields">
                        <div class="wp-pw-row wp-pw-row-aligned">
                            <div class="wp-pw-row-label">
                                <label for="photo_wall_bing_download_url">下载按钮链接</label>
                            </div>
                            <div class="wp-pw-row-content wp-pw-row-content-stacked">
                                <div class="wp-pw-input-action-group">
                                    <input type="url" name="photo_wall_bing_download_url" id="photo_wall_bing_download_url"
                                        class="wp-pw-input-text code" value="<?php echo esc_attr($bing_download_url); ?>" placeholder="https://">
                                    <button type="button" class="button wp-pw-btn-outline" id="wp-pw-bing-test-download"><span>测试连接</span></button>
                                </div>
                                <p class="wp-pw-field-tip">填写后，前端会在必应壁纸下方显示下载按钮；留空则不显示。</p>
                            </div>
                        </div>

                        <div class="wp-pw-row wp-pw-row-aligned">
                            <div class="wp-pw-row-label">
                                <label for="photo_wall_bing_download_text">下载按钮名称</label>
                            </div>
                            <div class="wp-pw-row-content wp-pw-row-content-stacked">
                                <input type="text" name="photo_wall_bing_download_text" id="photo_wall_bing_download_text"
                                    class="wp-pw-input-text" maxlength="60" value="<?php echo esc_attr($bing_download_text); ?>"
                                    placeholder="<?php echo esc_attr(wp_photo_wall_text('bing_download_default')); ?>">
                                <p class="wp-pw-field-tip">留空则使用默认名称「<?php echo esc_html(wp_photo_wall_text('bing_download_default')); ?>」。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons Toolbar -->
                <div class="wp-pw-bing-actions-bar">
                    <div class="wp-pw-actions-left">
                        <input type="submit" name="submit" class="button wp-pw-btn-primary wp-photo-wall-save-btn"
                            value="<?php echo esc_attr(wp_photo_wall_text('save_changes')); ?>">
                        <button type="button" class="button wp-pw-btn-outline" id="wp-photo-wall-bing-refresh"><span>立即执行一次</span></button>
                        <button type="button" class="button wp-pw-btn-outline" id="wp-photo-wall-bing-reset"><span>恢复时间排序</span></button>
                        <button type="button" class="button wp-pw-btn-outline" id="wp-pw-bing-reset-all"><span>重置为默认设置</span></button>
                    </div>
                    <div class="wp-pw-actions-right">
                        <span class="wp-pw-last-updated" id="wp-photo-wall-bing-status">
                            最后更新：<?php echo esc_html(wp_photo_wall_bing_last_updated()); ?>
                        </span>
                    </div>
                </div>

                <!-- Preview Area -->
                <div class="wp-pw-bing-preview-box">
                    <h3 class="wp-pw-preview-title">效果预览 (按时间倒序，拖拽可调整顺序)</h3>
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
                                    <div class="wp-pw-bing-thumb-box">
                                        <img class="wp-pw-bing-thumb" src="<?php echo esc_url($item['thumb']); ?>" alt=""
                                            data-full="<?php echo esc_attr($item['full']); ?>" decoding="async" loading="lazy">
                                    </div>
                                    <span class="wp-pw-bing-meta" title="<?php echo esc_attr($label); ?>"><?php echo esc_html($label); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <input type="hidden" name="photo_wall_bing_order" id="photo_wall_bing_order"
                    value="<?php echo esc_attr(wp_json_encode($bing_order)); ?>">
            </div>
        </div>
</div>
