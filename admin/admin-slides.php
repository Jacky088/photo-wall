<?php
/**
 * Top banner carousel settings block (admin).
 * Self-contained: rendered above the group management area.
 */
if (!defined('ABSPATH')) exit;
?>

<div class="wp-photo-wall-slides-box">
    <h2><?php echo esc_html(wp_photo_wall_text('slides_title')); ?></h2>
    <p class="description"><?php echo esc_html(wp_photo_wall_text('slides_desc')); ?></p>

    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><?php echo esc_html(wp_photo_wall_text('slides_enable')); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="photo_wall_slides_enabled" value="1"
                            <?php checked(1, (int) get_option('photo_wall_slides_enabled', 0)); ?>>
                        <?php echo esc_html(wp_photo_wall_text('slides_enable_label')); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html(wp_photo_wall_text('slides_interval')); ?></th>
                <td>
                    <input type="number" name="photo_wall_slides_interval" min="2" max="30" step="1"
                        value="<?php echo esc_attr((int) get_option('photo_wall_slides_interval', 5)); ?>">
                    <?php echo esc_html(wp_photo_wall_text('seconds')); ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html(wp_photo_wall_text('slides_link')); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="photo_wall_slides_link" value="1"
                            <?php checked(1, (int) get_option('photo_wall_slides_link', 1)); ?>>
                        <?php echo esc_html(wp_photo_wall_text('slides_link_label')); ?>
                    </label>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="wp-photo-wall-slides-manager">
        <div class="wp-pw-slides-toolbar">
            <button type="button" class="button wp-pw-add-local">
                <?php echo esc_html(wp_photo_wall_text('slides_add_local')); ?>
            </button>
            <button type="button" class="button wp-pw-add-external">
                <?php echo esc_html(wp_photo_wall_text('slides_add_external')); ?>
            </button>
        </div>

        <ul class="wp-pw-slides-list" id="wp-pw-slides-list">
            <?php
            $saved_slides = get_option('photo_wall_slides', array());
            if (is_array($saved_slides)) {
                foreach ($saved_slides as $s) {
                    $type = isset($s['type']) ? $s['type'] : 'external';
                    $id   = isset($s['id']) ? (int) $s['id'] : 0;
                    $url  = isset($s['url']) ? $s['url'] : '';
                    $full = isset($s['full']) ? $s['full'] : $url;
                    $thumb = $type === 'local' ? wp_get_attachment_image_url($id, 'thumbnail') : $url;
                    ?>
                    <li class="wp-pw-slide-item" data-type="<?php echo esc_attr($type); ?>"
                        data-id="<?php echo esc_attr($id); ?>"
                        data-url="<?php echo esc_attr($url); ?>"
                        data-full="<?php echo esc_attr($full); ?>">
                        <span class="wp-pw-slide-handle" title="<?php echo esc_attr(wp_photo_wall_text('drag')); ?>">&#8942;&#8942;</span>
                        <img class="wp-pw-slide-thumb" src="<?php echo esc_url($thumb); ?>" alt="">
                        <span class="wp-pw-slide-type"><?php echo esc_html($type === 'local' ? wp_photo_wall_text('local') : wp_photo_wall_text('external')); ?></span>
                        <button type="button" class="button-link wp-pw-slide-remove" aria-label="<?php echo esc_attr(wp_photo_wall_text('remove')); ?>">&times;</button>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
        <p class="description"><?php echo esc_html(wp_photo_wall_text('slides_order_hint')); ?></p>
    </div>
</div>
