<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Career Institute CRM</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f5f7fb; padding: 24px; color: #222;">
    <table cellpadding="0" cellspacing="0" border="0" align="center" width="600" style="background: #fff; border-radius: 8px; padding: 24px;">
        <tr>
            <td>
                <h2 style="color: #0f3c6e; margin: 0 0 16px;">Welcome to Career Institute CRM</h2>
                <p>Hi {{ $user->name }},</p>
                <p>An account has been created for you on the Career Institute CRM.</p>
                <table cellpadding="6" cellspacing="0" border="0" style="margin: 16px 0; border: 1px solid #e2e8f0; border-radius: 6px;">
                    <tr><td style="font-weight: 600; color: #54667a;">Email</td><td>{{ $user->email }}</td></tr>
                    <tr><td style="font-weight: 600; color: #54667a;">Role(s)</td><td>{{ $roleList ?: 'No role assigned yet' }}</td></tr>
                    @if($assignedBy)
                        <tr><td style="font-weight: 600; color: #54667a;">Created by</td><td>{{ $assignedBy }}</td></tr>
                    @endif
                </table>
                <p>To set your password and activate your account, click the button below. The link is valid for <strong>1 hour</strong>.</p>
                <p style="margin: 24px 0;">
                    <a href="{{ $setupUrl }}" style="background: #19b6e6; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">Set My Password</a>
                </p>
                <p style="font-size: 12px; color: #5b6b80;">If the button does not work, copy and paste this URL into your browser:</p>
                <p style="font-size: 12px; word-break: break-all; color: #5b6b80;">{{ $setupUrl }}</p>
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;">
                <p style="font-size: 12px; color: #8a99a8;">If you did not expect this email, please ignore it. The setup link will expire automatically.</p>
            </td>
        </tr>
    </table>
</body>
</html>
