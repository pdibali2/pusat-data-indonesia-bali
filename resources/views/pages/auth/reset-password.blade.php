<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/js/app.ts', 'resources/css/app.css'])
    <title>Reset Password</title>
</head>
<body class="h-screen flex items-center justify-center bg-stikom/10">

    <div class="flex min-h-full min-w-full flex-row justify-center px-4 py-12 lg:px-4">

        <div class="w-full max-w-md shadow-xl border-b p-7 bg-stikom-blue">
            <h2 class="text-center text-xl/9 font-bold tracking-tight bg-linear-to-r from-white to-gray-300 bg-clip-text text-transparent">
                RESET PASSWORD
            </h2>
        </div>

        <div class="w-full max-w-md bg-white shadow-xl p-8">
            @if (session('reset_success'))
                {{-- ✅ Badge Sukses --}}
                <div class="flex flex-col items-center justify-center py-8 space-y-5">
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-green-100">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor" class="w-8 h-8 text-green-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>

                    <div class="text-center space-y-1">
                        <h3 class="text-lg font-semibold text-gray-900">Password Berhasil Direset!</h3>
                        <p class="text-sm text-gray-500">Silakan login menggunakan password baru Anda.</p>
                    </div>
                </div>

            @else
                {{-- Form Reset Password --}}
                <form action="/reset-password" method="POST" class="space-y-5" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    @if ($errors->any())
                        <div class="rounded-sm bg-red-50 border border-red-200 px-4 py-3">
                            @foreach ($errors->all() as $error)
                                <p class="text-sm text-red-600">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label for="password" class="block text-sm/6 font-medium text-gray-900">Password Baru</label>
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
                        <label for="password_confirmation" class="block text-sm/6 font-medium text-gray-900">Konfirmasi Password Baru</label>
                        <div class="mt-2">
                            <input id="password_confirmation" type="password" name="password_confirmation" required
                                class="block w-full rounded-sm bg-black/1 px-3 py-1.5 text-base text-black outline-1 -outline-offset-1 outline-black/30 focus:outline-2 focus:-outline-offset-2 focus:outline-stikom-blue sm:text-sm/6" />
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="flex w-full justify-center rounded-sm bg-linear-to-r from-blue-500 to-blue-400 px-3 py-1.5 text-white text-sm/6 font-semibold hover:from-blue-700 hover:to-blue-600 shadow-lg shadow-blue-500/30 transition duration-300 ease-in-out hover:scale-104">
                            Reset Password
                        </button>
                    </div>
                </form>
            @endif

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
    </script>
</body>
</html>