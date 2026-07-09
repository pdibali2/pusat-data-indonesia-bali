<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Undangan Organisasi</title>
</head>
<body>
    <h2>Undangan bergabung ke organisasi</h2>
    <p>Anda telah diundang untuk bergabung ke organisasi <strong>{{ $organization->name }}</strong>.</p>
    <p>Silakan buka tautan berikut untuk menerima undangan:</p>
    <p><a href="{{ route('invitation.accept', ['token' => $token]) }}">{{ route('invitation.accept', ['token' => $token]) }}</a></p>
    <p>Apabila Anda belum memiliki akun, gunakan alamat email ini saat mendaftar: <strong>{{ $email }}</strong>.</p>
    <p>Terima kasih.</p>
</body>
</html>
