<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/js/app.ts', 'resources/css/app.css'])
    <title>Registrasi</title>
</head>
<body class="h-screen flex items-center justify-center bg-stikom/10">

    <div class="flex min-h-full min-w-full flex-row justify-center px-4 py-12 lg:px-4">

        <div class="w-full max-w-md shadow-xl border-b p-7 bg-stikom-blue">
            <h2 class="text-center text-xl/9 font-bold tracking-tight bg-linear-to-r from-white to-gray-300 bg-clip-text text-transparent">
                DAFTAR AKUN
            </h2>
        </div>

        <div class="w-full max-w-md bg-white shadow-xl p-8">
            <form action="/register" method="POST" class="space-y-5" novalidate>
                @csrf

                @if ($errors->any())
                    <div class="rounded-sm bg-red-50 border border-red-200 px-4 py-3">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-600">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if (session('success'))
                    <div class="rounded-sm bg-green-50 border border-green-200 px-4 py-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                @endif

                <div>
                    <label for="name" class="block text-sm/6 font-medium text-gray-900">Nama Lengkap</label>
                    <div class="mt-2">
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                            class="block w-full rounded-sm bg-black/1 px-3 py-1.5 text-base text-black outline-1 -outline-offset-1 outline-black/30 focus:outline-2 focus:-outline-offset-2 focus:outline-stikom-blue sm:text-sm/6" />
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-sm/6 font-medium text-gray-900">Username</label>
                    <div class="mt-2">
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required
                            class="block w-full rounded-sm bg-black/1 px-3 py-1.5 text-base text-black outline-1 -outline-offset-1 outline-black/30 focus:outline-2 focus:-outline-offset-2 focus:outline-stikom-blue sm:text-sm/6" />
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email</label>
                    <div class="mt-2">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            class="block w-full rounded-sm bg-black/1 px-3 py-1.5 text-base text-black outline-1 -outline-offset-1 outline-black/30 focus:outline-2 focus:-outline-offset-2 focus:outline-stikom-blue sm:text-sm/6" />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                    <div class="mt-2 relative">
                        <input id="password" type="password" name="password" required
                            class="block w-full rounded-sm bg-black/1 px-3 py-1.5 pr-10 text-base text-black outline-1 -outline-offset-1 outline-black/30 focus:outline-2 focus:-outline-offset-2 focus:outline-stikom-blue sm:text-sm/6" />
                        <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm/6 font-medium text-gray-900">Konfirmasi Password</label>
                    <div class="mt-2">
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            class="block w-full rounded-sm bg-black/1 px-3 py-1.5 text-base text-black outline-1 -outline-offset-1 outline-black/30 focus:outline-2 focus:-outline-offset-2 focus:outline-stikom-blue sm:text-sm/6" />
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <input 
                        id="privacy_policy" 
                        type="checkbox" 
                        name="privacy_policy" 
                        class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                    />
                    <label for="privacy_policy" class="text-sm text-gray-600">
                        Saya telah membaca dan menyetujui
                        <button 
                            type="button" 
                            id="openPrivacyModal"
                            class="text-blue-600 hover:underline font-medium"
                        >
                            Kebijakan Privasi
                        </button>
                    </label>
                </div>

                {{-- Modal Kebijakan Privasi --}}
                <div 
                    id="privacyModal" 
                    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
                >
                    <div class="w-full max-w-lg bg-white rounded-sm shadow-xl">
                        
                        {{-- Header Modal --}}
                        <div class="flex items-center justify-between bg-stikom-blue px-6 py-4">
                            <h3 class="text-white font-bold text-base tracking-wide">Kebijakan Privasi</h3>
                            <button type="button" id="closePrivacyModal" class="text-white hover:text-gray-200 text-xl font-bold">
                                &times;
                            </button>
                        </div>

                        {{-- Isi Kebijakan --}}
                        <div class="px-6 py-5 max-h-96 overflow-y-auto text-sm text-gray-700 space-y-4">
                            <p class="font-semibold text-gray-900">1. Pengumpulan Data</p>
                            <p>Kami mengumpulkan data pribadi seperti nama, username, dan email yang Anda berikan saat mendaftar untuk keperluan pengelolaan akun.</p>

                            <p class="font-semibold text-gray-900">2. Penggunaan Data</p>
                            <p>Data yang dikumpulkan digunakan untuk autentikasi, komunikasi terkait layanan, serta peningkatan kualitas platform Pusat Data Indonesia Bali.</p>

                            <p class="font-semibold text-gray-900">3. Keamanan Data</p>
                            <p>Kami menerapkan langkah-langkah keamanan teknis untuk melindungi data Anda dari akses tidak sah, perubahan, atau penghapusan.</p>

                            <p class="font-semibold text-gray-900">4. Berbagi Data</p>
                            <p>Kami tidak menjual atau membagikan data pribadi Anda kepada pihak ketiga tanpa persetujuan Anda, kecuali diwajibkan oleh hukum.</p>

                            <p class="font-semibold text-gray-900">5. Hak Pengguna</p>
                            <p>Anda berhak mengakses, memperbarui, atau menghapus data pribadi Anda dengan menghubungi administrator platform.</p>

                            <p class="font-semibold text-gray-900">6. Perubahan Kebijakan</p>
                            <p>Kebijakan ini dapat diperbarui sewaktu-waktu tanpa pemberitahuan sebelumnya.</p>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="px-6 py-4 border-t flex justify-end gap-3">
                            <button 
                                type="button" 
                                id="closePrivacyModalBtn"
                                class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-sm hover:bg-gray-50"
                            >
                                Tutup
                            </button>
                            <button 
                                type="button" 
                                id="agreePrivacyBtn"
                                class="px-4 py-2 text-sm text-white bg-blue-600 rounded-sm hover:bg-blue-700 font-semibold"
                            >
                                Saya Setuju
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit"
                        class="flex w-full justify-center rounded-sm bg-linear-to-r from-blue-500 to-blue-400 px-3 py-1.5 text-white text-sm/6 font-semibold hover:from-blue-700 hover:to-blue-600 shadow-lg shadow-blue-500/30 transition duration-300 ease-in-out hover:scale-104">
                        Daftar
                    </button>
                </div>

                <p class="text-center text-sm text-gray-500">
                    Sudah punya akun?
                    <a href="/login" class="text-blue-600 hover:underline font-medium">Login</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        const password = document.getElementById("password");
        const toggle = document.getElementById("togglePassword");
        const eyeIcon = document.getElementById("eyeIcon");

        toggle.addEventListener("click", function () {
            if (password.type === "password") {
                password.type = "text";
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.58 10.58a3 3 0 004.24 4.24" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.1A9.77 9.77 0 0112 4.5c6 0 9.75 7.5 9.75 7.5a15.3 15.3 0 01-4.24 5.33" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.53 6.53A15.05 15.05 0 002.25 12s3.75 7.5 9.75 7.5c1.24 0 2.39-.22 3.44-.61" />`;
            } else {
                password.type = "password";
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5z" />`;
            }
        });

        // Modal Kebijakan Privasi
        const modal = document.getElementById('privacyModal');
        const openBtn = document.getElementById('openPrivacyModal');
        const closeBtn = document.getElementById('closePrivacyModal');
        const closeBtnFooter = document.getElementById('closePrivacyModalBtn');
        const agreeBtn = document.getElementById('agreePrivacyBtn');
        const checkbox = document.getElementById('privacy_policy');

        openBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        closeBtn.addEventListener('click', closeModal);
        closeBtnFooter.addEventListener('click', closeModal);

        // Klik di luar modal = tutup
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        // Tombol "Saya Setuju" = centang checkbox + tutup modal
        agreeBtn.addEventListener('click', () => {
            checkbox.checked = true;
            closeModal();
        });
    </script>
</body>
</html>