<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

delete_option('photo_wall_data');
delete_option('photo_wall_groups');
delete_option('photo_wall_ids');
delete_option('wp_photo_wall_enable_lightbox');
delete_option('wp_photo_wall_download_link');
delete_option('wp_photo_wall_auto_delete_orphans');

// Top banner carousel options (added in 2.1.0)
delete_option('photo_wall_slides');
delete_option('photo_wall_slides_enabled');
delete_option('photo_wall_slides_interval');
delete_option('photo_wall_slides_link');

// Bing wallpaper options (added in 2.6.0)
delete_option('photo_wall_bing_enabled');
delete_option('photo_wall_bing_api');
delete_option('photo_wall_bing_title');
delete_option('photo_wall_bing_order');
delete_option('photo_wall_bing_updated');
delete_transient('wp_photo_wall_bing_' . md5('https://www.bing.com/HPImageArchive.aspx?format=js&idx=0&n=7&mkt=zh-CN'));

// Bing wallpaper: daily cron options (added in 2.7.0)
delete_option('photo_wall_bing_cron_enabled');
delete_option('photo_wall_bing_cron_hour');
delete_option('photo_wall_bing_cron_last');
wp_clear_scheduled_hook('wp_photo_wall_bing_daily_pull');

// Bing wallpaper: download button options (added in 2.8.0)
delete_option('photo_wall_bing_download_url');
delete_option('photo_wall_bing_download_text');
