<?php

namespace App\Http\Controllers;

use App\Enums\CommissionStatus;
use App\Http\Requests\Commission\UpdateCommissionRequest;
use App\Models\Agent;
use App\Models\Commission;
use App\Services\AccessContext;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly BranchContext $branchContext,
        private readonly AccessContext $access,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Commission::class);

        $user = $request->user();

        $query = Commission::query()
            ->with(['agent', 'registration.pilgrim', 'registration.package']);

        if ($user->isAgent()) {
            $query->where('agent_id', $this->access->agentId() ?? -1);
        } else {
            $this->branchContext->scope($query);
        }

        // Rekap per status (uses the same scope as the listing).
        $rekap = (clone $query)
            ->selectRaw('status, COUNT(*) as total, SUM(amount) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy(fn ($row) => $row->status?->value);

        $commissions = $query
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($user->isStaff() && $request->filled('agent'), fn ($q) => $q->where('agent_id', $request->integer('agent')))
            ->latest()
            ->paginate(15)->withQueryString();

        $agents = $user->isStaff()
            ? $this->branchContext->scope(Agent::query())->orderBy('name')->get()
            : collect();

        return view('commissions.index', compact('commissions', 'rekap', 'agents'));
    }

    public function updateStatus(UpdateCommissionRequest $request, Commission $commission): RedirectResponse
    {
        $this->authorize('manage', $commission);

        $status = CommissionStatus::from($request->validated('status'));

        $commission->status = $status;
        $commission->paid_at = $status === CommissionStatus::Paid ? today() : null;
        if ($request->filled('note')) {
            $commission->note = $request->validated('note');
        }
        $commission->save();

        $this->activity->updated($commission, "Mengubah status komisi #{$commission->id} menjadi {$status->label()}");

        return redirect()->route('commissions.index')->with('success', 'Status komisi berhasil diperbarui.');
    }
}
