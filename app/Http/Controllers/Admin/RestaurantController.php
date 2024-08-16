<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantRequest;
use Illuminate\Http\Request;
use App\Models\Restaurant;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->keyword;

        if ($keyword !== null) {
            $restaurants = Restaurant::where('name', 'like', "%{$keyword}%")->paginate(15);
            $total = Restaurant::where('name', 'like', "%{$keyword}%")->count();
        } else {
            $restaurants = Restaurant::paginate(15);
            $total = 0;
        }
        return view('admin.restaurants.index', compact('keyword', 'restaurants', 'total'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.restaurants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RestaurantRequest $request)
    {
        $restaurant = new Restaurant($request->except('image')); // image以外を一括で保存する

        // 画像の処理
        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('images', 'public');
            $restaurant->image_name = basename($image);
        } else {
            $image = '';
        }

        // データベースに保存
        $restaurant->save();

        // 成功した場合のレスポンス
        return redirect("admin/restaurants/{$restaurant->id}")->with('flash_message', '店舗を登録しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Restaurant $restaurant)
    {
        return view('admin.restaurants.show', compact('restaurant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Restaurant $restaurant)
    {
        return view('admin.restaurants.edit', compact('restaurant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RestaurantRequest $request)
    {
        $restaurant = new Restaurant($request->except('image')); // image以外を一括で保存する

        // 画像の処理
        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('images', 'public');
            $restaurant->image_name = basename($image);
        } else {
            $image = '';
        }

        // データベースに保存
        $restaurant->save();

        // 成功した場合のレスポンス
        return redirect("admin/restaurants/{$restaurant->id}")->with('flash_message', '店舗を編集しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant)
    {
        // データベースを削除
        $restaurant->delete();

        // 成功した場合のレスポンス
        return redirect("admin/restaurants")->with('flash_message', '店舗を削除しました。');
    }
}
