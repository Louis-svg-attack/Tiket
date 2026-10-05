<?php

namespace App\Models;

use App\Enums\AnnouncementAudience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'package_id', 'created_by', 'title', 'body',
        'audience', 'is_published', 'publish_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'audience' => AnnouncementAudience::class,
            'is_published' => 'boolean',
            'publish_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Only currently-live announcements (published, within publish/expiry window). */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('publish_at')->orWhere('publish_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }
}
