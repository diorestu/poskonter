<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(): View
    {
        return view('expenses.index');
    }

    public function dashboard(): JsonResponse
    {
        $expenses = Expense::query()
            ->with('creator:id,name')
            ->latest('expense_date')
            ->latest('id')
            ->limit(50)
            ->get();

        return response()->json([
            'summary' => [
                'today_total' => (float) Expense::whereDate('expense_date', today())->sum('amount'),
                'month_total' => (float) Expense::whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
                'today_count' => Expense::whereDate('expense_date', today())->count(),
            ],
            'expenses' => $expenses,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expense_date' => ['nullable', 'date'],
            'shift' => ['nullable', 'string', 'in:pagi,sore'],
            'category' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:160'],
            'amount' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $expense = Expense::create([
            'reference_number' => 'EXP-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            'expense_date' => $validated['expense_date'] ?? now(),
            'shift' => $validated['shift'] ?? 'pagi',
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Pengeluaran berhasil dicatat.',
            'expense' => $expense->load('creator:id,name'),
        ], 201);
    }
}
