<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 検索キーワードを取得
        $keyword = $request->input('keyword');

        // categoriesテーブルからデータを取得し、検索条件を適用
        $query = Category::query();

        if (!empty($keyword)) {
            // 部分一致検索をカテゴリに適用
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        // ページネーションを適用してデータを取得
        $categories = $query->paginate(10); // ここで10件ずつのページネーションを設定

        // 総数を取得
        $total = $categories->total();

        // ビューに変数を渡して表示
        return view('admin.categories.index', compact('categories', 'keyword', 'total'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // バリデーション
        $validatedData  = $request->validate([
            'name' => 'required',
        ]);

        // カテゴリの登録
        Category::create($validatedData);

        // フラッシュメッセージを設定してリダイレクト
        return redirect()->route('admin.categories.index')->with('flash_message', 'カテゴリを登録しました。');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        // バリデーション
        $validatedData = $request->validate([
            'name' => 'sometimes|required',
        ]);

        // カテゴリの更新
        $category->update($validatedData);

        // フラッシュメッセージを設定してリダイレクト
        return redirect()->route('admin.categories.index')->with('flash_message', 'カテゴリを編集しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // データベースの削除
        $category->delete();

        return redirect()->route('admin.categories.index')->with('flash_message', 'カテゴリを削除しました。');
    }
}
