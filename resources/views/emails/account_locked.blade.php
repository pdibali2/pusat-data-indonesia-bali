<div style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; line-height:1.4; color:#111">
    <h2>Hai {{ $name }},</h2>
    <p>Kami mendeteksi beberapa percobaan login yang gagal sehingga akun Anda sementara dikunci.</p>
    <p><strong>Email akun terkunci:</strong> {{ $user->email }}</p>
    <p>Untuk membuka kunci akun, klik tautan di bawah ini (tautan berlaku 24 jam):</p>
    <p><a href="{{ $unlockUrl }}">Buka kunci akun</a></p>
    <p>Jika Anda tidak melakukan percobaan login tersebut, pertimbangkan untuk mereset password Anda segera.</p>
    <p>Terima kasih,<br>Pusat Data Indonesia Bali</p>
</div>
