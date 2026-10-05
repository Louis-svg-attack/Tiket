<?php

namespace App\Http\Controllers;

use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Services\ActivityLogService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly ActivityLogService $activity,
    ) {}

    public function edit(): View
    {
        $this->authorizeAccess();

        return view('settings.edit', [
            'settings' => $this->settings->all(),
        ]);
    }

    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->safe()->except(['logo', 'favicon']);

        // Image uploads go to the PUBLIC disk (they are meant to be served).
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('branding', 'public');
        }
        if ($request->hasFile('favicon')) {
            $data['favicon_path'] = $request->file('favicon')->store('branding', 'public');
        }

        $this->settings->setMany($data);
        $this->activity->log('updated', 'Memperbarui pengaturan white-label');

        return redirect()->route('settings.edit')->with('success', 'Pengaturan white-label berhasil disimpan.');
    }

    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        // Ensure the public storage symlink exists for freshly-uploaded assets.
        if (! file_exists(public_path('storage'))) {
            try {
                Storage::disk('public')->makeDirectory('branding');
            } catch (\Throwable) {
                // ignore
            }
        }
    }
}
