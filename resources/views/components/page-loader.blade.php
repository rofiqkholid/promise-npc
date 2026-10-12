<style>
    @keyframes promiseSpinnerRotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .promise-spin-ring {
        animation: promiseSpinnerRotate 0.8s linear infinite !important;
        transform-origin: 50% 50%;
    }
    [x-cloak] {
        display: none !important;
    }
</style>

<!-- Global Transparent Page Loader Overlay (Full Transparan, Bukan Blok Putih) -->
<div id="global-page-loader"
     class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/10 dark:bg-black/25 backdrop-blur-[1px] transition-opacity duration-200 ease-out select-none"
     style="opacity: 1; display: flex;">
    
    <!-- Spinner & Static Logo Container (Melayang Transparan Tanpa Kotak) -->
    <div class="relative flex items-center justify-center w-20 h-20 pointer-events-none">
        <!-- SVG Spinner Ring (Berputar Mulus) -->
        <svg class="promise-spin-ring w-20 h-20 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.5"></circle>
            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>

        <!-- Static PROMISE Logo in the Center (Tetap Diam di Tengah) -->
        <div class="absolute inset-0 flex items-center justify-center">
            <img src="{{ asset('assets/image/logo-promise.png') }}" alt="PROMISE" class="h-10 w-auto drop-shadow-sm">
        </div>
    </div>
</div>

<script>
    (function() {
        const loader = document.getElementById('global-page-loader');
        if (!loader) return;

        let hideTimeout = null;

        function hideLoader() {
            if (!loader) return;
            clearTimeout(hideTimeout);
            loader.classList.add('opacity-0', 'pointer-events-none');
            hideTimeout = setTimeout(function() {
                loader.style.display = 'none';
            }, 200);
        }

        function showLoader() {
            if (!loader) return;
            clearTimeout(hideTimeout);
            loader.style.display = 'flex';
            loader.classList.remove('opacity-0', 'pointer-events-none');
        }

        // Hide loader setelah DOM siap
        function triggerHide() {
            requestAnimationFrame(function() {
                setTimeout(hideLoader, 80);
            });
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            triggerHide();
        } else {
            document.addEventListener('DOMContentLoaded', triggerHide);
            window.addEventListener('load', triggerHide);
        }

        // Safety fallback: Auto-hide maksimal 3.5 detik
        setTimeout(hideLoader, 3500);

        // Hide saat navigasi browser bfcache (Back/Forward)
        window.addEventListener('pageshow', function() {
            hideLoader();
        });

        // Tampilkan loader saat klik link menu internal
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            const target = link.getAttribute('target');

            if (
                href &&
                !href.startsWith('#') &&
                !href.startsWith('javascript:') &&
                !href.startsWith('mailto:') &&
                !href.startsWith('tel:') &&
                (!target || target === '_self') &&
                !e.ctrlKey && !e.metaKey && !e.shiftKey && !e.altKey &&
                e.button === 0 &&
                !link.hasAttribute('download') &&
                !link.dataset.noLoader
            ) {
                try {
                    const targetUrl = new URL(link.href, window.location.origin);
                    if (targetUrl.href === window.location.href) {
                        return;
                    }
                } catch (err) {}

                showLoader();
            }
        });

        // Tampilkan loader saat refresh atau sebelum unload halaman
        window.addEventListener('beforeunload', function() {
            showLoader();
        });
    })();
</script>
