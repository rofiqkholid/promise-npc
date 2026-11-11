{{-- Scanner Modal Partial for Quality Label QR Scanner --}}
@php
    $mode = $mode ?? 'mgm'; // 'mgm' or 'approval' or 'auto'
    $scanButtonId = $scanButtonId ?? '#btn-scan-qr';
    $modalId = $modalId ?? '#scannerModal';
@endphp

<div id="scannerModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-[100] justify-center items-center w-full h-full bg-slate-900/70 backdrop-blur-sm flex p-4 transition-all duration-300">
    <div class="relative w-full max-w-md h-auto">
        <div class="relative bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between bg-slate-50 dark:bg-gray-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm font-bold shadow-xs">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white tracking-tight leading-tight">Scan Quality Label</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Arahkan kamera ke QR Code label</p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" id="toggleTorch" class="w-8 h-8 rounded-lg hidden flex items-center justify-center text-gray-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition" title="Nyalakan Lampu Flash">
                        <i class="fa-solid fa-bolt text-xs"></i>
                    </button>
                    <button type="button" id="toggleMirror" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-gray-700 transition" title="Mirror Camera">
                        <i class="fa-solid fa-arrows-left-right text-xs"></i>
                    </button>
                    <button type="button" id="closeScanner" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Scanner Viewport Area -->
            <div class="p-4">
                <!-- Camera Select if multiple available -->
                <div id="cameraSelectWrapper" class="mb-3 hidden">
                    <div class="flex items-center justify-between mb-1">
                        <label for="cameraSelection" class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400">
                            <i class="fa-solid fa-camera-rotate mr-1 text-blue-500"></i> Pilih Kamera:
                        </label>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">Pilih kamera belakang utama</span>
                    </div>
                    <select id="cameraSelection" class="w-full text-xs py-1.5 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-white rounded-md shadow-xs">
                    </select>
                </div>

                <div class="relative overflow-hidden bg-black aspect-square rounded-lg border-2 border-slate-300 dark:border-gray-600 shadow-inner group">
                    <div id="qr-reader" class="w-full h-full overflow-hidden bg-black flex items-center justify-center"></div>
                    
                    <!-- Viewfinder Overlay frame corners -->
                    <div class="pointer-events-none absolute inset-6 border border-white/20 rounded-lg flex flex-col justify-between p-2 z-20">
                        <div class="flex justify-between">
                            <div class="w-6 h-6 border-t-2 border-l-2 border-blue-400"></div>
                            <div class="w-6 h-6 border-t-2 border-r-2 border-blue-400"></div>
                        </div>
                        <!-- Laser line animation -->
                        <div class="w-full h-0.5 bg-gradient-to-r from-transparent via-red-500 to-transparent shadow-[0_0_8px_rgba(239,68,68,0.9)] animate-pulse opacity-90 my-auto"></div>
                        <div class="flex justify-between">
                            <div class="w-6 h-6 border-b-2 border-l-2 border-blue-400"></div>
                            <div class="w-6 h-6 border-b-2 border-r-2 border-blue-400"></div>
                        </div>
                    </div>

                    <!-- Floating Zoom Controls -->
                    <div id="zoomControls" class="hidden absolute bottom-3 right-3 z-30 flex items-center gap-1 bg-black/70 backdrop-blur-xs p-1 rounded-lg border border-white/20 shadow-md">
                        <span class="text-[10px] text-white/70 px-1 font-semibold"><i class="fa-solid fa-magnifying-glass-plus text-[9px] mr-0.5"></i>Zoom</span>
                        <button type="button" data-zoom="1" class="btn-zoom active px-2 py-0.5 rounded text-[11px] font-bold text-white bg-blue-600 transition">1x</button>
                        <button type="button" data-zoom="2" class="btn-zoom px-2 py-0.5 rounded text-[11px] font-bold text-white/80 hover:text-white hover:bg-white/20 transition">2x</button>
                        <button type="button" data-zoom="3" class="btn-zoom hidden px-2 py-0.5 rounded text-[11px] font-bold text-white/80 hover:text-white hover:bg-white/20 transition">3x</button>
                    </div>
                </div>

                <!-- Status indicator -->
                <div id="qr-status" class="mt-3 flex items-center justify-center gap-2 py-2 px-3 text-xs font-semibold rounded-md border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 text-slate-700 dark:text-gray-300">
                    <i class="fa-solid fa-circle-notch fa-spin text-xs text-blue-500"></i> 
                    <span>Membuka Kamera...</span>
                </div>

                <!-- Fallback: Take or Upload Photo directly -->
                <div class="mt-2.5 flex items-center gap-2">
                    <label for="qrFileInput" class="flex-1 cursor-pointer flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-lg border border-slate-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:bg-slate-100 dark:hover:bg-gray-600 text-xs font-semibold text-slate-700 dark:text-gray-200 transition shadow-xs">
                        <i class="fa-solid fa-camera text-blue-500"></i>
                        <span>Ambil / Upload Foto Label</span>
                    </label>
                    <input type="file" id="qrFileInput" accept="image/*" class="hidden">
                </div>

                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 space-y-0.5 text-center">
                    <p class="flex items-center gap-1 justify-center">
                        <i class="fa-solid fa-circle-info text-blue-500 text-[10px]"></i>
                        <span>Jaga jarak ~20-30 cm agar fokus tajam, gunakan <b>zoom 2x</b> jika label kecil.</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('style')
