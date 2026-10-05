<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Http\Requests\Manifest\ManifestEntryRequest;
use App\Models\Package;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use App\Services\ManifestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ManifestController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly BranchContext $branchContext,
        private readonly ManifestService $manifests,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Package::class);

        $query = Package::query()
            ->with('branch')
            ->withCount(['registrations as active_registrations_count' => fn ($q) => $q->where('status', '!=', RegistrationStatus::Cancelled->value)]);
        $this->branchContext->scope($query);

        $packages = $query->latest('departure_date')->paginate(15)->withQueryString();

        return view('manifests.index', compact('packages'));
    }

    public function show(Package $package): View
    {
        $this->authorize('view', $package);

        $registrations = $package->registrations()
            ->where('status', '!=', RegistrationStatus::Cancelled->value)
            ->with(['pilgrim', 'manifestEntry'])
            ->get()
            ->sortBy(fn ($r) => $r->pilgrim?->name ?? '')
            ->values();

        return view('manifests.show', compact('package', 'registrations'));
    }

    public function update(ManifestEntryRequest $request, Package $package): RedirectResponse
    {
        $this->authorize('view', $package);

        $rows = (array) $request->validated('rows', []);
        $this->manifests->saveEntries($package, $rows);
        $this->activity->log('updated', "Memperbarui manifest paket {$package->name}", $package);

        return redirect()->route('manifests.show', $package)->with('success', 'Manifest berhasil disimpan.');
    }
}
