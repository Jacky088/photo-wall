<?php
/**
 * Top banner carousel settings block (admin).
 * Self-contained: rendered as the "首页海报" tab.
 */
if (!defined('ABSPATH')) exit;

$slides_enabled  = (int) get_option('photo_wall_slides_enabled', 0);
$slides_interval = (int) get_option('photo_wall_slides_interval', 5);
$slides_link     = (int) get_option('photo_wall_slides_link', 1);
$saved_slides    = get_option('photo_wall_slides', array());
if (!is_array($saved_slides)) {
    $saved_slides = array();
}
$slide_count = count($saved_slides);
?>

<div class="wp-pw-two-col-layout wp-pw-slides-page">
    <!-- Left Column: Main Configuration -->
    <div class="wp-pw-main-col">
        <div class="card wp-pw-card wp-pw-slides-main-card">
            <div class="wp-pw-card-header">
                <div>
                    <h2 class="wp-pw-card-title"><?php echo esc_html(wp_photo_wall_text('slides_title')); ?></h2>
                    <p class="wp-pw-card-desc"><?php echo esc_html(sprintf(wp_photo_wall_text('slides_desc'), WP_PHOTO_WALL_SLIDES_MAX)); ?></p>
                </div>
            </div>

            <div class="wp-pw-form-body">
                <!-- 1. Enable Banner Carousel -->
                <div class="wp-pw-row">
                    <div class="wp-pw-row-label">
                        <span class="wp-pw-label-text"><?php echo esc_html(wp_photo_wall_text('slides_enable')); ?></span>
                    </div>
                    <div class="wp-pw-row-content">
                        <label class="wp-pw-switch">
                            <input type="checkbox" name="photo_wall_slides_enabled" id="photo_wall_slides_enabled" value="1"
                                <?php checked(1, $slides_enabled); ?>>
                            <span class="wp-pw-switch-track"></span>
                        </label>
                        <span class="wp-pw-switch-text"><?php echo esc_html(wp_photo_wall_text('slides_enable_label')); ?></span>
                    </div>
                </div>

                <!-- 2. Carousel Interval -->
                <div class="wp-pw-row">
                    <div class="wp-pw-row-label">
                        <span class="wp-pw-label-text"><?php echo esc_html(wp_photo_wall_text('slides_interval')); ?></span>
                    </div>
                    <div class="wp-pw-row-content">
                        <div class="wp-pw-stepper">
                            <input type="number" name="photo_wall_slides_interval" id="photo_wall_slides_interval" min="2" max="30" step="1"
                                value="<?php echo esc_attr($slides_interval); ?>">
                            <span class="wp-pw-stepper-unit"><?php echo esc_html(wp_photo_wall_text('seconds')); ?></span>
                        </div>
                        <span class="wp-pw-row-tip">建议设置 3–10 秒，过短会影响浏览体验</span>
                    </div>
                </div>

                <!-- 3. Lightbox on Click -->
                <div class="wp-pw-row">
                    <div class="wp-pw-row-label">
                        <span class="wp-pw-label-text"><?php echo esc_html(wp_photo_wall_text('slides_link')); ?></span>
                    </div>
                    <div class="wp-pw-row-content">
                        <label class="wp-pw-switch">
                            <input type="checkbox" name="photo_wall_slides_link" id="photo_wall_slides_link" value="1"
                                <?php checked(1, $slides_link); ?>>
                            <span class="wp-pw-switch-track"></span>
                        </label>
                        <span class="wp-pw-switch-text"><?php echo esc_html(wp_photo_wall_text('slides_link_label')); ?></span>
                    </div>
                </div>

                <!-- 4. Image List -->
                <div class="wp-pw-row wp-pw-row-slides-list">
                    <div class="wp-pw-row-label">
                        <span class="wp-pw-label-text">
                            图片列表 <span class="wp-pw-count-pill" id="wp-pw-slides-count-label">(<?php echo esc_html($slide_count); ?>/<?php echo WP_PHOTO_WALL_SLIDES_MAX; ?>)</span>
                        </span>
                    </div>
                    <div class="wp-pw-row-content wp-pw-row-content-stacked">
                        <div class="wp-pw-slides-toolbar">
                            <button type="button" class="button wp-pw-btn-primary wp-pw-add-local">
                                <span class="dashicons dashicons-admin-media"></span>
                                <span><?php echo esc_html(wp_photo_wall_text('slides_add_local')); ?></span>
                            </button>
                            <button type="button" class="button wp-pw-btn-outline wp-pw-add-external">
                                <span class="dashicons dashicons-admin-links"></span>
                                <span><?php echo esc_html(wp_photo_wall_text('slides_add_external')); ?></span>
                            </button>
                        </div>

                        <div class="wp-pw-slides-grid-container">
                            <ul class="wp-pw-slides-list" id="wp-pw-slides-list">
                                <?php
                                foreach ($saved_slides as $s) {
                                    $type = isset($s['type']) ? $s['type'] : 'external';
                                    $id   = isset($s['id']) ? (int) $s['id'] : 0;
                                    $url  = isset($s['url']) ? $s['url'] : '';
                                    $full = isset($s['full']) ? $s['full'] : $url;
                                    $thumb = $type === 'local' ? wp_get_attachment_image_url($id, 'medium_large') : $url;
                                    if (!$thumb && $type === 'local') {
                                        $thumb = wp_get_attachment_image_url($id, 'thumbnail');
                                    }
                                    if (!$thumb) {
                                        $thumb = $full;
                                    }
                                    ?>
                                    <li class="wp-pw-slide-item" data-type="<?php echo esc_attr($type); ?>"
                                        data-id="<?php echo esc_attr($id); ?>"
                                        data-url="<?php echo esc_attr($url); ?>"
                                        data-full="<?php echo esc_attr($full); ?>">
                                        <span class="wp-pw-slide-handle" title="<?php echo esc_attr(wp_photo_wall_text('drag')); ?>">&#8942;&#8942;</span>
                                        <button type="button" class="wp-pw-slide-remove" aria-label="<?php echo esc_attr(wp_photo_wall_text('remove')); ?>">&times;</button>
                                        <div class="wp-pw-slide-img-box">
                                            <img class="wp-pw-slide-thumb" src="<?php echo esc_url($thumb); ?>" alt="">
                                        </div>
                                        <span class="wp-pw-slide-badge"><?php echo esc_html($type === 'local' ? '来自媒体库' : '来自链接'); ?></span>
                                    </li>
                                    <?php
                                }
                                ?>
                                <li class="wp-pw-slide-placeholder" id="wp-pw-slide-placeholder" style="<?php echo $slide_count >= WP_PHOTO_WALL_SLIDES_MAX ? 'display:none;' : ''; ?>">
                                    <div class="wp-pw-placeholder-body">
                                        <div class="wp-pw-placeholder-icon">+</div>
                                        <div class="wp-pw-placeholder-text">最多可添加 <?php echo WP_PHOTO_WALL_SLIDES_MAX; ?> 张</div>
                                        <div class="wp-pw-placeholder-sub">支持 JPG/PNG/WebP</div>
                                        <div class="wp-pw-placeholder-sub">建议尺寸 1920 &times; 1080</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Drag Hint -->
                <div class="wp-pw-row wp-pw-row-plain">
                    <div class="wp-pw-row-content">
                        <span class="dashicons dashicons-info"></span>
                        <span class="wp-pw-muted-tip"><?php echo esc_html(wp_photo_wall_text('slides_order_hint')); ?></span>
                    </div>
                </div>
            </div>

            <!-- Bottom Actions in Card -->
            <div class="wp-pw-card-footer">
                <div class="wp-pw-footer-left">
                    <input type="submit" name="submit" class="button wp-pw-btn-primary wp-photo-wall-save-btn" value="<?php echo esc_attr(wp_photo_wall_text('save_changes')); ?>">
                    <button type="button" class="button wp-pw-btn-cancel" onclick="location.reload();"><span><?php echo esc_html(wp_photo_wall_text('cancel')); ?></span></button>
                </div>
                <div class="wp-pw-footer-right">
                    <span class="wp-pw-draft-status"><span class="dashicons dashicons-yes-alt"></span><span>已自动保存草稿 <?php echo esc_html(date_i18n('H:i')); ?></span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Live Preview & Usage Advice -->
    <div class="wp-pw-side-col">
        <!-- 效果预览 Card -->
        <div class="card wp-pw-card wp-pw-side-card wp-pw-preview-box">
            <h3 class="wp-pw-side-card-title">效果预览</h3>
            <div class="wp-pw-live-slider" id="wp-pw-live-slider">
                <div class="wp-pw-live-screen">
                    <div class="wp-pw-live-slide-track" id="wp-pw-live-slide-track">
                        <!-- Populated by JS -->
                    </div>
                    <button type="button" class="wp-pw-live-nav wp-pw-live-prev" id="wp-pw-live-prev" aria-label="上一张">&lsaquo;</button>
                    <button type="button" class="wp-pw-live-nav wp-pw-live-next" id="wp-pw-live-next" aria-label="下一张">&rsaquo;</button>
                    <div class="wp-pw-live-dots" id="wp-pw-live-dots"></div>
                </div>
                <p class="wp-pw-side-tip">预览图仅供参考，实际效果以站点为准</p>
            </div>
        </div>

        <!-- 使用建议 Card -->
        <div class="card wp-pw-card wp-pw-side-card wp-pw-advice-card">
            <h3 class="wp-pw-advice-title"><span class="dashicons dashicons-lightbulb"></span><span>使用建议</span></h3>
            <ul class="wp-pw-advice-list">
                <li>建议使用横向大图，比例 16:9，尺寸不小于 1920&times;1080</li>
                <li>图片数量控制在 1–6 张，避免过多影响加载速度</li>
                <li>可以使用高质量的 Banner 图提升页面视觉效果</li>
            </ul>
        </div>
    </div>
</div>
