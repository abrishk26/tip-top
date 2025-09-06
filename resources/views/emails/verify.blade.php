<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
        <tr>
            <td style="padding: 20px; text-align: center; background: #4f46e5; color: #ffffff;">
                <h1 style="margin: 0; font-size: 20px;">Verify Your Email</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px; color: #333;">
                <p style="font-size: 16px;">Hello,</p>
                <p style="font-size: 16px;">
                    Thanks for signing up! Please click the button below to verify your email address.
                </p>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ $verificationUrl }}" 
                       style="display: inline-block; padding: 12px 24px; background: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">
                        Verify Email
                    </a>
                </p>
                <p style="font-size: 14px; color: #666;">
                    If the button above doesn’t work, copy and paste this link into your browser:
                </p>
                <p style="font-size: 14px; word-break: break-word; color: #4f46e5;">
                    {{ $verificationUrl }}
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px; text-align: center; background: #f3f4f6; font-size: 12px; color: #999;">
                © {{ date('Y') }} YourApp. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
