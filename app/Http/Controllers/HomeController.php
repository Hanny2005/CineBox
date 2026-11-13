<?php

namespace App\Http\Controllers;

use App\Models\Movie;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'featured' => Movie::with('categories')->nowShowing()->where('is_featured', true)->latest('release_date')->take(5)->get(),
            'nowShowing' => Movie::with('categories')->nowShowing()->latest('release_date')->take(12)->get(),
            'comingSoon' => Movie::with('categories')->comingSoon()->orderBy('release_date')->take(8)->get(),
        ]);
    }
}
