<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request) {
        $keyword = $request->keyword;

        if ($keyword !== null) {
             $users = User::where([
                ['name', 'like', "%{$keyword}%"],
                ['kana', 'like', "%{$keyword}%"],
             ])->get();
             $total = $users->total();
        } else {
            $users = null;
            $total = "";
        }
        return view('admin.users.index',compact('keyword','users','total'));
    }
    public function show() {
        $user = User::all();
        return view('admin.users.show',compact('user'));
    }
}
