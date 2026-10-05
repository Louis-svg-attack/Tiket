<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Requests\Payment\GenerateBillsRequest;
use App\Http\Requests\Payment\RecordPaymentRequest;
use App\Models\BankAccount;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use App\Services\AccessContext;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly AccessContext $access,
        private readonly BranchContext $branchContext,
        private readonly PaymentService $payments,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $user = $request->user();

        $query = Payment::query()->with(['registration.pilgrim', 'registration.package']);

        if ($user && $user->isStaff()) {
            $this->branchContext->scope($query);
        } else {
            $query->whereIn('registration_id', $this->access->registrations()->select('id'));
        }

        $bills = $query
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->latest()
            ->paginate(15)->withQueryString();

        return view('payments.index', [
            'bills' => $bills,
            'statuses' => PaymentStatus::options(),
            'types' => PaymentType::options(),
        ]);
    }

    public function show(Registration $registration): View
    {
        $this->authorize('view', $registration);

        $registration->load(['pilgrim', 'package', 'payments.bankAccount']);

        return view('payments.show', [
            'registration' => $registration,
            'bankAccounts' => $this->bankAccounts(),
        ]);
    }

    public function generate(GenerateBillsRequest $request, Registration $registration): RedirectResponse
    {
        $this->authorize('create', Payment::class);
        $this->authorize('view', $registration);

        $data = $request->validated();

        $count = $this->payments->generateForRegistration(
            $registration,
            (int) $data['installments'],
            isset($data['dp_amount']) ? (float) $data['dp_amount'] : null,
            ! empty($data['first_due']) ? Carbon::parse($data['first_due']) : null,
        );

        if ($count === 0) {
            return redirect()->route('payments.bills', $registration)->with('error', 'Tagihan sudah pernah dibuat untuk pendaftaran ini.');
        }

        $this->activity->created($registration, "Membuat {$count} tagihan untuk {$registration->code}");

        return redirect()->route('payments.bills', $registration)->with('success', "Berhasil membuat {$count} tagihan.");
    }

    public function generateMassalForm(): View
    {
        $this->authorize('create', Payment::class);

        $packages = $this->branchContext->scope(Package::query())
            ->orderBy('name')->get()
            ->mapWithKeys(fn (Package $p) => [$p->id => $p->name])->all();

        return view('payments.generate', ['packages' => $packages]);
    }

    public function generateMassal(GenerateBillsRequest $request): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        $validated = $request->validate([
            'package_id' => ['required', 'integer', 'exists:packages,id'],
        ] + $request->rules());

        $package = $this->branchContext->scope(Package::query())->whereKey($validated['package_id'])->firstOrFail();

        $processed = $this->payments->generateMassal($package, [
            'installments' => (int) $validated['installments'],
            'dp_amount' => isset($validated['dp_amount']) ? (float) $validated['dp_amount'] : null,
            'first_due' => ! empty($validated['first_due']) ? Carbon::parse($validated['first_due']) : null,
        ]);

        $this->activity->log('generated', "Membuat tagihan massal paket {$package->name} ({$processed} jamaah)", $package);

        return redirect()->route('payments.index')->with('success', "Tagihan massal dibuat untuk {$processed} pendaftaran.");
    }

    public function record(RecordPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->authorize('record', $payment);

        $data = $request->validated();

        $this->payments->record($payment, (float) $data['amount'], [
            'method' => $data['method'] ?? null,
            'bank_account_id' => $data['bank_account_id'] ?? null,
            'paid_at' => ! empty($data['paid_at']) ? Carbon::parse($data['paid_at']) : null,
            'note' => $data['note'] ?? null,
        ]);

        $this->activity->updated($payment, "Mencatat pembayaran {$payment->code}");

        // TODO: tombol cetak kwitansi PDF ditambahkan menyusul.
        return redirect()->route('payments.bills', $payment->registration_id)->with('success', 'Pembayaran berhasil dicatat.');
    }

    private function bankAccounts()
    {
        return $this->branchContext->scope(BankAccount::query()->where('is_active', true))
            ->orderBy('bank_name')->get()
            ->mapWithKeys(fn (BankAccount $b) => [$b->id => $b->label()])->all();
    }
}
