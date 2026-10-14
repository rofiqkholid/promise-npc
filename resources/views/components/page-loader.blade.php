<style>
    /* 1. Global Page Loader Root Layer Isolation (Mencegah reflow/repaint pada halaman utama) */
    #global-page-loader {
        contain: strict;
        isolation: isolate;
        pointer-events: auto;
        -webkit-user-select: none;
        user-select: none;
        will-change: opacity;
        transform: translateZ(0);
        -webkit-transform: translateZ(0);
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
    }

    /* 2. Pure GPU Hardware-Accelerated Off-Main-Thread Spinner (Tetap 60 FPS Mulus Meskipun JavaScript Sibuk) */
    .promise-gpu-spinner {
        display: block;
        width: 5rem;
        height: 5rem;
        position: absolute;
        inset: 0;
        transform-origin: 50% 50%;
        will-change: transform;
        transform: translate3d(0, 0, 0) rotateZ(0deg);
        -webkit-transform: translate3d(0, 0, 0) rotateZ(0deg);
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        perspective: 1000px;
        -webkit-perspective: 1000px;
        animation: promiseGpuSpin 0.75s linear infinite !important;
        -webkit-animation: promiseGpuSpin 0.75s linear infinite !important;
    }

    @keyframes promiseGpuSpin {
        0% {
            transform: translate3d(0, 0, 0) rotateZ(0deg);
        }
        100% {
            transform: translate3d(0, 0, 0) rotateZ(360deg);
        }
    }

    @-webkit-keyframes promiseGpuSpin {
        0% {
            -webkit-transform: translate3d(0, 0, 0) rotateZ(0deg);
        }
        100% {
            -webkit-transform: translate3d(0, 0, 0) rotateZ(360deg);
        }
    }

    [x-cloak] {
        display: none !important;
    }
</style>

<!-- Global Transparent Page Loader Overlay (Transparan Murni Tanpa Backdrop Blur yang Membebani GPU) -->
<div id="global-page-loader"
     class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/10 dark:bg-black/25 transition-opacity duration-200 ease-out select-none"
     style="opacity: 1; display: flex;">
    
    <!-- Spinner & Static Logo Container (Melayang Transparan Tanpa Kotak) -->
    <div class="relative flex items-center justify-center w-20 h-20 pointer-events-none">
        <!-- SVG Spinner Ring (Di-wrap dalam GPU Compositor Layer) -->
        <div class="promise-gpu-spinner">
            <svg class="w-full h-full text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.5"></circle>
                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <!-- Static PROMISE Logo in the Center (Tetap Diam di Tengah, Eager Decoded) -->
        <div class="relative flex items-center justify-center pointer-events-none">
            <img src="{{ asset('assets/image/logo-promise.png') }}"
                 alt="PROMISE"
                 class="h-10 w-auto drop-shadow-sm select-none"
                 loading="eager"
                 decoding="sync"
                 fetchpriority="high">
        </div>
    </div>
</div>

