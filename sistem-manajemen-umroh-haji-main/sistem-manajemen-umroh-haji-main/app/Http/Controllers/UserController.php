<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\User\UserRequest;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly BranchContext $branchContext,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()->with('branch');
        $this->branchContext->scope($query);

        $users = $query
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->string('q').'%')->orWhere('email', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', [
            'user' => new User(['is_active' => true]),
            'branches' => $this->branchContext->selectableBranches(),
            'roles' => $this->roleOptions(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        if (! $request->user()->isSuperAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }

        // User model casts 'password' as hashed.
        $user = User::create($data);
        $this->activity->created($user, "Menambah pengguna {$user->name}");

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'branches' => $this->branchContext->selectableBranches(),
            'roles' => $this->roleOptions(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        if (! $request->user()->isSuperAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }

        // Only change the password when one was provided.
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);
        $this->activity->updated($user, "Memperbarui pengguna {$user->name}");

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();
        $this->activity->deleted($user, "Menghapus pengguna {$user->name}");

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Reset a user's password to a freshly generated temporary one and surface
     * it once to the admin (no email needed — handed over manually).
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        // Model casts 'password' as hashed, so assign the plain value.
        $plain = Str::password(10, symbols: false);
        $user->forceFill(['password' => $plain])->save();

        $this->activity->updated($user, "Mereset kata sandi pengguna {$user->name}");

        return redirect()->route('users.index')
            ->with('success', "Kata sandi {$user->name} berhasil direset.")
            ->with('generated_password', $plain);
    }

    /**
     * Role options, excluding super_admin for non-super-admins.
     *
     * @return array<string, string>
     */
    private function roleOptions(): array
    {
        $options = Role::options();

        if (! $this->branchContext->user()?->isSuperAdmin()) {
            unset($options[Role::SuperAdmin->value]);
        }

        return $options;
    }
}
