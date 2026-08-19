<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

delete_option('photo_wall_data');
delete_option('photo_wall_groups');
delete_option('photo_wall_ids');
delete_option('wp_photo_wall_enable_lightbox');
delete_option('wp_photo_wall_download_link');

// Top banner carousel options (added in 2.1.0)
delete_option('photo_wall_slides');
delete_option('photo_wall_slides_enabled');
delete_option('photo_wall_slides_interval');
delete_option('photo_wall_slides_link');
