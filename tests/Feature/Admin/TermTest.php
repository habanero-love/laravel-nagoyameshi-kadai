<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Term;

class TermTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user, $term, $term_data;

    // 共通のデータ
    public function setUp(): void
    {
        parent::setUp();

        // ファクトリを実行して一般ユーザーを生成
        $this->user = User::factory()->create();

        // シーダーを実行して管理者ユーザーを生成
        $this->seed(\Database\Seeders\AdminSeeder::class);

        // シーダーで作成された管理者を取得してクラスプロパティに保存
        $this->admin = Admin::where('email', 'admin@example.com')->first();

        // ダミーデータの作成
        $this->term = Term::factory()->create();
        $this->term_data = [
            'content' => '編集成功！'
        ];
    }

    // indexアクション
    public function test_未ログインのユーザーは管理者側の会社概要ページにアクセスできない()
    {
        $response = $this->get(route('admin.terms.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの一般ユーザーは管理者側の会社概要ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('admin.terms.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの管理者は管理者側の会社概要ページにアクセスできる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.terms.index'));

        $response->assertStatus(200);
    }

    // editアクション
    public function test_未ログインのユーザーは管理者側の会社概要編集ページにアクセスできない()
    {
        $response = $this->get(route('admin.terms.edit', $this->term));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの一般ユーザーは管理者側の会社概要編集ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('admin.terms.edit', $this->term));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの管理者は管理者側の会社概要編集ページにアクセスできる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.terms.edit', $this->term));

        $response->assertStatus(200);
    }

    // updateアクション
    public function test_未ログインのユーザーは管理者側の店舗を更新できない()
    {
        $response = $this->put(route('admin.terms.update', $this->term), $this->term_data);

        $response->assertRedirect(route('admin.login'));

        $this->assertDatabaseMissing(Term::class, $this->term_data);
    }

    public function test_ログイン済みの一般ユーザーは管理者側の店舗を更新できない()
    {
        $response = $this->actingAs($this->user)->put(route('admin.terms.update', $this->term), $this->term_data);

        $response->assertRedirect(route('admin.login'));

        $this->assertDatabaseMissing(Term::class, $this->term_data);
    }

    public function test_ログイン済みの管理者は管理者側の店舗を更新できる()
    {
        $response = $this->actingAs($this->admin, 'admin')->put(route('admin.terms.update', $this->term), $this->term_data);

        $response->assertRedirect(route('admin.terms.index'));

        $this->assertDatabaseHas(Term::class, $this->term_data);
    }
}
