<?php
/**
 * Localization Helper
 * 
 * @package WP_Photo_Wall
 */

if (!defined('ABSPATH')) exit;

function wp_photo_wall_text($key)
{
    $locale = get_locale();
    $lang = 'en';

    if (strpos($locale, 'zh_CN') === 0) {
        $lang = 'zh_CN';
    } elseif (strpos($locale, 'zh_TW') === 0 || strpos($locale, 'zh_HK') === 0) {
        $lang = 'zh_TW';
    }

    $texts = array(
        // General
        'photo_wall_settings' => array(
            'en' => 'Photo Wall Settings',
            'zh_CN' => '照片墙设置',
            'zh_TW' => '照片牆設定'
        ),
        'photo_wall' => array(
            'en' => 'Photo Wall',
            'zh_CN' => '照片墙',
            'zh_TW' => '照片牆'
        ),
        'manage_photos' => array(
            'en' => 'Manage Photos',
            'zh_CN' => '管理照片',
            'zh_TW' => '管理照片'
        ),
        'manage_photos_desc' => array(
            'en' => 'Support Local Images (uploaded to Media Library) and External Links. Images are uploaded to "Uncategorized" by default. Drag to move them.',
            'zh_CN' => '支持本地图片（上传到媒体库）和外部链接（减少数据库压力）。图片默认存放在"未分组"，拖动图片即可调整至新分组。',
            'zh_TW' => '支持本地圖片（上傳到媒體庫）和外部鏈接（減少數據庫壓力）。圖片默認存放在「未分組」，拖動圖片即可調整至新分組。'
        ),
        'select_upload_photos' => array(
            'en' => 'Select / Upload Photos',
            'zh_CN' => '选择/上传照片',
            'zh_TW' => '選擇/上傳照片'
        ),
        'add_external_image' => array(
            'en' => 'Add External Image',
            'zh_CN' => '添加外部链接',
            'zh_TW' => '添加外部鏈接'
        ),
        'bulk_delete_selected' => array(
            'en' => 'Bulk Delete Selected',
            'zh_CN' => '批量删除选中',
            'zh_TW' => '批量刪除選中'
        ),
        'clear_all' => array(
            'en' => 'Clear All',
            'zh_CN' => '清空所有',
            'zh_TW' => '清空所有'
        ),
        'preview' => array(
            'en' => 'Preview',
            'zh_CN' => '预览',
            'zh_TW' => '預覽'
        ),
        'preview_drag_reorder' => array(
            'en' => 'Drag and drop to reorder',
            'zh_CN' => '拖拽排序',
            'zh_TW' => '拖拽排序'
        ),
        'preview_drag_group' => array(
            'en' => 'Drag and drop to reorder or move between groups',
            'zh_CN' => '拖拽排序或移动到通过分组',
            'zh_TW' => '拖拽排序或移動到通過分組'
        ),
        'manage_groups' => array(
            'en' => 'Manage Groups',
            'zh_CN' => '管理分组',
            'zh_TW' => '管理分組'
        ),
        'new_group_name' => array(
            'en' => 'New Group Name',
            'zh_CN' => '新分组名称',
            'zh_TW' => '新分組名稱'
        ),
        'add_group' => array(
            'en' => 'Add Group',
            'zh_CN' => '添加分组',
            'zh_TW' => '添加分組'
        ),
        'manage_groups_desc' => array(
            'en' => 'Creating groups allows you to categorize your photos. Photos can be dragged between groups. Drag group headers to reorder.',
            'zh_CN' => '创建分组可以对照片进行分类，照片可以在分组之间拖拽。拖动分组标题可调整分组顺序。',
            'zh_TW' => '創建分組可以對照片進行分類，照片可以在分組之間拖拽。拖動分組標題可調整分組順序。'
        ),
        'loading' => array(
            'en' => 'Loading...',
            'zh_CN' => '加载中...',
            'zh_TW' => '加載中...'
        ),
        'settings' => array(
            'en' => 'Settings',
            'zh_CN' => '设置',
            'zh_TW' => '設定'
        ),
        'enable_lightbox' => array(
            'en' => 'Enable Built-in Lightbox',
            'zh_CN' => '启用内置灯箱',
            'zh_TW' => '啟用內置燈箱'
        ),
        'enable_lightbox_desc' => array(
            'en' => 'If you use a third-party theme lightbox and see two lightboxes, uncheck this to disable the plugin\'s built-in lightbox.',
            'zh_CN' => '如果您的主题已有灯箱效果导致出现双重弹窗，请取消勾选此项以禁用插件内置灯箱。',
            'zh_TW' => '如果您的主題已有燈箱效果導致出現雙重彈窗，請取消勾選此項以禁用插件內置燈箱。'
        ),
        'download_button_link' => array(
            'en' => 'Download Button Link',
            'zh_CN' => '下载按钮链接',
            'zh_TW' => '下載按鈕鏈接'
        ),
        'download_button_desc' => array(
            'en' => 'Enter a URL to display a download button at the bottom of the gallery. Leave empty to hide.',
            'zh_CN' => '输入链接以在照片墙底部显示下载按钮。留空则隐藏。',
            'zh_TW' => '輸入鏈接以在照片牆底部顯示下載按鈕。留空則隱藏。'
        ),
        'save_changes' => array(
            'en' => 'Save Changes',
            'zh_CN' => '保存更改',
            'zh_TW' => '保存更改'
        ),
        'how_to_use' => array(
            'en' => 'How to use',
            'zh_CN' => '使用说明',
            'zh_TW' => '使用說明'
        ),
        'how_to_use_desc' => array(
            'en' => 'Use the shortcode [photo_wall] in any post or page to display the gallery.',
            'zh_CN' => '在任何文章或页面中使用简码 [photo_wall] 来显示照片墙。',
            'zh_TW' => '在任何文章或頁面中使用簡碼 [photo_wall] 來顯示照片牆。'
        ),

        // Modal
        'add_external_image_title' => array(
            'en' => 'Add External Image',
            'zh_CN' => '添加外链图片',
            'zh_TW' => '添加外鏈圖片'
        ),
        'image_url' => array(
            'en' => 'Image URL',
            'zh_CN' => '图片链接',
            'zh_TW' => '圖片鏈接'
        ),
        'preview_area' => array(
            'en' => 'Preview Area',
            'zh_CN' => '预览区域',
            'zh_TW' => '預覽區域'
        ),
        'cancel' => array(
            'en' => 'Cancel',
            'zh_CN' => '取消',
            'zh_TW' => '取消'
        ),
        'add_to_wall' => array(
            'en' => 'Add to Wall',
            'zh_CN' => '添加',
            'zh_TW' => '添加'
        ),

        // AJAX / JS Labels
        'from_media_library' => array(
            'en' => 'From Media Library',
            'zh_CN' => '来自媒体库',
            'zh_TW' => '來自媒體庫'
        ),
        'from_link' => array(
            'en' => 'From Link',
            'zh_CN' => '来自链接',
            'zh_TW' => '來自鏈接'
        ),
        'clear_confirm' => array(
            'en' => 'Clear all? This cannot be undone.',
            'zh_CN' => '确认清空所有内容？此操作无法撤销。',
            'zh_TW' => '確認清空所有內容？此操作無法撤銷。'
        ),
        'remove_confirm' => array(
            'en' => 'Remove %d items?',
            'zh_CN' => '确认移除选中的 %d 项？',
            'zh_TW' => '確認移除選中的 %d 項？'
        ),
        'delete_server_confirm' => array(
            'en' => 'WARNING: %d local images will be DELETED from server! This cannot be undone!',
            'zh_CN' => '警告：%d 张本地图片将从服务器永久删除！此操作无法撤销！',
            'zh_TW' => '警告：%d 張本地圖片將從服務器永久刪除！此操作無法撤銷！'
        ),
        'checking' => array(
            'en' => 'Checking...',
            'zh_CN' => '检测中...',
            'zh_TW' => '檢測中...'
        ),
        'invalid_url' => array(
            'en' => 'Invalid URL',
            'zh_CN' => '无效链接',
            'zh_TW' => '無效鏈接'
        ),
        'delete_group_confirm' => array(
            'en' => 'Delete this group?',
            'zh_CN' => '确定删除该分组？',
            'zh_TW' => '確定刪除該分組？'
        ),
        'group_not_empty' => array(
            'en' => 'This group contains photos. Please move or delete them before deleting the group.',
            'zh_CN' => '该分组包含照片。请在删除分组前先移动或删除其中的照片。',
            'zh_TW' => '該分組包含照片。請在刪除分組前先移動或刪除其中的照片。'
        ),
        'delete_group' => array(
            'en' => 'Delete Group',
            'zh_CN' => '删除分组',
            'zh_TW' => '刪除分組'
        ),
        'ajax_error' => array(
            'en' => 'AJAX Error',
            'zh_CN' => 'AJAX 错误',
            'zh_TW' => 'AJAX 錯誤'
        ),
        'done' => array(
            'en' => 'Done',
            'zh_CN' => '操作成功',
            'zh_TW' => '操作成功'
        ),
        'settings_saved' => array(
            'en' => 'Settings saved.',
            'zh_CN' => '设置已保存。',
            'zh_TW' => '設定已保存。'
        ),
        'json_error' => array(
            'en' => 'JSON Error: Invalid data format.',
            'zh_CN' => 'JSON 错误：无效的数据格式。',
            'zh_TW' => 'JSON 錯誤：無效的數據格式。'
        ),
        'permission_denied' => array(
            'en' => 'Permission denied',
            'zh_CN' => '权限不足',
            'zh_TW' => '權限不足'
        ),
        'no_images_selected' => array(
            'en' => 'No images selected',
            'zh_CN' => '未选择图片',
            'zh_TW' => '未選擇圖片'
        ),
        'success_delete' => array(
            'en' => 'Successfully deleted %d images',
            'zh_CN' => '成功删除 %d 张图片',
            'zh_TW' => '成功刪除 %d 張圖片'
        ),
        'failed_delete' => array(
            'en' => 'Failed to delete images',
            'zh_CN' => '删除图片失败',
            'zh_TW' => '刪除圖片失敗'
        ),
        'no_images_found' => array(
            'en' => 'No images found',
            'zh_CN' => '没有找到图片',
            'zh_TW' => '沒有找到圖片'
        ),
        'delete_from_media' => array(
            'en' => 'Permanently Delete from Media Library',
            'zh_CN' => '从媒体库永久删除',
            'zh_TW' => '從媒體庫永久刪除'
        ),
        'uncategorized_hidden_hint' => array(
            'en' => 'Uncategorized photos will not be displayed on the frontend.',
            'zh_CN' => '未分组图片不会在前台显示。',
            'zh_TW' => '未分組圖片不會在前台顯示。'
        ),
        'move_to_group' => array(
            'en' => 'Move to Group',
            'zh_CN' => '移动到分组',
            'zh_TW' => '移動到分組'
        ),
        'select_target_group' => array(
            'en' => '-- Select Target Group --',
            'zh_CN' => '-- 选择目标分组 --',
            'zh_TW' => '-- 選擇目標分組 --'
        ),
        'uncategorized_warning' => array(
            'en' => 'Photos here will NOT be shown on the frontend. Please move them to a group above.',
            'zh_CN' => '此处的图片不会在前台显示，请将它们移动到上方的分组中。',
            'zh_TW' => '此處的圖片不會在前台顯示，請將它們移動到上方的分組中。'
        ),
        'moved_count' => array(
            'en' => 'Moved %d photo(s) to "%s"',
            'zh_CN' => '已将 %d 张图片移动到「%s」',
            'zh_TW' => '已將 %d 張圖片移動到「%s」'
        ),
        'no_group_selected' => array(
            'en' => 'Please select a target group',
            'zh_CN' => '请选择目标分组',
            'zh_TW' => '請選擇目標分組'
        ),
        'move_confirm' => array(
            'en' => 'Move %d selected photo(s) to "%s"?',
            'zh_CN' => '确定将 %d 张选中的图片移动到「%s」？',
            'zh_TW' => '確定將 %d 張選中的圖片移動到「%s」？'
        ),
        'save_reminder' => array(
            'en' => 'All changes require clicking "Save Changes" to take effect.',
            'zh_CN' => '所有操作必须点击"保存更改"才会生效。',
            'zh_TW' => '所有操作必須點擊「儲存變更」才會生效。'
        ),

        // Frontend
        'uncategorized' => array(
            'en' => 'Uncategorized',
            'zh_CN' => '未分组',
            'zh_TW' => '未分組'
        ),
        'load_more' => array(
            'en' => 'Load More...',
            'zh_CN' => '加载更多...',
            'zh_TW' => '加載更多...'
        ),
        'download_all' => array(
            'en' => 'Download All Photos',
            'zh_CN' => '下载全部照片 (网盘)',
            'zh_TW' => '下載全部照片 (網盤)'
        ),

        // Frontend gallery labels
        'image_load_error' => array(
            'en' => 'Image failed to load, click to open original',
            'zh_CN' => '图片加载失败，点击直接打开原图',
            'zh_TW' => '圖片加載失敗，點擊直接打開原圖'
        ),

        // Admin UI labels
        'unsaved_changes' => array(
            'en' => 'You have unsaved changes!',
            'zh_CN' => '有未保存的更改！',
            'zh_TW' => '有未保存的更改！'
        ),
        'clear_all_confirm_text' => array(
            'en' => 'Type CONFIRM to clear all photos:',
            'zh_CN' => '输入 CONFIRM 以确认清空所有照片：',
            'zh_TW' => '輸入 CONFIRM 以確認清空所有照片：'
        ),
        'move_selected' => array(
            'en' => 'Move Selected',
            'zh_CN' => '移动选中',
            'zh_TW' => '移動選中'
        ),
        'settings_link' => array(
            'en' => 'Settings',
            'zh_CN' => '设置',
            'zh_TW' => '設定'
        ),

        // Top banner carousel (slides)
        'slides_title' => array(
            'en' => 'Top Banner Carousel',
            'zh_CN' => '顶部海报轮播',
            'zh_TW' => '頂部海報輪播'
        ),
        'slides_desc' => array(
            'en' => 'Manually selected images shown as an auto-playing carousel at the top of each [photo_wall]. Supports local media library images and external links.',
            'zh_CN' => '在每个 [photo_wall] 顶部展示的自动轮播海报。支持本地媒体库图片和外部链接图片。',
            'zh_TW' => '在每個 [photo_wall] 頂部展示的自動輪播海報。支持本地媒體庫圖片和外部鏈接圖片。'
        ),
        'slides_enable' => array(
            'en' => 'Enable Carousel',
            'zh_CN' => '启用轮播',
            'zh_TW' => '啟用輪播'
        ),
        'slides_enable_label' => array(
            'en' => 'Show the banner carousel above the photo wall',
            'zh_CN' => '在照片墙上方显示海报轮播',
            'zh_TW' => '在照片牆上方顯示海報輪播'
        ),
        'slides_interval' => array(
            'en' => 'Auto-play Interval',
            'zh_CN' => '轮播间隔',
            'zh_TW' => '輪播間隔'
        ),
        'seconds' => array(
            'en' => 'seconds',
            'zh_CN' => '秒',
            'zh_TW' => '秒'
        ),
        'slides_link' => array(
            'en' => 'Open in Lightbox',
            'zh_CN' => '点击打开灯箱',
            'zh_TW' => '點擊打開燈箱'
        ),
        'slides_link_label' => array(
            'en' => 'Clicking a slide opens the full image in the lightbox',
            'zh_CN' => '点击轮播图在灯箱中查看大图',
            'zh_TW' => '點擊輪播圖在燈箱中查看大圖'
        ),
        'slides_add_local' => array(
            'en' => 'Add from Media Library',
            'zh_CN' => '从媒体库添加',
            'zh_TW' => '從媒體庫添加'
        ),
        'slides_add_external' => array(
            'en' => 'Add External Link',
            'zh_CN' => '添加外部链接',
            'zh_TW' => '添加外部鏈接'
        ),
        'slides_order_hint' => array(
            'en' => 'Drag to reorder. Order is saved when you click "Save Changes".',
            'zh_CN' => '拖拽可排序，点击"保存更改"后生效。',
            'zh_TW' => '拖拽可排序，點擊「儲存變更」後生效。'
        ),
        'drag' => array(
            'en' => 'Drag to reorder',
            'zh_CN' => '拖拽排序',
            'zh_TW' => '拖拽排序'
        ),
        'local' => array(
            'en' => 'Local',
            'zh_CN' => '本地',
            'zh_TW' => '本地'
        ),
        'external' => array(
            'en' => 'External',
            'zh_CN' => '外部',
            'zh_TW' => '外部'
        ),
        'remove' => array(
            'en' => 'Remove',
            'zh_CN' => '移除',
            'zh_TW' => '移除'
        ),
        'previous' => array(
            'en' => 'Previous',
            'zh_CN' => '上一张',
            'zh_TW' => '上一張'
        ),
        'next' => array(
            'en' => 'Next',
            'zh_CN' => '下一张',
            'zh_TW' => '下一張'
        ),
        'add_to_wall' => array(
            'en' => 'Add to Carousel',
            'zh_CN' => '添加到轮播',
            'zh_TW' => '添加到輪播'
        ),
        'image_url' => array(
            'en' => 'Image URL',
            'zh_CN' => '图片链接',
            'zh_TW' => '圖片鏈接'
        ),
    );

    if (isset($texts[$key][$lang])) {
        return $texts[$key][$lang];
    }
    return isset($texts[$key]['en']) ? $texts[$key]['en'] : $key;
}
