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
        // 検索キーワードを取得
        $keyword = $request->input('keyword');
        
        // restaurantsテーブルからデータを取得し、検索条件を適用
        $query = Restaurant::query();
        
        if (!empty($keyword)) {
            // 部分一致検索を店舗名に適用
            $query->where('name', 'like', '%' . $keyword . '%');
        }
        
        // ページネーションを適用してデータを取得
        $restaurants = $query->paginate(10); // ここで10件ずつのページネーションを設定
        
        // 総数を取得
        $total = $restaurants->total();

        // ビューに変数を渡して表示
        return view('admin.restaurants.index', compact('restaurants', 'keyword', 'total'));
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
        // リクエストデータの取得（image以外のデータ)
        $data = $request->except('image');

        // 画像のアップロード処理
        if ($request->hasFile('image')) {
            // 画像を指定のディレクトリに保存
            $image = $request->file('image')->store('public/restaurants');
            // パスからファイル名のみを取得して設定
            $data['image'] = basename($image);
        } else {
            // 画像がアップロードされていない場合
            $data['image'] = '';
        }

        // データベースに保存
        Restaurant::create($data);

        // フラッシュメッセージをセッションに保存し、リダイレクト
        return redirect()->route('admin.restaurants.index')->with('flash_message', '店舗を登録しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Restaurant $restaurant)
    {
        return view('admin.restaurants.show',compact('restaurant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Restaurant $restaurant)
    {
        return view('admin.restaurants.edit',compact('restaurant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RestaurantRequest $request,Restaurant $restaurant)
    {
        // リクエストデータの取得（image以外のデータ)
        $data = $request->except('image');

        // 画像のアップロード処理
        if ($request->hasFile('image')) {
            // 画像を指定のディレクトリに保存
            $image = $request->file('image')->store('public/restaurants');
            // パスからファイル名のみを取得して設定
            $data['image'] = basename($image);
        }

        // データベースを更新
        $restaurant->update($data);

        // フラッシュメッセージをセッションに保存し、リダイレクト
        return redirect()->route('admin.restaurants.show',$restaurant)->with('flash_message', '店舗を編集しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant)
    {
        // データベースの削除
        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')->with('flash_message', '店舗を削除しました。');
    }
}
