<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user, $company;

    public function setUp(): void
    {
        parent::setUp();

        // 一般ユーザー
        $this->user = User::factory()->create();

        // 管理者のシーダーを実行
        $this->seed(\Database\Seeders\AdminSeeder::class);

        // 管理者を取得
        $this->admin = Admin::where('email', 'admin@example.com')->first();

        // ダミーデータの作成
        $this->company = Company::factory()->create();
    }

    public function test_未ログインユーザーは会社概要ページにアクセスできる()
    {
        $response = $this->get(route('company.index'));
        $response->assertStatus(200);
    }

    public function test_ログイン済み一般ユーザーは会社概要ページにアクセスできる()
    {
        $response = $this->actingAs($this->user)->get(route('company.index'));
        $response->assertStatus(200);
    }

    public function test_ログイン済み管理者は会社概要ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('company.index'));
        $response->assertRedirect(route('admin.home'));
    }
}
