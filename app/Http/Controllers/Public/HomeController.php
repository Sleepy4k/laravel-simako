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

    public function contact(): Response
    {
        return Inertia::render('Public/Contact');
    }

    public function help(): Response
    {
        return Inertia::render('Public/Help');
    }

    public function terms(): Response
    {
        return Inertia::render('Public/Terms');
    }

    public function privacy(): Response
    {
        return Inertia::render('Public/Privacy');
    }
}
