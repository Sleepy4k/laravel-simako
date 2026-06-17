<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kost;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KostController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Kost::active()
            ->with('address:addressable_type,addressable_id,city,province', 'images')
            ->withCount(['rooms', 'rooms as available_rooms_count' => fn ($q) => $q->where('is_available', true)]);

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('city')) {
            $query->whereHas('address', fn ($q) => $q->where('city', 'like', '%'.$request->city.'%'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('rooms.prices', function ($q) use ($request) {
                $q->where('period', 'monthly');
                if ($request->filled('min_price')) {
                    $q->where('price', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $q->where('price', '<=', $request->max_price);
                }
            });
        }

        $kosts = $query->paginate(12)->withQueryString();

        return Inertia::render('Kosts/Index', [
            'kosts' => $kosts,
            'filters' => $request->only(['search', 'type', 'city', 'min_price', 'max_price']),
        ]);
    }

    public function show(Kost $kost): Response
    {
        abort_if($kost->status !== 'active', 404);

        $kost->load([
            'address',
            'images',
            'facilities.category:id,name',
            'tenant:id',
            'tenant.userProfile:user_id,name,avatar',
            'tenant.tenantProfile:user_id,business_name,verified_at',
            'rooms' => fn ($q) => $q->where('is_available', true)->with('prices', 'images', 'facilities:id,name,icon'),
        ]);

        $reviews = Review::whereHas('booking.room.kost', fn ($q) => $q->where('id', $kost->id))
            ->with('user.userProfile:user_id,name,avatar')
            ->latest()
            ->limit(10)
            ->get();

        $averageRating = $reviews->avg('rating');

        return Inertia::render('Kosts/Show', [
            'kost' => $kost,
            'reviews' => $reviews,
            'averageRating' => $averageRating ? round($averageRating, 1) : null,
        ]);
    }
}
