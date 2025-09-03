<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Status Update</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
        <tr>
            <td style="padding: 20px; text-align: center; background: #4f46e5; color: #ffffff;">
                <h1 style="margin: 0; font-size: 20px;">Registration Status Update</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px; color: #333;">
                <p style="font-size: 16px;">Hello {{ $providerName }},</p>
                @if ($status === 'accepted')
                    <p style="font-size: 16px;">Good news! Your registration has been <strong>accepted</strong>.</p>
                @else
                    <p style="font-size: 16px;">We’re sorry to inform you that your registration has been <strong>rejected</strong>.</p>
                @endif

                <p style="font-size: 14px; color: #666;">If you have any questions, please reply to this email.</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px; text-align: center; background: #f3f4f6; font-size: 12px; color: #999;">
                © {{ date('Y') }} TipTop. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
