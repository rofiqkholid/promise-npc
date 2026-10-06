<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Approval PROMISE NPC</title>
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
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .content {
            padding: 24px;
        }
        .status-banner {
            padding: 14px;
            border-radius: 8px;
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 20px;
        }
        .status-REQUESTED { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-APPROVED { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .status-REJECTED { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
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
        .info-label { color: #64748b; font-weight: 500; }
        .info-value { color: #0f172a; font-weight: 600; }
        .notes-box {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 13px;
            color: #78350f;
            margin-bottom: 20px;
        }
        .btn {
            display: block;
            width: fit-content;
            margin: 24px auto 0 auto;
            padding: 12px 32px;
            background: #0f172a;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
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
            <h1>PROMISE NPC - Checksheet Approval</h1>
            <p>Notifikasi Transaksi Persetujuan</p>
        </div>
        <div class="content">
            <div class="status-banner status-{{ $actionType }}">
                @if($actionType === 'APPROVED')
                    ✅ PERSETUJUAN DIBERIKAN (Stage: {{ $approvalStage }})
                @elseif($actionType === 'REJECTED')
                    ❌ PENOLAKAN / REJECT (Stage: {{ $approvalStage }})
                @else
                    ⏳ MENUNGGU PERSETUJUAN ANDA (Stage: {{ $approvalStage }})
                @endif
            </div>

            <p style="font-size: 14px; color: #475569; line-height: 1.5;">
                Transaksi Checksheet untuk Part berikut membutuhkan perhatian / pembaruan status oleh <strong>{{ $actionBy }}</strong>.
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
                    <span class="info-label">Stage Approval</span>
                    <span class="info-value">{{ $approvalStage }}</span>
                </div>
            </div>

            @if(!empty($notes))
            <div class="notes-box">
                <strong>Catatan / Reason:</strong><br>
                {{ $notes }}
            </div>
            @endif

            <a href="{{ $actionUrl }}" class="btn" target="_blank">Buka Halaman Approval Checksheet</a>
        </div>
        <div class="footer">
            Email ini dikirimkan secara otomatis oleh Sistem PROMISE NPC.<br>
            Thread PO ID: <code>po-{{ preg_replace('/[^a-zA-Z0-9]/', '', strtolower($poNo)) }}</code>
        </div>
    </div>
</body>
</html>
