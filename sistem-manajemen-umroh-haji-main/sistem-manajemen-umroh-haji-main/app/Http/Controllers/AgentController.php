<?php

namespace App\Http\Controllers;

use App\Http\Requests\Agent\AgentRequest;
use App\Models\Agent;
use App\Services\ActivityLogService;
use App\Services\AgentService;
use App\Services\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly BranchContext $branchContext,
        private readonly AgentService $agents,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Agent::class);

        $query = Agent::query()
            ->with(['branch', 'user'])
            ->withCount('registrations');
        $this->branchContext->scope($query);

        $agents = $query
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->string('q').'%')->orWhere('code', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->string('status') === 'active'))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('agents.index', compact('agents'));
    }

    public function create(): View
    {
        $this->authorize('create', Agent::class);

        return view('agents.create', [
            'agent' => new Agent(['is_active' => true, 'commission_rate' => 0]),
            'branches' => $this->branchContext->selectableBranches(),
        ]);
    }

    public function store(AgentRequest $request): RedirectResponse
    {
        $this->authorize('create', Agent::class);

        $data = $request->validated();
        $result = $this->agents->createAgent($data, (bool) ($data['create_account'] ?? false));
        $this->activity->created($result['agent'], "Menambah agen {$result['agent']->name}");

        $redirect = redirect()->route('agents.index')->with('success', 'Agen berhasil ditambahkan.');

        if ($result['password']) {
            $redirect->with('generated_password', $result['password']);
        }

        return $redirect;
    }

    public function edit(Agent $agent): View
    {
        $this->authorize('update', $agent);

        return view('agents.edit', [
            'agent' => $agent,
            'branches' => $this->branchContext->selectableBranches(),
        ]);
    }

    public function update(AgentRequest $request, Agent $agent): RedirectResponse
    {
        $this->authorize('update', $agent);

        $data = $request->validated();
        $result = $this->agents->updateAgent($agent, $data, (bool) ($data['create_account'] ?? false));
        $this->activity->updated($result['agent'], "Memperbarui agen {$result['agent']->name}");

        $redirect = redirect()->route('agents.index')->with('success', 'Agen berhasil diperbarui.');

        if ($result['password']) {
            $redirect->with('generated_password', $result['password']);
        }

        return $redirect;
    }

    public function destroy(Agent $agent): RedirectResponse
    {
        $this->authorize('delete', $agent);

        $agent->delete();
        $this->activity->deleted($agent, "Menghapus agen {$agent->name}");

        return redirect()->route('agents.index')->with('success', 'Agen berhasil dihapus.');
    }
}
