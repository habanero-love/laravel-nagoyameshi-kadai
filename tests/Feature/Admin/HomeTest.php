<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user, $Term;

    public function setUp(): void
    {
        parent::setUp();

        // 一般ユーザー
        $this->user = User::factory()->create();

        // 管理者のシーダーを実行
        $this->seed(\Database\Seeders\AdminSeeder::class);

        // 管理者を取得
        $this->admin = Admin::where('email', 'admin@example.com')->first();
    }

    public function test_未ログインのユーザーは管理者側のトップページにアクセスできない()
    {
        $response = $this->get(route('admin.home'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの一般ユーザーは管理者側のトップページにアクセスでない()
    {
        $response = $this->actingAs($this->user)->get(route('admin.home'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの管理者は管理者側のトップページにアクセスできる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.home'));
        $response->assertStatus(200);
    }
}