<script>
    (function() {
        const loader = document.getElementById('global-page-loader');
        if (!loader) return;

        let hideTimeout = null;
        let safetyTimeout = null;
        let isVisible = true;

        function hideLoader() {
            if (!loader) return;
            clearTimeout(hideTimeout);
            clearTimeout(safetyTimeout);
            isVisible = false;
            loader.classList.add('opacity-0', 'pointer-events-none');
            hideTimeout = setTimeout(function() {
                if (!isVisible) {
                    loader.style.display = 'none';
                }
            }, 200);
        }

        function showLoader() {
            if (!loader) return;
            clearTimeout(hideTimeout);
            clearTimeout(safetyTimeout);
            
            if (!isVisible) {
                isVisible = true;
                loader.style.display = 'flex';
                requestAnimationFrame(function() {
                    loader.classList.remove('opacity-0', 'pointer-events-none');
                });
            }

            // Safety fallback: jika halaman tidak berpindah dalam 2.5 detik (misal download file atau navigasi batal), sembunyikan loader otomatis
            safetyTimeout = setTimeout(function() {
                hideLoader();
            }, 2500);
        }

        window.hidePageLoader = hideLoader;
        window.showPageLoader = showLoader;

        // Hide loader setelah DOM siap
        function triggerHide() {
            requestAnimationFrame(function() {
                setTimeout(hideLoader, 60);
            });
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            triggerHide();
        } else {
            document.addEventListener('DOMContentLoaded', triggerHide);
            window.addEventListener('load', triggerHide);
        }

        // Safety fallback awal
        setTimeout(hideLoader, 3000);

        // Hide saat navigasi browser bfcache (Back/Forward) atau saat jendela browser kembali aktif
        window.addEventListener('pageshow', hideLoader);
        window.addEventListener('focus', hideLoader);

        // Flag untuk mencegah beforeunload memicu loader saat blob download
        window.isExportingBlob = false;

        // Fungsi pengecekan link export / download
        function isDownloadOrExportLink(link) {
            if (!link) return false;
            if (link.hasAttribute('download') || link.hasAttribute('data-no-loader') || link.classList.contains('no-loader')) {
                return true;
            }
            const href = (link.getAttribute('href') || '').toLowerCase();
            if (/(export|download|\.xlsx|\.xls|\.csv|\.pdf|\.zip|\.doc|\.docx)($|\?|\/)/i.test(href)) {
                return true;
            }
            const text = (link.textContent || '').trim().toLowerCase();
            if (/^(export|download)\b/i.test(text) || text.includes('export excel') || text.includes('export pdf') || text.includes('download')) {
                return true;
            }
            return false;
        }

        // Pengecekan spesifik apakah link ini merupakan unduhan file/export yang valid
        function isActualExportDownload(link) {
            if (!link) return false;
            // Abaikan link temporary bypass yang sengaja dibuat untuk memicu unduhan browser
            if (link.hasAttribute('data-bypass-loader') || link.dataset.bypassLoader === 'true') {
                return false;
            }
            const href = link.getAttribute('href');
            if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) {
                return false;
            }
            if (link.hasAttribute('download')) {
                return true;
            }
            const hrefLower = href.toLowerCase();
            if (/(export|download|\.xlsx|\.xls|\.csv|\.pdf|\.zip|\.doc|\.docx)($|\?|\/)/i.test(hrefLower)) {
                return true;
            }
            const textLower = (link.textContent || '').trim().toLowerCase();
            if (textLower.includes('export') || textLower.includes('download')) {
                return true;
            }
            return false;
        }

        // Handler tombol export dengan fetch blob: spinner aktif selama pembuatan file dan langsung berhenti saat unduhan siap
        async function handleExportButtonClick(link, e) {
            if (!isActualExportDownload(link)) return;

            // Jika sedang proses download pada tombol ini, cegah klik ganda
            if (link.dataset.isDownloading === 'true') {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                return;
            }

            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            link.dataset.isDownloading = 'true';
            const originalHtml = link.innerHTML;
            link.dataset.originalHtml = originalHtml;

            // Kunci tombol agar tidak bisa diklik berulang kali selama proses
            link.style.pointerEvents = 'none';
            link.classList.add('opacity-85', 'cursor-wait');

            // Tampilkan spinner pada tombol
            link.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Exporting...</span>`;

            // Mencegah beforeunload memicu overlay loader
            window.isExportingBlob = true;

            try {
                const response = await fetch(link.href, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`Export failed with HTTP status ${response.status}`);
                }

                // Cek jika response ternyata halaman HTML (misal sesi expired / redirect login)
                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('text/html')) {
                    window.location.href = response.url || link.href;
                    return;
                }

                // Ambil nama file dari header Content-Disposition
                let filename = '';
                const disposition = response.headers.get('content-disposition');
                if (disposition && disposition.indexOf('filename=') !== -1) {
                    const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, '').trim();
                    }
                }
                if (!filename) {
                    filename = link.getAttribute('download') || ('Checksheet_Export_' + Date.now() + '.xlsx');
                }

                // Konversi response stream menjadi file Blob
                const blob = await response.blob();
                const blobUrl = window.URL.createObjectURL(blob);

                // Buat link download sementara dengan bypass loader agar tidak dicegat
                const tempLink = document.createElement('a');
                tempLink.style.display = 'none';
                tempLink.href = blobUrl;
                tempLink.download = filename;
                tempLink.setAttribute('data-bypass-loader', 'true');
                document.body.appendChild(tempLink);
                tempLink.click();

                // Bersihkan object URL dan elemen sementara setelah browser memproses download
                setTimeout(function() {
                    window.URL.revokeObjectURL(blobUrl);
                    tempLink.remove();
                }, 1000);

                // Segera ubah status tombol menjadi Downloaded! dengan ikon centang
                link.innerHTML = `<i class="fa-solid fa-check text-white"></i> <span>Downloaded!</span>`;

                setTimeout(function() {
                    restoreExportButton(link, originalHtml);
                    window.isExportingBlob = false;
                }, 1200);

            } catch (err) {
                console.error('Export download error:', err);
                link.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-yellow-300"></i> <span>Export Failed</span>`;

                setTimeout(function() {
                    restoreExportButton(link, originalHtml);
                    window.isExportingBlob = false;
                }, 2000);
            }
        }

        function restoreExportButton(link, originalHtml) {
            if (!link) return;
            link.innerHTML = originalHtml;
            link.style.pointerEvents = '';
            link.classList.remove('opacity-85', 'cursor-wait');
            delete link.dataset.isDownloading;
            delete link.dataset.originalHtml;
        }

        // Tampilkan loader saat klik link menu internal
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            // Jika link download sementara internal kita, izinkan aksi native browser dan jangan diintervensi
            if (link.hasAttribute('data-bypass-loader') || link.dataset.bypassLoader === 'true') {
                return;
            }

            // Jika link export / download:
            // 1. Jangan pernah tampilkan full-page overlay loader
            // 2. Berikan spinner interaktif di tombol itu sendiri hingga selesai
            if (isDownloadOrExportLink(link)) {
                hideLoader();
                if (isActualExportDownload(link)) {
                    handleExportButtonClick(link, e);
                }
                return;
            }

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
                e.button === 0
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

        // Tampilkan loader saat form disubmit (misal simpan / update)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form) return;
            if (form.getAttribute('target') === '_blank' || form.classList.contains('no-loader') || form.hasAttribute('data-no-loader')) {
                return;
            }
            showLoader();
        });

        // Tampilkan loader saat refresh atau sebelum unload halaman
        window.addEventListener('beforeunload', function() {
            if (window.isExportingBlob) return;
            showLoader();
        });
    })();
</script>
