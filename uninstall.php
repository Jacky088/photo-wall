<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

delete_option('photo_wall_data');
delete_option('photo_wall_groups');
delete_option('photo_wall_ids');
delete_option('wp_photo_wall_enable_lightbox');
delete_option('wp_photo_wall_download_link');
