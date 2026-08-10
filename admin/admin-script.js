jQuery(document).ready(function ($) {
    var frame;
    var $groupsContainer = $('#wp-photo-wall-groups-container');
    var $dataInput = $('#photo_wall_data');
    var $groupsInput = $('#photo_wall_groups');
    var $addBtn = $('#wp-photo-wall-add-btn');
    var $clearBtn = $('#wp-photo-wall-clear-btn');
    var $bulkRemoveBtn = $('#wp-photo-wall-bulk-remove-btn');
    var $deleteMediaBtn = $('#wp-photo-wall-delete-media-btn');
    var $moveBtn = $('#wp-photo-wall-move-btn');
    var $unsavedNotice = $('#wp-photo-wall-unsaved');

    function escapeHtml(value) {
        return $('<div>').text(String(value || '')).html();
    }

    // Group UI
    var $newGroupNameInput = $('#wp-photo-wall-new-group-name');
    var $addGroupBtn = $('#wp-photo-wall-add-group-btn');

    // External Image UI
    var $addExternalBtn = $('#wp-photo-wall-add-external-btn');
    var $externalModal = $('#wp-photo-wall-external-modal');
    var $externalInput = $('#wp-photo-wall-external-url');
    var $externalPreview = $('#wp-photo-wall-external-preview');
    var $externalConfirm = $('#wp-photo-wall-external-confirm');
    var $externalCancel = $('#wp-photo-wall-external-cancel');

    // Move Modal UI
    var $moveModal = $('#wp-photo-wall-move-modal');
    var $moveTarget = $('#wp-photo-wall-move-target');
    var $moveConfirm = $('#wp-photo-wall-move-confirm');
    var $moveCancel = $('#wp-photo-wall-move-cancel');

    // Globals
    var currentData = [];
    var currentGroups = [];
    var isDirty = false;
    var initialized = false;

    // Safely Parse Data
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

    if (typeof wp_photo_wall_ajax === 'undefined') {
        console.error('Photo Wall: Localization object not found.');
        return;
    }

    renderAll();

    // Mark as initialized after a short delay to ignore any sortable init events
    setTimeout(function () { initialized = true; }, 300);

    // ==========================================
    // Unsaved Changes Tracking
    // ==========================================
    function markDirty() {
        if (!initialized) return;
        if (!isDirty) {
            isDirty = true;
            $unsavedNotice.addClass('is-visible');
            $('#submit').addClass('wp-photo-wall-btn-pulse');
        }
    }

    function markClean() {
        isDirty = false;
        $unsavedNotice.removeClass('is-visible');
        $('#submit').removeClass('wp-photo-wall-btn-pulse');
    }

    // Warn before leaving with unsaved changes
    $(window).on('beforeunload', function (e) {
        if (isDirty) {
            e.preventDefault();
            return '';
        }
    });

    // Clear dirty on form submit
    $('#wp-photo-wall-form').on('submit', function () {
        markClean();
    });

    // ==========================================
    // Core Rendering Logic
    // ==========================================
    function renderAll() {
        try {
            var allGroupsHtml = '';

            var groupsToRender = [];
            if (Array.isArray(currentGroups)) {
                groupsToRender = currentGroups.slice();
            }
            // Pin Uncategorized at top
            groupsToRender.unshift({
                id: 'uncategorized',
                name: (wp_photo_wall_ajax.labels.uncategorized || 'Uncategorized'),
                isDefault: true
            });

            // Group items by group_id
            var itemsByGroup = {};
            if (Array.isArray(currentData)) {
                currentData.forEach(function (item) {
                    var groupId = item.group_id;
                    if (!groupId || groupId === '' || (groupId !== 'uncategorized' && !currentGroups.some(function (g) { return g.id == groupId; }))) {
                        groupId = 'uncategorized';
                        item.group_id = 'uncategorized';
                    }
                    if (!itemsByGroup[groupId]) itemsByGroup[groupId] = [];
                    itemsByGroup[groupId].push(item);
                });
            }

            // Build HTML
            groupsToRender.forEach(function (group) {
                allGroupsHtml += buildGroupHtml(group, itemsByGroup[group.id] || []);
            });

            $groupsContainer.html(allGroupsHtml);

            // Init Sortable for items and groups
            if ($.fn.sortable) {
                initSortable();
            }

            updateBulkBtnState();
            updateMoveTargetOptions();

        } catch (err) {
            console.error('Photo Wall: rendering error', err);
        } finally {
            $('#wp-photo-wall-loading').remove();
        }
    }

    function buildGroupHtml(group, items) {
        var safeId = escapeHtml(group.id);
        var html = '<div class="wp-photo-wall-group" data-group-id="' + safeId + '">';
        html += '<div class="wp-photo-wall-group-header">';
        html += '<span class="wp-photo-wall-group-drag-handle dashicons dashicons-menu"></span>';
        html += '<h4 class="wp-photo-wall-group-title">' + escapeHtml(group.name) + '</h4>';
        if (!group.isDefault) {
            html += '<button type="button" class="wp-photo-wall-delete-group" data-id="' + safeId + '">' + escapeHtml(wp_photo_wall_ajax.labels.delete_group || 'Delete Group') + '</button>';
        }
        html += '</div>';
        // Warning for uncategorized
        if (group.id === 'uncategorized') {
            html += '<div class="wp-photo-wall-uncategorized-warning">';
            html += '<span class="dashicons dashicons-warning"></span> ';
            html += escapeHtml(wp_photo_wall_ajax.labels.uncategorized_warning);
            html += '</div>';
        }
        html += '<div class="wp-photo-wall-group-items">';

        items.forEach(function (item) {
            html += buildItemHtml(item);
        });

        html += '</div>';
        html += '</div>';
        return html;
    }

    function buildItemHtml(item) {
        var html = '';

        if (item.type === 'local') {
            var thumbUrl = item.thumb_url || '';
            html = '<div class="wp-photo-wall-preview-item" data-type="local" data-id="' + item.id + '">';
            html += '<span class="wp-photo-wall-badge wp-photo-wall-badge-local" title="' + escapeHtml(wp_photo_wall_ajax.labels.local) + '"><span class="dashicons dashicons-admin-media"></span></span>';
            html += '<input type="checkbox" class="photo-wall-checkbox" value="' + item.id + '">';
            if (thumbUrl) {
                html += '<img src="' + escapeHtml(thumbUrl) + '" decoding="async" loading="lazy">';
            } else {
                html += '<img src="" style="min-height:50px; background:#eee;" decoding="async" loading="lazy">';
            }
            html += '<button type="button" class="photo-wall-remove" aria-label="Remove">&times;</button>';
            html += '</div>';
        } else {
            var safeUrl = escapeHtml(item.url);
            html = '<div class="wp-photo-wall-preview-item" data-type="external" data-url="' + safeUrl + '">';
            html += '<span class="wp-photo-wall-badge wp-photo-wall-badge-external" title="' + escapeHtml(wp_photo_wall_ajax.labels.external) + '"><span class="dashicons dashicons-admin-links"></span></span>';
            html += '<input type="checkbox" class="photo-wall-checkbox" value="ext">';
            html += '<img src="' + safeUrl + '" decoding="async" loading="lazy">';
            html += '<button type="button" class="photo-wall-remove" aria-label="Remove">&times;</button>';
            html += '</div>';
        }
        return html;
    }

    function initSortable() {
        // Sortable for items within/between groups
        $('.wp-photo-wall-group-items').sortable({
            connectWith: '.wp-photo-wall-group-items',
            placeholder: 'ui-sortable-placeholder',
            forcePlaceholderSize: true,
            tolerance: 'pointer',
            update: function () {
                updateDataFromDOM();
                markDirty();
            }
        }).disableSelection();

        // Sortable for group order (drag by handle)
        $groupsContainer.sortable({
            handle: '.wp-photo-wall-group-drag-handle',
            items: '.wp-photo-wall-group:not([data-group-id="uncategorized"])',
            placeholder: 'wp-photo-wall-group-placeholder',
            forcePlaceholderSize: true,
            tolerance: 'pointer',
            update: function () {
                updateGroupOrderFromDOM();
                markDirty();
            }
        });
    }

    // ==========================================
    // Data Persistence
    // ==========================================
    function updateDataFromDOM() {
        var newData = [];

        $('.wp-photo-wall-group').each(function () {
            var groupId = $(this).data('group-id');
            $(this).find('.wp-photo-wall-preview-item').each(function () {
                var type = $(this).data('type');
                var item = { type: type, group_id: groupId };
                if (type === 'local') {
                    item.id = $(this).data('id');
                    // Preserve thumb_url
                    var $img = $(this).find('img');
                    if ($img.attr('src')) item.thumb_url = $img.attr('src');
                } else {
                    item.url = $(this).data('url');
                }
                newData.push(item);
            });
        });

        currentData = newData;
        $dataInput.val(JSON.stringify(currentData));
        $groupsInput.val(JSON.stringify(currentGroups));
    }

    function updateGroupOrderFromDOM() {
        var newGroups = [];
        $('.wp-photo-wall-group').each(function () {
            var groupId = $(this).data('group-id');
            if (groupId === 'uncategorized') return;
            for (var i = 0; i < currentGroups.length; i++) {
                if (currentGroups[i].id === groupId) {
                    newGroups.push(currentGroups[i]);
                    break;
                }
            }
        });
        currentGroups = newGroups;
        $groupsInput.val(JSON.stringify(currentGroups));
    }

    // ==========================================
    // Group Management
    // ==========================================
    $addGroupBtn.on('click', function () {
        var name = $newGroupNameInput.val().trim();
        if (!name) return;

        var newGroup = {
            id: 'g_' + new Date().getTime().toString(36),
            name: name
        };

        currentGroups.push(newGroup);
        $groupsInput.val(JSON.stringify(currentGroups));

        renderAll();
        $newGroupNameInput.val('');
        markDirty();
    });

    $(document).on('click', '.wp-photo-wall-delete-group', function () {
        var groupId = $(this).data('id');

        var hasItems = currentData.some(function (item) {
            return item.group_id == groupId;
        });

        if (hasItems) {
            alert(wp_photo_wall_ajax.labels.group_not_empty);
            return;
        }

        if (!confirm(wp_photo_wall_ajax.labels.delete_group_confirm)) {
            return;
        }

        currentGroups = currentGroups.filter(function (g) {
            return g.id != groupId;
        });
        $groupsInput.val(JSON.stringify(currentGroups));

        currentData.forEach(function (item) {
            if (item.group_id == groupId) {
                item.group_id = 'uncategorized';
            }
        });
        $dataInput.val(JSON.stringify(currentData));

        renderAll();
        markDirty();
    });

    // ==========================================
    // Image Actions
    // ==========================================

    // Add Local
    $(document).on('click', '#wp-photo-wall-add-btn', function (e) {
        e.preventDefault();
        if (frame) { frame.open(); return; }

        if (typeof wp === 'undefined' || !wp.media) {
            alert('WordPress Media Library is not available.');
            return;
        }

        frame = wp.media({
            title: 'Select Photos',
            button: { text: 'Add to Wall' },
            multiple: true
        });

        frame.on('select', function () {
            var selection = frame.state().get('selection');

            selection.map(function (attachment) {
                attachment = attachment.toJSON();
                var thumbUrl = '';
                if (attachment.sizes) {
                    if (attachment.sizes.thumbnail) thumbUrl = attachment.sizes.thumbnail.url;
                    else if (attachment.sizes.medium) thumbUrl = attachment.sizes.medium.url;
                    else thumbUrl = attachment.url;
                } else {
                    thumbUrl = attachment.url;
                }
                currentData.push({ type: 'local', id: attachment.id, group_id: 'uncategorized', thumb_url: thumbUrl });
            });

            renderAll();
            updateDataFromDOM();
            markDirty();
        });
        frame.open();
    });

    // Add External
    $(document).on('click', '#wp-photo-wall-add-external-btn', function (e) {
        e.preventDefault();
        $externalModal.prop('hidden', false);
        $externalInput.val('').focus();
        $externalPreview.html(escapeHtml(wp_photo_wall_ajax.labels.preview));
        $externalConfirm.prop('disabled', true);
    });

    $externalCancel.on('click', function () {
        $externalModal.prop('hidden', true);
    });

    var checkTimeout;
    $externalInput.on('input', function () {
        var url = $(this).val().trim();
        clearTimeout(checkTimeout);
        $externalConfirm.prop('disabled', true);
        if (!url) return;

        var urlPattern = /^https?:\/\/.+/i;
        if (!urlPattern.test(url)) {
            $externalPreview.html('<span style="color:red">' + escapeHtml(wp_photo_wall_ajax.labels.invalid_url) + '</span>');
            return;
        }

        $externalPreview.html(escapeHtml(wp_photo_wall_ajax.labels.checking));
        checkTimeout = setTimeout(function () {
            var img = new Image();
            img.onload = function () {
                $externalPreview.html('<img src="' + escapeHtml(url) + '" style="max-height:100px;">');
                $externalConfirm.prop('disabled', false);
            };
            img.onerror = function () {
                $externalPreview.html('<span style="color:red">' + escapeHtml(wp_photo_wall_ajax.labels.invalid_url) + '</span>');
            };
            img.src = url;
        }, 500);
    });

    $externalConfirm.on('click', function () {
        var url = $externalInput.val().trim();
        if (url) {
            currentData.push({ type: 'external', url: url, group_id: 'uncategorized' });
            renderAll();
            updateDataFromDOM();
            $externalModal.prop('hidden', true);
            markDirty();
        }
    });

    // Remove Item
    $(document).on('click', '.photo-wall-remove', function (e) {
        e.preventDefault();
        $(this).parent().remove();
        updateDataFromDOM();
        updateBulkBtnState();
        markDirty();
    });

    // Clear All (with typed confirmation)
    $(document).on('click', '#wp-photo-wall-clear-btn', function (e) {
        e.preventDefault();
        var input = prompt(wp_photo_wall_ajax.labels.clear_all_confirm_text);
        if (input && input.trim().toUpperCase() === 'CONFIRM') {
            currentData = [];
            renderAll();
            updateDataFromDOM();
            markDirty();
        }
    });

    // ==========================================
    // Bulk Selection
    // ==========================================
    $(document).on('change', '.photo-wall-checkbox', function () {
        updateBulkBtnState();
    });

    function updateBulkBtnState() {
        var $checked = $('.photo-wall-checkbox:checked');
        var hasChecked = $checked.length > 0;
        $bulkRemoveBtn.prop('disabled', !hasChecked);
        $deleteMediaBtn.prop('disabled', $checked.closest('.wp-photo-wall-preview-item[data-type="local"]').length === 0);
        $moveBtn.prop('disabled', !hasChecked);
    }

    function updateMoveTargetOptions() {
        var html = '<option value="">' + escapeHtml(wp_photo_wall_ajax.labels.select_target_group) + '</option>';
        currentGroups.forEach(function (group) {
            html += '<option value="' + escapeHtml(group.id) + '">' + escapeHtml(group.name) + '</option>';
        });
        html += '<option value="uncategorized">' + escapeHtml(wp_photo_wall_ajax.labels.uncategorized) + '</option>';
        $moveTarget.html(html);
    }

    // ==========================================
    // Move Selected (button-triggered modal)
    // ==========================================
    $moveBtn.on('click', function () {
        var $checked = $('.photo-wall-checkbox:checked');
        if (!$checked.length) return;
        updateMoveTargetOptions();
        $moveTarget.val('');
        $moveConfirm.prop('disabled', true);
        $moveModal.prop('hidden', false);
    });

    $moveTarget.on('change', function () {
        $moveConfirm.prop('disabled', !$(this).val());
    });

    $moveCancel.on('click', function () {
        $moveModal.prop('hidden', true);
    });

    $moveConfirm.on('click', function () {
        var targetGroupId = $moveTarget.val();
        if (!targetGroupId) return;

        var $checked = $('.photo-wall-checkbox:checked');
        if (!$checked.length) return;

        var targetGroupName = wp_photo_wall_ajax.labels.uncategorized;
        if (targetGroupId !== 'uncategorized') {
            currentGroups.forEach(function (g) {
                if (g.id === targetGroupId) targetGroupName = g.name;
            });
        }

        var movedCount = 0;
        $checked.each(function () {
            var $item = $(this).closest('.wp-photo-wall-preview-item');
            var type = $item.data('type');
            var itemId = (type === 'local') ? $item.data('id') : null;
            var itemUrl = (type === 'external') ? $item.data('url') : null;

            for (var i = 0; i < currentData.length; i++) {
                var d = currentData[i];
                if (type === 'local' && d.type === 'local' && d.id == itemId) {
                    if (d.group_id !== targetGroupId) {
                        d.group_id = targetGroupId;
                        movedCount++;
                    }
                    break;
                } else if (type === 'external' && d.type === 'external' && d.url === itemUrl) {
                    if (d.group_id !== targetGroupId) {
                        d.group_id = targetGroupId;
                        movedCount++;
                    }
                    break;
                }
            }
        });

        $moveModal.prop('hidden', true);

        if (movedCount > 0) {
            $dataInput.val(JSON.stringify(currentData));
            renderAll();
            markDirty();
            var resultMsg = wp_photo_wall_ajax.labels.moved_count.replace(/%d/g, movedCount).replace(/%s/g, targetGroupName);
            alert(resultMsg);
        }
    });

    // ==========================================
    // Bulk Remove
    // ==========================================
    $(document).on('click', '#wp-photo-wall-bulk-remove-btn', function (e) {
        e.preventDefault();
        var $checked = $('.photo-wall-checkbox:checked');
        if (!$checked.length) return;
        if (confirm(wp_photo_wall_ajax.labels.remove_confirm.replace(/%d/g, $checked.length))) {
            $checked.closest('.wp-photo-wall-preview-item').remove();
            updateDataFromDOM();
            updateBulkBtnState();
            markDirty();
        }
    });

    // ==========================================
    // Delete from Media Library
    // ==========================================
    $(document).on('click', '#wp-photo-wall-delete-media-btn', function (e) {
        e.preventDefault();
        var $checked = $('.photo-wall-checkbox:checked');
        if ($checked.length === 0) return;

        var localIds = [];
        var $localItems = $();
        $checked.each(function () {
            var $p = $(this).closest('.wp-photo-wall-preview-item');
            if ($p.data('type') === 'local') {
                localIds.push($p.data('id'));
                $localItems = $localItems.add($p);
            }
        });

        if (localIds.length > 0) {
            var doDelete = confirm(wp_photo_wall_ajax.labels.delete_server_confirm.replace(/%d/g, localIds.length));

            if (doDelete) {
                $.ajax({
                    url: wp_photo_wall_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'delete_photo_wall_image',
                        attachment_ids: localIds,
                        security: wp_photo_wall_ajax.nonce
                    },
                    success: function (res) {
                        if (res.success) {
                            $localItems.remove();
                            updateDataFromDOM();
                            updateBulkBtnState();
                            markDirty();
                            alert(wp_photo_wall_ajax.labels.done);
                        } else {
                            alert(res.data);
                        }
                    },
                    error: function () {
                        alert(wp_photo_wall_ajax.labels.ajax_error);
                    }
                });
            }
        }
    });

});
