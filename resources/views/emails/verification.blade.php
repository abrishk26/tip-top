<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #007bff;
        }
        .content {
            padding: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to Tip-Top!</h1>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }},</h2>
        
        <p>Thank you for registering with us! To complete your registration, please verify your email address by clicking the button below:</p>
        
        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
        </div>
        
        <p>If the button above doesn't work, you can copy and paste this link into your browser:</p>
        <p style="word-break: break-all; color: #007bff;">{{ $verificationUrl }}</p>
        
        <div class="warning">
            <strong>Important:</strong> This verification link will expire in 24 hours. If you don't verify your email within this time, you'll need to request a new verification link.
        </div>
        
        <p>After verifying your email, you'll be asked to set a password to complete your account setup.</p>
        
        <p>If you didn't create an account with us, please ignore this email.</p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} Tip-Top. All rights reserved.</p>
        <p>This is an automated email, please do not reply.</p>
    </div>
</body>
</html>
