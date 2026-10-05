<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hotel\HotelRequest;
use App\Models\Hotel;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelController extends Controller
{
    public function __construct(private readonly ActivityLogService $activity) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Hotel::class);

        $hotels = Hotel::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->string('city')))
            ->orderBy('city')->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('hotels.index', compact('hotels'));
    }

    public function create(): View
    {
        $this->authorize('create', Hotel::class);

        return view('hotels.create', ['hotel' => new Hotel(['star' => 4, 'is_active' => true])]);
    }

    public function store(HotelRequest $request): RedirectResponse
    {
        $this->authorize('create', Hotel::class);

        $hotel = Hotel::create($request->validated());
        $this->activity->created($hotel, "Menambah hotel {$hotel->name}");

        return redirect()->route('hotels.index')->with('success', 'Hotel berhasil ditambahkan.');
    }

    public function edit(Hotel $hotel): View
    {
        $this->authorize('update', $hotel);

        return view('hotels.edit', compact('hotel'));
    }

    public function update(HotelRequest $request, Hotel $hotel): RedirectResponse
    {
        $this->authorize('update', $hotel);

        $hotel->update($request->validated());
        $this->activity->updated($hotel, "Memperbarui hotel {$hotel->name}");

        return redirect()->route('hotels.index')->with('success', 'Hotel berhasil diperbarui.');
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        $this->authorize('delete', $hotel);

        $hotel->delete();
        $this->activity->deleted($hotel, "Menghapus hotel {$hotel->name}");

        return redirect()->route('hotels.index')->with('success', 'Hotel berhasil dihapus.');
    }
}
