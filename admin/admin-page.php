<?php
if (!defined('ABSPATH')) exit;
$initial_data = wp_photo_wall_get_items_with_thumbnails();
$initial_groups = wp_photo_wall_get_groups();
?>
<div class="wrap">
    <h1><?php echo esc_html(wp_photo_wall_text('photo_wall_settings')); ?></h1>
    <!-- Unsaved changes notice - prominent sticky bar -->
    <div class="wp-photo-wall-unsaved-bar" id="wp-photo-wall-unsaved">
        <span class="dashicons dashicons-warning"></span>
        <strong><?php echo esc_html(wp_photo_wall_text('unsaved_changes')); ?></strong>
    </div>
    <form method="post" id="wp-photo-wall-form">
        <?php wp_nonce_field('wp_photo_wall_save', 'wp_photo_wall_nonce'); ?>
        <input type="hidden" name="photo_wall_data" id="photo_wall_data" value="<?php echo esc_attr(wp_json_encode($initial_data)); ?>">
        <input type="hidden" name="photo_wall_groups" id="photo_wall_groups" value="<?php echo esc_attr(wp_json_encode($initial_groups)); ?>">
        <input type="hidden" name="photo_wall_slides" id="photo_wall_slides" value="<?php echo esc_attr(wp_json_encode(wp_photo_wall_get_slides())); ?>">

        <?php include WP_PHOTO_WALL_PATH . 'admin/admin-slides.php'; ?>

        <div class="card wp-photo-wall-admin-card">
            <h2><?php echo esc_html(wp_photo_wall_text('manage_photos')); ?></h2>
            <p class="description"><?php echo esc_html(wp_photo_wall_text('manage_photos_desc')); ?> <?php echo esc_html(wp_photo_wall_text('save_reminder')); ?></p>

            <!-- Inline group management -->
            <div class="wp-photo-wall-inline-group-mgmt">
                <input type="text" id="wp-photo-wall-new-group-name" maxlength="80" placeholder="<?php echo esc_attr(wp_photo_wall_text('new_group_name')); ?>">
                <button type="button" class="button" id="wp-photo-wall-add-group-btn"><?php echo esc_html(wp_photo_wall_text('add_group')); ?></button>
            </div>

            <div class="wp-photo-wall-actions">
                <button type="button" class="button button-primary" id="wp-photo-wall-add-btn"><?php echo esc_html(wp_photo_wall_text('select_upload_photos')); ?></button>
                <button type="button" class="button" id="wp-photo-wall-add-external-btn"><?php echo esc_html(wp_photo_wall_text('add_external_image')); ?></button>
                <button type="button" class="button" id="wp-photo-wall-move-btn" disabled><?php echo esc_html(wp_photo_wall_text('move_selected')); ?></button>
                <button type="button" class="button" id="wp-photo-wall-bulk-remove-btn" disabled><?php echo esc_html(wp_photo_wall_text('bulk_delete_selected')); ?></button>
                <button type="button" class="button button-link-delete" id="wp-photo-wall-delete-media-btn" disabled><?php echo esc_html(wp_photo_wall_text('delete_from_media')); ?></button>
            </div>
            <div id="wp-photo-wall-groups-container"><p id="wp-photo-wall-loading"><?php echo esc_html(wp_photo_wall_text('loading')); ?></p></div>

            <!-- Clear all - placed at bottom with danger styling -->
            <div class="wp-photo-wall-danger-zone">
                <button type="button" class="button button-link-delete" id="wp-photo-wall-clear-btn"><?php echo esc_html(wp_photo_wall_text('clear_all')); ?></button>
            </div>
        </div>

        <div class="card wp-photo-wall-admin-card">
            <h2><?php echo esc_html(wp_photo_wall_text('settings')); ?></h2>
            <p><label><input type="checkbox" name="wp_photo_wall_enable_lightbox" value="1" <?php checked(get_option('wp_photo_wall_enable_lightbox', '1'), '1'); ?>> <?php echo esc_html(wp_photo_wall_text('enable_lightbox')); ?></label></p>
            <p><label for="wp_photo_wall_download_link"><?php echo esc_html(wp_photo_wall_text('download_button_link')); ?></label><br>
                <input class="regular-text" type="url" name="wp_photo_wall_download_link" id="wp_photo_wall_download_link" value="<?php echo esc_attr(get_option('wp_photo_wall_download_link', '')); ?>" placeholder="https://"></p>
            <p><label><input type="checkbox" name="wp_photo_wall_auto_delete_orphans" value="1" <?php checked(get_option('wp_photo_wall_auto_delete_orphans', '1'), '1'); ?>> <?php echo esc_html(wp_photo_wall_text('auto_delete_orphans')); ?></label><br>
                <span class="description"><?php echo esc_html(wp_photo_wall_text('auto_delete_orphans_desc')); ?></span></p>
            <div class="wp-photo-wall-save-area">
                <?php submit_button(wp_photo_wall_text('save_changes'), 'primary', 'submit', false); ?>
            </div>
        </div>
    </form>

    <!-- Move to group modal -->
    <div id="wp-photo-wall-move-modal" class="wp-photo-wall-modal" hidden>
        <div class="wp-photo-wall-modal-dialog" role="dialog" aria-modal="true">
            <h2><?php echo esc_html(wp_photo_wall_text('move_to_group')); ?></h2>
            <select id="wp-photo-wall-move-target" class="widefat">
                <option value=""><?php echo esc_html(wp_photo_wall_text('select_target_group')); ?></option>
            </select>
            <p class="submit">
                <button type="button" class="button" id="wp-photo-wall-move-cancel"><?php echo esc_html(wp_photo_wall_text('cancel')); ?></button>
                <button type="button" class="button button-primary" id="wp-photo-wall-move-confirm" disabled><?php echo esc_html(wp_photo_wall_text('move_selected')); ?></button>
            </p>
        </div>
    </div>

    <!-- External image modal -->
    <div id="wp-photo-wall-external-modal" class="wp-photo-wall-modal" hidden>
        <div class="wp-photo-wall-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="wp-photo-wall-modal-title">
            <h2 id="wp-photo-wall-modal-title"><?php echo esc_html(wp_photo_wall_text('add_external_image_title')); ?></h2>
            <label for="wp-photo-wall-external-url"><?php echo esc_html(wp_photo_wall_text('image_url')); ?></label>
            <input type="url" id="wp-photo-wall-external-url" class="widefat" placeholder="https://example.com/image.jpg">
            <div id="wp-photo-wall-external-preview" aria-live="polite"><?php echo esc_html(wp_photo_wall_text('preview_area')); ?></div>
            <p class="submit"><button type="button" class="button" id="wp-photo-wall-external-cancel"><?php echo esc_html(wp_photo_wall_text('cancel')); ?></button> <button type="button" class="button button-primary" id="wp-photo-wall-external-confirm" disabled><?php echo esc_html(wp_photo_wall_text('add_to_wall')); ?></button></p>
        </div>
    </div>
</div>
