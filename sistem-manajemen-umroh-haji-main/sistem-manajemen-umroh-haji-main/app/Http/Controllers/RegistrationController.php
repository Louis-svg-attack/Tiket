<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Http\Requests\Registration\RegistrationRequest;
use App\Models\Guide;
use App\Models\Package;
use App\Models\Registration;
use App\Services\AccessContext;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly AccessContext $access,
        private readonly BranchContext $branchContext,
        private readonly RegistrationService $registrations,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Registration::class);

        $registrations = $this->access->registrations()
            ->with(['pilgrim', 'package', 'agent', 'guide'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('package'), fn ($q) => $q->where('package_id', $request->integer('package')))
            ->latest()
            ->paginate(15)->withQueryString();

        return view('registrations.index', [
            'registrations' => $registrations,
            'packages' => $this->packageOptions(),
            'statuses' => RegistrationStatus::options(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Registration::class);

        return view('registrations.create', $this->formData(new Registration([
            'registered_at' => now()->toDateString(),
        ])));
    }

    public function store(RegistrationRequest $request): RedirectResponse
    {
        $this->authorize('create', Registration::class);

        try {
            $registration = $this->registrations->book($request->validated());
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $this->activity->created($registration, "Mendaftarkan jamaah {$registration->pilgrim?->name}");

        return redirect()->route('registrations.show', $registration)->with('success', 'Pendaftaran berhasil dibuat.');
    }

    public function show(Registration $registration): View
    {
        $this->authorize('view', $registration);

        $registration->load(['pilgrim', 'package', 'agent', 'guide', 'branch', 'payments']);

        return view('registrations.show', [
            'registration' => $registration,
            'statuses' => RegistrationStatus::options(),
        ]);
    }

    public function edit(Registration $registration): View
    {
        $this->authorize('update', $registration);

        return view('registrations.edit', $this->formData($registration));
    }

    public function update(RegistrationRequest $request, Registration $registration): RedirectResponse
    {
        $this->authorize('update', $registration);

        $registration->update($request->safe()->only(['guide_id', 'registered_at', 'notes']));
        $this->activity->updated($registration, "Memperbarui pendaftaran {$registration->code}");

        return redirect()->route('registrations.show', $registration)->with('success', 'Pendaftaran berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Registration $registration): RedirectResponse
    {
        $this->authorize('update', $registration);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', RegistrationStatus::values())],
        ]);

        $this->registrations->updateStatus($registration, RegistrationStatus::from($validated['status']));
        $this->activity->updated($registration, "Mengubah status pendaftaran {$registration->code}");

        return redirect()->route('registrations.show', $registration)->with('success', 'Status pendaftaran diperbarui.');
    }

    public function destroy(Registration $registration): RedirectResponse
    {
        $this->authorize('delete', $registration);

        $registration->delete();
        $this->activity->deleted($registration, "Menghapus pendaftaran {$registration->code}");

        return redirect()->route('registrations.index')->with('success', 'Pendaftaran berhasil dihapus.');
    }

    /**
     * Shared payload for the create/edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(Registration $registration): array
    {
        $packages = $this->branchContext->scope(Package::open())
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Package $p) => [
                $p->id => $p->name.' — sisa '.$p->remainingQuota().' — '.currency($p->price),
            ])->all();

        $guides = $this->branchContext->scope(Guide::query()->where('is_active', true))
            ->orderBy('name')->get()
            ->mapWithKeys(fn (Guide $g) => [$g->id => $g->name])->all();

        return [
            'registration' => $registration,
            'pilgrims' => $this->access->pilgrims()->orderBy('name')->get()
                ->mapWithKeys(fn ($p) => [$p->id => $p->name])->all(),
            'packages' => $packages,
            'guides' => $guides,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function packageOptions(): array
    {
        return $this->branchContext->scope(Package::query())
            ->orderBy('name')->get()
            ->mapWithKeys(fn (Package $p) => [$p->id => $p->name])->all();
    }
}
