<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Http\Requests\Guide\GuideRequest;
use App\Models\Guide;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use App\Services\GuideService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuideController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly BranchContext $branchContext,
        private readonly GuideService $guides,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Guide::class);

        $query = Guide::query()->with(['branch', 'user']);
        $this->branchContext->scope($query);

        $guides = $query
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->string('q').'%')->orWhere('code', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->string('status') === 'active'))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('guides.index', compact('guides'));
    }

    public function create(): View
    {
        $this->authorize('create', Guide::class);

        return view('guides.create', [
            'guide' => new Guide(['is_active' => true, 'gender' => Gender::Male]),
            'branches' => $this->branchContext->selectableBranches(),
        ]);
    }

    public function store(GuideRequest $request): RedirectResponse
    {
        $this->authorize('create', Guide::class);

        $data = $request->validated();
        $result = $this->guides->createGuide($data, (bool) ($data['create_account'] ?? false));
        $this->activity->created($result['guide'], "Menambah muthawif {$result['guide']->name}");

        $redirect = redirect()->route('guides.index')->with('success', 'Muthawif berhasil ditambahkan.');

        if ($result['password']) {
            $redirect->with('generated_password', $result['password']);
        }

        return $redirect;
    }

    public function edit(Guide $guide): View
    {
        $this->authorize('update', $guide);

        return view('guides.edit', [
            'guide' => $guide,
            'branches' => $this->branchContext->selectableBranches(),
        ]);
    }

    public function update(GuideRequest $request, Guide $guide): RedirectResponse
    {
        $this->authorize('update', $guide);

        $data = $request->validated();
        $result = $this->guides->updateGuide($guide, $data, (bool) ($data['create_account'] ?? false));
        $this->activity->updated($result['guide'], "Memperbarui muthawif {$result['guide']->name}");

        $redirect = redirect()->route('guides.index')->with('success', 'Muthawif berhasil diperbarui.');

        if ($result['password']) {
            $redirect->with('generated_password', $result['password']);
        }

        return $redirect;
    }

    public function destroy(Guide $guide): RedirectResponse
    {
        $this->authorize('delete', $guide);

        $guide->delete();
        $this->activity->deleted($guide, "Menghapus muthawif {$guide->name}");

        return redirect()->route('guides.index')->with('success', 'Muthawif berhasil dihapus.');
    }
}
