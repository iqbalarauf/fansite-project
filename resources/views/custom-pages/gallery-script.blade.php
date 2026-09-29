<script>
    (function () {
        function setupGalleryCarousel(root) {
            if (root.dataset.pageGalleryReady === 'true') {
                return;
            }

            var track = root.querySelector('[data-page-gallery-track]');
            var items = Array.prototype.slice.call(root.querySelectorAll('[data-page-gallery-item]'));
            var prev = root.querySelector('[data-page-gallery-prev]');
            var next = root.querySelector('[data-page-gallery-next]');

            if (!track || items.length === 0) {
                return;
            }

            root.dataset.pageGalleryReady = 'true';

            var visible = parseInt(root.dataset.pageGalleryVisible || '3', 10) || 3;
            var index = 0;
            var maxIndex = Math.max(0, items.length - visible);

            function update() {
                var styles = window.getComputedStyle(track);
                var gap = parseFloat(styles.columnGap || styles.gap) || 0;
                var step = items[0].getBoundingClientRect().width + gap;

                track.style.transform = 'translateX(' + (-index * step) + 'px)';

                if (prev) prev.disabled = index <= 0;
                if (next) next.disabled = index >= maxIndex;
            }

            if (prev) {
                prev.addEventListener('click', function () {
                    if (index > 0) {
                        index--;
                        update();
                    }
                });
            }

            if (next) {
                next.addEventListener('click', function () {
                    if (index < maxIndex) {
                        index++;
                        update();
                    }
                });
            }

            window.addEventListener('resize', update);

            update();
        }

        function initAllGalleryCarousels() {
            document.querySelectorAll('[data-page-gallery]').forEach(setupGalleryCarousel);
        }

        window.__initGalleryCarousels = initAllGalleryCarousels;
        document.addEventListener('DOMContentLoaded', initAllGalleryCarousels);
        document.addEventListener('livewire:navigated', initAllGalleryCarousels);
        document.addEventListener('livewire:init', function () {
            Livewire.hook('morphed', function () {
                initAllGalleryCarousels();
            });
        });

        // The page builder loads the preview asynchronously; watch for new galleries.
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function () {
                initAllGalleryCarousels();
            });

            observer.observe(document.documentElement, { childList: true, subtree: true });
        }

        initAllGalleryCarousels();
    })();
</script>
