<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RestaurantTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_未ログインのユーザーは管理者側の店舗一覧ページにアクセスできない(): void
    {
        $response = $this->get('/admin/restaurants');
        $response->assertRedirect('/admin/login');
    }

    public function test_ログイン済みの一般ユーザーは管理者側の店舗一覧ページにアクセスできない()
    {
        $user = User::factory()->create();
        $response = $this
            ->actingAs($user)
            ->get('/admin/restaurants');
        $response->assertRedirect('/admin/login');
    }

    public function test_ログイン済みの管理者は管理者側の店舗一覧ページにアクセスできる()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'nagoyameshi',
        ]);
        $response = $this
            ->get(route('admin.restaurants.index'));
        $response->assertStatus(200);
    }

    public function test_未ログインのユーザーは管理者側の会員詳細ページにアクセスできない()
    {
        $response = $this->get('/admin/users/{id}');
        $response->assertRedirect('/admin/login');
    }

    public function test_ログイン済みの一般ユーザーは管理者側の会員詳細ページにアクセスできない()
    {
        $user = User::factory()->create();
        $response = $this
            ->actingAs($user)
            ->get('/admin/users/{id}');
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

        $user = User::factory()->create();

        $response = $this
            ->get(route('admin.users.show', ['id' => $user->id]));
        $response->assertStatus(200);
    }
}
