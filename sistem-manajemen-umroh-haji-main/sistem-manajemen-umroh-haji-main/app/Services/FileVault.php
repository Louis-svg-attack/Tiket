<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Stores sensitive files (pilgrim documents, payment proofs) on the PRIVATE
 * disk only. Dangerous executable / scriptable types are rejected outright so
 * a malicious upload can never be served back as code.
 */
class FileVault
{
    private const DISK = 'private';

    /** Extensions that must never be accepted. */
    private const BLOCKED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar',
        'exe', 'com', 'bat', 'cmd', 'sh', 'bash', 'msi', 'dll',
        'svg', 'svgz', 'js', 'mjs', 'html', 'htm', 'xhtml',
        'jsp', 'asp', 'aspx', 'cgi', 'pl', 'py', 'rb',
    ];

    /** Mime types that must never be accepted. */
    private const BLOCKED_MIMES = [
        'image/svg+xml', 'text/html', 'application/x-httpd-php',
        'application/x-php', 'application/x-msdownload', 'application/x-sh',
        'text/x-php', 'application/javascript', 'text/javascript',
    ];

    /** Allowed extensions for document uploads. */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

    public function store(UploadedFile $file, string $directory): array
    {
        $this->assertSafe($file);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $name = Str::uuid()->toString().'.'.$extension;
        $path = trim($directory, '/').'/'.$name;

        Storage::disk(self::DISK)->putFileAs(
            trim($directory, '/'),
            $file,
            $name,
        );

        return [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ];
    }

    public function exists(?string $path): bool
    {
        return ! empty($path) && Storage::disk(self::DISK)->exists($path);
    }

    public function delete(?string $path): void
    {
        if ($this->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /** Streamed, authorization-gated download (caller must authorize first). */
    public function download(string $path, ?string $downloadName = null): StreamedResponse
    {
        if (! $this->exists($path)) {
            abort(404);
        }

        return Storage::disk(self::DISK)->download($path, $downloadName);
    }

    public function url(string $path): string
    {
        return Storage::disk(self::DISK)->path($path);
    }

    private function assertSafe(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new RuntimeException('Berkas yang diunggah tidak valid.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $mime = strtolower((string) $file->getClientMimeType());

        if (in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
            throw new RuntimeException("Tipe berkas .{$extension} tidak diizinkan demi keamanan.");
        }

        if (in_array($mime, self::BLOCKED_MIMES, true)) {
            throw new RuntimeException('Tipe berkas tersebut diblokir demi keamanan.');
        }
    }
}
