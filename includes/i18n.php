<?php
/**
 * Localization Helper
 * 
 * @package WP_Photo_Wall
 */

if (!defined('ABSPATH')) exit;

function wp_photo_wall_text($key)
{
    static $texts = null, $lang = null;

    if ($texts === null) {
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
            'zh_CN' => '图片墙设置',
            'zh_TW' => '圖片牆設定'
        ),
        'photo_wall' => array(
            'en' => 'Photo Wall',
            'zh_CN' => '图片墙',
            'zh_TW' => '圖片牆'
        ),
        'manage_photos' => array(
            'en' => 'Manage Photos',
            'zh_CN' => '管理图片',
            'zh_TW' => '管理圖片'
        ),
        'manage_photos_desc' => array(
            'en' => 'Support Local Images (uploaded to Media Library) and External Links. Images are uploaded to "Uncategorized" by default. Drag to move them.',
            'zh_CN' => '支持本地图片（上传到媒体库）和外部链接（减少数据库压力）。图片默认存放在"未分组"，拖动图片即可调整至新分组。',
            'zh_TW' => '支持本地圖片（上傳到媒體庫）和外部鏈接（減少數據庫壓力）。圖片默認存放在「未分組」，拖動圖片即可調整至新分組。'
        ),
        'select_upload_photos' => array(
            'en' => 'Select / Upload Photos',
            'zh_CN' => '选择/上传图片',
            'zh_TW' => '選擇/上傳圖片'
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
            'zh_CN' => '拖拽排序或移动到其他分组',
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
            'zh_CN' => '创建分组可以对图片进行分类，图片可以在分组之间拖拽。拖动分组标题可调整分组顺序。',
            'zh_TW' => '創建分組可以對圖片進行分類，圖片可以在分組之間拖拽。拖動分組標題可調整分組順序。'
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
            'zh_CN' => '输入链接以在图片墙底部显示下载按钮。留空则隐藏。',
            'zh_TW' => '輸入鏈接以在圖片牆底部顯示下載按鈕。留空則隱藏。'
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
            'zh_CN' => '在任何文章或页面中使用简码 [photo_wall] 来显示图片墙。',
            'zh_TW' => '在任何文章或頁面中使用簡碼 [photo_wall] 來顯示圖片牆。'
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
        'image_url' => array(
            'en' => 'Image URL',
            'zh_CN' => '图片链接',
            'zh_TW' => '圖片鏈接'
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
            'zh_CN' => '该分组包含图片。请在删除分组前先移动或删除其中的图片。',
            'zh_TW' => '該分組包含圖片。請在刪除分組前先移動或刪除其中的圖片。'
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
            'zh_CN' => '下载全部图片 (网盘)',
            'zh_TW' => '下載全部圖片 (網盤)'
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
            'zh_CN' => '输入 CONFIRM 以确认清空所有图片：',
            'zh_TW' => '輸入 CONFIRM 以確認清空所有圖片：'
        ),

        // Deletion confirmation dialogs (type-aware: Media Library vs external)
        'confirm_remove_title' => array(
            'en' => 'Remove Image',
            'zh_CN' => '移除图片',
            'zh_TW' => '移除圖片'
        ),
        'confirm_perm_delete_title' => array(
            'en' => 'Permanently Delete Image',
            'zh_CN' => '永久删除图片',
            'zh_TW' => '永久刪除圖片'
        ),
        'confirm_clear_title' => array(
            'en' => 'Clear All Images',
            'zh_CN' => '清空所有图片',
            'zh_TW' => '清空所有圖片'
        ),
        'confirm_remove_local' => array(
            'en' => 'Remove this local image from the photo wall?',
            'zh_CN' => '确定从图片墙移除这张本地图片吗？',
            'zh_TW' => '確定從圖片牆移除這張本地圖片嗎？'
        ),
        'confirm_remove_external' => array(
            'en' => 'Remove this external image from the photo wall?',
            'zh_CN' => '确定从图片墙移除此外部链接图片吗？',
            'zh_TW' => '確定從圖片牆移除此外部鏈接圖片嗎？'
        ),
        'confirm_remove_bulk' => array(
            'en' => 'Remove the %d selected image(s) from the photo wall?',
            'zh_CN' => '确定从图片墙移除选中的 %d 张图片吗？',
            'zh_TW' => '確定從圖片牆移除選中的 %d 張圖片嗎？'
        ),
        'confirm_remove_slide' => array(
            'en' => 'Remove this image from the top banner carousel?',
            'zh_CN' => '确定从顶部海报轮播移除这张图片吗？',
            'zh_TW' => '確定從頂部海報輪播移除這張圖片嗎？'
        ),
        'confirm_perm_delete' => array(
            'en' => 'Permanently delete the %d selected local image(s) from the Media Library?',
            'zh_CN' => '确定从媒体库永久删除选中的 %d 张本地图片吗？',
            'zh_TW' => '確定從媒體庫永久刪除選中的 %d 張本地圖片嗎？'
        ),
        'confirm_clear' => array(
            'en' => 'Clear all %d image(s) from the photo wall?',
            'zh_CN' => '确定清空图片墙中的全部 %d 张图片吗？',
            'zh_TW' => '確定清空圖片牆中的全部 %d 張圖片嗎？'
        ),
        'impact_external' => array(
            'en' => 'External image: only the reference is removed. The original image on its host is not affected.',
            'zh_CN' => '外部链接图片：仅移除引用，不会影响源站图片。',
            'zh_TW' => '外部鏈接圖片：僅移除引用，不會影響源站圖片。'
        ),
        'impact_local_delete' => array(
            'en' => 'Local (Media Library) image: on save it is permanently deleted from the Media Library to avoid orphans (unrecoverable). If it is still used elsewhere (e.g. the carousel), it is kept.',
            'zh_CN' => '本地图片（媒体库）：保存后将从媒体库永久删除，避免产生孤儿图片（不可恢复）；若该图片仍被图片墙或轮播的其他位置使用，则会保留。',
            'zh_TW' => '本地圖片（媒體庫）：儲存後將從媒體庫永久刪除，避免產生孤兒圖片（不可恢復）；若該圖片仍被圖片牆或輪播的其他位置使用，則會保留。'
        ),
        'impact_perm_delete' => array(
            'en' => 'This permanently deletes the image file from the server and cannot be undone. It is also removed from the photo wall and the top banner carousel.',
            'zh_CN' => '将从服务器永久删除图片文件，无法恢复，同时会从图片墙和顶部海报轮播中移除。',
            'zh_TW' => '將從伺服器永久刪除圖片檔案，無法恢復，同時會從圖片牆和頂部海報輪播中移除。'
        ),
        'impact_slide_local' => array(
            'en' => 'Local (Media Library) image: if it is no longer used by the photo wall or the carousel, saving also deletes it from the Media Library (unrecoverable).',
            'zh_CN' => '本地图片（媒体库）：若它不再被图片墙或轮播使用，保存时会同步从媒体库删除（不可恢复）。',
            'zh_TW' => '本地圖片（媒體庫）：若它不再被圖片牆或輪播使用，儲存時會同步從媒體庫刪除（不可恢復）。'
        ),
        'impact_clear' => array(
            'en' => 'This clears every image from the photo wall. Local images that are no longer used anywhere are deleted from the Media Library as well.',
            'zh_CN' => '将清空图片墙中的全部图片；其中已不再被使用的本地图片会同步从媒体库删除。',
            'zh_TW' => '將清空圖片牆中的全部圖片；其中已不再被使用的本地圖片會同步從媒體庫刪除。'
        ),
        'detail_local_count' => array(
            'en' => 'Local (Media Library): %d',
            'zh_CN' => '本地图片（媒体库）：%d 张',
            'zh_TW' => '本地圖片（媒體庫）：%d 張'
        ),
        'detail_external_count' => array(
            'en' => 'External links: %d',
            'zh_CN' => '外部链接图片：%d 张',
            'zh_TW' => '外部鏈接圖片：%d 張'
        ),
        'save_to_apply_note' => array(
            'en' => 'You must click "Save Changes" for this to take effect; if you leave without saving, the page restores the original state on reload.',
            'zh_CN' => '需点击「保存更改」后才会生效；未保存则刷新页面后恢复原状。',
            'zh_TW' => '需點擊「儲存變更」後才會生效；未儲存則重新整理頁面後恢復原狀。'
        ),
        'save_to_apply_note_perm' => array(
            'en' => 'The file is actually deleted only after you click "Save Changes"; if you leave without saving, nothing is deleted.',
            'zh_CN' => '需点击「保存更改」后才会真正删除；未保存则不会删除任何文件。',
            'zh_TW' => '需點擊「儲存變更」後才會真正刪除；未儲存則不會刪除任何檔案。'
        ),
        'confirm_word_hint' => array(
            'en' => 'Type CONFIRM to proceed:',
            'zh_CN' => '请输入 CONFIRM 以确认操作：',
            'zh_TW' => '請輸入 CONFIRM 以確認操作：'
        ),
        'confirm_delete' => array(
            'en' => 'Confirm',
            'zh_CN' => '确定',
            'zh_TW' => '確定'
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
        'tab_banner' => array(
            'en' => 'Home Banner',
            'zh_CN' => '首页海报',
            'zh_TW' => '首頁海報'
        ),
        'tab_photos' => array(
            'en' => 'Photo Management',
            'zh_CN' => '图片管理',
            'zh_TW' => '圖片管理'
        ),

        // Top banner carousel (slides)
        'slides_title' => array(
            'en' => 'Top Banner Carousel',
            'zh_CN' => '顶部海报轮播',
            'zh_TW' => '頂部海報輪播'
        ),
        'slides_desc' => array(
            'en' => 'Supports local media library images and external links.',
            'zh_CN' => '支持本地媒体库图片和外部链接图片。',
            'zh_TW' => '支持本地媒體庫圖片和外部鏈接圖片。'
        ),
        'slides_enable' => array(
            'en' => 'Enable Carousel',
            'zh_CN' => '启用轮播',
            'zh_TW' => '啟用輪播'
        ),
        'slides_enable_label' => array(
            'en' => 'Show the banner carousel above the photo wall',
            'zh_CN' => '在图片墙上方显示海报轮播',
            'zh_TW' => '在圖片牆上方顯示海報輪播'
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
        'slides_add_to_carousel' => array(
            'en' => 'Add to Carousel',
            'zh_CN' => '添加到轮播',
            'zh_TW' => '添加到輪播'
        ),

        // Orphan media cleanup (delete media library images no longer used by the plugin)
        'orphan_auto_cleanup_note' => array(
            'en' => 'Orphan images are cleaned up automatically: after you remove a Media Library image from the photo wall or the banner carousel and click "Save Changes", the file is permanently deleted from the Media Library if the plugin no longer uses it anywhere.',
            'zh_CN' => '孤儿图片自动清理：从图片墙或顶部轮播移除媒体库图片并点击「保存更改」后，若该图片已不被本插件任何位置引用，将自动从媒体库永久删除，避免产生孤儿图片。',
            'zh_TW' => '孤兒圖片自動清理：從圖片牆或頂部輪播移除媒體庫圖片並點擊「儲存變更」後，若該圖片已不被本插件任何位置引用，將自動從媒體庫永久刪除，避免產生孤兒圖片。'
        ),
        'settings_saved_deleted' => array(
            'en' => 'Settings saved. %d media file(s) were permanently deleted from the Media Library.',
            'zh_CN' => '设置已保存,并已从媒体库永久删除 %d 个图片文件。',
            'zh_TW' => '設定已保存,並已從媒體庫永久刪除 %d 個圖片檔案。'
        ),
    );

    }

    if (isset($texts[$key][$lang])) {
        return $texts[$key][$lang];
    }
    return isset($texts[$key]['en']) ? $texts[$key]['en'] : $key;
}
