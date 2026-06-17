<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KostController extends Controller
{
    public function index(Request $request): Response
    {
        $kosts = Kost::with('address:addressable_type,addressable_id,city,province', 'tenant.userProfile:user_id,name')
            ->withCount('rooms')
            ->when($request->search, fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Kosts/Index', [
            'kosts' => $kosts,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function show(Kost $kost): Response
    {
        $kost->load('address', 'tenant.userProfile:user_id,name', 'rooms.prices', 'facilities:id,name');

        return Inertia::render('Admin/Kosts/Show', ['kost' => $kost]);
    }
}
