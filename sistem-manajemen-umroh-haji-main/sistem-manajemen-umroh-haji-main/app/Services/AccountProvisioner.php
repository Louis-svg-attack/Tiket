<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates / links login accounts for Jamaah, Agen and Muthawif directly from
 * their management forms ("Buatkan akun login"). Always called inside the
 * surrounding DB transaction of the owning service.
 */
class AccountProvisioner
{
    /**
     * @return array{user: User, password: ?string} password is non-null only when freshly generated
     */
    public function provision(
        Role $role,
        string $name,
        ?string $email,
        ?int $branchId,
        ?string $phone = null,
        ?string $password = null,
    ): array {
        $email = $email ? strtolower(trim($email)) : null;

        // Fall back to a derived email when none is provided.
        if (! $email) {
            $email = Str::slug($name, '.').'.'.Str::lower(Str::random(4)).'@'.$role->value.'.local';
        }

        $existing = User::where('email', $email)->first();

        if ($existing) {
            // Re-use the account but keep it pointed at the right role/branch.
            $existing->fill([
                'name' => $name,
                'role' => $role->value,
                'branch_id' => $branchId,
                'phone' => $phone ?? $existing->phone,
                'is_active' => true,
            ])->save();

            return ['user' => $existing, 'password' => null];
        }

        $plain = $password ?: Str::password(10, symbols: false);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($plain),
            'role' => $role->value,
            'branch_id' => $branchId,
            'phone' => $phone,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return ['user' => $user, 'password' => $plain];
    }
}
