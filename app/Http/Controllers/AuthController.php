<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        Log::info('--- LOGIN ATTEMPT INITIATED ---', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'email' => $request->input('email'),
            'session_driver' => config('session.driver'),
            'session_id' => $request->session()->getId(),
        ]);

        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            Log::warning('Login Validation Failed', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        }

        try {
            $user = User::where('email', $credentials['email'])->first();

            if (! $user) {
                $availableUsers = User::pluck('email')->toArray();
                Log::warning('Login Failed: User Not Found in Database', [
                    'email_searched' => $credentials['email'],
                    'available_users_in_db' => $availableUsers,
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Akun dengan email ini tidak ditemukan di database server. User terdaftar: ' . (count($availableUsers) ? implode(', ', $availableUsers) : 'Belum ada user, jalankan php artisan db:seed'),
                ]);
            }

            $passwordMatch = Hash::check($credentials['password'], $user->password);
            if (! $passwordMatch) {
                Log::warning('Login Failed: Password Mismatch', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_role' => $user->role,
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Password yang dimasukkan salah.',
                ]);
            }

            if (! Auth::attempt($credentials, $request->boolean('remember'))) {
                Log::warning('Login Failed: Auth::attempt returned false', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Gagal melakukan otentikasi login.',
                ]);
            }

            $intended = $request->session()->pull('url.intended');
            $request->session()->regenerate();

            Log::info('Login Successful', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'user_role' => Auth::user()->role,
                'redirect_to' => $intended ?: route('pos.index'),
            ]);

            return redirect()->to($intended ?: route('pos.index'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('CRITICAL LOGIN ERROR (Database/System Exception)', [
                'error_message' => $e->getMessage(),
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Terjadi kesalahan sistem di server: ' . $e->getMessage(),
            ]);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User Logged Out', ['user_id' => $userId]);

        return redirect()->route('login');
    }
}
