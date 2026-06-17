<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Kost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class KostController extends Controller
{
    public function index(Request $request): Response
    {
        $kosts = $request->user()
            ->kosts()
            ->withCount('rooms')
            ->with('address:addressable_type,addressable_id,city,province')
            ->latest()
            ->paginate(10);

        return Inertia::render('Tenant/Kosts/Index', ['kosts' => $kosts]);
    }

    public function create(): Response
    {
        $facilities = Facility::with('category:id,name')->get();

        return Inertia::render('Tenant/Kosts/Create', ['facilities' => $facilities]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:putra,putri,campur'],
            'street' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
            'city' => ['required', 'string'],
            'province' => ['required', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'facility_ids' => ['nullable', 'array'],
            'facility_ids.*' => ['integer', 'exists:facilities,id'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:4096'],
        ]);

        $kost = DB::transaction(function () use ($request, $validated) {
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('kosts/thumbnails', 'public');
            }

            $slug = Str::slug($validated['name']);
            $uniqueSlug = $slug;
            $count = 1;
            while (Kost::where('slug', $uniqueSlug)->exists()) {
                $uniqueSlug = $slug.'-'.$count++;
            }

            $kost = $request->user()->kosts()->create([
                'name' => $validated['name'],
                'slug' => $uniqueSlug,
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'status' => 'draft',
                'thumbnail' => $thumbnailPath,
            ]);

            $kost->address()->create([
                'street' => $validated['street'] ?? null,
                'district' => $validated['district'] ?? null,
                'city' => $validated['city'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'] ?? null,
            ]);

            if (! empty($validated['facility_ids'])) {
                $kost->facilities()->attach($validated['facility_ids']);
            }

            if ($request->hasFile('images')) {
                $sort = 0;
                foreach ($request->file('images') as $image) {
                    $kost->images()->create([
                        'path' => $image->store('kosts/images', 'public'),
                        'sort_order' => $sort++,
                    ]);
                }
            }

            return $kost;
        });

        return redirect()->route('dashboard.tenant.kosts.index')
            ->with('success', 'Kost berhasil dibuat. Tambahkan kamar untuk mempublikasikan.');
    }

    public function edit(Kost $kost): Response
    {
        $this->authorize('update', $kost);

        $kost->load('address', 'images', 'facilities:id');
        $facilities = Facility::with('category:id,name')->get();

        return Inertia::render('Tenant/Kosts/Edit', [
            'kost' => $kost,
            'facilities' => $facilities,
        ]);
    }

    public function update(Request $request, Kost $kost): RedirectResponse
    {
        $this->authorize('update', $kost);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:putra,putri,campur'],
            'status' => ['required', 'in:draft,active,inactive'],
            'street' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
            'city' => ['required', 'string'],
            'province' => ['required', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'facility_ids' => ['nullable', 'array'],
            'facility_ids.*' => ['integer', 'exists:facilities,id'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
        ]);

        DB::transaction(function () use ($request, $kost, $validated) {
            $updateData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'status' => $validated['status'],
            ];

            if ($request->hasFile('thumbnail')) {
                $updateData['thumbnail'] = $request->file('thumbnail')->store('kosts/thumbnails', 'public');
            }

            $kost->update($updateData);

            $kost->address()->updateOrCreate(
                ['addressable_type' => Kost::class, 'addressable_id' => $kost->id],
                [
                    'street' => $validated['street'] ?? null,
                    'district' => $validated['district'] ?? null,
                    'city' => $validated['city'],
                    'province' => $validated['province'],
                    'postal_code' => $validated['postal_code'] ?? null,
                ],
            );

            $kost->facilities()->sync($validated['facility_ids'] ?? []);
        });

        return back()->with('success', 'Kost berhasil diperbarui.');
    }

    public function destroy(Kost $kost): RedirectResponse
    {
        $this->authorize('delete', $kost);

        $kost->delete();

        return redirect()->route('dashboard.tenant.kosts.index')
            ->with('success', 'Kost berhasil dihapus.');
    }
}
