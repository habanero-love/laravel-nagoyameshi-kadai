<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Http\Requests\ReviewRequest;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Restaurant $restaurant)
    {
        $user = auth()->user();

        if ($user->subscribed('premium_plan')) {
            $reviews = $restaurant->reviews()->orderBy('created_at', 'desc')->paginate(5);
        } else {
            $reviews = $restaurant->reviews()->orderBy('created_at', 'desc')->take(3)->get();
        }

        return view("reviews.index", compact('restaurant', 'reviews'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Restaurant $restaurant)
    {
        return view("reviews.create", compact('restaurant'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Restaurant $restaurant, ReviewRequest $request)
    {
        $request->merge([
            'restaurant_id' => $restaurant->id,
            'user_id' => $request->user()->id
        ]);

        Review::create($request->all());

        return redirect()->route('restaurants.reviews.index', $restaurant)->with('flash_message', 'レビューを投稿しました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Restaurant $restaurant, Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            return redirect()->route('restaurants.reviews.index',$restaurant)->with('error_message', '不正なアクセスです。');
        };
        
        return view('reviews.edit', compact('restaurant', 'review'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Restaurant $restaurant, ReviewRequest $request, Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            return redirect()->route('restaurants.reviews.index',$restaurant)->with('error_message', '不正なアクセスです。');
        };

        $review->update($request->all());

        return redirect()->route('restaurants.reviews.index', $restaurant)->with('flash_message', 'レビューを編集しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant,Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            return redirect()->route('restaurants.reviews.index',$restaurant)->with('error_message', '不正なアクセスです。');
        };
        
        // データベースの削除
        $review->delete();

        return redirect()->route('restaurants.reviews.index',$restaurant)->with('flash_message', 'レビューを削除しました。');
    }
}
