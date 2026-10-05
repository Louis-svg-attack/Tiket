<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentStatus;
use App\Http\Requests\Equipment\EquipmentRequest;
use App\Models\EquipmentDistribution;
use App\Services\AccessContext;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity,
        private readonly AccessContext $access,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', EquipmentDistribution::class);

        $equipment = EquipmentDistribution::query()
            ->whereIn('registration_id', $this->access->registrations()->select('id'))
            ->with(['registration.pilgrim', 'registration.package'])
            ->latest()
            ->paginate(15)->withQueryString();

        return view('equipment.index', compact('equipment'));
    }

    public function create(): View
    {
        $this->authorize('create', EquipmentDistribution::class);

        return view('equipment.create', $this->formData(new EquipmentDistribution([
            'quantity' => 1,
            'status' => EquipmentStatus::Pending,
        ])));
    }

    public function store(EquipmentRequest $request): RedirectResponse
    {
        $this->authorize('create', EquipmentDistribution::class);

        $data = $this->prepare($request->validated());

        $equipment = EquipmentDistribution::create($data);
        $this->activity->created($equipment, "Menambah perlengkapan {$equipment->item}");

        return redirect()->route('equipment.index')->with('success', 'Perlengkapan berhasil ditambahkan.');
    }

    public function edit(EquipmentDistribution $equipment): View
    {
        $this->authorize('update', $equipment);

        return view('equipment.edit', $this->formData($equipment));
    }

    public function update(EquipmentRequest $request, EquipmentDistribution $equipment): RedirectResponse
    {
        $this->authorize('update', $equipment);

        $data = $this->prepare($request->validated(), $equipment);

        $equipment->update($data);
        $this->activity->updated($equipment, "Memperbarui perlengkapan {$equipment->item}");

        return redirect()->route('equipment.index')->with('success', 'Perlengkapan berhasil diperbarui.');
    }

    public function destroy(EquipmentDistribution $equipment): RedirectResponse
    {
        $this->authorize('delete', $equipment);

        $equipment->delete();
        $this->activity->deleted($equipment, "Menghapus perlengkapan {$equipment->item}");

        return redirect()->route('equipment.index')->with('success', 'Perlengkapan berhasil dihapus.');
    }

    /**
     * Fill handed_by when an item is marked as handed over.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepare(array $data, ?EquipmentDistribution $equipment = null): array
    {
        if (($data['status'] ?? null) === EquipmentStatus::HandedOver->value
            && empty($equipment?->handed_by)) {
            $data['handed_by'] = auth()->id();
        }

        return $data;
    }

    /**
     * Shared payload for the create/edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(EquipmentDistribution $equipment): array
    {
        $registrations = $this->access->registrations()
            ->with(['pilgrim', 'package'])
            ->get()
            ->mapWithKeys(fn ($r) => [
                $r->id => ($r->pilgrim?->name ?? '-').' - '.($r->package?->name ?? '-'),
            ])
            ->all();

        return [
            'equipment' => $equipment,
            'registrations' => $registrations,
        ];
    }
}
