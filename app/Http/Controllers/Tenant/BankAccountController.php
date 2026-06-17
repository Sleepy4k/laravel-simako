<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    public function index(Request $request): Response
    {
        $bankAccounts = $request->user()->bankAccounts()->latest()->get();

        return Inertia::render('Tenant/BankAccounts/Index', ['bankAccounts' => $bankAccounts]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:255'],
            'is_primary' => ['boolean'],
        ]);

        if ($validated['is_primary'] ?? false) {
            $request->user()->bankAccounts()->update(['is_primary' => false]);
        }

        $request->user()->bankAccounts()->create($validated);

        return back()->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        abort_if($bankAccount->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:255'],
            'is_primary' => ['boolean'],
        ]);

        if ($validated['is_primary'] ?? false) {
            $request->user()->bankAccounts()->where('id', '!=', $bankAccount->id)->update(['is_primary' => false]);
        }

        $bankAccount->update($validated);

        return back()->with('success', 'Rekening berhasil diperbarui.');
    }

    public function destroy(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        abort_if($bankAccount->user_id !== $request->user()->id, 403);

        $bankAccount->delete();

        return back()->with('success', 'Rekening berhasil dihapus.');
    }
}
