<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Produksi PROMISE NPC</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            background: #dbeafe;
            color: #1e40af;
            margin-bottom: 12px;
        }
        .content {
            padding: 24px;
        }
        .info-card {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 14px;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #64748b;
            font-weight: 500;
        }
        .info-value {
            color: #0f172a;
            font-weight: 600;
        }
        .step-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            text-align: center;
        }
        .step-title {
            font-size: 12px;
            color: #166534;
            font-weight: 700;
            text-transform: uppercase;
        }
        .step-name {
            font-size: 18px;
            color: #15803d;
            font-weight: 800;
            margin: 6px 0;
        }
        .btn {
            display: block;
            width: fit-content;
            margin: 24px auto 0 auto;
            padding: 12px 32px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
        }
        .footer {
            background: #f8fafc;
            padding: 16px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="badge">Sistem Notifikasi Otomatis</span>
            <h1>PROMISE NPC - Production Tracking</h1>
            <p>Update Progress Transaksi Produksi</p>
        </div>
        <div class="content">
            <p style="margin-top: 0; font-size: 15px; color: #334155;">
                Halo Tim <strong>{{ $nextDepartmentName }}</strong>,
            </p>
            <p style="font-size: 14px; color: #475569; line-height: 1.5;">
                Proses produksi sebelumnya untuk part di bawah ini telah selesai dikerjakan oleh <strong>{{ $completedBy }}</strong>. Silakan lanjutkan ke proses berikutnya.
            </p>

            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Nomor PO</span>
                    <span class="info-value">{{ $poNo }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Part Name</span>
                    <span class="info-value">{{ $partName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Part No</span>
                    <span class="info-value">{{ $partNo }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer</span>
                    <span class="info-value">{{ $customerName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Proses Selesai</span>
                    <span class="info-value" style="color: #16a34a;">✅ {{ $completedProcessName }}</span>
                </div>
            </div>

            <div class="step-box">
                <div class="step-title">📍 NEXT STEP (GILIRAN PROSES BERIKUTNYA)</div>
                <div class="step-name">{{ $nextProcessName }}</div>
                <div style="font-size: 13px; color: #166534;">Departemen: <strong>{{ $nextDepartmentName }}</strong></div>
            </div>

            <a href="{{ $actionUrl }}" class="btn" target="_blank">Buka Halaman Tracking QC / Produksi</a>
        </div>
        <div class="footer">
            Email ini dikirimkan secara otomatis oleh Sistem PROMISE NPC.<br>
            Thread PO ID: <code>po-{{ preg_replace('/[^a-zA-Z0-9]/', '', strtolower($poNo)) }}</code>
        </div>
    </div>
</body>
</html>
