<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Http\Requests\Document\UploadDocumentRequest;
use App\Http\Requests\Document\VerifyDocumentRequest;
use App\Models\Document;
use App\Models\Pilgrim;
use App\Services\AccessContext;
use App\Services\ActivityLogService;
use App\Services\DocumentService;
use App\Services\FileVault;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documents,
        private readonly AccessContext $access,
        private readonly ActivityLogService $activity,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        // Pilgrims jump straight to their own checklist.
        if ($request->user()->isPilgrim()) {
            $pilgrim = $request->user()->pilgrim;
            if (! $pilgrim) {
                $empty = new LengthAwarePaginator([], 0, 15);

                return view('documents.index', ['pilgrims' => $empty, 'selfOnly' => true]);
            }

            return redirect()->route('documents.show', $pilgrim);
        }

        $pilgrims = $this->access->pilgrims()
            ->with('branch')
            ->withCount([
                'documents',
                'documents as verified_documents_count' => fn ($q) => $q->where('status', DocumentStatus::Verified->value),
            ])
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('documents.index', ['pilgrims' => $pilgrims, 'selfOnly' => false]);
    }

    public function show(Pilgrim $pilgrim): View
    {
        $this->authorize('view', $pilgrim);

        $documents = $pilgrim->documents()->orderBy('id')->get();

        return view('documents.show', compact('pilgrim', 'documents'));
    }

    /** Seed the standard checklist or add a single custom requirement. */
    public function store(Request $request, Pilgrim $pilgrim): RedirectResponse
    {
        $this->authorize('update', $pilgrim);

        if ($request->input('action') === 'seed') {
            $count = $this->documents->seedChecklist($pilgrim);

            return back()->with('success', "$count item checklist dokumen ditambahkan.");
        }

        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
        ]);

        $pilgrim->documents()->create([
            'type' => 'custom',
            'label' => $data['label'],
            'status' => DocumentStatus::Pending,
        ]);

        return back()->with('success', 'Dokumen ditambahkan ke checklist.');
    }

    public function upload(UploadDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->documents->attachFile($document, $request->file('file'));

        return back()->with('success', "Dokumen {$document->label} berhasil diunggah.");
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        abort_if(! $document->hasFile(), 404, 'Berkas belum diunggah.');

        $this->activity->log('downloaded', "Mengunduh dokumen {$document->label}", $document);

        return app(FileVault::class)
            ->download($document->file_path, $document->original_name);
    }

    public function verify(VerifyDocumentRequest $request, Document $document): RedirectResponse
    {
        $status = DocumentStatus::from($request->validated()['status']);
        $this->documents->setVerification($document, $status, $request->validated()['note'] ?? null);

        return back()->with('success', "Status dokumen diperbarui: {$status->label()}.");
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $this->documents->deleteFile($document);
        $document->delete();

        return back()->with('success', 'Dokumen dihapus dari checklist.');
    }
}
