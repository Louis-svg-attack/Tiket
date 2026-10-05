<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankAccount\BankAccountRequest;
use App\Models\BankAccount;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly BranchContext $branchContext,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BankAccount::class);

        $query = BankAccount::query()->with('branch');
        $this->branchContext->scope($query);

        $accounts = $query
            ->when($request->filled('q'), fn ($q) => $q->where('bank_name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('bank_name')
            ->paginate(15)->withQueryString();

        return view('bank-accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        $this->authorize('create', BankAccount::class);

        return view('bank-accounts.create', [
            'account' => new BankAccount(['is_active' => true]),
            'branches' => $this->branchContext->selectableBranches(),
        ]);
    }

    public function store(BankAccountRequest $request): RedirectResponse
    {
        $this->authorize('create', BankAccount::class);

        $data = $request->validated();
        $data['branch_id'] = $data['branch_id'] ?? $this->branchContext->currentBranchId();

        $account = BankAccount::create($data);
        $this->activity->created($account, "Menambah rekening bank {$account->bank_name}");

        return redirect()->route('bank-accounts.index')->with('success', 'Rekening bank berhasil ditambahkan.');
    }

    public function edit(BankAccount $bankAccount): View
    {
        $this->authorize('update', $bankAccount);

        return view('bank-accounts.edit', [
            'account' => $bankAccount,
            'branches' => $this->branchContext->selectableBranches(),
        ]);
    }

    public function update(BankAccountRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('update', $bankAccount);

        $bankAccount->update($request->validated());
        $this->activity->updated($bankAccount, "Memperbarui rekening bank {$bankAccount->bank_name}");

        return redirect()->route('bank-accounts.index')->with('success', 'Rekening bank berhasil diperbarui.');
    }

    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('delete', $bankAccount);

        $bankAccount->delete();
        $this->activity->deleted($bankAccount, "Menghapus rekening bank {$bankAccount->bank_name}");

        return redirect()->route('bank-accounts.index')->with('success', 'Rekening bank berhasil dihapus.');
    }
}
