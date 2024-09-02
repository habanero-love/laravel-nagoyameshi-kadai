<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('user.index', compact('user'));
    }

    public function edit(User $user)
    {
        if (Auth::id() !== $user->id) {
            return redirect()->route('user.index')->with('error_message', '不正なアクセスです。');
        }
        return view('user.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {

        if (Auth::id() !== $user->id) {
            return redirect()->route('user.index')->with('error_message', '不正なアクセスです。');
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'kana' => ['sometimes', 'required', 'string', 'max:255', 'regex:/^[ァ-ヶー]+$/u'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'postal_code' => 'sometimes|required|digits:7',
            'address' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|digits_between:10,11',
            'birthday' => 'nullable|digits:8',
            'occupation' => 'nullable|string|max:255',
        ]);

        $user->update($validatedData);

        return redirect()->route('user.index')->with('flash_message', '会員情報を編集しました。');
    }
}
