<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user, $category;

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

        // ファクトリを実行してカテゴリを生成
        $this->category = Category::factory()->create();
    }

    // indexアクション
    public function test_未ログインのユーザーは管理者側のカテゴリ一覧ページにアクセスできない()
    {
        $response = $this->get(route('admin.categories.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの一般ユーザーは管理者側のカテゴリ一覧ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('admin.categories.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの管理者は管理者側のカテゴリ一覧ページにアクセスできる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.categories.index'));

        $response->assertStatus(200);
    }
    
    // storeアクション
    public function test_未ログインのユーザーは管理者側のカテゴリを登録できない()
    {
        $response = $this->post(route('admin.categories.store'), $this->category->toArray());

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの一般ユーザーは管理者側のカテゴリを登録できない()
    {
        $response = $this->actingAs($this->user)->post(route('admin.categories.store'), $this->category->toArray());

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの管理者は管理者側のカテゴリを登録できる()
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.categories.store'), $this->category->toArray());

        $response->assertRedirect(route('admin.categories.index'));
    }
    
    // updateアクション
    public function test_未ログインのユーザーは管理者側のカテゴリを更新できない()
    {
        $response = $this->put(route('admin.categories.update', $this->category), [
            'name' => '更新成功！',
        ]);

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの一般ユーザーは管理者側のカテゴリを更新できない()
    {
        $response = $this->actingAs($this->user)->put(route('admin.categories.update', $this->category), [
            'name' => '更新成功！',
        ]);

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの管理者は管理者側のカテゴリを更新できる()
    {
        $response = $this->actingAs($this->admin, 'admin')->put(route('admin.categories.update', $this->category), [
            'name' => '更新成功！',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
    }

    // destroyアクション
    public function test_未ログインのユーザーは管理者側のカテゴリを削除できない()
    {
        $response = $this->delete(route('admin.categories.destroy', $this->category));

        $response->assertRedirect(route('admin.login'));

        $this->assertModelExists($this->category);
    }

    public function test_ログイン済みの一般ユーザーは管理者側のカテゴリを削除できない()
    {
        $response = $this->actingAs($this->user)->delete(route('admin.categories.destroy', $this->category));

        $response->assertRedirect(route('admin.login'));

        $this->assertModelExists($this->category);
    }

    public function test_ログイン済みの管理者は管理者側のカテゴリを削除できる()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete(route('admin.categories.destroy', $this->category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertModelMissing($this->category);
    }
}
