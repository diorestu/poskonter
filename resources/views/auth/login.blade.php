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
                <div class="app-alert-error mb-4 px-3 py-2 text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.authenticate') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block font-medium">Email</label>
                    <input class="field" type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div>
                    <label class="mb-1 block font-medium">Password</label>
                    <input class="field" type="password" name="password" required>
                </div>
                <label class="flex items-center gap-2 text-sm text-[color:var(--muted)]">
                    <input type="checkbox" name="remember" value="1">
                    <span>Ingat saya</span>
                </label>
                <button class="btn btn-primary w-full" type="submit">
                    <span class="btn-icon"><svg viewBox="0 0 24 24"><path d="M10 17 15 12 10 7"/><path d="M4 12h11"/></svg></span>
                    <span>Masuk</span>
                </button>
            </form>
        </div>
    </body>
</html>
