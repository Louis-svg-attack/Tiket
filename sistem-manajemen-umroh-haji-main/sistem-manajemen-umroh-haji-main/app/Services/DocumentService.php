<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\Pilgrim;
use Illuminate\Http\UploadedFile;

class DocumentService
{
    /** Standard Umroh/Haji document checklist. */
    public const STANDARD = [
        'ktp' => 'KTP',
        'kk' => 'Kartu Keluarga',
        'passport' => 'Paspor',
        'photo' => 'Pas Foto 4x6 (latar putih)',
        'marriage_book' => 'Buku Nikah / Akta Lahir',
        'vaccine' => 'Sertifikat Vaksin Meningitis',
    ];

    public function __construct(
        private readonly FileVault $vault,
        private readonly ActivityLogService $activity,
    ) {}

    /** Create any missing standard checklist rows for a pilgrim (idempotent). */
    public function seedChecklist(Pilgrim $pilgrim): int
    {
        $existing = $pilgrim->documents()->pluck('type')->all();
        $created = 0;

        foreach (self::STANDARD as $type => $label) {
            if (in_array($type, $existing, true)) {
                continue;
            }
            $pilgrim->documents()->create([
                'type' => $type,
                'label' => $label,
                'status' => DocumentStatus::Pending,
            ]);
            $created++;
        }

        return $created;
    }

    public function attachFile(Document $document, UploadedFile $file): void
    {
        // Replace any previous file on the private disk.
        $this->vault->delete($document->file_path);

        $stored = $this->vault->store($file, 'documents/'.$document->pilgrim_id);

        $document->update([
            'file_path' => $stored['path'],
            'original_name' => $stored['original_name'],
            'mime_type' => $stored['mime_type'],
            'file_size' => $stored['size'],
            'status' => DocumentStatus::Uploaded,
            'uploaded_at' => now(),
            // Re-uploading clears any previous verification verdict.
            'verified_by' => null,
            'verified_at' => null,
        ]);

        $this->activity->log('uploaded', "Mengunggah dokumen {$document->label}", $document);
    }

    public function setVerification(Document $document, DocumentStatus $status, ?string $note = null): void
    {
        $document->update([
            'status' => $status,
            'note' => $note,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $this->activity->log('verified', "Verifikasi dokumen {$document->label}: {$status->label()}", $document);
    }

    public function deleteFile(Document $document): void
    {
        $this->vault->delete($document->file_path);
        $document->update([
            'file_path' => null,
            'original_name' => null,
            'mime_type' => null,
            'file_size' => null,
            'status' => DocumentStatus::Pending,
            'uploaded_at' => null,
            'verified_by' => null,
            'verified_at' => null,
        ]);
    }
}
