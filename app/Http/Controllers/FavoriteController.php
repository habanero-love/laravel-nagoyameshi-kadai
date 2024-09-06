<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorite_restaurants = auth()->user()->favorite_restaurants()->orderBy('pivot_created_at', 'desc')
            ->paginate(15);

        return view("favorites.index", compact('favorite_restaurants'));
    }

    public function store(Restaurant $restaurant)
    {
        $user = auth()->user();

        $user->favorite_restaurants()->attach($restaurant->id);

        return redirect()->back()->with('flash_message', 'お気に入りに追加しました。');
    }

    public function destroy(Restaurant $restaurant)
    {
        $user = auth()->user();

        $user->favorite_restaurants()->detach($restaurant->id);

        return redirect()->back()->with('flash_message', 'お気に入りを解除しました。');
    }
}
