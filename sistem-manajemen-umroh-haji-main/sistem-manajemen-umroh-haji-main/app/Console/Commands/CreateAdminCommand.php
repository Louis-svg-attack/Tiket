<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Membuat akun Super Admin untuk server produksi.
 *
 * Tanpa perintah ini, deploy yang hanya menjalankan `php artisan migrate --force`
 * akan menghasilkan tabel users kosong sehingga tidak ada akun yang bisa login.
 */
class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin
                            {--name= : Nama lengkap}
                            {--email= : Email untuk login}
                            {--password= : Password (min. 8 karakter)}
                            {--branch= : ID cabang (opsional, super admin boleh kosong)}';

    protected $description = 'Buat akun Super Admin (dipakai saat deploy pertama kali)';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama lengkap', 'Super Admin');
        $email = $this->option('email') ?: $this->ask('Email login');
        $password = $this->option('password') ?: $this->secret('Password (min. 8 karakter)');
        $branchId = $this->option('branch');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password, 'branch_id' => $branchId],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
                'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $existing = User::withTrashed()->where('email', $email)->first();

        if ($existing && ! $this->confirm("Akun {$email} sudah ada. Perbarui menjadi Super Admin aktif?", true)) {
            return self::FAILURE;
        }

        $user = $existing ?: new User;
        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => Role::SuperAdmin->value,
            'branch_id' => $branchId ?: $user->branch_id,
            'is_active' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'deleted_at' => null,
        ])->save();

        $this->newLine();
        $this->components->info('Akun Super Admin siap dipakai.');
        $this->table(
            ['Nama', 'Email', 'Peran', 'Cabang', 'Aktif'],
            [[
                $user->name,
                $user->email,
                Role::SuperAdmin->label(),
                $user->branch_id ? (Branch::find($user->branch_id)?->name ?? $user->branch_id) : '— (semua cabang)',
                'Ya',
            ]]
        );

        return self::SUCCESS;
    }
}