<style>
    #qr-reader {
        border: none !important;
        width: 100% !important;
        height: 100% !important;
        position: relative;
    }
    #qr-reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        border-radius: 6px !important;
        z-index: 10;
        position: relative;
    }
    #qr-reader__scan_region {
        background: black !important;
        width: 100% !important;
        height: 100% !important;
    }
    #qr-reader__dashboard_section,
    #qr-reader__dashboard {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    class QualityLabelScanner {
        constructor(config = {}) {
            this.mode = config.mode || '{{ $mode }}';
            this.scanButtonId = config.scanButtonId || '{{ $scanButtonId }}';
            this.modalId = config.modalId || '{{ $modalId }}';
            this.html5QrCode = null;
            this.isMirrored = false;
            this.isTorchOn = false;
            this.currentZoom = 1;
            this.zoomCapabilities = null;
            this.currentCameraId = null;
            this.isProcessing = false;

            // Hardware wedge buffer
            this.scannerBuffer = "";
            this.scannerTimeout = null;

            this.init();
        }

        init() {
            this.initHardwareListener();
            this.initCameraListener();
        }

        initHardwareListener() {
            $(document).on('keypress', (e) => {
                if ($(e.target).is('input, textarea, select')) return;
                
                if (this.scannerTimeout) clearTimeout(this.scannerTimeout);

                if (e.which === 13) { // Enter
                    if (this.scannerBuffer.length > 3) {
                        e.preventDefault();
                        this.processQR(this.scannerBuffer);
                        this.scannerBuffer = "";
                    }
                } else {
                    this.scannerBuffer += String.fromCharCode(e.which);
                }

                this.scannerTimeout = setTimeout(() => {
                    this.scannerBuffer = "";
                }, 60);
            });
        }

        initCameraListener() {
            $(document).on('click', this.scanButtonId, () => {
                this.openScanner();
            });

            $('#closeScanner').on('click', () => {
                this.stopCamera();
            });

            $(this.modalId).on('click', (e) => {
                if (e.target === $(this.modalId)[0]) {
                    this.stopCamera();
                }
            });

            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && !$(this.modalId).hasClass('hidden')) {
                    this.stopCamera();
                }
            });

            $('#toggleMirror').on('click', () => {
                this.isMirrored = !this.isMirrored;
                this.applyMirror();
                $('#toggleMirror').toggleClass('text-blue-600 dark:text-blue-400', this.isMirrored);
            });

            $('#toggleTorch').on('click', () => {
                this.toggleTorch();
            });

            $(document).on('click', '.btn-zoom', (e) => {
                const targetZoom = Number($(e.currentTarget).data('zoom'));
                if (targetZoom) {
                    this.setZoom(targetZoom);
                }
            });

            $('#qrFileInput').on('change', (e) => {
                this.handleFileInput(e);
            });

            $('#cameraSelection').on('change', (e) => {
                const newCamId = $(e.target).val();
                if (newCamId && newCamId !== this.currentCameraId) {
                    this.switchCamera(newCamId);
                }
            });
        }

        openScanner() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Koneksi HTTPS Diperlukan',
                        text: 'Browser seluler memerlukan koneksi aman (HTTPS) untuk mengizinkan akses kamera.',
                        confirmButtonColor: '#3b82f6'
                    });
                    return;
                }
            }

            this.isProcessing = false;
            $(this.modalId).removeClass('hidden').addClass('flex');
            $('#qr-status').html('<i class="fa-solid fa-circle-notch fa-spin text-xs mr-2 text-blue-500"></i> Membuka Kamera...');
            this.startCamera();
        }

        startCamera() {
            if (this.html5QrCode === null) {
                this.html5QrCode = new Html5Qrcode("qr-reader", { verbose: false });
            }

            const scanConfig = {
                fps: 10,
                qrbox: (w, h) => {
                    const min = Math.min(w, h);
                    const size = Math.floor(min * 0.82);
                    return { width: size, height: size };
                },
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
            };

            const cameraTarget = this.currentCameraId 
                ? this.currentCameraId 
                : { facingMode: "environment" };

            this.html5QrCode.start(
                cameraTarget,
                scanConfig,
                (decodedText) => this.onScanSuccess(decodedText),
                () => {}
            ).then(() => {
                $('#qr-status').html('<i class="fa-solid fa-expand fa-beat text-xs mr-2 text-emerald-500"></i> Kamera Aktif - Arahkan ke QR Label');
                this.applyMirror();
                this.detectCapabilities();
                this.loadCameraList();
            }).catch(err => {
                console.warn("Primary camera start failed, attempting fallback:", err);

                // Fallback for laptops/desktops where environment camera doesn't exist
                if (typeof cameraTarget !== 'string') {
                    this.html5QrCode.start(
                        { facingMode: "user" },
                        scanConfig,
                        (decodedText) => this.onScanSuccess(decodedText),
                        () => {}
                    ).then(() => {
                        $('#qr-status').html('<i class="fa-solid fa-expand fa-beat text-xs mr-2 text-emerald-500"></i> Kamera Aktif - Arahkan ke QR Label');
                        this.applyMirror();
                        this.detectCapabilities();
                        this.loadCameraList();
                    }).catch(err2 => {
                        this.onCameraError(err2);
                    });
                } else {
                    this.onCameraError(err);
                }
            });
        }

        loadCameraList() {
            Html5Qrcode.getCameras().then(cameras => {
                if (cameras && cameras.length > 1) {
                    $('#cameraSelectWrapper').removeClass('hidden');
                    let optionsHtml = '';
                    cameras.forEach(cam => {
                        const isBack = cam.label.toLowerCase().includes('back') || 
                                       cam.label.toLowerCase().includes('rear') || 
                                       cam.label.toLowerCase().includes('environment') ||
                                       cam.label.toLowerCase().includes('belakang');
                        const isSelected = this.currentCameraId ? (cam.id === this.currentCameraId) : isBack;
                        optionsHtml += `<option value="${cam.id}" ${isSelected ? 'selected' : ''}>${cam.label || 'Kamera ' + cam.id.substring(0, 8)}</option>`;
                    });
                    $('#cameraSelection').html(optionsHtml);
                }
            }).catch(() => {});
        }

        switchCamera(newCamId) {
            if (!newCamId || newCamId === this.currentCameraId) return;
            this.currentCameraId = newCamId;

            $('#qr-status').html('<i class="fa-solid fa-circle-notch fa-spin text-xs mr-2 text-blue-500"></i> Mengganti Kamera...');

            if (this.html5QrCode && this.html5QrCode.isScanning) {
                this.html5QrCode.stop().then(() => {
                    this.startCamera();
                }).catch(() => {
                    this.startCamera();
                });
            } else {
                this.startCamera();
            }
        }

        getTrack() {
            try {
                const video = document.querySelector('#qr-reader video');
                if (video && video.srcObject && typeof video.srcObject.getVideoTracks === 'function') {
                    const tracks = video.srcObject.getVideoTracks();
                    if (tracks && tracks.length > 0) {
                        return tracks[0];
                    }
                }
            } catch (e) {}
            return null;
        }

        detectCapabilities() {
            this.isTorchOn = false;
            this.currentZoom = 1;
            this.updateTorchUI();
            this.updateZoomUI(1);

            setTimeout(() => {
                try {
                    const track = this.getTrack();
                    if (!track || typeof track.getCapabilities !== 'function') return;

                    const caps = track.getCapabilities();

                    // Check Torch
                    if (caps.torch) {
                        $('#toggleTorch').removeClass('hidden');
                    } else {
                        $('#toggleTorch').addClass('hidden');
                    }

                    // Check Zoom
                    if (caps.zoom) {
                        this.zoomCapabilities = caps.zoom;
                        $('#zoomControls').removeClass('hidden');
                        if (caps.zoom.max >= 3) {
                            $('#zoomControls button[data-zoom="3"]').removeClass('hidden');
                        } else {
                            $('#zoomControls button[data-zoom="3"]').addClass('hidden');
                        }
                    } else {
                        $('#zoomControls').addClass('hidden');
                    }
                } catch (e) {
                    console.warn("Track capabilities query error:", e);
                }
            }, 300);
        }

        async toggleTorch() {
            const track = this.getTrack();
            if (!track) return;

            this.isTorchOn = !this.isTorchOn;

            try {
                await track.applyConstraints({
                    advanced: [{ torch: this.isTorchOn }]
                });
                this.updateTorchUI();
            } catch (e) {
                console.warn("Torch failed:", e);
                this.isTorchOn = false;
                this.updateTorchUI();
            }
        }

        updateTorchUI() {
            const btn = $('#toggleTorch');
            if (this.isTorchOn) {
                btn.addClass('text-amber-500 bg-amber-50 dark:bg-amber-950/40').removeClass('text-gray-400');
                btn.attr('title', 'Matikan Lampu Flash');
            } else {
                btn.removeClass('text-amber-500 bg-amber-50 dark:bg-amber-950/40').addClass('text-gray-400');
                btn.attr('title', 'Nyalakan Lampu Flash');
            }
        }

        async setZoom(level) {
            const track = this.getTrack();
            if (!track || !this.zoomCapabilities) return;

            const min = this.zoomCapabilities.min || 1;
            const max = this.zoomCapabilities.max || 1;
            const targetZoom = Math.min(Math.max(level, min), max);

            try {
                await track.applyConstraints({
                    advanced: [{ zoom: targetZoom }]
                });
                this.currentZoom = level;
                this.updateZoomUI(level);
            } catch (e) {
                console.warn("Zoom constraint failed:", e);
            }
        }

        updateZoomUI(level) {
            $('.btn-zoom').removeClass('bg-blue-600 text-white').addClass('text-white/80 hover:text-white hover:bg-white/20');
            $(`.btn-zoom[data-zoom="${level}"]`).addClass('bg-blue-600 text-white').removeClass('text-white/80 hover:text-white hover:bg-white/20');
        }

        handleFileInput(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            this.scanFile(file);
            e.target.value = '';
        }

        async scanFile(file) {
            if (this.isProcessing) return;

            $('#qr-status').html('<i class="fa-solid fa-spinner fa-spin text-xs text-blue-500 mr-2"></i> Menganalisis gambar...');

            if (this.html5QrCode && this.html5QrCode.isScanning) {
                try {
                    await this.html5QrCode.stop();
                } catch (e) {}
            }

            if (!this.html5QrCode) {
                this.html5QrCode = new Html5Qrcode("qr-reader", { verbose: false });
            }

            this.html5QrCode.scanFile(file, true)
                .then(decodedText => {
                    this.onScanSuccess(decodedText);
                })
                .catch(err => {
                    console.error("Scan file error:", err);
                    $('#qr-status').html('<i class="fa-solid fa-triangle-exclamation text-xs text-amber-500 mr-2"></i> QR Code tidak terdeteksi pada gambar.');
                    Swal.fire({
                        icon: 'warning',
                        title: 'QR Code Tidak Terdeteksi',
                        text: 'Pastikan foto label tajam (tidak buram), pencahayaan cukup, dan seluruh kotak QR Code terlihat jelas.',
                        confirmButtonColor: '#3b82f6',
                        confirmButtonText: 'Buka Kamera Kembali'
                    }).then(() => {
                        this.startCamera();
                    });
                });
        }

        onCameraError(err) {
            console.error("Camera start error:", err);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Membuka Kamera',
                text: 'Pastikan Anda telah memberikan izin akses kamera pada browser Anda.',
                confirmButtonColor: '#3b82f6'
            });
            this.stopCamera();
        }

        stopCamera() {
            this.isTorchOn = false;
            this.updateTorchUI();
            $('#zoomControls').addClass('hidden');
            $('#toggleTorch').addClass('hidden');
            $(this.modalId).addClass('hidden').removeClass('flex');
            
            if (this.html5QrCode && this.html5QrCode.isScanning) {
                this.html5QrCode.stop().catch(() => {});
            }
        }

        applyMirror() {
            const video = $('#qr-reader video')[0];
            if (video) {
                video.style.transform = this.isMirrored ? 'scaleX(-1)' : 'scaleX(1)';
            }
        }

        playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.15);
            } catch(e) {}
        }

        extractHashedId(qrText) {
            if (!qrText) return null;
            qrText = qrText.trim();

            // 1. URL pattern: /tracking/{hash}/scan or /tracking/{hash}
            const urlMatch = qrText.match(/\/tracking\/([a-zA-Z0-9_-]+)(?:\/scan)?(?:[?#]|$)/);
            if (urlMatch && urlMatch[1]) {
                return urlMatch[1];
            }

            // 2. Direct hash string (5 to 30 alphanumeric characters)
            if (/^[a-zA-Z0-9_-]{5,30}$/.test(qrText)) {
                return qrText;
            }

            // 3. JSON format
            if (qrText.startsWith('{') && qrText.endsWith('}')) {
                try {
                    const data = JSON.parse(qrText);
                    return data.id || data.hashed_id || data.part_id || null;
                } catch (e) {}
            }

            return null;
        }

        onScanSuccess(decodedText) {
            if (this.isProcessing) return;
            this.isProcessing = true;
            this.playBeep();
            this.stopCamera();

            this.processQR(decodedText);
        }

        processQR(decodedText) {
            const hashedId = this.extractHashedId(decodedText);
            if (!hashedId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format QR Tidak Dikenal',
                    text: 'QR Code ini tidak sesuai dengan format Quality Label PROMISE NPC.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            Swal.fire({
                title: 'Memverifikasi Quality Label...',
                html: '<div class="flex flex-col items-center gap-3 py-2"><i class="fa-solid fa-spinner fa-spin text-3xl text-blue-500"></i><span class="text-sm text-gray-500">Memeriksa data part dan checksheet...</span></div>',
                allowOutsideClick: false,
                showConfirmButton: false
            });

            // Fetch part info from scan endpoint
            fetch(`{{ url('/tracking') }}/${hashedId}/scan?format=json`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(data => { throw new Error(data.message || 'Gagal memverifikasi label'); });
                }
                return res.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Label tidak valid');
                }

                if (this.mode === 'mgm') {
                    // In MGM Check (Part Event Delivery Check):
                    if (data.can_mgm && data.mgm_url) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Quality Label Ditemukan!',
                            html: `Part: <b>${data.part.part_no}</b><br><small class="text-gray-500">Membuka form Part Event Delivery Checksheet...</small>`,
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = data.mgm_url;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Akses Ditolak',
                            text: 'Anda tidak memiliki hak akses untuk Input MGM Check pada part ini.',
                            confirmButtonColor: '#3b82f6'
                        });
                    }
                } else if (this.mode === 'approval') {
                    // In Checksheet Approvals:
                    if (!data.can_approval) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Akses Ditolak',
                            text: 'Anda tidak memiliki hak akses untuk Checksheet Approval.',
                            confirmButtonColor: '#3b82f6'
                        });
                    } else if (data.is_in_approval && data.approval_url) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Checksheet Ditemukan!',
                            html: `Part: <b>${data.part.part_no}</b> (PO: ${data.part.po_no})<br><small class="text-gray-500">Membuka lembar approval checksheet...</small>`,
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = data.approval_url;
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Belum Masuk Fase Approval',
                            html: `Part: <b>${data.part.part_no}</b> (PO: ${data.part.po_no})<br>Status saat ini: <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">${data.part.status}</span><br><br><span class="text-xs text-gray-500">Part ini belum selesai proses MGM Check atau belum diajukan ke fase approval.</span>`,
                            confirmButtonColor: '#3b82f6'
                        });
                    }
                } else {
                    // Auto mode / fallback to scan options
                    window.location.href = `{{ url('/tracking') }}/${hashedId}/scan`;
                }
            })
            .catch(err => {
                console.error("Scan error:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Quality Label Tidak Ditemukan',
                    text: err.message || 'QR Code tidak terdaftar dalam sistem.',
                    confirmButtonColor: '#3b82f6'
                });
            });
        }
    }

    $(document).ready(function() {
        window.qualityLabelScanner = new QualityLabelScanner({
            mode: '{{ $mode }}',
            scanButtonId: '{{ $scanButtonId }}',
            modalId: '{{ $modalId }}'
        });
    });
</script>
@endpush