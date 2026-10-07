<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Checksheet - PROMISE NPC</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px 0;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); margin: 0 auto;">
                    
                    <!-- Header Bar -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 24px 32px; text-align: left;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">SYSTEM NOTIFICATION</div>
                                        <div style="font-size: 20px; font-weight: 700; color: #ffffff; margin: 0;">PROMISE NPC</div>
                                        <div style="font-size: 13px; color: #cbd5e1; margin-top: 2px;">Update Persetujuan (Approval) Checksheet</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px;">
                            
                            <!-- Status Banner -->
                            @if($actionType === 'APPROVED')
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 14px 20px; text-align: center; font-size: 14px; font-weight: 700; color: #15803d;">
                                        Persetujuan Diberikan (Stage: {{ $approvalStage }})
                                    </td>
                                </tr>
                            </table>
                            @elseif($actionType === 'REJECTED')
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 14px 20px; text-align: center; font-size: 14px; font-weight: 700; color: #b91c1c;">
                                        Checksheet Ditolak / Reject (Stage: {{ $approvalStage }})
                                    </td>
                                </tr>
                            </table>
                            @else
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 14px 20px; text-align: center; font-size: 14px; font-weight: 700; color: #b45309;">
                                        Menunggu Persetujuan Anda (Stage: {{ $approvalStage }})
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <div style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 24px;">
                                Transaksi Checksheet untuk part di bawah ini membutuhkan perhatian dan pembaruan oleh <strong>{{ $actionBy }}</strong>.
                            </div>

                            <!-- Data Summary Table -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 24px;">
                                <tr>
                                    <td width="35%" style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">Nomor PO</td>
                                    <td width="65%" style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 700; text-align: right;">{{ $poNo }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">Part Name</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 700; text-align: right;">{{ $partName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">Part No</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 700; text-align: right;">{{ $partNo }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">Customer</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 700; text-align: right;">{{ $customerName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 13px; color: #64748b; font-weight: 600;">Tahap Approval</td>
                                    <td style="padding: 12px 16px; font-size: 13px; color: #0f172a; font-weight: 700; text-align: right;">{{ $approvalStage }}</td>
                                </tr>
                            </table>

                            @if(!empty($notes))
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 4px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 14px 16px; font-size: 13px; color: #78350f; line-height: 1.5;">
                                        <strong style="display: block; margin-bottom: 4px;">Catatan / Alasan:</strong>
                                        {{ $notes }}
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- Call to Action Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $actionUrl }}" target="_blank" style="display: inline-block; background-color: #0f172a; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 14px; font-weight: 700; text-align: center; box-shadow: 0 2px 4px rgba(15, 23, 42, 0.2);">
                                            Buka Halaman Approval Checksheet &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 32px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #94a3b8; line-height: 1.5;">
                            Pemberitahuan otomatis dari sistem PROMISE NPC.<br>
                            Thread Reference: <span style="font-family: monospace; color: #64748b;">po-{{ preg_replace('/[^a-zA-Z0-9]/', '', strtolower($poNo)) }}</span>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
