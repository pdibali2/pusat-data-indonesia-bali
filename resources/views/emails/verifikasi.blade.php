<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Email</title>
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
                Terima kasih telah mendaftar. Klik tombol di bawah untuk memverifikasi email Anda.
            </p>

            <div style="text-align:center; margin:32px 0;">
                <a href="{{ url('/verify-email/' . $token) }}"
                   style="background:#2563eb; color:#fff; padding:12px 32px; border-radius:6px; text-decoration:none; font-weight:600; font-size:15px;">
                    Verifikasi Email
                </a>
            </div>

            <p style="color:#6b7280; font-size:13px;">
                Jika Anda tidak merasa mendaftar, abaikan email ini.
            </p>
            <p style="color:#6b7280; font-size:13px; word-break:break-all;">
                Atau salin link berikut ke browser:<br>
                <a href="{{ url('/verify-email/' . $token) }}" style="color:#2563eb;">
                    {{ url('/verify-email/' . $token) }}
                </a>
            </p>
        </div>
    </div>
</body>
</html>