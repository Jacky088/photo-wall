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
            $('.wp-photo-wall-save-btn, #submit').addClass('wp-photo-wall-btn-pulse');
        }
    }

    function markClean() {
        isDirty = false;
        $unsavedNotice.removeClass('is-visible');
        $('.wp-photo-wall-save-btn, #submit').removeClass('wp-photo-wall-btn-pulse');
    }

    // Every settings field feeds the same "save to apply" flow: touching it
    // marks the page as dirty until the form is actually submitted.
    var $settingsFields = $('#wp-photo-wall-form')
        .find('input[type="checkbox"], input[type="text"], input[type="url"], input[type="number"], select, textarea')
        .not('#wp-photo-wall-new-group-name, #wp-photo-wall-external-url, #wp-photo-wall-confirm-word, #wp-photo-wall-move-target');

    $settingsFields.on('change input', function () {
        markDirty();
    });

    /**
     * Leave guard. The browser dialog covers closing / reloading / typing a new
     * address, while in-page links get our own dialog so the wording matches
     * the plugin ("Save Changes" is required for anything to apply).
     */
    function leaveMessage() {
        return (wp_photo_wall_ajax.labels && wp_photo_wall_ajax.labels.leave_confirm) || '';
    }

    var $leaveModal = $('#wp-photo-wall-leave-modal');
    var pendingLeaveUrl = '';

    $(window).on('beforeunload', function (e) {
        if (!isDirty) return;

        var message = leaveMessage();
        if (e.originalEvent) {
            e.originalEvent.returnValue = message;
        }
        e.preventDefault();
        return message;
    });

    $(document).on('click', 'a[href]', function (e) {
        if (!isDirty) return;
        // Let the browser handle new-tab / new-window / middle clicks.
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        var $link = $(this);
        var href = $link.attr('href');
        if (!href || href.charAt(0) === '#') return;      // in-page anchor
        if ($link.hasClass('nav-tab')) return;            // tab switch, same page
        if ($link.closest('.wp-photo-wall-modal').length) return;
        if ($link.attr('target') && $link.attr('target') !== '_self') return;

        e.preventDefault();
        pendingLeaveUrl = href;
        $leaveModal.prop('hidden', false);
    });

    $leaveModal.on('click', '.wp-photo-wall-leave-stay', function () {
        pendingLeaveUrl = '';
        $leaveModal.prop('hidden', true);

        // Point the user at the save button of the tab they are on.
        var $btn = $('.wp-photo-wall-tab-panel:not([hidden])').find('.wp-photo-wall-save-btn, #submit').first();
        if ($btn.length && $btn[0].scrollIntoView) {
            $btn[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    $leaveModal.on('click', '.wp-photo-wall-leave-discard', function () {
        if (!pendingLeaveUrl) {
            $leaveModal.prop('hidden', true);
            return;
        }
        // Dropped on purpose: the page is left as-is, so the pending edits are
        // simply gone and the last saved state is restored on the next visit.
        markClean();
        window.location.href = pendingLeaveUrl;
    });

    // Clear dirty on form submit
    $('#wp-photo-wall-form').on('submit', function () {
        markClean();
    });

    // ==========================================
    // Tab Navigation
    // ==========================================
    var $tabs = $('.wp-photo-wall-tabs .nav-tab');
    var $panels = $('.wp-photo-wall-tab-panel');
    var $activeTabInput = $('#wp_photo_wall_active_tab');

    function switchTab(tab) {
        $tabs.removeClass('nav-tab-active')
            .filter('[data-tab="' + tab + '"]').addClass('nav-tab-active');

        $panels.each(function () {
            $(this).prop('hidden', $(this).data('tab') !== tab);
        });

        $activeTabInput.val(tab);

        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '#' + tab);
        }
    }

    $tabs.on('click', function (e) {
        e.preventDefault();
        switchTab($(this).data('tab'));
    });

    // Restore tab from the URL hash (if any) on page load.
    var initialTab = window.location.hash.replace('#', '');
    if (initialTab && $tabs.filter('[data-tab="' + initialTab + '"]').length) {
        switchTab(initialTab);
    }

    // ==========================================
    // Reusable Confirmation Dialog
    // ==========================================
    var $confirmModal = $('#wp-photo-wall-confirm-modal');
    var $confirmTitle = $confirmModal.find('#wp-photo-wall-confirm-title');
    var $confirmMessage = $confirmModal.find('.wp-photo-wall-confirm-message');
    var $confirmDetails = $confirmModal.find('.wp-photo-wall-confirm-details');
    var $confirmTyped = $confirmModal.find('.wp-photo-wall-confirm-typed');
    var $confirmTypedLabel = $confirmTyped.find('label');
    var $confirmWord = $('#wp-photo-wall-confirm-word');
    var $confirmOk = $confirmModal.find('.wp-photo-wall-confirm-ok');
    var $confirmCancel = $confirmModal.find('.wp-photo-wall-confirm-cancel');
    var confirmCallback = null;
    var confirmRequiredWord = '';

    function closeConfirm() {
        $confirmModal.prop('hidden', true);
        confirmCallback = null;
        confirmRequiredWord = '';
    }

    /**
     * Show the confirmation dialog.
     * opts: { title, message, details:[], confirmLabel, danger, requireWord }
     */
    function wpPwConfirm(opts, onConfirm) {
        opts = opts || {};
        confirmCallback = typeof onConfirm === 'function' ? onConfirm : null;
        confirmRequiredWord = opts.requireWord || '';

        $confirmTitle.text(opts.title || '');
        $confirmMessage.text(opts.message || '').prop('hidden', !opts.message);

        var details = opts.details || [];
        if (details.length) {
            var detailsHtml = '';
            details.forEach(function (line) {
                detailsHtml += '<li>' + escapeHtml(line) + '</li>';
            });
            $confirmDetails.html(detailsHtml).prop('hidden', false);
        } else {
            $confirmDetails.empty().prop('hidden', true);
        }

        if (confirmRequiredWord) {
            $confirmTypedLabel.text(wp_photo_wall_ajax.labels.confirm_word_hint || '');
            $confirmWord.val('');
            $confirmTyped.prop('hidden', false);
            $confirmOk.prop('disabled', true);
        } else {
            $confirmTyped.prop('hidden', true);
            $confirmOk.prop('disabled', false);
        }

        $confirmOk
            .text(opts.confirmLabel || wp_photo_wall_ajax.labels.confirm_delete || 'OK')
            .toggleClass('wp-photo-wall-btn-danger', !!opts.danger);
        $confirmCancel.text(wp_photo_wall_ajax.labels.cancel || '');

        $confirmModal.prop('hidden', false);

        setTimeout(function () {
            if (confirmRequiredWord) {
                $confirmWord.focus();
            } else {
                $confirmOk.focus();
            }
        }, 0);
    }

    $confirmWord.on('input', function () {
        var typed = $(this).val().trim().toUpperCase();
        $confirmOk.prop('disabled', typed !== confirmRequiredWord);
    });

    $confirmCancel.on('click', closeConfirm);

    $confirmOk.on('click', function () {
        if ($confirmOk.prop('disabled')) return;
        var cb = confirmCallback;
        closeConfirm();
        if (typeof cb === 'function') cb();
    });

    // Close when clicking the backdrop or pressing Escape.
    $confirmModal.on('click', function (e) {
        if (e.target === this) closeConfirm();
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && !$confirmModal.prop('hidden')) {
            closeConfirm();
        }
    });

    // ==========================================
    // Pending permanent deletions (applied on save)
    // ==========================================
    var $pendingInput = $('#photo_wall_pending_deletions');

    function getPendingDeletions() {
        try {
            var parsed = JSON.parse($pendingInput.val() || '[]');
            return Array.isArray(parsed) ? parsed : [];
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

    // Drop a queued deletion when the same attachment is (re)added to the wall/carousel.
    function unqueuePendingDeletion(id) {
        id = parseInt(id, 10) || 0;
        if (id <= 0) return;
        var pending = getPendingDeletions().filter(function (pid) {
            return parseInt(pid, 10) !== id;
        });
        setPendingDeletions(pending);
    }

    // Impact text for a local (Media Library) image: deleting it also deletes the
    // Media Library file on save so no orphan is left behind.
    function localRemovalImpact() {
        return wp_photo_wall_ajax.labels.impact_local_delete;
    }

    function countItemsByType($items) {
        var counts = { local: 0, external: 0 };
        $items.each(function () {
            var type = $(this).data('type');
            if (type === 'local') {
                counts.local++;
            } else {
                counts.external++;
            }
        });
        return counts;
    }

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
                unqueuePendingDeletion(attachment.id);
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

    // Remove Item (single) - type-aware confirmation
    $(document).on('click', '.photo-wall-remove', function (e) {
        e.preventDefault();
        var $item = $(this).closest('.wp-photo-wall-preview-item');
        var type = $item.data('type');
        var L = wp_photo_wall_ajax.labels;

        var details = type === 'local'
            ? [localRemovalImpact(), L.save_to_apply_note]
            : [L.impact_external, L.save_to_apply_note];

        wpPwConfirm({
            title: L.confirm_remove_title,
            message: type === 'local' ? L.confirm_remove_local : L.confirm_remove_external,
            details: details,
            confirmLabel: L.remove
        }, function () {
            if (type === 'local') {
                queuePendingDeletion($item.data('id'));
            }
            $item.remove();
            updateDataFromDOM();
            updateBulkBtnState();
            markDirty();
        });
    });

    // Clear All (typed confirmation + type breakdown)
    $(document).on('click', '#wp-photo-wall-clear-btn', function (e) {
        e.preventDefault();
        var L = wp_photo_wall_ajax.labels;
        var counts = countItemsByType($('.wp-photo-wall-preview-item'));

        var details = [];
        if (counts.local > 0) {
            details.push(L.detail_local_count.replace(/%d/g, counts.local) + ' — ' + localRemovalImpact());
        }
        if (counts.external > 0) {
            details.push(L.detail_external_count.replace(/%d/g, counts.external) + ' — ' + L.impact_external);
        }
        details.push(L.impact_clear);
        details.push(L.save_to_apply_note);

        wpPwConfirm({
            title: L.confirm_clear_title,
            message: L.confirm_clear.replace(/%d/g, currentData.length),
            details: details,
            confirmLabel: L.clear_all,
            danger: true,
            requireWord: 'CONFIRM'
        }, function () {
            currentData.forEach(function (item) {
                if (item.type === 'local') {
                    queuePendingDeletion(item.id);
                }
            });
            currentData = [];
            renderAll();
            updateDataFromDOM();
            markDirty();
        });
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
    // Bulk Remove (type-aware confirmation)
    // ==========================================
    $(document).on('click', '#wp-photo-wall-bulk-remove-btn', function (e) {
        e.preventDefault();
        var $checked = $('.photo-wall-checkbox:checked');
        if (!$checked.length) return;

        var L = wp_photo_wall_ajax.labels;
        var $items = $checked.closest('.wp-photo-wall-preview-item');
        var counts = countItemsByType($items);

        var details = [];
        if (counts.local > 0) {
            details.push(L.detail_local_count.replace(/%d/g, counts.local) + ' — ' + localRemovalImpact());
        }
        if (counts.external > 0) {
            details.push(L.detail_external_count.replace(/%d/g, counts.external) + ' — ' + L.impact_external);
        }
        details.push(L.save_to_apply_note);

        wpPwConfirm({
            title: L.confirm_remove_title,
            message: L.confirm_remove_bulk.replace(/%d/g, $items.length),
            details: details,
            confirmLabel: L.bulk_delete_selected
        }, function () {
            $items.each(function () {
                var $it = $(this);
                if ($it.data('type') === 'local') {
                    queuePendingDeletion($it.data('id'));
                }
            });
            $items.remove();
            updateDataFromDOM();
            updateBulkBtnState();
            markDirty();
        });
    });

    // ==========================================
    // Permanently delete from Media Library (queued; applied on save)
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
                localIds.push(parseInt($p.data('id'), 10) || 0);
                $localItems = $localItems.add($p);
            }
        });

        localIds = localIds.filter(function (id) { return id > 0; });
        if (!localIds.length) return;

        var L = wp_photo_wall_ajax.labels;

        wpPwConfirm({
            title: L.confirm_perm_delete_title,
            message: L.confirm_perm_delete.replace(/%d/g, localIds.length),
            details: [L.impact_perm_delete, L.save_to_apply_note_perm],
            confirmLabel: L.delete_from_media,
            danger: true
        }, function () {
            localIds.forEach(function (id) {
                queuePendingDeletion(id);
            });
            $localItems.remove();
            updateDataFromDOM();
            updateBulkBtnState();
            markDirty();
        });
    });

    // ==========================================
    // Top Banner Carousel (slides) management
    // ==========================================
    var $slidesInput = $('#photo_wall_slides');
    var $slidesList = $('#wp-pw-slides-list');
    var slidesFrame;

    // The carousel is capped (WP_PHOTO_WALL_SLIDES_MAX on the PHP side).
    var slidesMax = parseInt(wp_photo_wall_ajax.slides_max, 10) || 6;
    var $slidesCount = $('.wp-pw-slides-count');

    function updateSlidesCount() {
        if (!$slidesCount.length) return;
        var count = getSlides().length;
        $slidesCount
            .text(wp_photo_wall_ajax.labels.slides_count.replace('%d', count).replace('%d', slidesMax))
            .toggleClass('is-full', count >= slidesMax);
    }

    // Returns true (and warns) when no more slides may be added.
    function slidesLimitReached() {
        if (getSlides().length >= slidesMax) {
            alert(wp_photo_wall_ajax.labels.slides_limit_reached.replace(/%d/g, slidesMax));
            return true;
        }
        return false;
    }

    function getSlides() {
        try {
            var raw = $slidesInput.val();
            if (!raw) return [];
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function setSlides(slides) {
        $slidesInput.val(JSON.stringify(slides));
        markDirty();
    }

    function renderSlides() {
        var slides = getSlides();
        var html = '';
        slides.forEach(function (s) {
            var type = s.type === 'local' ? 'local' : 'external';
            var id = s.type === 'local' ? s.id : 'external';
            var url = s.url || '';
            var full = s.full || url;
            var thumb = type === 'local' ? (s.thumb_url || url) : url;
            html += '<li class="wp-pw-slide-item" data-type="' + type + '"' +
                ' data-id="' + id + '"' +
                ' data-url="' + escapeHtml(url) + '"' +
                ' data-full="' + escapeHtml(full) + '"' +
                ' data-thumb="' + escapeHtml(thumb) + '">' +
                '<span class="wp-pw-slide-handle" title="' + escapeHtml(wp_photo_wall_ajax.labels.drag) + '">&#8942;&#8942;</span>' +
                '<img class="wp-pw-slide-thumb" src="' + escapeHtml(thumb) + '" alt="">' +
                '<span class="wp-pw-slide-type">' + escapeHtml(type === 'local' ? wp_photo_wall_ajax.labels.local : wp_photo_wall_ajax.labels.external) + '</span>' +
                '<button type="button" class="button-link wp-pw-slide-remove" aria-label="' + escapeHtml(wp_photo_wall_ajax.labels.remove) + '"></button>' +
                '</li>';
        });
        $slidesList.html(html);
        updateSlidesCount();
    }

    // Init sortable for slides list
    function initSlidesSortable() {
        if ($.fn.sortable) {
            $slidesList.sortable({
                handle: '.wp-pw-slide-handle',
                placeholder: 'wp-pw-slide-placeholder',
                forcePlaceholderSize: true,
                tolerance: 'pointer',
                update: function () {
                    syncSlidesFromDOM();
                    markDirty();
                }
            }).disableSelection();
        }
    }

    function syncSlidesFromDOM() {
        var slides = [];
        $slidesList.find('.wp-pw-slide-item').each(function () {
            var $li = $(this);
            var type = $li.data('type');
            var slide = { type: type, url: $li.data('url'), full: $li.data('full') };
            if (type === 'local') {
                slide.id = parseInt($li.data('id'), 10) || 0;
            }
            slides.push(slide);
        });
        setSlides(slides);
    }

    // Add from Media Library
    $(document).on('click', '.wp-pw-add-local', function (e) {
        e.preventDefault();
        if (typeof wp === 'undefined' || !wp.media) {
            alert('WordPress Media Library is not available.');
            return;
        }
        if (slidesLimitReached()) return;
        if (slidesFrame) { slidesFrame.open(); return; }
        slidesFrame = wp.media({
            title: wp_photo_wall_ajax.labels.slides_add_local,
            button: { text: wp_photo_wall_ajax.labels.slides_add_to_carousel },
            multiple: true
        });
        slidesFrame.on('select', function () {
            var selection = slidesFrame.state().get('selection');
            var slides = getSlides();
            var added = 0;
            var skipped = 0;

            selection.map(function (attachment) {
                if (slides.length >= slidesMax) {
                    skipped++;
                    return;
                }
                attachment = attachment.toJSON();
                var thumbUrl = '';
                if (attachment.sizes) {
                    if (attachment.sizes.thumbnail) thumbUrl = attachment.sizes.thumbnail.url;
                    else if (attachment.sizes.medium) thumbUrl = attachment.sizes.medium.url;
                    else thumbUrl = attachment.url;
                } else {
                    thumbUrl = attachment.url;
                }
                unqueuePendingDeletion(attachment.id);
                slides.push({ type: 'local', id: attachment.id, url: thumbUrl, full: attachment.url, thumb_url: thumbUrl });
                added++;
            });

            if (added > 0) {
                setSlides(slides);
                renderSlides();
                initSlidesSortable();
            }
            if (skipped > 0) {
                alert(wp_photo_wall_ajax.labels.slides_limit_reached.replace(/%d/g, slidesMax));
            }
        });
        slidesFrame.open();
    });

    // Add external link
    var $slideExternalUrl = '';
    var slideExternalPreview = null;
    $(document).on('click', '.wp-pw-add-external', function (e) {
        e.preventDefault();
        if (slidesLimitReached()) return;
        var url = prompt(wp_photo_wall_ajax.labels.image_url + ' (https://...):');
        if (!url) return;
        url = url.trim();
        if (!/^https?:\/\/.+/i.test(url)) {
            alert(wp_photo_wall_ajax.labels.invalid_url);
            return;
        }
        var slides = getSlides();
        slides.push({ type: 'external', id: 'external', url: url, full: url });
        setSlides(slides);
        renderSlides();
        initSlidesSortable();
    });

    // Remove slide (type-aware confirmation)
    $(document).on('click', '.wp-pw-slide-remove', function (e) {
        e.preventDefault();
        var $slide = $(this).closest('.wp-pw-slide-item');
        var type = $slide.data('type');
        var L = wp_photo_wall_ajax.labels;

        var details = type === 'local'
            ? [L.impact_slide_local, L.save_to_apply_note]
            : [L.impact_external, L.save_to_apply_note];

        wpPwConfirm({
            title: L.confirm_remove_title,
            message: L.confirm_remove_slide,
            details: details,
            confirmLabel: L.remove
        }, function () {
            if (type === 'local') {
                queuePendingDeletion($slide.data('id'));
            }
            $slide.remove();
            syncSlidesFromDOM();
        });
    });

    // Initial render + sortable
    renderSlides();
    initSlidesSortable();

    // ==========================================
    // Bing Wallpaper tab
    // ==========================================
    var $bingApiInput = $('#photo_wall_bing_api');
    var $bingList = $('#wp-photo-wall-bing-list');
    var $bingOrderInput = $('#photo_wall_bing_order');
    var $bingRefreshBtn = $('#wp-photo-wall-bing-refresh');
    var $bingResetBtn = $('#wp-photo-wall-bing-reset');
    var $bingStatus = $('#wp-photo-wall-bing-status');
    // Last fetched list, always kept in natural (newest first) order.
    var bingNatural = [];

    function getBingOrder() {
        try {
            var parsed = JSON.parse($bingOrderInput.val() || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function setBingOrder(keys) {
        $bingOrderInput.val(JSON.stringify(keys));
    }

    function syncBingOrderFromDOM() {
        var keys = [];
        $bingList.find('.wp-pw-bing-item').each(function () {
            keys.push(String($(this).data('key')));
        });
        setBingOrder(keys);
    }

    function applyBingOrder(items) {
        var order = getBingOrder();
        if (!order.length) return items;

        var map = {};
        items.forEach(function (item) { map[item.key] = item; });

        var sorted = [];
        order.forEach(function (key) {
            if (map[key]) {
                sorted.push(map[key]);
                delete map[key];
            }
        });
        // Wallpapers not covered by the saved order (new days) keep date order.
        items.forEach(function (item) {
            if (map[item.key]) sorted.push(item);
        });
        return sorted;
    }

    function renderBing(items) {
        if (!$bingList.length) return;

        if (Array.isArray(items)) bingNatural = items;
        var ordered = applyBingOrder(bingNatural);

        if (!ordered.length) {
            $bingList.html('<li class="wp-pw-bing-empty">' +
                escapeHtml(wp_photo_wall_ajax.labels.bing_empty || 'No wallpapers found.') + '</li>');
            syncBingOrderFromDOM();
            return;
        }

        var html = '';
        ordered.forEach(function (item) {
            var label = item.date || '';
            if (item.title) {
                label = label ? label + ' · ' + item.title : item.title;
            }
            html += '<li class="wp-pw-bing-item" data-key="' + escapeHtml(item.key) + '"' +
                ' title="' + escapeHtml(item.copyright || label) + '">' +
                '<span class="wp-pw-bing-handle" title="' + escapeHtml(wp_photo_wall_ajax.labels.drag || '') + '">&#8942;&#8942;</span>' +
                '<img class="wp-pw-bing-thumb" src="' + escapeHtml(item.thumb) + '" alt=""' +
                ' data-full="' + escapeHtml(item.full) + '" decoding="async" loading="lazy">' +
                '<span class="wp-pw-bing-meta">' + escapeHtml(label) + '</span>' +
                '</li>';
        });
        $bingList.html(html);
        syncBingOrderFromDOM();
    }

    // Thumbnails come from a third-party host: fall back to the full image.
    if ($bingList.length) {
        $bingList[0].addEventListener('error', function (e) {
            var el = e.target;
            if (!el || el.tagName !== 'IMG' || !$(el).hasClass('wp-pw-bing-thumb')) return;
            var full = el.getAttribute('data-full');
            if (full && el.getAttribute('src') !== full) {
                el.setAttribute('src', full);
            }
        }, true);
    }

    // Seed the in-memory list from the server-rendered markup so "Restore Date
    // Order" also works before the first manual refresh.
    function seedBingFromDOM() {
        var items = [];
        $bingList.find('.wp-pw-bing-item').each(function () {
            var $li = $(this);
            var $img = $li.find('.wp-pw-bing-thumb');
            items.push({
                key: String($li.data('key')),
                thumb: $img.attr('src') || '',
                full: $img.attr('data-full') || '',
                title: $li.find('.wp-pw-bing-meta').text() || '',
                copyright: $li.attr('title') || '',
                date: ''
            });
        });
        // Newest first (keys are "d20260915" for dated entries).
        items.sort(function (a, b) {
            return a.key < b.key ? 1 : (a.key > b.key ? -1 : 0);
        });
        return items;
    }

    function initBingSortable() {
        if (!$.fn.sortable || !$bingList.length) return;
        $bingList.sortable({
            handle: '.wp-pw-bing-handle',
            items: '.wp-pw-bing-item',
            placeholder: 'wp-pw-bing-placeholder',
            forcePlaceholderSize: true,
            tolerance: 'pointer',
            update: function () {
                syncBingOrderFromDOM();
                markDirty();
            }
        }).disableSelection();
    }

    function setBingStatus(text) {
        if ($bingStatus.length) $bingStatus.text(text);
    }

    $bingResetBtn.on('click', function () {
        setBingOrder([]);
        renderBing();
        markDirty();
    });

    $bingRefreshBtn.on('click', function () {
        if (!$bingList.length) return;

        var L = wp_photo_wall_ajax.labels;
        $bingRefreshBtn.prop('disabled', true).text(L.bing_refreshing || '');

        $.post(ajaxurl, {
            action: 'wp_photo_wall_bing_refresh',
            nonce: wp_photo_wall_ajax.bing_nonce || '',
            api: $bingApiInput.val()
        }).done(function (response) {
            if (response && response.success) {
                renderBing(response.data.items || []);
                setBingStatus(response.data.updated || '');
            } else {
                setBingStatus(L.bing_fetch_failed || (L.ajax_error || ''));
            }
        }).fail(function () {
            setBingStatus(L.bing_fetch_failed || (L.ajax_error || ''));
        }).always(function () {
            $bingRefreshBtn.prop('disabled', false).text(L.bing_refresh || '');
        });
    });

    if ($bingList.length) {
        bingNatural = seedBingFromDOM();
    }
    initBingSortable();

});
