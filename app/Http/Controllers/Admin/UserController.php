<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->keyword;

        if ($keyword !== null) {
            $users = User::where('name', 'like', "%{$keyword}%")->orWhere('kana', 'like', "%{$keyword}%")->paginate(15);
            $total = User::where('name', 'like', "%{$keyword}%")->orWhere('kana', 'like', "%{$keyword}%")->count();
        } else {
            $users = User::paginate(15);
            $total = 0;
        }
        return view('admin.users.index', compact('keyword', 'users', 'total'));
    }
    public function show($id)
    {
        $user = User::find($id);
        return view('admin.users.show', compact('user'));
    }
}