<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Kost;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(Kost $kost): Response
    {
        $this->authorize('update', $kost);

        $kost->load(['rooms.prices', 'rooms.images', 'rooms.facilities:id,name']);

        return Inertia::render('Tenant/Rooms/Index', ['kost' => $kost]);
    }

    public function create(Kost $kost): Response
    {
        $this->authorize('update', $kost);

        $facilities = Facility::with('category:id,name')->get();

        return Inertia::render('Tenant/Rooms/Create', [
            'kost' => $kost->only('id', 'name', 'slug'),
            'facilities' => $facilities,
        ]);
    }

    public function store(Request $request, Kost $kost): RedirectResponse
    {
        $this->authorize('update', $kost);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'floor' => ['nullable', 'integer', 'min:1', 'max:100'],
            'size_sqm' => ['nullable', 'numeric', 'min:1'],
            'facility_ids' => ['nullable', 'array'],
            'facility_ids.*' => ['integer', 'exists:facilities,id'],
            'prices' => ['required', 'array', 'min:1'],
            'prices.*.period' => ['required', 'in:monthly,quarterly,semi_annual,annual'],
            'prices.*.price' => ['required', 'integer', 'min:0'],
            'prices.*.deposit' => ['nullable', 'integer', 'min:0'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:4096'],
        ]);

        DB::transaction(function () use ($request, $kost, $validated) {
            $room = $kost->rooms()->create([
                'name' => $validated['name'],
                'floor' => $validated['floor'] ?? null,
                'size_sqm' => $validated['size_sqm'] ?? null,
                'is_available' => true,
            ]);

            foreach ($validated['prices'] as $price) {
                $room->prices()->create([
                    'period' => $price['period'],
                    'price' => $price['price'],
                    'deposit' => $price['deposit'] ?? 0,
                ]);
            }

            if (! empty($validated['facility_ids'])) {
                $room->facilities()->attach($validated['facility_ids']);
            }

            if ($request->hasFile('images')) {
                $sort = 0;
                foreach ($request->file('images') as $image) {
                    $room->images()->create([
                        'path' => $image->store('rooms/images', 'public'),
                        'sort_order' => $sort++,
                    ]);
                }
            }

            $kost->increment('total_rooms');
            $kost->increment('available_rooms');
        });

        return redirect()->route('dashboard.tenant.rooms.index', $kost)
            ->with('success', 'Kamar berhasil ditambahkan.');
    }

    public function edit(Kost $kost, Room $room): Response
    {
        $this->authorize('update', $room);

        $room->load('prices', 'images', 'facilities:id');
        $facilities = Facility::with('category:id,name')->get();

        return Inertia::render('Tenant/Rooms/Edit', [
            'kost' => $kost->only('id', 'name', 'slug'),
            'room' => $room,
            'facilities' => $facilities,
        ]);
    }

    public function update(Request $request, Kost $kost, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'floor' => ['nullable', 'integer', 'min:1', 'max:100'],
            'size_sqm' => ['nullable', 'numeric', 'min:1'],
            'is_available' => ['boolean'],
            'facility_ids' => ['nullable', 'array'],
            'facility_ids.*' => ['integer', 'exists:facilities,id'],
            'prices' => ['required', 'array', 'min:1'],
            'prices.*.period' => ['required', 'in:monthly,quarterly,semi_annual,annual'],
            'prices.*.price' => ['required', 'integer', 'min:0'],
            'prices.*.deposit' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($kost, $room, $validated) {
            $wasAvailable = $room->is_available;
            $isNowAvailable = $validated['is_available'] ?? true;

            $room->update([
                'name' => $validated['name'],
                'floor' => $validated['floor'] ?? null,
                'size_sqm' => $validated['size_sqm'] ?? null,
                'is_available' => $isNowAvailable,
            ]);

            $room->prices()->delete();
            foreach ($validated['prices'] as $price) {
                $room->prices()->create([
                    'period' => $price['period'],
                    'price' => $price['price'],
                    'deposit' => $price['deposit'] ?? 0,
                ]);
            }

            $room->facilities()->sync($validated['facility_ids'] ?? []);

            if ($wasAvailable && ! $isNowAvailable) {
                $kost->decrement('available_rooms');
            } elseif (! $wasAvailable && $isNowAvailable) {
                $kost->increment('available_rooms');
            }
        });

        return back()->with('success', 'Kamar berhasil diperbarui.');
    }

    public function destroy(Kost $kost, Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        $wasAvailable = $room->is_available;
        $room->delete();

        $kost->decrement('total_rooms');
        if ($wasAvailable) {
            $kost->decrement('available_rooms');
        }

        return redirect()->route('dashboard.tenant.rooms.index', $kost)
            ->with('success', 'Kamar berhasil dihapus.');
    }
}
