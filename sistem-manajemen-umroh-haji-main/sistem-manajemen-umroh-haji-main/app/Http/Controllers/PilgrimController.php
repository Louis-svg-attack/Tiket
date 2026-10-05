<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Http\Requests\Pilgrim\PilgrimRequest;
use App\Models\Agent;
use App\Models\Pilgrim;
use App\Services\AccessContext;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use App\Services\PilgrimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PilgrimController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly BranchContext $branchContext,
        private readonly PilgrimService $pilgrims,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Pilgrim::class);

        $pilgrims = app(AccessContext::class)->pilgrims()
            ->with(['branch', 'agent', 'user'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('nik', 'like', '%'.$request->string('q').'%')
                ->orWhere('passport_number', 'like', '%'.$request->string('q').'%')
                ->orWhere('code', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->string('status') === 'active'))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('pilgrims.index', compact('pilgrims'));
    }

    public function create(): View
    {
        $this->authorize('create', Pilgrim::class);

        return view('pilgrims.create', [
            'pilgrim' => new Pilgrim(['is_active' => true, 'gender' => Gender::Male]),
            'branches' => $this->branchContext->selectableBranches(),
            'agents' => $this->selectableAgents(),
        ]);
    }

    public function store(PilgrimRequest $request): RedirectResponse
    {
        $this->authorize('create', Pilgrim::class);

        $data = $request->validated();
        $result = $this->pilgrims->createPilgrim($data, (bool) ($data['create_account'] ?? false));
        $this->activity->created($result['pilgrim'], "Menambah jamaah {$result['pilgrim']->name}");

        $redirect = redirect()->route('pilgrims.index')->with('success', 'Jamaah berhasil ditambahkan.');

        if ($result['password']) {
            $redirect->with('generated_password', $result['password']);
        }

        return $redirect;
    }

    public function show(Pilgrim $pilgrim): View
    {
        $this->authorize('view', $pilgrim);

        $pilgrim->load(['branch', 'agent', 'user']);
        $pilgrim->loadCount('documents');
        $registrations = $pilgrim->registrations()
            ->with('package')
            ->latest('registered_at')
            ->get();

        return view('pilgrims.show', compact('pilgrim', 'registrations'));
    }

    public function edit(Pilgrim $pilgrim): View
    {
        $this->authorize('update', $pilgrim);

        return view('pilgrims.edit', [
            'pilgrim' => $pilgrim,
            'branches' => $this->branchContext->selectableBranches(),
            'agents' => $this->selectableAgents(),
        ]);
    }

    public function update(PilgrimRequest $request, Pilgrim $pilgrim): RedirectResponse
    {
        $this->authorize('update', $pilgrim);

        $data = $request->validated();
        $result = $this->pilgrims->updatePilgrim($pilgrim, $data, (bool) ($data['create_account'] ?? false));
        $this->activity->updated($result['pilgrim'], "Memperbarui jamaah {$result['pilgrim']->name}");

        $redirect = redirect()->route('pilgrims.index')->with('success', 'Jamaah berhasil diperbarui.');

        if ($result['password']) {
            $redirect->with('generated_password', $result['password']);
        }

        return $redirect;
    }

    public function destroy(Pilgrim $pilgrim): RedirectResponse
    {
        $this->authorize('delete', $pilgrim);

        $pilgrim->delete();
        $this->activity->deleted($pilgrim, "Menghapus jamaah {$pilgrim->name}");

        return redirect()->route('pilgrims.index')->with('success', 'Jamaah berhasil dihapus.');
    }

    /**
     * Active agents within the branches the current user may select.
     */
    private function selectableAgents()
    {
        $query = Agent::query()->where('is_active', true);
        $this->branchContext->scope($query);

        return $query->orderBy('name')->get();
    }
}
