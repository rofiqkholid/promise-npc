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
                    <label for="cameraSelection" class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Pilih Kamera:</label>
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
                </div>

                <!-- Status indicator -->
                <div id="qr-status" class="mt-3 flex items-center justify-center gap-2 py-2 px-3 text-xs font-semibold rounded-md border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 text-slate-700 dark:text-gray-300">
                    <i class="fa-solid fa-circle-notch fa-spin text-xs text-blue-500"></i> 
                    <span>Menyiapkan Engine Kamera...</span>
                </div>

                <p class="mt-2.5 text-[11px] text-gray-400 dark:text-gray-500 font-medium text-center">
                    Posisikan QR Code pada Quality Label tepat di dalam area bidik.
                </p>
            </div>
        </div>
    </div>
</div>

@push('style')
<style>
    #qr-reader video {
        object-fit: cover !important;
        border-radius: 6px !important;
        z-index: 10;
        position: relative;
    }
    #qr-reader {
        border: none !important;
    }
    #qr-reader__scan_region {
        background: black !important;
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

            $('#cameraSelection').on('change', (e) => {
                const newCamId = $(e.target).val();
                if (newCamId && newCamId !== this.currentCameraId) {
                    this.currentCameraId = newCamId;
                    this.restartCameraWithDevice(newCamId);
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
            $('#qr-status').html('<i class="fa-solid fa-circle-notch fa-spin text-xs mr-2 text-blue-500"></i> Menyiapkan Engine Kamera...');
            this.startCamera();
        }

        startCamera() {
            if (this.html5QrCode === null) {
                this.html5QrCode = new Html5Qrcode("qr-reader", { verbose: false });
            }

            Html5Qrcode.getCameras().then(cameras => {
                if (cameras && cameras.length) {
                    if (cameras.length > 1) {
                        $('#cameraSelectWrapper').removeClass('hidden');
                        let optionsHtml = '';
                        cameras.forEach(cam => {
                            const isBack = cam.label.toLowerCase().includes('back') || cam.label.toLowerCase().includes('rear') || cam.label.toLowerCase().includes('environment');
                            optionsHtml += `<option value="${cam.id}" ${isBack ? 'selected' : ''}>${cam.label || 'Camera ' + cam.id}</option>`;
                        });
                        $('#cameraSelection').html(optionsHtml);
                    }

                    const backCam = cameras.find(c => c.label.toLowerCase().includes('back') || c.label.toLowerCase().includes('rear') || c.label.toLowerCase().includes('environment'));
                    const selectedId = this.currentCameraId || (backCam ? backCam.id : cameras[0].id);
                    this.currentCameraId = selectedId;
                    this.startCameraWithDevice(selectedId);
                } else {
                    this.startCameraWithFacingMode();
                }
            }).catch(err => {
                console.warn("Could not list cameras, falling back to facingMode:", err);
                this.startCameraWithFacingMode();
            });
        }

        startCameraWithDevice(cameraId) {
            const config = {
                fps: 20,
                qrbox: (w, h) => {
                    const min = Math.min(w, h);
                    const size = Math.floor(min * 0.85);
                    return { width: size, height: size };
                },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true,
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
            };

            this.html5QrCode.start(
                cameraId,
                config,
                (decodedText) => this.onScanSuccess(decodedText),
                () => {}
            ).then(() => {
                $('#qr-status').html('<i class="fa-solid fa-expand fa-beat text-xs mr-2 text-emerald-500"></i> Kamera Aktif - Arahkan ke QR Label');
                this.applyMirror();
            }).catch(err => {
                this.onCameraError(err);
            });
        }

        startCameraWithFacingMode() {
            const config = {
                fps: 20,
                qrbox: (w, h) => {
                    const min = Math.min(w, h);
                    const size = Math.floor(min * 0.85);
                    return { width: size, height: size };
                },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true,
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                videoConstraints: {
                    facingMode: "environment"
                }
            };

            this.html5QrCode.start(
                { facingMode: "environment" },
                config,
                (decodedText) => this.onScanSuccess(decodedText),
                () => {}
            ).then(() => {
                $('#qr-status').html('<i class="fa-solid fa-expand fa-beat text-xs mr-2 text-emerald-500"></i> Kamera Aktif - Arahkan ke QR Label');
                this.applyMirror();
            }).catch(err => {
                this.onCameraError(err);
            });
        }

        restartCameraWithDevice(cameraId) {
            if (this.html5QrCode && this.html5QrCode.isScanning) {
                this.html5QrCode.stop().then(() => {
                    this.startCameraWithDevice(cameraId);
                }).catch(console.error);
            } else {
                this.startCameraWithDevice(cameraId);
            }
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
            const urlMatch = qrText.match(/\/tracking\/([a-zA-Z0-9_-]+)(?:\/scan)?/);
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
