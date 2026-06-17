<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kost;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $featuredKosts = Kost::active()
            ->with('address:addressable_type,addressable_id,city,province', 'images')
            ->withCount('rooms')
            ->latest()
            ->limit(8)
            ->get();

        return Inertia::render('Home/Index', [
            'featuredKosts' => $featuredKosts,
        ]);
    }
}
