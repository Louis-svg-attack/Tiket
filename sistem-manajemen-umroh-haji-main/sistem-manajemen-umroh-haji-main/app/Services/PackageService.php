<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Support\Str;

class PackageService
{
    public function __construct(private readonly BranchContext $branchContext) {}

    public function createPackage(array $data): Package
    {
        $data = $this->prepare($data);
        $data['branch_id'] = $data['branch_id'] ?? $this->branchContext->currentBranchId();

        if (empty($data['code'])) {
            $data['code'] = 'PKG-'.strtoupper(Str::random(6));
        }

        return Package::create($data);
    }

    public function updatePackage(Package $package, array $data): Package
    {
        $data = $this->prepare($data);
        $package->update($data);

        return $package;
    }

    /**
     * Normalise the incoming form payload: convert the facilities textareas
     * (one item per line) into arrays for the json columns.
     */
    private function prepare(array $data): array
    {
        $data['facilities_included'] = $this->linesToArray($data['facilities_included'] ?? null);
        $data['facilities_excluded'] = $this->linesToArray($data['facilities_excluded'] ?? null);

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function linesToArray(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
