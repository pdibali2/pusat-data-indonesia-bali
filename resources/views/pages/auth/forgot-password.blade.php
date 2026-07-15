<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/js/app.ts', 'resources/css/app.css'])
    <title>Lupa Password</title>
</head>
<body class="h-screen flex items-center justify-center bg-stikom/10">

    <div class="flex min-h-full min-w-full flex-row justify-center px-4 py-12 lg:px-4">

        <div class="w-full max-w-md shadow-xl border-b p-7 bg-stikom-blue">
            <h2 class="text-center text-xl/9 font-bold tracking-tight bg-linear-to-r from-white to-gray-300 bg-clip-text text-transparent">
                LUPA PASSWORD
            </h2>
        </div>

        <div class="w-full max-w-md bg-white shadow-xl p-8">
            <p class="text-sm text-gray-500 mb-6">
                Masukkan email yang terdaftar. Kami akan mengirimkan link untuk reset password.
            </p>

            <form action="/forgot-password" method="POST" class="space-y-5" novalidate>
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
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email</label>
                    <div class="mt-2">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            class="block w-full rounded-sm bg-black/1 px-3 py-1.5 text-base text-black outline-1 -outline-offset-1 outline-black/30 focus:outline-2 focus:-outline-offset-2 focus:outline-stikom-blue sm:text-sm/6" />
                    </div>
                </div>

                <div>
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response" value="">
                    <button type="submit"
                        class="flex w-full justify-center rounded-sm bg-linear-to-r from-blue-500 to-blue-400 px-3 py-1.5 text-white text-sm/6 font-semibold hover:from-blue-700 hover:to-blue-600 shadow-lg shadow-blue-500/30 transition duration-300 ease-in-out hover:scale-104">
                        Kirim Link Reset
                    </button>
                </div>

                <p class="text-center text-sm text-gray-500">
                    Ingat password?
                    <a href="/login" class="text-blue-600 hover:underline font-medium">Login</a>
                </p>
            </form>
        </div>
    </div>

    @php $recaptchaSiteKey = env('RECAPTCHA_SITE_KEY'); @endphp
    @if($recaptchaSiteKey)
        <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
    @endif

    <script>
        const form = document.querySelector('form[action="/forgot-password"]');
        const tokenInput = document.getElementById('g-recaptcha-response');

        if (form && tokenInput && '{{ $recaptchaSiteKey }}') {
            form.addEventListener('submit', function (event) {
                if (tokenInput.value) {
                    return;
                }

                event.preventDefault();

                if (!window.grecaptcha) {
                    form.submit();
                    return;
                }

                window.grecaptcha.ready(function () {
                    window.grecaptcha.execute('{{ $recaptchaSiteKey }}', { action: 'forgot_password' })
                        .then(function (token) {
                            tokenInput.value = token;
                            form.submit();
                        });
                });
            });
        }
    </script>
</body>
</html>