<?php

namespace App\Services;

use App\Models\ManifestEntry;
use App\Models\Package;

class ManifestService
{
    /**
     * Persist manifest/rooming entries for a package.
     *
     * @param  array<int|string, array<string, mixed>>  $rows  [registration_id => [...fields]]
     */
    public function saveEntries(Package $package, array $rows): void
    {
        foreach ($rows as $registrationId => $fields) {
            ManifestEntry::updateOrCreate(
                ['package_id' => $package->id, 'registration_id' => (int) $registrationId],
                [
                    'room_number' => $fields['room_number'] ?? null,
                    'room_type' => $fields['room_type'] ?? null,
                    'bus_number' => $fields['bus_number'] ?? null,
                    'seat_number' => $fields['seat_number'] ?? null,
                    'mahram_group' => $fields['mahram_group'] ?? null,
                ],
            );
        }
    }
}
