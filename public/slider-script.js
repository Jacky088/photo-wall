/**
 * WP Photo Wall - Top Banner Carousel (slides)
 * Auto-play with slide transition. Each [photo_wall] instance is independent.
 */
(function ($) {
    'use strict';

    function PWSlider(root) {
        this.$root = $(root);
        this.$track = this.$root.find('.wp-pw-track');
        this.$slides = this.$root.find('.wp-pw-slide');
        this.$dots = this.$root.find('.wp-pw-dot');
        this.count = this.$slides.length;
        this.index = 0;
        this.interval = parseInt(this.$root.data('interval'), 10) || 5000;
        this.timer = null;
        this.reducedMotion = window.matchMedia &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (this.count <= 1) {
            // Single slide: no auto-play, hide nav/dots.
            this.$root.find('.wp-pw-nav, .wp-pw-dots').hide();
            return;
        }

        this.bind();
        this.start();
    }

    PWSlider.prototype.bind = function () {
        var self = this;

        this.$root.find('.wp-pw-next').on('click', function (e) {
            e.preventDefault();
            self.go(1);
        });
        this.$root.find('.wp-pw-prev').on('click', function (e) {
            e.preventDefault();
            self.go(-1);
        });
        this.$dots.on('click', function () {
            self.show(parseInt($(this).data('index'), 10));
        });

        // Pause on hover / focus.
        this.$root.on('mouseenter', function () { self.stop(); });
        this.$root.on('mouseleave', function () { self.start(); });
        this.$root.on('focusin', function () { self.stop(); });
        this.$root.on('focusout', function () { self.start(); });

        // Pause when tab is hidden.
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                self.stop();
            } else {
                self.start();
            }
        });

        // Swipe support for touch devices.
        var startX = 0;
        this.$root.on('touchstart', function (e) {
            startX = e.originalEvent.touches[0].clientX;
            self.stop();
        });
        this.$root.on('touchend', function (e) {
            var dx = e.originalEvent.changedTouches[0].clientX - startX;
            if (Math.abs(dx) > 40) {
                self.go(dx < 0 ? 1 : -1);
            }
            self.start();
        });

        // Pause autoplay whenever the user interacts with a slide
        // (click / Enter / Space). Capture phase so it still fires even though
        // the lightbox delegation stops propagation in the bubbling phase.
        var slidesRoot = this.$slides[0];
        if (slidesRoot) {
            slidesRoot.addEventListener('click', function (e) {
                if (e.target.closest('.wp-pw-slide a, .wp-pw-slide button')) self.stop();
            }, true);
            slidesRoot.addEventListener('keydown', function (e) {
                if ((e.key === 'Enter' || e.key === ' ') && e.target.closest('.wp-pw-slide a, .wp-pw-slide button')) self.stop();
            }, true);
        }
    };

    PWSlider.prototype.go = function (dir) {
        var next = this.index + dir;
        if (next >= this.count) {
            next = 0;
        } else if (next < 0) {
            next = this.count - 1;
        }
        this.show(next);
    };

    PWSlider.prototype.show = function (i) {
        this.index = i;
        var offset = -i * 100;
        if (this.reducedMotion) {
            this.$track.css('transition', 'none');
        } else {
            this.$track.css('transition', '');
        }
        this.$track.css('transform', 'translateX(' + offset + '%)');

        this.$slides.removeClass('is-active');
        this.$slides.eq(i).addClass('is-active');
        this.$dots.removeClass('is-active');
        this.$dots.eq(i).addClass('is-active');

        this.restart();
    };

    PWSlider.prototype.start = function () {
        var self = this;
        if (this.reducedMotion) {
            return; // Respect reduced-motion: no auto-advance.
        }
        this.stop();
        this.timer = setInterval(function () {
            self.go(1);
        }, this.interval);
    };

    PWSlider.prototype.stop = function () {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    };

    PWSlider.prototype.restart = function () {
        if (this.timer) {
            this.start();
        }
    };

    $(function () {
        $('.wp-photo-wall-slider').each(function () {
            new PWSlider(this);
        });
    });
})(jQuery);
