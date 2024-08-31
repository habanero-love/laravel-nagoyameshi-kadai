<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\RegularHoliday;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user, $restaurant, $category_ids, $regular_holiday_ids, $restaurant_data;

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
        $this->category_ids = Category::factory()->count(3)->create()->pluck('id')->toArray();

        // 定休日のダミーデータ
        $this->regular_holiday_ids = RegularHoliday::factory()->count(3)->create()->pluck('id')->toArray();

        // ファクトリを実行してレストランを生成
        $this->restaurant = Restaurant::factory()->create();

        // レストランの更新ダミーデータ
        $this->restaurant_data = [
            'name' => '更新成功！',
            'category_ids' => $this->category_ids,
            'regular_holiday_ids' => $this->regular_holiday_ids,
        ];
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
    // 共通処理
    private function test_データベースが変更されていないか確認する()
    {
        // データベースに新しいカテゴリが登録されていないことを確認
        foreach ($this->category_ids as $category_id) {
            $this->assertDatabaseMissing('category_restaurant', [
                'category_id' => $category_id,
            ]);
        }

        // データベースに新しい定休日が登録されていないことを確認
        foreach ($this->regular_holiday_ids as $regular_holiday_id) {
            $this->assertDatabaseMissing('regular_holiday_restaurant', [
                'regular_holiday_id' => $regular_holiday_id,
            ]);
        }
    }

    public function test_未ログインのユーザーは管理者側の店舗を登録できない()
    {
        $response = $this->post(route('admin.restaurants.store'), array_merge($this->restaurant->toArray(), [
            'category_ids' => $this->category_ids,
            'regular_holiday_ids' => $this->regular_holiday_ids,
        ]));

        $response->assertRedirect(route('admin.login'));

        $this->test_データベースが変更されていないか確認する();
    }

    public function test_ログイン済みの一般ユーザーは管理者側の店舗を登録できない()
    {
        $response = $this->actingAs($this->user)->post(route('admin.restaurants.store'), array_merge($this->restaurant->toArray(), [
            'category_ids' => $this->category_ids,
            'regular_holiday_ids' => $this->regular_holiday_ids,
        ]));

        $response->assertRedirect(route('admin.login'));

        $this->test_データベースが変更されていないか確認する();
    }

    public function test_ログイン済みの管理者は管理者側の店舗を登録できる()
    {
        // 新しいレストランのデータを用意し、カテゴリID・定休日を追加
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.restaurants.store'), array_merge($this->restaurant->toArray(), [
            'category_ids' => $this->category_ids,
            'regular_holiday_ids' => $this->regular_holiday_ids,
        ]));

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

        // 定休日が正しいレストランIDで登録されているかを確認
        foreach ($this->regular_holiday_ids as $regular_holiday_id) {
            $this->assertDatabaseHas('regular_holiday_restaurant', [
                'regular_holiday_id' => $regular_holiday_id,
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
        $response = $this->put(route('admin.restaurants.update', $this->restaurant), $this->restaurant_data);

        $response->assertRedirect(route('admin.login'));

        $this->test_データベースが変更されていないか確認する();
    }

    public function test_ログイン済みの一般ユーザーは管理者側の店舗を更新できない()
    {
        $response = $this->actingAs($this->user)->put(route('admin.restaurants.update', $this->restaurant), $this->restaurant_data);

        $response->assertRedirect(route('admin.login'));

        $this->test_データベースが変更されていないか確認する();
    }

    public function test_ログイン済みの管理者は管理者側の店舗を更新できる()
    {
        $response = $this->actingAs($this->admin, 'admin')->put(route('admin.restaurants.update', $this->restaurant), $this->restaurant_data);

        $response->assertRedirect(route('admin.restaurants.show', $this->restaurant));

        foreach ($this->category_ids as $category_id) {
            $this->assertDatabaseHas('category_restaurant', [
                'category_id' => $category_id,
                'restaurant_id' => $this->restaurant->id,
            ]);
        }

        foreach ($this->regular_holiday_ids as $regular_holiday_id) {
            $this->assertDatabaseHas('regular_holiday_restaurant', [
                'regular_holiday_id' => $regular_holiday_id,
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
