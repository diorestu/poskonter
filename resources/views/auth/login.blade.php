<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Login - Zeni POS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="m-0 flex min-h-screen items-center justify-center bg-[#edf2f8] p-4">
        <div class="w-full max-w-md rounded-[10px] border border-[#d6e1ed] bg-white p-6 shadow-[0_20px_45px_rgba(15,23,42,0.12)]">
            <div class="mb-5">
                <div class="text-[22px] font-semibold">Login</div>
                <p class="mt-1 text-sm text-[color:var(--muted)]">Masuk untuk mengakses aplikasi POS.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.authenticate') }}" class="space-y-4" id="loginForm">
                @csrf
                <div>
                    <label class="mb-1 block font-medium text-xs">Email</label>
                    <input class="field" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="admin@zeni.local" required autofocus>
                </div>
                <div>
                    <label class="mb-1 block font-medium text-xs">Password</label>
                    <input class="field" type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-[color:var(--muted)] cursor-pointer">
                        <input type="checkbox" name="remember" value="1">
                        <span>Ingat saya</span>
                    </label>
                </div>
                <button class="btn btn-primary w-full py-2" type="submit">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M10 17 15 12 10 7"/><path d="M4 12h11"/></svg></span>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <div class="mt-6 border-t border-[#edf2f7] pt-4">
                <div class="mb-2 text-[11px] font-medium text-[color:var(--muted)] text-center">Login Cepat (Akun Demo / Uji Coba):</div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" class="btn btn-secondary text-xs py-1.5 quick-login" data-email="admin@zeni.local" data-pass="admin12345">
                        <span class="font-medium">Admin</span>
                    </button>
                    <button type="button" class="btn btn-secondary text-xs py-1.5 quick-login" data-email="cashier@zeni.local" data-pass="cashier12345">
                        <span class="font-medium">Kasir</span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            document.querySelectorAll('.quick-login').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('email').value = this.dataset.email;
                    document.getElementById('password').value = this.dataset.pass;
                    document.getElementById('loginForm').submit();
                });
            });
        </script>
    </body>
</html>
