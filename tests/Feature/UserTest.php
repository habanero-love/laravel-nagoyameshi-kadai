<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user, $user_data;

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
        $this->user_data = [
            'name' => '編集成功！'
        ];
    }
    
    // indexアクション
    public function test_未ログインのユーザーは会員側の会員情報ページにアクセスできない()
    {
        $response = $this->get(route('user.index'));

        $response->assertRedirect('/login');
    }

    public function test_ログイン済みの一般ユーザーは会員側の会員情報ページにアクセスできる()
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('user.index'));

        $response->assertStatus(200);
    }

    public function test_ログイン済みの管理者は会員側の会員情報ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('user.index'));

        $response->assertRedirect('/admin/home');
    }

    // editアクション
    public function test_未ログインのユーザーは会員側の会員情報編集ページにアクセスできない()
    {
        $response = $this->get(route('user.edit', $this->user));

        $response->assertRedirect('/login');
    }

    public function ログイン済みの一般ユーザーは会員側の他人の会員情報編集ページにアクセスできない()
    {
        $anotherUser = User::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('user.edit', $anotherUser));

        $response->assertRedirect(route('user.index'));
    }
    public function test_ログイン済みの一般ユーザーは会員側の自身の会員情報編集ページにアクセスできる()
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('user.edit', $this->user));

        $response->assertStatus(200);
    }

    public function test_ログイン済みの管理者は会員側の会員情報編集ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('user.edit', $this->user));

        $response->assertRedirect('/admin/home');
    }

    // updateアクション
    public function test_未ログインのユーザーは会員側の会員情報を更新できない()
    {
        $response = $this->put(route('user.update', $this->user),$this->user_data);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing(User::class, $this->user_data);
    }

    public function ログイン済みの一般ユーザーは会員側の他人の会員情報を更新できない()
    {
        $anotherUser = User::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->put(route('user.update', $anotherUser),$this->user_data);

        $response->assertRedirect(route('user.index'));

        $this->assertDatabaseMissing(User::class, $this->user_data);
    }
    public function test_ログイン済みの一般ユーザーは会員側の自身の会員情報を更新できる()
    {
        $response = $this
            ->actingAs($this->user)
            ->put(route('user.update', $this->user),$this->user_data);

            $response->assertRedirect(route('user.index'));

        $this->assertDatabaseHas(User::class, $this->user_data);
    }

    public function test_ログイン済みの管理者は会員側の会員情報を更新できない()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('user.update', $this->user),$this->user_data);

        $response->assertRedirect('/admin/home');

        $this->assertDatabaseMissing(User::class, $this->user_data);
    }
}
