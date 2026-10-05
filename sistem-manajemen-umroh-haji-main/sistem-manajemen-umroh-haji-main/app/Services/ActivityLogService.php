<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Records an audit trail (who, what, when, IP, user agent).
 * Failures are swallowed so logging can never break a user action.
 */
class ActivityLogService
{
    public function log(string $action, ?string $description = null, ?Model $subject = null, array $properties = []): void
    {
        try {
            $user = Auth::user();

            ActivityLog::create([
                'user_id' => $user?->id,
                'branch_id' => $user?->branch_id,
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'description' => $description,
                'properties' => $properties ?: null,
                'ip_address' => Request::ip(),
                'user_agent' => substr((string) Request::userAgent(), 0, 255),
            ]);
        } catch (\Throwable) {
            // Never let audit logging interrupt the request.
        }
    }

    public function created(Model $subject, ?string $description = null): void
    {
        $this->log('created', $description ?? 'Membuat '.class_basename($subject), $subject);
    }

    public function updated(Model $subject, ?string $description = null): void
    {
        $this->log('updated', $description ?? 'Memperbarui '.class_basename($subject), $subject);
    }

    public function deleted(Model $subject, ?string $description = null): void
    {
        $this->log('deleted', $description ?? 'Menghapus '.class_basename($subject), $subject);
    }
}
