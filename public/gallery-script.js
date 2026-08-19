(function ($) {
    'use strict';

    // Scroll throttle: disable hover transitions during scroll
    var scrollTimer;
    $(window).on('scroll', function () {
        if (!scrollTimer) {
            document.documentElement.classList.add('wp-photo-wall-is-scrolling');
        }
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function () {
            document.documentElement.classList.remove('wp-photo-wall-is-scrolling');
            scrollTimer = null;
        }, 150);
    });

    $('.wp-photo-wall-instance').each(function () {
        var $instance = $(this);
        var $wrapper = $instance.find('.wp-photo-wall-wrapper');
        var $loader = $instance.find('.wp-photo-wall-loader');
        var $lightbox = $instance.find('.wp-photo-wall-lightbox');
        var page = 1;
        var loading = false;
        var items = [];
        var currentIndex = -1;
        var lastFocus = null;
        var observer = null;

        function updateItems() {
            items = [];
            $instance.find('.wp-photo-wall-lightbox-trigger').each(function (index) {
                items.push($(this).attr('data-full-url'));
                $(this).attr({ role: 'button', tabindex: '0', 'aria-label': 'Open photo ' + (index + 1), 'data-index': index });
            });
        }

        function appendSections(html) {
            $('<div>').html(html).find('.wp-photo-wall-group-section').each(function () {
                var $section = $(this);
                var $last = $wrapper.find('.wp-photo-wall-group-section').last();
                if ($last.length && $last.attr('data-group-id') === $section.attr('data-group-id')) {
                    $last.find('.wp-photo-wall-group-grid').append($section.find('.wp-photo-wall-item'));
                } else {
                    $wrapper.append($section);
                }
            });
            updateItems();
        }

        function loadMore() {
            if (loading || !$loader.length) return;
            loading = true;
            $loader.addClass('is-loading');
            $.post(wp_photo_wall_config.ajax_url, {
                action: 'wp_photo_wall_load_more',
                page: page + 1,
                nonce: wp_photo_wall_config.nonce
            }).done(function (response) {
                if (response.success && response.data.html) {
                    appendSections(response.data.html);
                    page++;
                }
                if (!response.success || !response.data || !response.data.has_more) {
                    if (observer) observer.disconnect();
                    $loader.remove();
                }
            }).always(function () {
                loading = false;
                $loader.removeClass('is-loading');
            });
        }

        if ($loader.length) {
            if ('IntersectionObserver' in window) {
                observer = new IntersectionObserver(function (entries) {
                    if (entries[0].isIntersecting) loadMore();
                }, { rootMargin: '150px' });
                observer.observe($loader[0]);
            } else {
                $loader.css({ opacity: 1, visibility: 'visible', cursor: 'pointer' }).on('click', loadMore);
            }
        }

        if (!$lightbox.length) return;
        var $content = $lightbox.find('.wp-photo-wall-lightbox-content');
        var activeImage = null;
        var requestSerial = 0;
        var $close = $lightbox.find('.wp-photo-wall-close');

        function show(index) {
            if (!items.length) return;
            currentIndex = (index + items.length) % items.length;
            var url = items[currentIndex];
            var serial = ++requestSerial;
            var image = new Image();

            $lightbox.addClass('is-loading');
            image.alt = '';
            image.decoding = 'async';
            image.loading = 'eager';
            image.referrerPolicy = 'no-referrer';
            image.className = 'wp-photo-wall-full-image skip-lazy no-lazy';
            image.setAttribute('data-no-lazy', '1');
            image.setAttribute('data-skip-lazy', '1');

            image.onload = function () {
                if (serial !== requestSerial) return;
                activeImage = image;
                $content.empty().append(image);
                $lightbox.removeClass('is-loading');
            };
            image.onerror = function () {
                if (serial !== requestSerial) return;
                activeImage = null;
                var errorText = (wp_photo_wall_config.labels && wp_photo_wall_config.labels.image_load_error)
                    ? wp_photo_wall_config.labels.image_load_error
                    : 'Image failed to load, click to open original';
                $content.empty().append(
                    $('<a>', {
                        class: 'wp-photo-wall-image-error',
                        href: url,
                        target: '_blank',
                        rel: 'noopener noreferrer',
                        text: errorText
                    })
                );
                $lightbox.removeClass('is-loading');
            };
            image.src = url;
        }

        function open(index, trigger) {
            lastFocus = trigger;
            show(index);
            $lightbox.addClass('active').attr('aria-hidden', 'false');
            $('body').addClass('wp-photo-wall-lightbox-open');
            $close.trigger('focus');
        }

        function close() {
            $lightbox.removeClass('active').attr('aria-hidden', 'true');
            $('body').removeClass('wp-photo-wall-lightbox-open');
            requestSerial++;
            activeImage = null;
            $content.empty();
            if (lastFocus) $(lastFocus).trigger('focus');
        }

        updateItems();

        // Handle trigger clicks in the CAPTURE phase at the WINDOW level. This
        // is the outermost point possible, so we run before any third-party
        // lightbox (e.g. the JustNews theme) that registers its listener on
        // document/body. Stopping propagation guarantees the foreign listener
        // never receives the event, while we still open our own lightbox.
        function handleTrigger(event) {
            if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
            var trigger = event.target.closest('.wp-photo-wall-lightbox-trigger');
            if (!trigger || !$instance[0].contains(trigger)) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            event.stopPropagation();
            open(parseInt(trigger.getAttribute('data-index'), 10) || 0, trigger);
        }
        window.addEventListener('click', handleTrigger, true);
        window.addEventListener('keydown', handleTrigger, true);

        $lightbox.find('.wp-photo-wall-prev').on('click', function () { show(currentIndex - 1); });
        $lightbox.find('.wp-photo-wall-next').on('click', function () { show(currentIndex + 1); });
        $close.add($lightbox.find('.wp-photo-wall-lightbox-overlay')).on('click', close);

        // Touch swipe to navigate between photos (mirrors the slider's swipe support)
        var touchStartX = 0;
        var touchStartY = 0;
        $lightbox.on('touchstart', function (e) {
            touchStartX = e.originalEvent.touches[0].clientX;
            touchStartY = e.originalEvent.touches[0].clientY;
        });
        $lightbox.on('touchend', function (e) {
            var dx = e.originalEvent.changedTouches[0].clientX - touchStartX;
            var dy = e.originalEvent.changedTouches[0].clientY - touchStartY;
            // Only navigate on a horizontal swipe (ignore vertical pans/taps)
            if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
                show(dx < 0 ? currentIndex + 1 : currentIndex - 1);
            }
        });

        $lightbox.on('keydown', function (event) {
            if (event.key === 'Escape') close();
            else if (event.key === 'ArrowLeft') show(currentIndex - 1);
            else if (event.key === 'ArrowRight') show(currentIndex + 1);
            else if (event.key === 'Tab') {
                var $focusable = $lightbox.find('button:visible');
                var first = $focusable[0], last = $focusable[$focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            }
        });
    });
})(jQuery);
