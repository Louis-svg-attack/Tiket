<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'pilgrim_id', 'type', 'label', 'status', 'file_path', 'original_name',
        'mime_type', 'file_size', 'note', 'verified_by', 'verified_at', 'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DocumentStatus::class,
            'file_size' => 'integer',
            'verified_at' => 'datetime',
            'uploaded_at' => 'datetime',
        ];
    }

    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function hasFile(): bool
    {
        return ! empty($this->file_path);
    }
}
