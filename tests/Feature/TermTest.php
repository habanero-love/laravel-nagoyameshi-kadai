<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TermTest extends TestCase
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

        // ダミーデータの作成
        $this->Term = Term::factory()->create();
    }

    public function test_未ログインユーザーは利用規約ページにアクセスできる()
    {
        $response = $this->get(route('terms.index'));
        $response->assertStatus(200);
    }

    public function test_ログイン済み一般ユーザーは利用規約ページにアクセスできる()
    {
        $response = $this->actingAs($this->user)->get(route('terms.index'));
        $response->assertStatus(200);
    }

    public function test_ログイン済み管理者は利用規約ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('terms.index'));
        $response->assertRedirect(route('admin.home'));
    }
}
