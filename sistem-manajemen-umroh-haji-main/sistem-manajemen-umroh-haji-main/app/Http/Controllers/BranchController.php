<?php

namespace App\Http\Controllers;

use App\Http\Requests\Branch\BranchRequest;
use App\Models\Branch;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function __construct(private readonly ActivityLogService $activity) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Branch::class);

        $branches = Branch::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('code', 'like', '%'.$request->string('q').'%'))
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('branches.index', compact('branches'));
    }

    public function create(): View
    {
        $this->authorize('create', Branch::class);

        return view('branches.create', ['branch' => new Branch(['is_active' => true])]);
    }

    public function store(BranchRequest $request): RedirectResponse
    {
        $this->authorize('create', Branch::class);

        $branch = Branch::create($request->validated());
        $this->activity->created($branch, "Menambah cabang {$branch->name}");

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function edit(Branch $branch): View
    {
        $this->authorize('update', $branch);

        return view('branches.edit', compact('branch'));
    }

    public function update(BranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->authorize('update', $branch);

        $branch->update($request->validated());
        $this->activity->updated($branch, "Memperbarui cabang {$branch->name}");

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->authorize('delete', $branch);

        $branch->delete();
        $this->activity->deleted($branch, "Menghapus cabang {$branch->name}");

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil dihapus.');
    }
}
