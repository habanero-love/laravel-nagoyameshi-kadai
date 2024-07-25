<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Admin;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_未ログインのユーザーは管理者側の会員一覧ページにアクセスできない()
    {
        $response = $this->get('/admin/index');
        $response->assertRedirect('/admin/login');
    }

    public function test_ログイン済みの一般ユーザーは管理者側の会員一覧ページにアクセスできない()
    {
        $user = User::factory()->create();
        $response = $this
            ->actingAs($user)
            ->get('/admin/index');
        $response->assertRedirect('/admin/login');
    }

    public function test_ログイン済みの管理者は管理者側の会員一覧ページにアクセスできる()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'nagoyameshi',
        ]);

        $this->assertTrue(Auth::guard('admin')->check());
        $response->assertRedirect(RouteServiceProvider::ADMIN_HOME);
    }

    public function test_未ログインのユーザーは管理者側の会員詳細ページにアクセスできない()
    {
        $response = $this->get('/admin/show');
        $response->assertRedirect('/admin/login');
    }

    public function test_ログイン済みの一般ユーザーは管理者側の会員詳細ページにアクセスできない()
    {
        $user = User::factory()->create();
        $response = $this
            ->actingAs($user)
            ->get('/admin/show');
        $response->assertRedirect('/admin/login');
    }

    public function test_ログイン済みの管理者は管理者側の会員詳細ページにアクセスできる()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'nagoyameshi',
        ]);

        $this->assertTrue(Auth::guard('admin')->check());
        $response->assertRedirect(RouteServiceProvider::ADMIN_HOME);
    }
}
