<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(private readonly BranchContext $branchContext) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->isStaff() ?? false, 403);

        $query = ActivityLog::query()->with('user');
        $this->branchContext->scope($query);

        $logs = $query
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->string('action')))
            ->when($request->filled('user'), fn ($q) => $q->where('user_id', $request->integer('user')))
            ->latest()
            ->paginate(30)->withQueryString();

        $actions = $this->branchContext->scope(ActivityLog::query())
            ->select('action')->distinct()->orderBy('action')->pluck('action');

        $users = $this->branchContext->scope(User::query())
            ->orderBy('name')->get(['id', 'name']);

        return view('activity-logs.index', compact('logs', 'actions', 'users'));
    }
}
