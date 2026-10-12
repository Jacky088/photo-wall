<?php
if (!defined('ABSPATH')) exit;
$initial_data   = wp_photo_wall_get_items_with_thumbnails();
$initial_groups = wp_photo_wall_get_groups();

// Remember the active tab across form submissions (page reloads).
$active_tab = isset($_POST['wp_photo_wall_active_tab']) ? sanitize_key(wp_unslash($_POST['wp_photo_wall_active_tab'])) : 'banner';
if (!in_array($active_tab, array('banner', 'photos', 'bing'), true)) {
    $active_tab = 'banner';
}
?>
<div class="wrap wp-pw-wrap">
    <!-- Top Header -->
    <header class="wp-pw-header">
        <div class="wp-pw-header-left">
            <h1 class="wp-pw-main-title">
                <span class="dashicons dashicons-images-alt2 wp-pw-header-icon"></span>
                <span><?php echo esc_html(wp_photo_wall_text('photo_wall_settings')); ?></span>
            </h1>
            <span class="wp-pw-version-tag">v<?php echo WP_PHOTO_WALL_VERSION; ?></span>
        </div>
        <div class="wp-pw-header-right">
            <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="button wp-pw-btn-outline wp-pw-header-preview-btn">
                <span class="dashicons dashicons-visibility"></span>
                <span>预览效果</span>
            </a>
            <button type="button" class="button wp-pw-btn-subtle" id="wp-pw-help-btn">
                <span class="dashicons dashicons-editor-help"></span>
                <span>使用帮助</span>
            </button>
        </div>
    </header>

    <form method="post" id="wp-photo-wall-form">
        <?php wp_nonce_field('wp_photo_wall_save', 'wp_photo_wall_nonce'); ?>
        <input type="hidden" name="photo_wall_data" id="photo_wall_data" value="<?php echo esc_attr(wp_json_encode($initial_data)); ?>">
        <input type="hidden" name="photo_wall_groups" id="photo_wall_groups" value="<?php echo esc_attr(wp_json_encode($initial_groups)); ?>">
        <input type="hidden" name="photo_wall_slides" id="photo_wall_slides" value="<?php echo esc_attr(wp_json_encode(wp_photo_wall_get_slides())); ?>">
        <input type="hidden" name="wp_photo_wall_active_tab" id="wp_photo_wall_active_tab" value="<?php echo esc_attr($active_tab); ?>">
        <input type="hidden" name="photo_wall_pending_deletions" id="photo_wall_pending_deletions" value="[]">

        <!-- Tabs Navigation -->
        <nav class="wp-pw-tabs-nav">
            <a href="#banner" class="wp-pw-tab-item <?php echo $active_tab === 'banner' ? 'is-active' : ''; ?>" data-tab="banner">
                <?php echo esc_html(wp_photo_wall_text('tab_banner')); ?>
            </a>
            <a href="#photos" class="wp-pw-tab-item <?php echo $active_tab === 'photos' ? 'is-active' : ''; ?>" data-tab="photos">
                <?php echo esc_html(wp_photo_wall_text('tab_photos')); ?>
            </a>
            <a href="#bing" class="wp-pw-tab-item <?php echo $active_tab === 'bing' ? 'is-active' : ''; ?>" data-tab="bing">
                <?php echo esc_html(wp_photo_wall_text('tab_bing')); ?>
            </a>
        </nav>

        <!-- Tab 1: 首页海报 -->
        <div class="wp-pw-tab-content" data-tab="banner" <?php echo $active_tab === 'banner' ? '' : 'hidden'; ?>>
            <?php include WP_PHOTO_WALL_PATH . 'admin/admin-slides.php'; ?>
        </div>

        <!-- Tab 2: 图片管理 -->
        <div class="wp-pw-tab-content" data-tab="photos" <?php echo $active_tab === 'photos' ? '' : 'hidden'; ?>>
            <div class="wp-pw-photos-page">
                <div class="card wp-pw-card wp-pw-manage-card">
                        <!-- Card Header -->
                        <div class="wp-pw-card-header">
                            <div>
                                <h2 class="wp-pw-card-title"><?php echo esc_html(wp_photo_wall_text('manage_photos')); ?></h2>
                                <p class="wp-pw-card-desc"><?php echo esc_html(wp_photo_wall_text('manage_photos_desc')); ?> <?php echo esc_html(wp_photo_wall_text('save_reminder')); ?></p>
                            </div>
                        </div>

                        <!-- Top Toolbar 1: Add Group & Search -->
                        <div class="wp-pw-top-toolbar">
                            <div class="wp-pw-add-group-box">
                                <input type="text" id="wp-photo-wall-new-group-name" maxlength="80" placeholder="请输入分组名称" class="wp-pw-input-text">
                                <button type="button" class="button wp-pw-btn-primary" id="wp-photo-wall-add-group-btn">
                                    <span class="dashicons dashicons-plus-alt2"></span>
                                    <span>添加分组</span>
                                </button>
                            </div>
                            <div class="wp-pw-search-box">
                                <span class="dashicons dashicons-search wp-pw-search-icon"></span>
                                <input type="search" id="wp-photo-wall-search-input" placeholder="搜索图片（文件名/分组）..." class="wp-pw-input-search">
                            </div>
                        </div>

                        <!-- Top Toolbar 2: Action Buttons -->
                        <div class="wp-pw-actions-toolbar">
                            <button type="button" class="button wp-pw-btn-primary" id="wp-photo-wall-add-btn">
                                <span class="dashicons dashicons-upload"></span>
                                <span><?php echo esc_html(wp_photo_wall_text('select_upload_photos')); ?></span>
                            </button>
                            <button type="button" class="button wp-pw-btn-outline" id="wp-photo-wall-add-external-btn">
                                <span class="dashicons dashicons-admin-links"></span>
                                <span><?php echo esc_html(wp_photo_wall_text('add_external_image')); ?></span>
                            </button>
                            <button type="button" class="button wp-pw-btn-outline" id="wp-photo-wall-move-btn" disabled>
                                <span class="dashicons dashicons-move"></span>
                                <span><?php echo esc_html(wp_photo_wall_text('move_selected')); ?></span>
                            </button>
                            <button type="button" class="button wp-pw-btn-outline wp-pw-btn-danger-outline" id="wp-photo-wall-bulk-remove-btn" disabled>
                                <span class="dashicons dashicons-trash"></span>
                                <span><?php echo esc_html(wp_photo_wall_text('bulk_delete_selected')); ?></span>
                            </button>
                            <button type="button" class="button wp-pw-btn-outline wp-pw-btn-danger-outline" id="wp-photo-wall-delete-media-btn" disabled>
                                <span class="dashicons dashicons-database-remove"></span>
                                <span><?php echo esc_html(wp_photo_wall_text('delete_from_media')); ?></span>
                            </button>
                        </div>

                        <!-- Master-Detail Gallery Layout -->
                        <div class="wp-pw-master-detail">
                            <!-- Left Sidebar: Group Management -->
                            <aside class="wp-pw-groups-sidebar">
                                <div class="wp-pw-sidebar-head">
                                    <span class="wp-pw-sidebar-title">分组</span>
                                    <span class="wp-pw-sidebar-badge" id="wp-pw-groups-count-badge">(0)</span>
                                </div>
                                <ul class="wp-pw-groups-list" id="wp-photo-wall-groups-list">
                                    <!-- Rendered dynamically by JS -->
                                </ul>
                            </aside>

                            <!-- Right Main: Photo Gallery Area -->
                            <main class="wp-pw-gallery-main">
                                <div class="wp-pw-gallery-subtoolbar">
                                    <div class="wp-pw-gallery-info">
                                        <h3 class="wp-pw-active-group-name" id="wp-pw-active-group-title">未分组</h3>
                                        <span class="wp-pw-group-count" id="wp-pw-active-group-count">(0 张图片)</span>
                                    </div>
                                    <div class="wp-pw-gallery-tools">
                                        <div class="wp-pw-view-toggle">
                                            <button type="button" class="wp-pw-view-btn is-active" id="wp-pw-view-grid" title="网格视图">
                                                <span class="dashicons dashicons-grid-view"></span>
                                            </button>
                                            <button type="button" class="wp-pw-view-btn" id="wp-pw-view-list" title="列表视图">
                                                <span class="dashicons dashicons-list-view"></span>
                                            </button>
                                        </div>
                                        <div class="wp-pw-sort-dropdown">
                                            <select id="wp-pw-sort-select" class="wp-pw-select">
                                                <option value="default">按上传时间 (默认)</option>
                                                <option value="time-asc">按上传时间 (由旧到新)</option>
                                                <option value="name">按文件名 (A-Z)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Photo Container & Empty State -->
                                <div class="wp-pw-gallery-content" id="wp-photo-wall-groups-container">
                                    <p id="wp-photo-wall-loading"><?php echo esc_html(wp_photo_wall_text('loading')); ?></p>
                                </div>
                            </main>
                        </div>
                    </div>

                    <!-- Settings Card -->
                    <div class="card wp-pw-card wp-pw-settings-card">
                        <div class="wp-pw-card-header">
                            <h2 class="wp-pw-card-title"><?php echo esc_html(wp_photo_wall_text('settings')); ?></h2>
                        </div>
                        <div class="wp-pw-settings-body">
                            <!-- Lightbox Option -->
                            <div class="wp-pw-setting-item">
                                <label class="wp-pw-checkbox-label">
                                    <input type="checkbox" name="wp_photo_wall_enable_lightbox" value="1"
                                        <?php checked(get_option('wp_photo_wall_enable_lightbox', '1'), '1'); ?>>
                                    <strong><?php echo esc_html(wp_photo_wall_text('enable_lightbox')); ?></strong>
                                </label>
                                <p class="wp-pw-setting-desc">开启后，点击图片将在页面内灯箱预览（推荐开启）。</p>
                            </div>

                            <!-- Download Link Option -->
                            <div class="wp-pw-setting-item">
                                <label for="wp_photo_wall_download_link" class="wp-pw-setting-label">
                                    <strong><?php echo esc_html(wp_photo_wall_text('download_button_link')); ?></strong>
                                </label>
                                <div class="wp-pw-input-with-hint">
                                    <input class="wp-pw-input-text code" type="url" name="wp_photo_wall_download_link" id="wp_photo_wall_download_link"
                                        value="<?php echo esc_attr(get_option('wp_photo_wall_download_link', '')); ?>" placeholder="https://">
                                    <span class="wp-pw-setting-desc">用户点击图片下的下载按钮时跳转的链接，留空则不显示。示例：https://example.com/download</span>
                                </div>
                            </div>

                            <!-- Download Text Option -->
                            <div class="wp-pw-setting-item">
                                <label for="wp_photo_wall_download_text" class="wp-pw-setting-label">
                                    <strong>下载按钮文字</strong>
                                </label>
                                <div class="wp-pw-input-with-hint">
                                    <input class="wp-pw-input-text" type="text" name="wp_photo_wall_download_text" id="wp_photo_wall_download_text" maxlength="60"
                                        value="<?php echo esc_attr(get_option('wp_photo_wall_download_text', '')); ?>" placeholder="<?php echo esc_attr(wp_photo_wall_text('download_all')); ?>">
                                    <span class="wp-pw-setting-desc">自定义下载按钮的显示文字，留空则默认显示「<?php echo esc_html(wp_photo_wall_text('download_all')); ?>」。</span>
                                </div>
                            </div>

                            <!-- Orphan Cleanup Info Banner -->
                            <div class="wp-pw-info-banner">
                                <span class="dashicons dashicons-info wp-pw-info-banner-icon"></span>
                                <div class="wp-pw-info-banner-text">
                                    <strong>孤儿图片自动清理</strong>
                                    <p>从图片墙或顶部轮播移除媒体库图片并点击「保存更改」后，若该图片已不被插件任何位置引用，将自动从媒体库永久删除，避免产生孤儿图片。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Tab 3: 必应壁纸 -->
        <div class="wp-pw-tab-content" data-tab="bing" <?php echo $active_tab === 'bing' ? '' : 'hidden'; ?>>
            <?php include WP_PHOTO_WALL_PATH . 'admin/admin-bing.php'; ?>
        </div>

        <!-- Sticky Bottom Bar -->
        <div class="wp-pw-sticky-bar" id="wp-photo-wall-unsaved">
            <div class="wp-pw-sticky-inner">
                <div class="wp-pw-sticky-left">
                    <span class="dashicons dashicons-warning wp-pw-sticky-warn-icon"></span>
                    <div class="wp-pw-sticky-notice-text">
                        <strong>有未保存的更改</strong>
                        <span>请点击「保存更改」使设置生效。</span>
                    </div>
                </div>
                <div class="wp-pw-sticky-right">
                    <button type="button" class="button wp-pw-btn-outline wp-pw-btn-danger-outline" id="wp-photo-wall-clear-btn">
                        <span><?php echo esc_html(wp_photo_wall_text('clear_all')); ?></span>
                    </button>
                    <input type="submit" name="submit" class="button wp-pw-btn-primary wp-photo-wall-save-btn" value="<?php echo esc_attr(wp_photo_wall_text('save_changes')); ?>">
                </div>
            </div>
        </div>
    </form>

    <!-- Move to Group Modal -->
    <div id="wp-photo-wall-move-modal" class="wp-photo-wall-modal" hidden>
        <div class="wp-photo-wall-modal-dialog" role="dialog" aria-modal="true">
            <div class="wp-pw-modal-header">
                <h2><?php echo esc_html(wp_photo_wall_text('move_to_group')); ?></h2>
                <button type="button" class="wp-pw-modal-close" id="wp-pw-move-close">&times;</button>
            </div>
            <div class="wp-pw-modal-body">
                <p>请选择要将所选图片移动到的目标分组：</p>
                <select id="wp-photo-wall-move-target" class="wp-pw-select wp-pw-full-width">
                    <option value=""><?php echo esc_html(wp_photo_wall_text('select_target_group')); ?></option>
                </select>
            </div>
            <div class="wp-pw-modal-footer">
                <button type="button" class="button wp-pw-btn-cancel" id="wp-photo-wall-move-cancel"><span><?php echo esc_html(wp_photo_wall_text('cancel')); ?></span></button>
                <button type="button" class="button wp-pw-btn-primary" id="wp-photo-wall-move-confirm" disabled><span><?php echo esc_html(wp_photo_wall_text('move_selected')); ?></span></button>
            </div>
        </div>
    </div>

    <!-- External Image Modal -->
    <div id="wp-photo-wall-external-modal" class="wp-photo-wall-modal" hidden>
        <div class="wp-photo-wall-modal-dialog" role="dialog" aria-modal="true">
            <div class="wp-pw-modal-header">
                <h2 id="wp-photo-wall-modal-title"><?php echo esc_html(wp_photo_wall_text('add_external_image_title')); ?></h2>
                <button type="button" class="wp-pw-modal-close" id="wp-pw-external-close">&times;</button>
            </div>
            <div class="wp-pw-modal-body">
                <p>
                    <label for="wp-photo-wall-external-url"><strong><?php echo esc_html(wp_photo_wall_text('image_url')); ?></strong></label>
                    <input type="url" id="wp-photo-wall-external-url" class="wp-pw-input-text wp-pw-full-width code" placeholder="https://example.com/image.jpg">
                </p>
                <div class="wp-pw-preview-box-modal">
                    <strong><?php echo esc_html(wp_photo_wall_text('preview_area')); ?></strong>
                    <div id="wp-photo-wall-external-preview" class="wp-photo-wall-preview-container">
                        <span class="wp-pw-preview-placeholder">输入有效图片链接后预览将在此显示</span>
                    </div>
                </div>
            </div>
            <div class="wp-pw-modal-footer">
                <button type="button" class="button wp-pw-btn-cancel" id="wp-photo-wall-external-cancel"><span><?php echo esc_html(wp_photo_wall_text('cancel')); ?></span></button>
                <button type="button" class="button wp-pw-btn-primary" id="wp-photo-wall-external-confirm" disabled><span><?php echo esc_html(wp_photo_wall_text('add_to_wall')); ?></span></button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="wp-photo-wall-confirm-modal" class="wp-photo-wall-modal" hidden>
        <div class="wp-photo-wall-modal-dialog wp-pw-confirm-dialog" role="dialog" aria-modal="true">
            <div class="wp-pw-modal-header">
                <h2 class="wp-photo-wall-confirm-title">确认操作</h2>
                <button type="button" class="wp-pw-modal-close wp-photo-wall-confirm-cancel">&times;</button>
            </div>
            <div class="wp-pw-modal-body">
                <p class="wp-photo-wall-confirm-message"></p>
                <ul class="wp-photo-wall-confirm-details" hidden></ul>
                <div class="wp-photo-wall-confirm-typed" hidden>
                    <label for="wp-photo-wall-confirm-word"></label>
                    <input type="text" id="wp-photo-wall-confirm-word" class="wp-pw-input-text wp-pw-full-width" autocomplete="off">
                </div>
            </div>
            <div class="wp-pw-modal-footer">
                <button type="button" class="button wp-pw-btn-cancel wp-photo-wall-confirm-cancel"><span><?php echo esc_html(wp_photo_wall_text('cancel')); ?></span></button>
                <button type="button" class="button wp-pw-btn-primary wp-photo-wall-confirm-ok"><span>确定</span></button>
            </div>
        </div>
    </div>

    <!-- Help Modal -->
    <div id="wp-pw-help-modal" class="wp-photo-wall-modal" hidden>
        <div class="wp-photo-wall-modal-dialog" role="dialog" aria-modal="true">
            <div class="wp-pw-modal-header">
                <h2><?php echo esc_html(wp_photo_wall_text('how_to_use')); ?></h2>
                <button type="button" class="wp-pw-modal-close" id="wp-pw-help-close">&times;</button>
            </div>
            <div class="wp-pw-modal-body wp-pw-help-body">
                <h4>1. 在页面或文章中插入图片墙</h4>
                <p>在任意文章或页面中使用简码：</p>
                <pre class="wp-pw-code-snippet">[photo_wall]</pre>
                <h4>2. 按分组展示</h4>
                <p>如果只想展示特定分组的图片，可以使用：</p>
                <pre class="wp-pw-code-snippet">[photo_wall group="分组名称"]</pre>
                <h4>3. 顶部轮播与必应壁纸</h4>
                <p>在后台开启对应选项后，前台将自动在图片墙上方呈现轮播海报及必应壁纸分组，无需额外设置。</p>
            </div>
            <div class="wp-pw-modal-footer">
                <button type="button" class="button wp-pw-btn-primary" id="wp-pw-help-ok"><span>我知道了</span></button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="wp-pw-toast-container" class="wp-pw-toast-container" aria-live="polite"></div>
</div>
