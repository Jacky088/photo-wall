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

        // Leave-without-saving guard
        'leave_confirm' => array(
            'en' => 'Your changes have not been saved yet. Leave this page anyway?',
            'zh_CN' => '你的修改尚未保存，确定要离开本页吗？',
            'zh_TW' => '你的修改尚未儲存，確定要離開本頁嗎？'
        ),
        'leave_confirm_title' => array(
            'en' => 'Unsaved Changes',
            'zh_CN' => '尚未保存更改',
            'zh_TW' => '尚未儲存變更'
        ),
        'leave_confirm_message' => array(
            'en' => 'Your changes only take effect after clicking "Save Changes". If you leave now, they will be lost and the page will fall back to the last saved state.',
            'zh_CN' => '所有更改都必须点击「保存更改」才会生效。若现在离开，未保存的内容将会丢失，页面将退回上一次保存的状态。',
            'zh_TW' => '所有更改都必須點擊「儲存變更」才會生效。若現在離開，未儲存的內容將會遺失，頁面將退回上一次儲存的狀態。'
        ),
        'leave_stay' => array(
            'en' => 'Stay and Save',
            'zh_CN' => '留在本页保存',
            'zh_TW' => '留在本頁儲存'
        ),
        'leave_discard' => array(
            'en' => 'Discard and Leave',
            'zh_CN' => '放弃更改并离开',
            'zh_TW' => '放棄變更並離開'
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
            'en' => 'Supports local media library images and external links. Up to %d images; once the limit is reached no more can be added.',
            'zh_CN' => '支持本地媒体库图片和外部链接图片，最多 %d 张，超出后无法继续添加。',
            'zh_TW' => '支持本地媒體庫圖片和外部鏈接圖片，最多 %d 張，超出後無法繼續添加。'
        ),
        'slides_count' => array(
            'en' => 'Added %d / %d',
            'zh_CN' => '已添加 %d / %d',
            'zh_TW' => '已加入 %d / %d'
        ),
        'slides_limit_reached' => array(
            'en' => 'The banner carousel allows up to %d images. Please remove some before adding more.',
            'zh_CN' => '顶部海报轮播最多 %d 张，无法继续添加，请先移除部分图片。',
            'zh_TW' => '頂部海報輪播最多 %d 張，無法繼續添加，請先移除部分圖片。'
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

        // Bing wallpaper module
        'tab_bing' => array(
            'en' => 'Bing Wallpaper',
            'zh_CN' => '必应壁纸',
            'zh_TW' => '必應壁紙'
        ),
        'bing_title' => array(
            'en' => 'Bing Wallpaper',
            'zh_CN' => '必应壁纸',
            'zh_TW' => '必應壁紙'
        ),
        'bing_desc' => array(
            'en' => 'Pulls the latest %d days of Bing wallpapers through the API below. They are appended as the very last group on the photo wall. If you have no custom group, only the wallpapers are shown.',
            'zh_CN' => '通过下方接口拉取最近 %d 天的必应壁纸，并自动追加到图片墙的最后一个分组。若你没有自定义分组，则只显示必应壁纸。',
            'zh_TW' => '通過下方介面拉取最近 %d 天的必應壁紙，並自動追加到圖片牆的最後一個分組。若你沒有自訂分組，則只顯示必應壁紙。'
        ),
        'bing_enable' => array(
            'en' => 'Enable',
            'zh_CN' => '启用',
            'zh_TW' => '啟用'
        ),
        'bing_enable_label' => array(
            'en' => 'Show the Bing wallpaper group on the frontend',
            'zh_CN' => '在前端显示必应壁纸分组',
            'zh_TW' => '在前端顯示必應壁紙分組'
        ),
        'bing_api' => array(
            'en' => 'API Endpoint',
            'zh_CN' => 'API 接口地址',
            'zh_TW' => 'API 介面地址'
        ),
        'bing_api_desc' => array(
            'en' => 'Any Bing-compatible JSON endpoint works. Leave empty to restore the default. Results are cached for 6 hours; use "Refresh" to pull immediately.',
            'zh_CN' => '可填写任意必应兼容的 JSON 接口，留空则恢复默认地址。结果缓存 6 小时，可点击「重新拉取」立即更新。',
            'zh_TW' => '可填寫任意必應相容的 JSON 介面，留空則恢復預設地址。結果快取 6 小時，可點擊「重新拉取」立即更新。'
        ),
        'bing_group_name' => array(
            'en' => 'Group Title',
            'zh_CN' => '前端分组标题',
            'zh_TW' => '前端分組標題'
        ),
        'bing_group_default' => array(
            'en' => 'Bing Wallpaper',
            'zh_CN' => '必应壁纸',
            'zh_TW' => '必應壁紙'
        ),
        'bing_refresh' => array(
            'en' => 'Refresh Now',
            'zh_CN' => '重新拉取',
            'zh_TW' => '重新拉取'
        ),
        'bing_refreshing' => array(
            'en' => 'Fetching...',
            'zh_CN' => '拉取中...',
            'zh_TW' => '拉取中...'
        ),
        'bing_reset_order' => array(
            'en' => 'Restore Date Order',
            'zh_CN' => '恢复时间排序',
            'zh_TW' => '恢復時間排序'
        ),
        'bing_order_hint' => array(
            'en' => 'Shown newest first by default. Drag the tiles to change the order; click "Save Changes" to apply.',
            'zh_CN' => '默认按时间倒序（最新在前）显示，拖拽可调整顺序，点击「保存更改」后生效。',
            'zh_TW' => '預設依時間倒序（最新在前）顯示，拖曳可調整順序，點擊「儲存變更」後生效。'
        ),
        'bing_last_update' => array(
            'en' => 'Last updated: %s',
            'zh_CN' => '最后更新：%s',
            'zh_TW' => '最後更新：%s'
        ),
        'bing_never' => array(
            'en' => 'Never',
            'zh_CN' => '尚未拉取',
            'zh_TW' => '尚未拉取'
        ),
        'bing_empty' => array(
            'en' => 'No wallpapers returned. Please check the API endpoint and try again.',
            'zh_CN' => '未拉取到任何壁纸，请检查接口地址后重试。',
            'zh_TW' => '未拉取到任何壁紙，請檢查介面地址後重試。'
        ),
        'bing_fetch_failed' => array(
            'en' => 'Fetch failed. Please check the API endpoint or try again later.',
            'zh_CN' => '拉取失败，请检查接口地址或稍后重试。',
            'zh_TW' => '拉取失敗，請檢查介面地址或稍後重試。'
        ),
        'bing_updated' => array(
            'en' => 'Fetched %d wallpaper(s).',
            'zh_CN' => '已拉取 %d 张壁纸。',
            'zh_TW' => '已拉取 %d 張壁紙。'
        ),

        // API response format documentation
        'bing_api_hint_toggle' => array(
            'en' => 'API response format (click to expand)',
            'zh_CN' => '接口返回格式要求（点击展开）',
            'zh_TW' => '介面回傳格式要求（點擊展開）'
        ),
        'bing_api_hint_intro' => array(
            'en' => 'The endpoint must answer a plain GET request with JSON. The plugin only reads the wallpaper array inside the response; every other field is ignored.',
            'zh_CN' => '接口需要通过 GET 请求返回 JSON 数据。插件只解析其中的壁纸数组，其余字段会被忽略。',
            'zh_TW' => '介面需要透過 GET 請求回傳 JSON 資料。插件只解析其中的壁紙陣列，其餘欄位會被忽略。'
        ),
        'bing_api_hint_container' => array(
            'en' => '1. Where the wallpaper array lives',
            'zh_CN' => '1. 壁纸数组所在层级',
            'zh_TW' => '1. 壁紙陣列所在層級'
        ),
        'bing_api_hint_container_desc' => array(
            'en' => 'The plugin looks for these keys in order: images, data, list, results, wallpapers. If none of them exists, the response itself is treated as the array.',
            'zh_CN' => '插件按顺序依次查找 images、data、list、results、wallpapers 这几个键；若都不存在，则把整个返回结果当作数组处理。',
            'zh_TW' => '插件依序查找 images、data、list、results、wallpapers 這幾個鍵；若都不存在，則把整個回傳結果當作陣列處理。'
        ),
        'bing_api_hint_fields' => array(
            'en' => '2. Supported fields per wallpaper',
            'zh_CN' => '2. 单张壁纸支持的字段',
            'zh_TW' => '2. 單張壁紙支援的欄位'
        ),
        'bing_api_hint_field_url' => array(
            'en' => 'url (required): full image address. Bing returns a relative path such as /th?id=OHR.Name_1920x1080.jpg&rf=...&pid=hp, which is completed with the endpoint host automatically. If url is missing the plugin falls back to full, image, src, img, download_url, thumbnail.',
            'zh_CN' => 'url（必填）：图片完整地址。必应官方返回的是相对路径，如 /th?id=OHR.Name_1920x1080.jpg&rf=...&pid=hp，插件会自动用接口域名补全为绝对地址。若没有 url，会依次尝试 full、image、src、img、download_url、thumbnail。',
            'zh_TW' => 'url（必填）：圖片完整地址。必應官方回傳的是相對路徑，如 /th?id=OHR.Name_1920x1080.jpg&rf=...&pid=hp，插件會自動用介面網域補全為絕對地址。若沒有 url，會依序嘗試 full、image、src、img、download_url、thumbnail。'
        ),
        'bing_api_hint_field_urlbase' => array(
            'en' => 'urlbase (optional): the resolution-less image base, e.g. /th?id=OHR.Name. It is used to build the 400x240 (admin) and 800x600 (frontend grid) thumbnails. When it is missing the thumbnails simply use url.',
            'zh_CN' => 'urlbase（可选）：去掉分辨率后缀的图片基址，如 /th?id=OHR.Name。插件据此自动生成 400×240（后台缩略图）和 800×600（前台网格）缩略图；不提供时缩略图直接使用 url。',
            'zh_TW' => 'urlbase（選填）：去掉解析度後綴的圖片基底，如 /th?id=OHR.Name。插件據此自動產生 400×240（後台縮圖）與 800×600（前台網格）縮圖；未提供時縮圖直接使用 url。'
        ),
        'bing_api_hint_field_date' => array(
            'en' => 'startdate (optional): wallpaper date in YYYYMMDD form, e.g. 20260915. It drives the newest-first sorting and acts as the stable id that remembers your drag order. date and enddate are accepted as fallbacks.',
            'zh_CN' => 'startdate（可选）：壁纸日期，格式为 YYYYMMDD，如 20260915。它决定按时间倒序排序，并作为唯一标识记住你的拖拽顺序。也接受 date 或 enddate。',
            'zh_TW' => 'startdate（選填）：壁紙日期，格式為 YYYYMMDD，如 20260915。它決定依時間倒序排序，並作為唯一識別記住你的拖曳順序。也接受 date 或 enddate。'
        ),
        'bing_api_hint_field_title' => array(
            'en' => 'title (optional): wallpaper title, shown together with the date below the admin thumbnail.',
            'zh_CN' => 'title（可选）：壁纸标题，与日期一起显示在后台缩略图下方。',
            'zh_TW' => 'title（選填）：壁紙標題，與日期一起顯示在後台縮圖下方。'
        ),
        'bing_api_hint_field_copyright' => array(
            'en' => 'copyright (optional): copyright / description text, shown as a tooltip on the admin thumbnail. description, desc and caption are accepted as fallbacks.',
            'zh_CN' => 'copyright（可选）：版权或描述文字，鼠标悬停后台缩略图时显示。也接受 description、desc、caption。',
            'zh_TW' => 'copyright（選填）：版權或描述文字，滑鼠懸停後台縮圖時顯示。也接受 description、desc、caption。'
        ),
        'bing_api_hint_notes' => array(
            'en' => '3. Other rules' . "\n" .
                'Only the first %d entries are used, the rest is ignored.' . "\n" .
                'Entries are sorted by startdate, newest first. Without a date the original order is kept.' . "\n" .
                'Response must be UTF-8; a JSONP wrapper is unwrapped automatically.' . "\n" .
                'Both the endpoint and the image URLs must be reachable directly by the visitor browser (hotlinking must be allowed); HTTPS is recommended.' . "\n" .
                'Successful responses are cached for 6 hours. Use "Refresh Now" to pull immediately; a failed request is retried after 5 minutes.',
            'zh_CN' => '3. 其它规则' . "\n" .
                '只取数组前 %d 条，超出部分忽略。' . "\n" .
                '按 startdate 倒序排列（最新在前）；未提供日期时保持接口返回的原始顺序。' . "\n" .
                '返回内容需为 UTF-8；若接口使用 JSONP 包裹，插件会自动剥离外层回调。' . "\n" .
                '接口与图片地址都必须允许访客浏览器直接访问（即支持直链/外链），建议使用 HTTPS。' . "\n" .
                '成功结果缓存 6 小时，可点「重新拉取」立即更新；拉取失败后 5 分钟才会再次请求。',
            'zh_TW' => '3. 其它規則' . "\n" .
                '只取陣列前 %d 條，超出部分忽略。' . "\n" .
                '依 startdate 倒序排列（最新在前）；未提供日期時維持介面回傳的原始順序。' . "\n" .
                '回傳內容需為 UTF-8；若介面使用 JSONP 包裹，插件會自動剝離外層回呼。' . "\n" .
                '介面與圖片地址都必須允許訪客瀏覽器直接存取（即支援直連/外連），建議使用 HTTPS。' . "\n" .
                '成功結果快取 6 小時，可點「重新拉取」立即更新；拉取失敗後 5 分鐘才會再次請求。'
        ),
        // Daily automatic pull (WP-Cron)
        'bing_cron_enable' => array(
            'en' => 'Auto Update',
            'zh_CN' => '自动更新',
            'zh_TW' => '自動更新'
        ),
        'bing_cron_enable_label' => array(
            'en' => 'Automatically pull the newest wallpapers once every day',
            'zh_CN' => '每天自动拉取一次最新壁纸',
            'zh_TW' => '每天自動拉取一次最新壁紙'
        ),
        'bing_cron_hour' => array(
            'en' => 'Run At',
            'zh_CN' => '执行时间',
            'zh_TW' => '執行時間'
        ),
        'bing_cron_desc' => array(
            'en' => 'Scheduled with WP-Cron, so it only fires while the site receives traffic. For exact timing, disable WP-Cron and add a real system cron job.',
            'zh_CN' => '基于 WordPress 定时任务（WP-Cron），需要有访问量时才会触发；若需精确执行，请为站点配置系统级 crontab。',
            'zh_TW' => '基於 WordPress 定時任務（WP-Cron），需要有流量時才會觸發；若需精確執行，請為站台設定系統級 crontab。'
        ),
        'bing_cron_next' => array(
            'en' => 'Next run: %s',
            'zh_CN' => '下次执行：%s',
            'zh_TW' => '下次執行：%s'
        ),
        'bing_cron_last' => array(
            'en' => 'Last automatic pull: %s',
            'zh_CN' => '上次自动拉取：%s',
            'zh_TW' => '上次自動拉取：%s'
        ),
        'bing_cron_not_scheduled' => array(
            'en' => 'Not scheduled',
            'zh_CN' => '未安排',
            'zh_TW' => '未安排'
        ),
        'bing_download_url' => array(
            'en' => 'Download Button Link',
            'zh_CN' => '下载按钮链接',
            'zh_TW' => '下載按鈕鏈接'
        ),
        'bing_download_section' => array(
            'en' => 'Download Button',
            'zh_CN' => '下载按钮',
            'zh_TW' => '下載按鈕'
        ),
        'bing_download_section_desc' => array(
            'en' => 'Only applies to the Bing wallpaper group and always sits right below it. The button is hidden when the group is disabled, when no wallpaper was fetched, or when the link below is empty.',
            'zh_CN' => '仅适用于必应壁纸，且始终紧跟在必应壁纸分组下方。关闭必应壁纸、暂未拉到壁纸或链接留空时，前端都不会显示该按钮。',
            'zh_TW' => '僅適用於必應壁紙，且始終緊跟在必應壁紙分組下方。關閉必應壁紙、尚未取得壁紙或鏈接留空時，前端都不會顯示該按鈕。'
        ),
        'bing_download_url_desc' => array(
            'en' => 'Fill in a link to show the download button right below the Bing wallpapers on the frontend. Leave empty to hide the button.',
            'zh_CN' => '填写后，前端会在必应壁纸下方显示下载按钮；留空则不显示。',
            'zh_TW' => '填寫後，前端會在必應壁紙下方顯示下載按鈕；留空則不顯示。'
        ),
        'bing_download_text' => array(
            'en' => 'Download Button Label',
            'zh_CN' => '下载按钮名称',
            'zh_TW' => '下載按鈕名稱'
        ),
        'bing_download_text_desc' => array(
            'en' => 'Leave empty to use the default label.',
            'zh_CN' => '留空则使用默认名称「下载壁纸」。',
            'zh_TW' => '留空則使用預設名稱「下載壁紙」。'
        ),
        'bing_download_default' => array(
            'en' => 'Download Wallpapers',
            'zh_CN' => '下载壁纸',
            'zh_TW' => '下載壁紙'
        ),
        'bing_api_default_hint' => array(
            'en' => 'Leave the field empty to fall back to the official Bing endpoint (no API key required):',
            'zh_CN' => '留空则恢复为必应官方接口（无需密钥）：',
            'zh_TW' => '留空則恢復為必應官方介面（無需金鑰）：'
        ),
        'bing_api_hint_example' => array(
            'en' => '4. Minimal working response',
            'zh_CN' => '4. 最小可用返回示例',
            'zh_TW' => '4. 最小可用回傳範例'
        ),
    );

    }

    if (isset($texts[$key][$lang])) {
        return $texts[$key][$lang];
    }
    return isset($texts[$key]['en']) ? $texts[$key]['en'] : $key;
}
