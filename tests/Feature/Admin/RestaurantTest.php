<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Restaurant;
use App\Models\Category;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user, $restaurant, $category_ids;

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

        // カテゴリのダミーデータ
        $categories = Category::factory()->count(3)->create();
        $this->category_ids = $categories->pluck('id')->toArray();

        // ファクトリを実行してレストランを生成
        $this->restaurant = Restaurant::factory()->create();
    }

    // indexアクション
    public function test_未ログインのユーザーは管理者側の店舗一覧ページにアクセスできない()
    {
        $response = $this->get(route('admin.restaurants.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの一般ユーザーは管理者側の店舗一覧ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('admin.restaurants.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの管理者は管理者側の店舗一覧ページにアクセスできる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.restaurants.index'));

        $response->assertStatus(200);
    }

    // showアクション
    public function test_未ログインのユーザーは管理者側の店舗詳細ページにアクセスできない()
    {
        $response = $this->get(route('admin.restaurants.show', $this->restaurant));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの一般ユーザーは管理者側の店舗詳細ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('admin.restaurants.show', $this->restaurant));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの管理者は管理者側の店舗詳細ページにアクセスできる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.restaurants.show', $this->restaurant));

        $response->assertStatus(200);
    }

    // createアクション
    public function test_未ログインのユーザーは管理者側の店舗登録ページにアクセスできない()
    {
        $response = $this->get(route('admin.restaurants.create'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの一般ユーザーは管理者側の店舗登録ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('admin.restaurants.create'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの管理者は管理者側の店舗登録ページにアクセスできる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.restaurants.create'));

        $response->assertStatus(200);
    }

    // storeアクション
    public function test_未ログインのユーザーは管理者側の店舗を登録できない()
    {
        $response = $this->post(route('admin.restaurants.store'), array_merge($this->restaurant->toArray(), ['category_ids' => $this->category_ids]));

        $response->assertRedirect(route('admin.login'));

        // データベースに新しいカテゴリが登録されていないことを確認
        foreach ($this->category_ids as $category_id) {
            $this->assertDatabaseMissing('category_restaurant', [
                'category_id' => $category_id,
            ]);
        }
    }

    public function test_ログイン済みの一般ユーザーは管理者側の店舗を登録できない()
    {
        $response = $this->actingAs($this->user)->post(route('admin.restaurants.store'), array_merge($this->restaurant->toArray(), ['category_ids' => $this->category_ids]));

        $response->assertRedirect(route('admin.login'));

        // データベースに新しいカテゴリが登録されていないことを確認
        foreach ($this->category_ids as $category_id) {
            $this->assertDatabaseMissing('category_restaurant', [
                'category_id' => $category_id,
            ]);
        }
    }

    public function test_ログイン済みの管理者は管理者側の店舗を登録できる()
    {
        // 新しいレストランのデータを用意し、カテゴリIDを追加
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.restaurants.store'), array_merge($this->restaurant->toArray(), ['category_ids' => $this->category_ids]));

        // レスポンスが店舗一覧ページにリダイレクトすることを確認
        $response->assertRedirect(route('admin.restaurants.index'));

        // 新しく作成されたレストランのIDを取得
        $restaurant_id = Restaurant::latest('id')->first()->id;

        // 各カテゴリが正しいレストランIDで登録されているかを確認
        foreach ($this->category_ids as $category_id) {
            $this->assertDatabaseHas('category_restaurant', [
                'category_id' => $category_id,
                'restaurant_id' => $restaurant_id,
            ]);
        }
    }

    // editアクション
    public function test_未ログインのユーザーは管理者側の店舗編集ページにアクセスできない()
    {
        $response = $this->get(route('admin.restaurants.edit', $this->restaurant));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの一般ユーザーは管理者側の店舗編集ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('admin.restaurants.edit', $this->restaurant));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_ログイン済みの管理者は管理者側の店舗編集ページにアクセスできる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.restaurants.edit', $this->restaurant));

        $response->assertStatus(200);
    }

    // updateアクション
    public function test_未ログインのユーザーは管理者側の店舗を更新できない()
    {
        $restaurant_data = [
            'name' => '更新成功！',
            'category_ids' => $this->category_ids,
        ];

        $response = $this->put(route('admin.restaurants.update', $this->restaurant), $restaurant_data);

        $response->assertRedirect(route('admin.login'));

        $this->assertDatabaseMissing('restaurants', [
            'id' => $this->restaurant->id,
            'name' => '更新成功！',
        ]);

        foreach ($this->category_ids as $category_id) {
            $this->assertDatabaseMissing('category_restaurant', [
                'category_id' => $category_id,
                'restaurant_id' => $this->restaurant->id,
            ]);
        }
    }

    public function test_ログイン済みの一般ユーザーは管理者側の店舗を更新できない()
    {
        $restaurant_data = [
            'name' => '更新成功！',
            'category_ids' => $this->category_ids,
        ];

        $response = $this->actingAs($this->user)->put(route('admin.restaurants.update', $this->restaurant), $restaurant_data);

        $response->assertRedirect(route('admin.login'));

        $this->assertDatabaseMissing('restaurants', [
            'id' => $this->restaurant->id,
            'name' => '更新成功！',
        ]);

        foreach ($this->category_ids as $category_id) {
            $this->assertDatabaseMissing('category_restaurant', [
                'category_id' => $category_id,
                'restaurant_id' => $this->restaurant->id,
            ]);
        }
    }

    public function test_ログイン済みの管理者は管理者側の店舗を更新できる()
    {
        $restaurant_data = [
            'name' => '更新成功！',
            'category_ids' => $this->category_ids,
        ];

        $response = $this->actingAs($this->admin, 'admin')->put(route('admin.restaurants.update', $this->restaurant), $restaurant_data);

        $response->assertRedirect(route('admin.restaurants.show', $this->restaurant));

        $this->assertDatabaseHas('restaurants', [
            'id' => $this->restaurant->id,
            'name' => '更新成功！',
        ]);

        foreach ($this->category_ids as $category_id) {
            $this->assertDatabaseHas('category_restaurant', [
                'category_id' => $category_id,
                'restaurant_id' => $this->restaurant->id,
            ]);
        }
    }

    // destroyアクション
    public function test_未ログインのユーザーは管理者側の店舗を削除できない()
    {
        $response = $this->delete(route('admin.restaurants.destroy', $this->restaurant));

        $response->assertRedirect(route('admin.login'));

        $this->assertModelExists($this->restaurant);
    }

    public function test_ログイン済みの一般ユーザーは管理者側の店舗を削除できない()
    {
        $response = $this->actingAs($this->user)->delete(route('admin.restaurants.destroy', $this->restaurant));

        $response->assertRedirect(route('admin.login'));

        $this->assertModelExists($this->restaurant);
    }

    public function test_ログイン済みの管理者は管理者側の店舗を削除できる()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete(route('admin.restaurants.destroy', $this->restaurant));

        $response->assertRedirect(route('admin.restaurants.index'));
        $this->assertModelMissing($this->restaurant);
    }
}
