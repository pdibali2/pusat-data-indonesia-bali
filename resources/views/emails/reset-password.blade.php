<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="font-family: sans-serif; background:#f3f4f6; margin:0; padding:40px 0;">
    <div style="max-width:520px; margin:auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

        <div style="background:#2563eb; padding:24px; text-align:center;">
            <h1 style="color:#fff; margin:0; font-size:20px; font-weight:700; letter-spacing:1px;">
                PUSAT DATA
            </h1>
        </div>

        <div style="padding:32px;">
            <p style="color:#374151; font-size:15px;">Halo, <strong>{{ $user->name }}</strong>!</p>
            <p style="color:#374151; font-size:15px;">
                Kami menerima permintaan reset password untuk akun Anda. Klik tombol di bawah untuk membuat password baru.
            </p>

            <div style="text-align:center; margin:32px 0;">
                <a href="{{ url('/reset-password/' . $token . '?email=' . urlencode($user->email)) }}"
                   style="background:#2563eb; color:#fff; padding:12px 32px; border-radius:6px; text-decoration:none; font-weight:600; font-size:15px;">
                    Reset Password
                </a>
            </div>

            <p style="color:#6b7280; font-size:13px;">
                ⚠️ Link ini akan <strong>kadaluarsa dalam 24 jam</strong>.
            </p>
            <p style="color:#6b7280; font-size:13px;">
                Jika Anda tidak merasa meminta reset password, abaikan email ini. Password Anda tidak akan berubah.
            </p>
            <p style="color:#6b7280; font-size:13px; word-break:break-all;">
                Atau salin link berikut ke browser:<br>
                <a href="{{ url('/reset-password/' . $token . '?email=' . urlencode($user->email)) }}" style="color:#2563eb;">
                    {{ url('/reset-password/' . $token . '?email=' . urlencode($user->email)) }}
                </a>
            </p>
        </div>
    </div>
</body>
</html>