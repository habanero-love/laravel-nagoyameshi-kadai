<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        // 検索条件の取得
        $keyword = $request->input('keyword');
        $category_id = $request->input('category_id');
        $price = $request->input('price');
        $sorted = 'created_at desc'; // デフォルトの並べ替え
        $sort_query = [];

        // 絞り込みクエリの構築
        $restaurants = Restaurant::query();

        if ($keyword) {
            $restaurants->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhereHas('categories', function ($q) use ($keyword) {
                        $q->where('categories.name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($category_id) {
            $restaurants->whereHas('categories', function ($query) use ($category_id) {
                $query->where('categories.id', '=', $category_id);
            });
        }

        if ($price) {
            $restaurants->where('lowest_price', '<=', $price);
        }

        // 並び替えの準備
        if ($request->has('select_sort')) {
            $slices = explode(' ', $request->input('select_sort')); // 配列に分ける
            $sort_query[$slices[0]] = $slices[1]; // $f["key"] = value は $f=[key => "value"]になる。配列にキーを指定して追加できる
            $sorted = $request->input('select_sort'); // 配列を受け取る
        }

        // 並べ替えとページネーションの適用
        $restaurants = $restaurants->sortable($sort_query)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // カテゴリデータの取得
        $categories = Category::all();

        // 総数の取得
        $total = $restaurants->total();

        // 並べ替えのオプション
        $sorts = [
            '掲載日が新しい順' => 'created_at desc',
            '価格が安い順' => 'lowest_price asc',
            '評価が高い順' => 'rating desc',
        ];

        // ビューに変数を渡して表示
        return view('restaurants.index', compact('keyword', 'category_id', 'price', 'sorts', 'sorted', 'restaurants', 'categories', 'total'));
    }

    public function show(Restaurant $restaurant){
        return view('restaurants.show',compact('restaurant'));
    }
}
