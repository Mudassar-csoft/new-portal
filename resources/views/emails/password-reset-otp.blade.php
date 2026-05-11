<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f5f7fb; padding: 24px; color: #222;">
    <table cellpadding="0" cellspacing="0" border="0" align="center" width="600" style="background: #fff; border-radius: 8px; padding: 24px;">
        <tr>
            <td>
                <h2 style="color: #0f3c6e; margin: 0 0 16px;">Password Reset Request</h2>
                <p>A password reset has been requested for the following user:</p>
                <table cellpadding="6" cellspacing="0" border="0" style="margin: 16px 0; border: 1px solid #e2e8f0; border-radius: 6px; width: 100%;">
                    <tr><td style="font-weight: 600; color: #54667a; width: 40%;">Name</td><td>{{ $user->name }}</td></tr>
                    <tr><td style="font-weight: 600; color: #54667a;">Email</td><td>{{ $user->email }}</td></tr>
                    <tr><td style="font-weight: 600; color: #54667a;">Request IP</td><td>{{ $requestIp }}</td></tr>
                    <tr><td style="font-weight: 600; color: #54667a;">Expires</td><td>{{ $expiresAt }}</td></tr>
                </table>
                <p>Share this OTP with the user only after verifying their identity:</p>
                <div style="background: #f3f8ff; border: 1px solid #cfe0f5; border-radius: 8px; padding: 24px; text-align: center; margin: 16px 0;">
                    <div style="font-size: 36px; font-weight: 700; letter-spacing: 8px; color: #0f3c6e;">{{ $otp }}</div>
                    <div style="font-size: 12px; color: #5b6b80; margin-top: 8px;">One-time password — expires in 15 minutes</div>
                </div>
                <p style="font-size: 12px; color: #8a99a8;">If you did not expect this request, you may safely ignore it. The OTP will expire automatically.</p>
            </td>
        </tr>
    </table>
</body>
</html>
