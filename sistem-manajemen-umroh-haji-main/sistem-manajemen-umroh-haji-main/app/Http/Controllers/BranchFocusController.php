<?php

namespace App\Http\Controllers;

use App\Services\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchFocusController extends Controller
{
    /** super_admin focuses the whole UI on a single branch (or all). */
    public function update(Request $request, BranchContext $branches): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $branches->setFocus($validated['branch_id'] ?? null);

        return back();
    }
}
