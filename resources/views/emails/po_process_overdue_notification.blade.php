<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Escalation - PROMISE NPC</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px 0;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); margin: 0 auto;">
                    
                    <!-- Header Bar (Urgent Red/Dark Slate) -->
                    <tr>
                        <td style="background-color: #881337; padding: 24px 32px; text-align: left;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <div style="font-size: 11px; font-weight: 700; color: #fecdd3; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">SYSTEM ESCALATION NOTIFICATION</div>
                                        <div style="font-size: 20px; font-weight: 800; color: #ffffff; margin: 0;">PROMISE NPC</div>
                                        <div style="font-size: 13px; color: #ffe4e6; margin-top: 2px;">Overdue Process Escalation Alert</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px;">
                            <div style="font-size: 15px; color: #334155; margin-bottom: 16px;">
                                Dear <strong>{{ $departmentName }} {{ $recipientRole }}</strong>,
                            </div>
                            <div style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 24px;">
                                This is an automated notification from the PROMISE NPC system. The production process below has <strong>exceeded its scheduled target date by {{ $daysOverdue }} day(s)</strong> and is still awaiting completion. Please review and ensure immediate follow-up with your team.
                            </div>

                            <!-- Overdue Highlight Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 6px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 16px 20px; text-align: center;">
                                        <div style="font-size: 11px; font-weight: 700; color: #9f1239; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">PROCESS EXCEEDED TARGET DATE</div>
                                        <div style="font-size: 18px; font-weight: 800; color: #881337; margin-bottom: 4px;">{{ $processName }} ({{ $departmentName }})</div>
                                        <div style="font-size: 13px; color: #be123c; font-weight: 600;">
                                            Target: <span style="text-decoration: underline;">{{ $targetDateFormatted }}</span> &bull; 
                                            <span style="background-color: #e11d48; color: #ffffff; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">Overdue {{ $daysOverdue }} Day(s)</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Data Summary Table -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 28px;">
                                <tr>
                                    <td width="38%" style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">PO Number</td>
                                    <td width="62%" style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 700; text-align: right;">{{ $poNo }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">Part Name</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 700; text-align: right;">{{ $partName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">Part No.</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 700; text-align: right;">{{ $partNo }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">Customer</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 700; text-align: right;">{{ $customerName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600;">Responsible Department</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: 700; text-align: right;">{{ $departmentName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 13px; color: #64748b; font-weight: 600;">Current Status</td>
                                    <td style="padding: 12px 16px; font-size: 13px; color: #d97706; font-weight: 700; text-align: right;">{{ $currentStatus }}</td>
                                </tr>
                            </table>

                            <!-- Call to Action Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $actionUrl }}" target="_blank" style="display: inline-block; background-color: #9f1239; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 14px; font-weight: 700; text-align: center; box-shadow: 0 2px 4px rgba(159, 18, 57, 0.25);">
                                            Open Production Tracking &amp; Take Action &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 32px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #94a3b8; line-height: 1.5;">
                            Automated escalation alert from PROMISE NPC system.<br>
                            Thread Reference: <span style="font-family: monospace; color: #64748b;">po-{{ preg_replace('/[^a-zA-Z0-9]/', '', strtolower($poNo)) }}</span>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

