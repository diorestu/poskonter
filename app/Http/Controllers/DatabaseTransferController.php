<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PDO;
use Throwable;

class DatabaseTransferController extends Controller
{
    public function export()
    {
        abort_unless(config('database.default') === 'sqlite', 422, 'Export database hanya tersedia untuk SQLite.');

        $path = database_path('database.sqlite');
        abort_unless(File::exists($path), 404, 'File database tidak ditemukan.');

        return response()->download($path, 'zeni_pos_backup_' . now()->format('Ymd_His') . '.sqlite');
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(config('database.default') === 'sqlite', 422, 'Import database hanya tersedia untuk SQLite.');

        $validated = $request->validate([
            'database_file' => ['required', 'file', 'max:51200'],
        ]);

        $currentUser = $request->user();
        $pdo = new PDO('sqlite:' . $validated['database_file']->getRealPath());
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $tables = ['users', 'providers', 'products', 'sales', 'sale_items', 'stock_movements', 'wallets', 'pulsa_sales'];
        $imported = [];

        foreach ($tables as $table) {
            if (! $this->hasTable($pdo, $table)) {
                return back()->withErrors([
                    'database_file' => "File database tidak valid. Tabel {$table} tidak ditemukan.",
                ]);
            }

            $imported[$table] = $this->readTable($pdo, $table);
        }

        $imported['product_wallet'] = $this->hasTable($pdo, 'product_wallet')
            ? $this->readTable($pdo, 'product_wallet')
            : [];
        $imported['provider_wallet'] = $this->hasTable($pdo, 'provider_wallet')
            ? $this->readTable($pdo, 'provider_wallet')
            : [];
        $imported['expenses'] = $this->hasTable($pdo, 'expenses')
            ? $this->readTable($pdo, 'expenses')
            : [];

        if (! collect($imported['users'])->contains(fn (array $row) => ($row['email'] ?? null) === $currentUser->email)) {
            $imported['users'][] = $currentUser->only(['id', 'name', 'email', 'role', 'password', 'email_verified_at', 'remember_token', 'created_at', 'updated_at']);
        }

        DB::beginTransaction();

        try {
            DB::statement('PRAGMA foreign_keys=OFF');

            foreach (['product_wallet', 'provider_wallet', 'pulsa_sales', 'sale_items', 'sales', 'stock_movements', 'expenses', 'wallets', 'products', 'providers', 'users'] as $table) {
                DB::table($table)->delete();
            }

            foreach (['users', 'providers', 'products', 'wallets', 'provider_wallet', 'product_wallet', 'expenses', 'sales', 'sale_items', 'stock_movements', 'pulsa_sales'] as $table) {
                $columns = Schema::getColumnListing($table);
                $rows = collect($imported[$table])
                    ->map(fn (array $row) => array_intersect_key($row, array_flip($columns)))
                    ->values();

                foreach ($rows->chunk(200) as $chunk) {
                    if ($chunk->isNotEmpty()) {
                        DB::table($table)->insert($chunk->all());
                    }
                }
            }

            DB::statement('PRAGMA foreign_keys=ON');
            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            return back()->withErrors([
                'database_file' => 'Import database gagal: ' . $exception->getMessage(),
            ]);
        }

        $restoredUserId = DB::table('users')->where('email', $currentUser->email)->value('id');
        if ($restoredUserId) {
            Auth::loginUsingId($restoredUserId);
            $request->session()->regenerate();
        }

        return back()->with('status', 'Database berhasil diimport.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readTable(PDO $pdo, string $table): array
    {
        $statement = $pdo->query("SELECT * FROM {$table}");

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    private function hasTable(PDO $pdo, string $table): bool
    {
        return (bool) $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'")->fetchColumn();
    }
}
