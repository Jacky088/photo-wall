jQuery(document).ready(function ($) {
    // ==========================================
    // Globals & Elements
    // ==========================================
    var currentData = [];
    var currentGroups = [];
    var isDirty = false;
    var initialized = false;
    var activeGroupId = 'uncategorized';
    var viewMode = 'grid'; // 'grid' | 'list'
    var searchQuery = '';

    var $form = $('#wp-photo-wall-form');
    var $dataInput = $('#photo_wall_data');
    var $groupsInput = $('#photo_wall_groups');
    var $slidesInput = $('#photo_wall_slides');
    var $pendingInput = $('#photo_wall_pending_deletions');
    var $unsavedBar = $('#wp-photo-wall-unsaved');

    // Modals
    var $moveModal = $('#wp-photo-wall-move-modal');
    var $externalModal = $('#wp-photo-wall-external-modal');
    var $confirmModal = $('#wp-photo-wall-confirm-modal');
    var $helpModal = $('#wp-pw-help-modal');

    // Parse Initial Data
    try {
        var rawData = $dataInput.val();
        if (rawData) currentData = JSON.parse(rawData);
    } catch (e) {
        console.error('Photo Wall: JSON parse error (data)', e);
    }

    try {
        var rawGroups = $groupsInput.val();
        if (rawGroups) currentGroups = JSON.parse(rawGroups);
    } catch (e) {
        console.error('Photo Wall: JSON parse error (groups)', e);
    }

    function escapeHtml(value) {
        return $('<div>').text(String(value || '')).html();
    }

    // ==========================================
    // Toast Notification System
    // ==========================================
    var $toastContainer = $('#wp-pw-toast-container');
    function showToast(message, type, duration) {
        type = type || 'info';
        duration = duration || 3500;
        var iconMap = {
            success: 'dashicons-yes-alt',
            error: 'dashicons-dismiss',
            info: 'dashicons-info'
        };
        var icon = iconMap[type] || 'dashicons-info';
        var $toast = $('<div class="wp-pw-toast wp-pw-toast-' + type + '">' +
            '<span class="dashicons ' + icon + '"></span>' +
            '<span>' + escapeHtml(message) + '</span>' +
            '</div>');

        $toastContainer.append($toast);
        setTimeout(function () {
            $toast.fadeOut(300, function () { $(this).remove(); });
        }, duration);
    }

    // ==========================================
    // Unsaved Changes Tracking
    // ==========================================
    function markDirty() {
        if (!initialized) return;
        if (!isDirty) {
            isDirty = true;
            $unsavedBar.addClass('is-visible');
            $('.wp-photo-wall-save-btn').addClass('wp-pw-btn-pulse');
        }
    }

    function markClean() {
        isDirty = false;
        $unsavedBar.removeClass('is-visible');
        $('.wp-photo-wall-save-btn').removeClass('wp-pw-btn-pulse');
    }

    // Watch all inputs for change
    $form.on('input change', 'input, select, textarea', function () {
        if (!$(this).is('#wp-photo-wall-search-input, #wp-photo-wall-new-group-name, #wp-photo-wall-confirm-word')) {
            markDirty();
        }
    });

    $(window).on('beforeunload', function (e) {
        if (!isDirty) return;
        var msg = (wp_photo_wall_ajax.labels && wp_photo_wall_ajax.labels.leave_confirm) || '您有未保存的更改，确定要离开吗？';
        if (e.originalEvent) e.originalEvent.returnValue = msg;
        return msg;
    });

    $form.on('submit', function () {
        markClean();
    });

    // ==========================================
    // Tab Navigation
    // ==========================================
    var $tabLinks = $('.wp-pw-tabs-nav .wp-pw-tab-item');
    var $tabPanels = $('.wp-pw-tab-content');
    var $activeTabInput = $('#wp_photo_wall_active_tab');

    function switchTab(tab) {
        $tabLinks.removeClass('is-active')
            .filter('[data-tab="' + tab + '"]').addClass('is-active');

        $tabPanels.each(function () {
            $(this).prop('hidden', $(this).data('tab') !== tab);
        });

        $activeTabInput.val(tab);
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '#' + tab);
        }

        if (tab === 'banner') {
            initLiveCarousel();
        }
    }

    $tabLinks.on('click', function (e) {
        e.preventDefault();
        switchTab($(this).data('tab'));
    });

    var hashTab = window.location.hash.replace('#', '');
    if (hashTab && $tabLinks.filter('[data-tab="' + hashTab + '"]').length) {
        switchTab(hashTab);
    } else {
        switchTab($activeTabInput.val() || 'banner');
    }

    // Modal universal close support
    $(document).on('click', '.wp-pw-modal-close', function () {
        $(this).closest('.wp-photo-wall-modal').prop('hidden', true);
    });

    // Help modal toggle
    $('#wp-pw-help-btn').on('click', function () {
        $helpModal.prop('hidden', false);
    });
    $('#wp-pw-help-close, #wp-pw-help-ok').on('click', function () {
        $helpModal.prop('hidden', true);
    });

    // ==========================================
    // Confirmation Dialog
    // ==========================================
    var confirmCallback = null;
    function wpPwConfirm(opts, onConfirm) {
        opts = opts || {};
        confirmCallback = typeof onConfirm === 'function' ? onConfirm : null;

        $confirmModal.find('.wp-photo-wall-confirm-title').text(opts.title || '确认操作');
        $confirmModal.find('.wp-photo-wall-confirm-message').text(opts.message || '');

        var $details = $confirmModal.find('.wp-photo-wall-confirm-details');
        if (opts.details && opts.details.length) {
            var html = '';
            opts.details.forEach(function (d) { html += '<li>' + escapeHtml(d) + '</li>'; });
            $details.html(html).prop('hidden', false);
        } else {
            $details.empty().prop('hidden', true);
        }

        var $okBtn = $confirmModal.find('.wp-photo-wall-confirm-ok');
        var label = opts.confirmLabel || '确定';
        if (opts.danger) {
            $okBtn.removeClass('wp-pw-btn-primary wp-pw-btn-danger-outline')
                .addClass('wp-pw-btn-danger');
        } else {
            $okBtn.removeClass('wp-pw-btn-danger wp-pw-btn-danger-outline')
                .addClass('wp-pw-btn-primary');
        }
        $okBtn.html('<span>' + escapeHtml(label) + '</span>');

        $confirmModal.prop('hidden', false);
    }

    $confirmModal.on('click', '.wp-photo-wall-confirm-cancel', function () {
        $confirmModal.prop('hidden', true);
        confirmCallback = null;
    });

    $confirmModal.on('click', '.wp-photo-wall-confirm-ok', function () {
        $confirmModal.prop('hidden', true);
        if (confirmCallback) {
            confirmCallback();
            confirmCallback = null;
        }
    });

    // ==========================================
    // Pending Deletions Management
    // ==========================================
    function getPendingDeletions() {
        try {
            return JSON.parse($pendingInput.val() || '[]');
        } catch (e) {
            return [];
        }
    }
    function setPendingDeletions(list) {
        $pendingInput.val(JSON.stringify(list));
    }
    function queuePendingDeletion(id) {
        id = parseInt(id, 10) || 0;
        if (id <= 0) return;
        var pending = getPendingDeletions();
        if (pending.indexOf(id) === -1) {
            pending.push(id);
            setPendingDeletions(pending);
        }
    }
    function unqueuePendingDeletion(id) {
        id = parseInt(id, 10) || 0;
        if (id <= 0) return;
        var pending = getPendingDeletions().filter(function (pid) {
            return parseInt(pid, 10) !== id;
        });
        setPendingDeletions(pending);
    }

    // ==========================================
    // TAB 1: 首页海报 (Slides) Logic
    // ==========================================
    var slidesMax = parseInt(wp_photo_wall_ajax.slides_max, 10) || 6;
    var $slidesList = $('#wp-pw-slides-list');
    var $slidesCountLabel = $('#wp-pw-slides-count-label');
    var $slidePlaceholder = $('#wp-pw-slide-placeholder');
    var slidesMediaFrame = null;
    var liveCarouselTimer = null;
    var liveCarouselIndex = 0;

    function getSlides() {
        try {
            var raw = $slidesInput.val();
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }

    function setSlides(slides) {
        $slidesInput.val(JSON.stringify(slides));
        markDirty();
        updateSlidesUI();
    }

    function updateSlidesUI() {
        var slides = getSlides();
        $slidesCountLabel.text('(' + slides.length + '/' + slidesMax + ')');
        if (slides.length >= slidesMax) {
            $slidePlaceholder.hide();
        } else {
            $slidePlaceholder.show();
        }
        updateLiveCarousel();
    }

    function syncSlidesFromDOM() {
        var slides = [];
        $slidesList.find('.wp-pw-slide-item').each(function () {
            var $item = $(this);
            var type = $item.data('type') || 'external';
            var s = {
                type: type,
                url: $item.data('url'),
                full: $item.data('full') || $item.data('url'),
                thumb_url: $item.find('.wp-pw-slide-thumb').attr('src')
            };
            if (type === 'local') {
                s.id = parseInt($item.data('id'), 10) || 0;
            }
            slides.push(s);
        });
        setSlides(slides);
    }

    function initSlidesSortable() {
        if ($.fn.sortable && $slidesList.length) {
            $slidesList.sortable({
                items: '.wp-pw-slide-item',
                handle: '.wp-pw-slide-handle',
                placeholder: 'wp-pw-slide-placeholder',
                tolerance: 'pointer',
                update: function () {
                    syncSlidesFromDOM();
                }
            });
        }
    }

    // Add Local Slide
    $(document).on('click', '.wp-pw-add-local, #wp-pw-slide-placeholder', function (e) {
        e.preventDefault();
        var slides = getSlides();
        if (slides.length >= slidesMax) {
            alert('海报轮播最多可添加 ' + slidesMax + ' 张图片。');
            return;
        }

        if (typeof wp === 'undefined' || !wp.media) {
            alert('WordPress 媒体库不可用。');
            return;
        }

        if (slidesMediaFrame) {
            slidesMediaFrame.open();
            return;
        }

        slidesMediaFrame = wp.media({
            title: '从媒体库选择轮播海报',
            button: { text: '添加到轮播海报' },
            multiple: true
        });

        slidesMediaFrame.on('select', function () {
            var selection = slidesMediaFrame.state().get('selection');
            var current = getSlides();
            selection.map(function (att) {
                if (current.length >= slidesMax) return;
                var data = att.toJSON();
                var thumb = data.url;
                if (data.sizes) {
                    if (data.sizes.medium_large) thumb = data.sizes.medium_large.url;
                    else if (data.sizes.medium) thumb = data.sizes.medium.url;
                }
                unqueuePendingDeletion(data.id);
                current.push({
                    type: 'local',
                    id: data.id,
                    url: thumb,
                    full: data.url,
                    thumb_url: thumb
                });
            });

            renderSlidesList(current);
            setSlides(current);
        });

        slidesMediaFrame.open();
    });

    function renderSlidesList(slides) {
        var html = '';
        slides.forEach(function (s) {
            var type = s.type === 'local' ? 'local' : 'external';
            var id = s.id || 0;
            var thumb = s.thumb_url || s.url;
            var full = s.full || s.url;
            html += '<li class="wp-pw-slide-item" data-type="' + type + '" data-id="' + id + '" data-url="' + escapeHtml(s.url) + '" data-full="' + escapeHtml(full) + '">' +
                '<span class="wp-pw-slide-handle" title="拖拽排序">&#8942;&#8942;</span>' +
                '<button type="button" class="wp-pw-slide-remove" aria-label="移除">&times;</button>' +
                '<div class="wp-pw-slide-img-box">' +
                '<img class="wp-pw-slide-thumb" src="' + escapeHtml(thumb) + '" alt="">' +
                '</div>' +
                '<span class="wp-pw-slide-badge">' + (type === 'local' ? '来自媒体库' : '来自链接') + '</span>' +
                '</li>';
        });

        $slidesList.find('.wp-pw-slide-item').remove();
        $slidesList.prepend(html);
        initSlidesSortable();
        updateSlidesUI();
    }

    // Remove Slide
    $slidesList.on('click', '.wp-pw-slide-remove', function () {
        var $item = $(this).closest('.wp-pw-slide-item');
        var id = $item.data('id');
        var type = $item.data('type');

        $item.fadeOut(200, function () {
            $(this).remove();
            if (type === 'local' && id) {
                queuePendingDeletion(id);
            }
            syncSlidesFromDOM();
        });
    });

    // Add External Slide
    $(document).on('click', '.wp-pw-add-external', function () {
        var slides = getSlides();
        if (slides.length >= slidesMax) {
            alert('海报轮播最多可添加 ' + slidesMax + ' 张图片。');
            return;
        }
        openExternalModal('slide');
    });

    // Live Carousel Preview Widget
    function initLiveCarousel() {
        updateLiveCarousel();
        clearInterval(liveCarouselTimer);
        var interval = parseInt($('#photo_wall_slides_interval').val(), 10) || 5;
        if (interval < 2) interval = 2;
        liveCarouselTimer = setInterval(function () {
            slideCarouselNext();
        }, interval * 1000);
    }

    function updateLiveCarousel() {
        var slides = getSlides();
        var $track = $('#wp-pw-live-slide-track');
        var $dots = $('#wp-pw-live-dots');

        $track.empty();
        $dots.empty();

        if (!slides.length) {
            $track.html('<div class="wp-pw-live-empty">暂未配置轮播图片</div>');
            return;
        }

        slides.forEach(function (s, idx) {
            var imgUrl = s.full || s.url || s.thumb_url;
            var activeClass = (idx === liveCarouselIndex % slides.length) ? ' is-active' : '';
            $track.append('<div class="wp-pw-live-slide' + activeClass + '" data-idx="' + idx + '"><img src="' + escapeHtml(imgUrl) + '" alt=""></div>');
            $dots.append('<div class="wp-pw-live-dot' + activeClass + '" data-idx="' + idx + '"></div>');
        });
    }

    function setCarouselIndex(idx) {
        var slides = getSlides();
        if (!slides.length) return;
        liveCarouselIndex = (idx + slides.length) % slides.length;
        $('#wp-pw-live-slide-track .wp-pw-live-slide').removeClass('is-active').filter('[data-idx="' + liveCarouselIndex + '"]').addClass('is-active');
        $('#wp-pw-live-dots .wp-pw-live-dot').removeClass('is-active').filter('[data-idx="' + liveCarouselIndex + '"]').addClass('is-active');
    }

    function slideCarouselNext() {
        setCarouselIndex(liveCarouselIndex + 1);
    }

    function slideCarouselPrev() {
        setCarouselIndex(liveCarouselIndex - 1);
    }

    $('#wp-pw-live-next').on('click', slideCarouselNext);
    $('#wp-pw-live-prev').on('click', slideCarouselPrev);
    $(document).on('click', '.wp-pw-live-dot', function () {
        setCarouselIndex(parseInt($(this).data('idx'), 10) || 0);
    });

    $('#photo_wall_slides_interval').on('change', function () {
        initLiveCarousel();
    });

    // ==========================================
    // TAB 2: 图片管理 (Photo Management) Logic
    // ==========================================
    var $groupsSidebar = $('#wp-photo-wall-groups-list');
    var $galleryContainer = $('#wp-photo-wall-groups-container');
    var $newGroupName = $('#wp-photo-wall-new-group-name');
    var $addGroupBtn = $('#wp-photo-wall-add-group-btn');
    var $searchInput = $('#wp-photo-wall-search-input');
    var $moveBtn = $('#wp-photo-wall-move-btn');
    var $bulkRemoveBtn = $('#wp-photo-wall-bulk-remove-btn');
    var $deleteMediaBtn = $('#wp-photo-wall-delete-media-btn');

    function syncDataToInputs() {
        $dataInput.val(JSON.stringify(currentData));
        $groupsInput.val(JSON.stringify(currentGroups));
        markDirty();
    }

    function renderPhotoTab() {
        renderSidebarGroups();
        renderGallery();
        updateToolbarButtons();
    }

    function getGroupCounts() {
        var counts = { uncategorized: 0 };
        currentGroups.forEach(function (g) {
            var idKey = String(g.id).trim();
            counts[idKey] = 0;
        });

        currentData.forEach(function (item) {
            var gid = item.group_id !== undefined && item.group_id !== null ? String(item.group_id).trim() : '';
            if (!gid || !counts.hasOwnProperty(gid)) {
                gid = 'uncategorized';
            }
            counts[gid] = (counts[gid] || 0) + 1;
        });
        return counts;
    }

    function renderSidebarGroups() {
        var counts = getGroupCounts();
        var html = '';

        // Pinned Uncategorized
        var uncatActive = activeGroupId === 'uncategorized' ? ' is-active' : '';
        html += '<li class="wp-pw-group-item' + uncatActive + '" data-id="uncategorized">' +
            '<span class="wp-pw-group-item-name">未分组</span>' +
            '<span class="wp-pw-group-item-count">' + (counts.uncategorized || 0) + '</span>' +
            '</li>';

        // Custom Groups
        currentGroups.forEach(function (g) {
            var gidStr = String(g.id).trim();
            var isActive = String(activeGroupId).trim() === gidStr ? ' is-active' : '';
            var count = counts[gidStr] !== undefined ? counts[gidStr] : 0;
            var tip = count > 0 ? '该分组包含 ' + count + ' 张图片，必须先清空才允许删除' : '删除分组';
            html += '<li class="wp-pw-group-item' + isActive + '" data-id="' + escapeHtml(g.id) + '">' +
                '<span class="wp-pw-group-item-name" title="' + escapeHtml(g.name) + '">' + escapeHtml(g.name) + '</span>' +
                '<span class="wp-pw-group-item-count">' + count + '</span>' +
                '<div class="wp-pw-group-item-actions">' +
                '<button type="button" class="wp-pw-group-action-btn wp-pw-delete-group' + (count > 0 ? ' has-photos' : '') + '" data-id="' + escapeHtml(g.id) + '" title="' + escapeHtml(tip) + '" aria-label="' + escapeHtml(tip) + '">&times;</button>' +
                '</div>' +
                '</li>';
        });

        $groupsSidebar.html(html);
        $('#wp-pw-groups-count-badge').text('(' + (currentGroups.length + 1) + ')');

        // Make sidebar groups droppable so dragging photos moves them!
        if ($.fn.droppable) {
            $groupsSidebar.find('.wp-pw-group-item').droppable({
                accept: '.wp-photo-wall-preview-item',
                hoverClass: 'is-drag-hover',
                drop: function (event, ui) {
                    var targetGid = $(this).data('id');
                    var itemId = ui.draggable.data('id');
                    var itemUrl = ui.draggable.data('url');

                    currentData.forEach(function (item) {
                        if ((item.type === 'local' && item.id == itemId) || (item.type === 'external' && item.url === itemUrl)) {
                            item.group_id = targetGid;
                        }
                    });

                    syncDataToInputs();
                    renderPhotoTab();
                    showToast('已移动至目标分组', 'success', 2000);
                }
            });
        }
    }

    function renderGallery() {
        var filtered = currentData.filter(function (item) {
            var gid = item.group_id || 'uncategorized';
            if (!currentGroups.some(function (g) { return g.id == gid; })) {
                gid = 'uncategorized';
            }
            if (activeGroupId && gid != activeGroupId) {
                return false;
            }
            if (searchQuery) {
                var q = searchQuery.toLowerCase();
                var matchName = (item.url || '').toLowerCase().indexOf(q) !== -1;
                return matchName;
            }
            return true;
        });

        // Update active group header
        var activeGroupName = '未分组';
        if (activeGroupId !== 'uncategorized') {
            var found = currentGroups.filter(function (g) { return g.id == activeGroupId; })[0];
            if (found) activeGroupName = found.name;
        }
        $('#wp-pw-active-group-title').text(activeGroupName);
        $('#wp-pw-active-group-count').text('(' + filtered.length + ' 张图片)');

        // Empty state
        if (!filtered.length) {
            $galleryContainer.html('<div class="wp-pw-empty-state" id="wp-photo-wall-empty-state">' +
                '<svg class="wp-pw-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>' +
                '<h4 class="wp-pw-empty-title">该分组暂无图片</h4>' +
                '<p class="wp-pw-empty-sub">点击“选择/上传图片”或“添加外部链接”开始上传</p>' +
                '<div class="wp-pw-empty-actions">' +
                '<button type="button" class="button wp-pw-btn-primary" id="wp-pw-empty-add-btn">选择/上传图片</button>' +
                '<button type="button" class="button wp-pw-btn-outline" id="wp-pw-empty-external-btn">添加外部链接</button>' +
                '</div>' +
                '</div>');
            return;
        }

        // Render photos
        var layoutClass = viewMode === 'list' ? 'wp-pw-photo-list' : 'wp-pw-photo-grid';
        var html = '<div class="' + layoutClass + '" id="wp-pw-items-list">';

        filtered.forEach(function (item) {
            var isLocal = item.type === 'local';
            var thumb = item.thumb_url || item.url;
            var full = item.url;
            var idVal = isLocal ? item.id : 'ext';

            html += '<div class="wp-photo-wall-preview-item" data-type="' + (isLocal ? 'local' : 'external') + '" data-id="' + (isLocal ? item.id : '') + '" data-url="' + escapeHtml(item.url) + '">' +
                '<span class="wp-photo-wall-badge" title="' + (isLocal ? '来自媒体库' : '来自链接') + '">' +
                '<span class="dashicons ' + (isLocal ? 'dashicons-admin-media' : 'dashicons-admin-links') + '"></span>' +
                '</span>' +
                '<input type="checkbox" class="photo-wall-checkbox" value="' + idVal + '">' +
                '<img src="' + escapeHtml(thumb) + '" alt="" loading="lazy">' +
                '<button type="button" class="photo-wall-remove" title="从图片墙移除">&times;</button>' +
                '</div>';
        });

        html += '</div>';
        $galleryContainer.html(html);

        // Make items sortable
        if ($.fn.sortable) {
            $('#wp-pw-items-list').sortable({
                items: '.wp-photo-wall-preview-item',
                tolerance: 'pointer',
                update: function () {
                    // Reorder currentData according to DOM order
                    var orderedUrls = [];
                    $('#wp-pw-items-list .wp-photo-wall-preview-item').each(function () {
                        orderedUrls.push($(this).data('url'));
                    });

                    currentData.sort(function (a, b) {
                        var idxA = orderedUrls.indexOf(a.url);
                        var idxB = orderedUrls.indexOf(b.url);
                        if (idxA === -1 || idxB === -1) return 0;
                        return idxA - idxB;
                    });

                    syncDataToInputs();
                }
            });
        }
    }

    // Switch Sidebar Group
    $groupsSidebar.on('click', '.wp-pw-group-item', function (e) {
        if ($(e.target).closest('.wp-pw-delete-group').length) return;
        activeGroupId = $(this).data('id');
        renderPhotoTab();
    });

    // Add Group
    function addGroupAction() {
        var name = $.trim($newGroupName.val());
        if (!name) {
            showToast('请输入分组名称', 'error');
            $newGroupName.focus();
            return;
        }

        var exists = currentGroups.some(function (g) { return g.name.toLowerCase() === name.toLowerCase(); });
        if (exists || name === '未分组') {
            showToast('分组名称已存在', 'error');
            return;
        }

        var newId = 'grp_' + Date.now();
        currentGroups.push({ id: newId, name: name });
        $newGroupName.val('');
        activeGroupId = newId;
        syncDataToInputs();
        renderPhotoTab();
        showToast('分组「' + name + '」创建成功', 'success');
    }

    $addGroupBtn.on('click', addGroupAction);
    $newGroupName.on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            addGroupAction();
        }
    });

    // Delete Group (strictly forbidden unless all photos inside are cleared)
    $groupsSidebar.on('click', '.wp-pw-delete-group', function (e) {
        e.stopPropagation();
        var gid = $(this).data('id');
        if (gid === undefined || gid === null) return;
        var gidStr = String(gid).trim();

        // Strict dual check: scan currentData directly for both local and external items
        var photosInGroup = currentData.filter(function (item) {
            var itemGid = item.group_id !== undefined && item.group_id !== null ? String(item.group_id).trim() : '';
            return itemGid === gidStr;
        });

        if (photosInGroup.length > 0) {
            showToast('该分组下仍有 ' + photosInGroup.length + ' 张图片（包含本地或外链图片），必须先清空或移出所有图片后才允许删除此分组！', 'warning', 4000);
            return;
        }

        var targetGroup = currentGroups.find(function (g) { return String(g.id).trim() === gidStr; });
        var groupName = targetGroup ? targetGroup.name : '该分组';

        wpPwConfirm({
            title: '删除分组',
            message: '确定要删除空分组「' + groupName + '」吗？（该分组已清空）',
            danger: true
        }, function () {
            currentGroups = currentGroups.filter(function (g) { return String(g.id).trim() !== gidStr; });
            // Safety guard: reassign any potential orphan items to 'uncategorized'
            currentData.forEach(function (item) {
                if (item.group_id !== undefined && String(item.group_id).trim() === gidStr) {
                    item.group_id = 'uncategorized';
                }
            });
            if (String(activeGroupId).trim() === gidStr) {
                activeGroupId = 'uncategorized';
            }
            syncDataToInputs();
            renderPhotoTab();
            showToast('分组「' + groupName + '」已成功删除', 'success');
        });
    });

    // Search Filter
    $searchInput.on('input', function () {
        searchQuery = $.trim($(this).val());
        renderGallery();
    });

    // View Toggle
    $('#wp-pw-view-grid').on('click', function () {
        viewMode = 'grid';
        $('#wp-pw-view-grid').addClass('is-active');
        $('#wp-pw-view-list').removeClass('is-active');
        renderGallery();
    });

    $('#wp-pw-view-list').on('click', function () {
        viewMode = 'list';
        $('#wp-pw-view-list').addClass('is-active');
        $('#wp-pw-view-grid').removeClass('is-active');
        renderGallery();
    });

    // Checkbox Selection & Batch Buttons
    function updateToolbarButtons() {
        var checkedCount = $galleryContainer.find('.photo-wall-checkbox:checked').length;
        var hasSelection = checkedCount > 0;
        $moveBtn.prop('disabled', !hasSelection);
        $bulkRemoveBtn.prop('disabled', !hasSelection);
        $deleteMediaBtn.prop('disabled', !hasSelection);
    }

    $galleryContainer.on('change', '.photo-wall-checkbox', function () {
        var $item = $(this).closest('.wp-photo-wall-preview-item');
        $item.toggleClass('is-selected', $(this).is(':checked'));
        updateToolbarButtons();
    });

    // Add Local Photos to Wall
    var wallMediaFrame = null;
    $(document).on('click', '#wp-photo-wall-add-btn, #wp-pw-empty-add-btn', function (e) {
        e.preventDefault();
        if (typeof wp === 'undefined' || !wp.media) {
            alert('WordPress 媒体库不可用。');
            return;
        }

        if (wallMediaFrame) {
            wallMediaFrame.open();
            return;
        }

        wallMediaFrame = wp.media({
            title: '选择图片添加到图片墙',
            button: { text: '添加到图片墙' },
            multiple: true
        });

        wallMediaFrame.on('select', function () {
            var selection = wallMediaFrame.state().get('selection');
            var targetGid = activeGroupId || 'uncategorized';

            selection.map(function (att) {
                var d = att.toJSON();
                var thumb = d.url;
                if (d.sizes) {
                    if (d.sizes.medium_large) thumb = d.sizes.medium_large.url;
                    else if (d.sizes.thumbnail) thumb = d.sizes.thumbnail.url;
                }
                unqueuePendingDeletion(d.id);
                currentData.push({
                    type: 'local',
                    id: d.id,
                    url: d.url,
                    thumb_url: thumb,
                    group_id: targetGid
                });
            });

            syncDataToInputs();
            renderPhotoTab();
            showToast('已添加所选图片', 'success');
        });

        wallMediaFrame.open();
    });

    // Add External Image Modal
    var externalTarget = 'wall'; // 'wall' | 'slide'
    function openExternalModal(target) {
        externalTarget = target;
        $('#wp-photo-wall-external-url').val('');
        $('#wp-photo-wall-external-preview').html('<span class="wp-pw-preview-placeholder">输入有效图片链接后预览将在此显示</span>');
        $('#wp-photo-wall-external-confirm').prop('disabled', true);
        $externalModal.prop('hidden', false);
    }

    $(document).on('click', '#wp-photo-wall-add-external-btn, #wp-pw-empty-external-btn', function () {
        openExternalModal('wall');
    });

    $('#wp-pw-external-close, #wp-photo-wall-external-close, #wp-photo-wall-external-cancel').on('click', function () {
        $externalModal.prop('hidden', true);
    });

    $('#wp-photo-wall-external-url').on('input', function () {
        var url = $.trim($(this).val());
        var $preview = $('#wp-photo-wall-external-preview');
        var $btn = $('#wp-photo-wall-external-confirm');

        if (/^https?:\/\/.+/i.test(url)) {
            $preview.html('<img src="' + escapeHtml(url) + '" alt="预览" style="max-height:160px;">');
            $btn.prop('disabled', false);
        } else {
            $preview.html('<span class="wp-pw-preview-placeholder">链接格式无效</span>');
            $btn.prop('disabled', true);
        }
    });

    $('#wp-photo-wall-external-confirm').on('click', function () {
        var url = $.trim($('#wp-photo-wall-external-url').val());
        if (!url) return;

        if (externalTarget === 'slide') {
            var slides = getSlides();
            slides.push({
                type: 'external',
                url: url,
                full: url,
                thumb_url: url
            });
            renderSlidesList(slides);
            setSlides(slides);
            showToast('已添加轮播海报', 'success');
        } else {
            var targetGid = activeGroupId || 'uncategorized';
            currentData.push({
                type: 'external',
                url: url,
                thumb_url: url,
                group_id: targetGid
            });
            syncDataToInputs();
            renderPhotoTab();
            showToast('已添加外部链接图片', 'success');
        }

        $externalModal.prop('hidden', true);
    });

    // Move Selected Modal
    $moveBtn.on('click', function () {
        var $select = $('#wp-photo-wall-move-target');
        $select.empty().append('<option value="">请选择目标分组</option>');
        $select.append('<option value="uncategorized">未分组</option>');
        currentGroups.forEach(function (g) {
            $select.append('<option value="' + escapeHtml(g.id) + '">' + escapeHtml(g.name) + '</option>');
        });
        $('#wp-photo-wall-move-confirm').prop('disabled', true);
        $moveModal.prop('hidden', false);
    });

    $('#wp-pw-move-close, #wp-photo-wall-move-cancel').on('click', function () {
        $moveModal.prop('hidden', true);
    });

    $('#wp-photo-wall-move-target').on('change', function () {
        $('#wp-photo-wall-move-confirm').prop('disabled', !$(this).val());
    });

    $('#wp-photo-wall-move-confirm').on('click', function () {
        var targetGid = $('#wp-photo-wall-move-target').val();
        if (!targetGid) return;

        var selectedUrls = [];
        $galleryContainer.find('.photo-wall-checkbox:checked').each(function () {
            selectedUrls.push($(this).closest('.wp-photo-wall-preview-item').data('url'));
        });

        currentData.forEach(function (item) {
            if (selectedUrls.indexOf(item.url) !== -1) {
                item.group_id = targetGid;
            }
        });

        syncDataToInputs();
        $moveModal.prop('hidden', true);
        renderPhotoTab();
        showToast('成功移动 ' + selectedUrls.length + ' 张图片', 'success');
    });

    // Remove single item from wall
    $galleryContainer.on('click', '.photo-wall-remove', function () {
        var $item = $(this).closest('.wp-photo-wall-preview-item');
        var url = $item.data('url');
        var id = $item.data('id');
        var type = $item.data('type');

        currentData = currentData.filter(function (d) { return d.url !== url; });
        if (type === 'local' && id) {
            queuePendingDeletion(id);
        }
        syncDataToInputs();
        renderPhotoTab();
        showToast('已从图片墙移除', 'info');
    });

    // Bulk Remove Selected
    $bulkRemoveBtn.on('click', function () {
        var $checked = $galleryContainer.find('.photo-wall-checkbox:checked');
        if (!$checked.length) return;

        wpPwConfirm({
            title: '批量移除图片',
            message: '确认将选中的 ' + $checked.length + ' 张图片从图片墙移除吗？',
            danger: true
        }, function () {
            var selectedUrls = [];
            $checked.each(function () {
                var $item = $(this).closest('.wp-photo-wall-preview-item');
                selectedUrls.push($item.data('url'));
                if ($item.data('type') === 'local') {
                    queuePendingDeletion($item.data('id'));
                }
            });

            currentData = currentData.filter(function (d) {
                return selectedUrls.indexOf(d.url) === -1;
            });

            syncDataToInputs();
            renderPhotoTab();
            showToast('已移除选中的 ' + selectedUrls.length + ' 张图片', 'info');
        });
    });

    // Delete from Media Library (Permanent)
    $deleteMediaBtn.on('click', function () {
        var $checked = $galleryContainer.find('.photo-wall-checkbox:checked');
        if (!$checked.length) return;

        wpPwConfirm({
            title: '从媒体库永久删除',
            message: '警告：选中的本地媒体库图片将在「保存更改」后从服务器彻底永久删除，此操作不可撤销！',
            danger: true
        }, function () {
            var selectedUrls = [];
            $checked.each(function () {
                var $item = $(this).closest('.wp-photo-wall-preview-item');
                selectedUrls.push($item.data('url'));
                if ($item.data('type') === 'local') {
                    queuePendingDeletion($item.data('id'));
                }
            });

            currentData = currentData.filter(function (d) {
                return selectedUrls.indexOf(d.url) === -1;
            });

            syncDataToInputs();
            renderPhotoTab();
            showToast('已加入永久删除队列，保存更改后生效', 'info');
        });
    });

    // Clear All Button
    $('#wp-photo-wall-clear-btn').on('click', function () {
        wpPwConfirm({
            title: '清空所有内容',
            message: '确定要清空图片墙中的所有分组和图片吗？此操作无法撤销！',
            danger: true
        }, function () {
            currentData.forEach(function (d) {
                if (d.type === 'local' && d.id) {
                    queuePendingDeletion(d.id);
                }
            });
            currentData = [];
            currentGroups = [];
            activeGroupId = 'uncategorized';
            syncDataToInputs();
            renderPhotoTab();
            showToast('已清空所有内容', 'info');
        });
    });

    // ==========================================
    // TAB 3: 必应壁纸 (Bing Wallpaper) Logic
    // ==========================================
    var $bingApiInput = $('#photo_wall_bing_api');
    var $bingTitleInput = $('#photo_wall_bing_title');
    var $bingHourSelect = $('#photo_wall_bing_cron_hour');
    var $bingList = $('#wp-photo-wall-bing-list');
    var $bingOrderInput = $('#photo_wall_bing_order');
    var $bingStatusPill = $('#wp-pw-bing-status-pill');

    // Switch toggle label reflection
    $('#photo_wall_bing_enabled').on('change', function () {
        var on = $(this).is(':checked');
        $bingStatusPill.text(on ? '已启用' : '已关闭').toggleClass('is-active', on);
    });

    // Test API URL Connection
    $('#wp-pw-bing-test-api').on('click', function () {
        var url = $.trim($bingApiInput.val()) || $bingApiInput.attr('placeholder');
        var $btn = $(this);
        $btn.prop('disabled', true).text('检测中...');

        $.ajax({
            url: wp_photo_wall_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wp_photo_wall_test_connection',
                nonce: wp_photo_wall_ajax.bing_nonce,
                url: url,
                type: 'bing'
            },
            success: function (res) {
                $btn.prop('disabled', false).text('测试连接');
                if (res.success) {
                    showToast(res.data.message || 'API 接口连通正常！', 'success');
                } else {
                    showToast(res.data || '连接测试失败', 'error');
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('测试连接');
                showToast('网络请求异常，请检查网络或重试', 'error');
            }
        });
    });

    // Reset API URL
    $('#wp-pw-bing-reset-api').on('click', function () {
        $bingApiInput.val($bingApiInput.attr('placeholder'));
        markDirty();
        showToast('已恢复为默认接口地址', 'info');
    });

    // Test Download URL Connection
    $('#wp-pw-bing-test-download').on('click', function () {
        var url = $.trim($('#photo_wall_bing_download_url').val());
        if (!url) {
            showToast('请先输入下载按钮链接', 'error');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).text('检测中...');

        $.ajax({
            url: wp_photo_wall_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wp_photo_wall_test_connection',
                nonce: wp_photo_wall_ajax.bing_nonce,
                url: url,
                type: 'general'
            },
            success: function (res) {
                $btn.prop('disabled', false).text('测试连接');
                if (res.success) {
                    showToast(res.data.message || '目标链接正常可访问！', 'success');
                } else {
                    showToast(res.data || '下载链接测试失败', 'error');
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('测试连接');
                showToast('请求超时或目标链接不可达', 'error');
            }
        });
    });

    // Refresh Bing Wallpapers Once
    $('#wp-photo-wall-bing-refresh').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).text('正在同步...');

        $.ajax({
            url: wp_photo_wall_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wp_photo_wall_bing_refresh',
                nonce: wp_photo_wall_ajax.bing_nonce,
                api: $bingApiInput.val()
            },
            success: function (res) {
                $btn.prop('disabled', false).text('立即执行一次');
                if (res.success) {
                    $('#wp-photo-wall-bing-status').text(res.data.updated || '刚刚更新');
                    showToast(res.data.message || '壁纸已成功更新！', 'success');
                    if (res.data.items && res.data.items.length) {
                        var html = '';
                        res.data.items.forEach(function (item) {
                            var label = item.date ? (item.date + (item.title ? ' · ' + item.title : '')) : (item.title || '');
                            html += '<li class="wp-pw-bing-item" data-key="' + escapeHtml(item.key) + '">' +
                                '<span class="wp-pw-bing-handle" title="拖拽排序">&#8942;&#8942;</span>' +
                                '<div class="wp-pw-bing-thumb-box"><img class="wp-pw-bing-thumb" src="' + escapeHtml(item.thumb) + '" alt="" loading="lazy"></div>' +
                                '<span class="wp-pw-bing-meta" title="' + escapeHtml(label) + '">' + escapeHtml(label) + '</span>' +
                                '</li>';
                        });
                        $bingList.html(html);
                        initBingSortable();
                    }
                } else {
                    showToast(res.data || '拉取壁纸失败', 'error');
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('立即执行一次');
                showToast('拉取壁纸请求失败', 'error');
            }
        });
    });

    // Reset Bing Order to Time Descending
    $('#wp-photo-wall-bing-reset').on('click', function () {
        $bingOrderInput.val('[]');
        markDirty();
        showToast('已恢复时间排序，点击保存后生效', 'info');
    });

    // Reset All Bing Settings to Default
    $('#wp-pw-bing-reset-all').on('click', function () {
        wpPwConfirm({
            title: '重置必应壁纸设置',
            message: '确认将必应壁纸的所有设置项重置为默认值吗？'
        }, function () {
            $bingApiInput.val($bingApiInput.attr('placeholder'));
            $bingTitleInput.val('必应壁纸');
            $('#photo_wall_bing_cron_enabled').prop('checked', true);
            $bingHourSelect.val('1');
            $('#photo_wall_bing_download_enabled').prop('checked', false);
            $('#photo_wall_bing_download_url').val('');
            $('#photo_wall_bing_download_text').val('');
            $bingOrderInput.val('[]');
            markDirty();
            showToast('已重置必应壁纸全部设置为默认值', 'info');
        });
    });

    function initBingSortable() {
        if ($.fn.sortable && $bingList.length) {
            $bingList.sortable({
                handle: '.wp-pw-bing-handle',
                items: '.wp-pw-bing-item',
                tolerance: 'pointer',
                update: function () {
                    var keys = [];
                    $bingList.find('.wp-pw-bing-item').each(function () {
                        keys.push(String($(this).data('key')));
                    });
                    $bingOrderInput.val(JSON.stringify(keys));
                    markDirty();
                }
            });
        }
    }

    // ==========================================
    // Initialization
    // ==========================================
    renderPhotoTab();
    initSlidesSortable();
    initBingSortable();
    updateSlidesUI();
    initLiveCarousel();

    setTimeout(function () {
        initialized = true;
    }, 400);
});
