<?php

namespace App\Http\Controllers;

use App\Http\Requests\Announcement\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\Package;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly BranchContext $branchContext,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Announcement::class);

        $query = Announcement::query()->with(['branch', 'package', 'author']);
        $announcements = $this->branchContext->scope($query)
            ->latest()
            ->paginate(15)->withQueryString();

        return view('announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        $this->authorize('create', Announcement::class);

        return view('announcements.create', [
            'announcement' => new Announcement(['is_published' => true]),
            'branches' => $this->branchContext->selectableBranches(),
            'packages' => $this->packageOptions(),
        ]);
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $this->authorize('create', Announcement::class);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        if (! $request->user()->isSuperAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }

        $announcement = Announcement::create($data);
        $this->activity->created($announcement, "Membuat pengumuman {$announcement->title}");

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('update', $announcement);

        return view('announcements.edit', [
            'announcement' => $announcement,
            'branches' => $this->branchContext->selectableBranches(),
            'packages' => $this->packageOptions(),
        ]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('update', $announcement);

        $data = $request->validated();

        if (! $request->user()->isSuperAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }

        $announcement->update($data);
        $this->activity->updated($announcement, "Memperbarui pengumuman {$announcement->title}");

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);

        $announcement->delete();
        $this->activity->deleted($announcement, "Menghapus pengumuman {$announcement->title}");

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    /** @return array<int, string> */
    private function packageOptions(): array
    {
        return $this->branchContext->scope(Package::query())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
