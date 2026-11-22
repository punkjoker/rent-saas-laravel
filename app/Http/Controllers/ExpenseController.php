<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Landlord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    private function landlordId(): int
    {
        $landlordId = Landlord::where('user_id', Auth::id())->value('id');

        abort_unless($landlordId, 403, 'Landlord account not found.');

        return (int) $landlordId;
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $month = $filters['month'] ?? now()->format('Y-m');
        $search = trim($filters['search'] ?? '');
        $monthStart = Carbon::createFromFormat('Y-m', $month)
            ->startOfMonth()
            ->toDateString();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)
            ->endOfMonth()
            ->toDateString();

        $query = Expense::where('landlord_id', $this->landlordId())
            ->whereBetween('expense_date', [$monthStart, $monthEnd]);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $expenses = $query->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();
        $total = round($expenses->sum(fn ($expense) => (float) $expense->amount), 2);

        return view('expenses.index', compact('expenses', 'month', 'search', 'total'));
    }

    public function create()
    {
        return view('expenses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        Expense::create([
            'landlord_id' => $this->landlordId(),
            'expense_date' => $validated['expense_date'],
            'category' => trim($validated['category']),
            'amount' => $validated['amount'],
            'description' => trim($validated['description']),
            'recorded_by' => Auth::id(),
        ]);

        return redirect()
            ->route('expenses.index', [
                'month' => Carbon::parse($validated['expense_date'])->format('Y-m'),
            ])
            ->with('success', 'Expense recorded successfully.');
    }
}
