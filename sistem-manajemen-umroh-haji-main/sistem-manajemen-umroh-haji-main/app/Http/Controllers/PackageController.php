<?php

namespace App\Http\Controllers;

use App\Enums\PackageStatus;
use App\Enums\PackageType;
use App\Enums\RegistrationStatus;
use App\Http\Requests\Package\PackageRequest;
use App\Models\Airline;
use App\Models\Hotel;
use App\Models\Package;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use App\Services\PackageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly BranchContext $branchContext,
        private readonly PackageService $packages,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Package::class);

        $query = Package::query()
            ->with(['hotelMakkah', 'hotelMadinah', 'airline', 'branch'])
            ->withCount(['registrations as active_registrations_count' => fn ($q) => $q->where('status', '!=', RegistrationStatus::Cancelled->value)]);
        $this->branchContext->scope($query);

        $packages = $query
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->string('q').'%')->orWhere('code', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)->withQueryString();

        return view('packages.index', compact('packages'));
    }

    public function create(): View
    {
        $this->authorize('create', Package::class);

        return view('packages.create', $this->formData(new Package([
            'type' => PackageType::Umroh,
            'status' => PackageStatus::Draft,
            'duration_days' => 9,
        ])));
    }

    public function store(PackageRequest $request): RedirectResponse
    {
        $this->authorize('create', Package::class);

        $package = $this->packages->createPackage($request->validated());
        $this->activity->created($package, "Menambah paket {$package->name}");

        return redirect()->route('packages.index')->with('success', 'Paket berhasil ditambahkan.');
    }

    public function show(Package $package): View
    {
        $this->authorize('view', $package);

        $package->load(['hotelMakkah', 'hotelMadinah', 'airline', 'branch']);

        return view('packages.show', compact('package'));
    }

    public function edit(Package $package): View
    {
        $this->authorize('update', $package);

        return view('packages.edit', $this->formData($package));
    }

    public function update(PackageRequest $request, Package $package): RedirectResponse
    {
        $this->authorize('update', $package);

        $this->packages->updatePackage($package, $request->validated());
        $this->activity->updated($package, "Memperbarui paket {$package->name}");

        return redirect()->route('packages.index')->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $this->authorize('delete', $package);

        $package->delete();
        $this->activity->deleted($package, "Menghapus paket {$package->name}");

        return redirect()->route('packages.index')->with('success', 'Paket berhasil dihapus.');
    }

    /**
     * Shared payload for the create/edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(Package $package): array
    {
        return [
            'package' => $package,
            'branches' => $this->branchContext->selectableBranches(),
            'hotelsMakkah' => Hotel::query()->where('is_active', true)->where('city', 'Mekah')->orderBy('name')->get(),
            'hotelsMadinah' => Hotel::query()->where('is_active', true)->where('city', 'Madinah')->orderBy('name')->get(),
            'airlines' => Airline::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
