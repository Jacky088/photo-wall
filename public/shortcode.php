<?php
if (!defined('ABSPATH')) exit;

$all_items = wp_photo_wall_get_visible_items();
if (!$all_items) return '';

$instance_id = wp_unique_id('wp-photo-wall-');
$batch = array_slice($all_items, 0, 12);
$last_gid = null;
$open = false;
?>
<section id="<?php echo esc_attr($instance_id); ?>" class="wp-photo-wall-instance" data-page="1">
    <?php echo wp_photo_wall_render_slider($instance_id); ?>
    <div class="wp-photo-wall-wrapper">
    <?php foreach ($batch as $index => $item) :
        $gid = $item['group_id'];
        if ($gid !== $last_gid) {
            if ($open) echo '</div></div>';
            echo '<div class="wp-photo-wall-group-section" data-group-id="' . esc_attr($gid) . '">';
            echo '<h3 class="wp-photo-wall-group-title">' . esc_html($item['group_name']) . '</h3><div class="wp-photo-wall-group-grid">';
            $open = true;
            $last_gid = $gid;
        }
        if ($item['type'] === 'local') {
            $id = absint($item['id']);
            $full = wp_get_attachment_image_url($id, 'full');
            $thumb = wp_get_attachment_image_url($id, 'medium_large');
            if ($full && $thumb) wp_photo_wall_render_image($thumb, $full, $index, $id);
        } else {
            wp_photo_wall_render_image($item['url'], $item['url'], $index, 0);
        }
    endforeach;
    if ($open) echo '</div></div>'; ?>
    </div>

    <?php if (count($all_items) > 12) : ?>
        <div class="wp-photo-wall-loader" role="status" aria-live="polite"><span class="screen-reader-text"><?php echo esc_html(wp_photo_wall_text('load_more')); ?></span></div>
    <?php endif; ?>

    <?php $download_link = get_option('wp_photo_wall_download_link', ''); if ($download_link) : ?>
        <a href="<?php echo esc_url($download_link); ?>" class="wp-photo-wall-download-btn" target="_blank" rel="noopener noreferrer"><?php echo esc_html(wp_photo_wall_text('download_all')); ?></a>
    <?php endif; ?>

    <?php if (get_option('wp_photo_wall_enable_lightbox', '1') === '1') : ?>
    <div class="wp-photo-wall-lightbox" role="dialog" aria-modal="true" aria-label="Photo viewer" aria-hidden="true">
        <div class="wp-photo-wall-lightbox-overlay"></div>
        <div class="wp-photo-wall-lightbox-content" aria-live="polite"></div>
        <button type="button" class="wp-photo-wall-prev" aria-label="Previous photo">&lt;</button>
        <button type="button" class="wp-photo-wall-next" aria-label="Next photo">&gt;</button>
        <button type="button" class="wp-photo-wall-close" aria-label="Close">&times;</button>
    </div>
    <?php endif; ?>
</section>
