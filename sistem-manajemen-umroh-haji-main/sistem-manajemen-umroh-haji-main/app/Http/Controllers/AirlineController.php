<?php

namespace App\Http\Controllers;

use App\Http\Requests\Airline\AirlineRequest;
use App\Models\Airline;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AirlineController extends Controller
{
    public function __construct(private readonly ActivityLogService $activity) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Airline::class);

        $airlines = Airline::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('airlines.index', compact('airlines'));
    }

    public function create(): View
    {
        $this->authorize('create', Airline::class);

        return view('airlines.create', ['airline' => new Airline(['is_active' => true])]);
    }

    public function store(AirlineRequest $request): RedirectResponse
    {
        $this->authorize('create', Airline::class);

        $airline = Airline::create($request->validated());
        $this->activity->created($airline, "Menambah maskapai {$airline->name}");

        return redirect()->route('airlines.index')->with('success', 'Maskapai berhasil ditambahkan.');
    }

    public function edit(Airline $airline): View
    {
        $this->authorize('update', $airline);

        return view('airlines.edit', compact('airline'));
    }

    public function update(AirlineRequest $request, Airline $airline): RedirectResponse
    {
        $this->authorize('update', $airline);

        $airline->update($request->validated());
        $this->activity->updated($airline, "Memperbarui maskapai {$airline->name}");

        return redirect()->route('airlines.index')->with('success', 'Maskapai berhasil diperbarui.');
    }

    public function destroy(Airline $airline): RedirectResponse
    {
        $this->authorize('delete', $airline);

        $airline->delete();
        $this->activity->deleted($airline, "Menghapus maskapai {$airline->name}");

        return redirect()->route('airlines.index')->with('success', 'Maskapai berhasil dihapus.');
    }
}
