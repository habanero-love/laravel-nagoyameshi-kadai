<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Http\RedirectResponse;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user, $user_subscription;

    // 共通のデータ
    public function setUp(): void
    {
        parent::setUp();

        // ファクトリを実行して一般ユーザーを生成
        $this->user = User::factory()->create();
        $this->user_subscription = User::factory()->create();

        // 一般有料会員
        $this->user_subscription->newSubscription('premium_plan', 'price_1PuVTqA0Has0zR6r4lRJ6FoQ')->create('pm_card_visa');

        // シーダーを実行して管理者ユーザーを生成
        $this->seed(\Database\Seeders\AdminSeeder::class);

        // シーダーで作成された管理者を取得してクラスプロパティに保存
        $this->admin = Admin::where('email', 'admin@example.com')->first();
    }

    // createアクション
    public function test_未ログインのユーザーは有料プラン登録ページにアクセスできない()
    {
        $response = $this->get(route('subscription.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員は有料プラン登録ページにアクセスできる()
    {
        $response = $this->actingAs($this->user)->get(route('subscription.create'));

        $response->assertStatus(200);
    }

    public function test_ログイン済みの有料会員は有料プラン登録ページにアクセスできない()
    {
        $response = $this->actingAs($this->user_subscription)->get(route('subscription.create'));

        $response->assertRedirect(route('subscription.edit'));
    }
    public function test_ログイン済みの管理者は有料プラン登録ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('subscription.create'));

        $response->assertRedirect(route('admin.home'));
    }

    // storeアクション
    public function test_未ログインのユーザーは有料プランに登録できない()
    {
        $response = $this->post(route('subscription.store'));

        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員は有料プランに登録できる()
    {
        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->actingAs($this->user)->post(route('subscription.store'), $request_parameter);

        $response->assertRedirect(route('home'));

        $this->assertTrue($this->user_subscription->subscribed('premium_plan'));
    }
    public function test_ログイン済みの有料会員は有料プランに登録できない()
    {
        $response = $this->actingAs($this->user_subscription)->post(route('subscription.store'));

        $response->assertRedirect(route('subscription.edit'));
    }

    public function test_ログイン済みの管理者は有料プランに登録できない()
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('subscription.store'));

        $response->assertRedirect(route('admin.home'));
    }

    // editアクション
    public function test_未ログインのユーザーはお支払い方法編集ページにアクセスできない()
    {
        $response = $this->get(route('subscription.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員はお支払い方法編集ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('subscription.edit'));

        $response->assertRedirect(route('subscription.create'));
    }

    public function test_ログイン済みの有料会員はお支払い方法編集ページにアクセスできる()
    {

        $response = $this->actingAs($this->user_subscription)->get(route('subscription.edit'));

        $response->assertStatus(200);
    }

    public function test_ログイン済みの管理者はお支払い方法編集ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('subscription.edit'));

        $response->assertRedirect(route('admin.home'));
    }

    // // updateアクション
    // public function test_未ログインのユーザーは管理者側の店舗を更新できない()
    // {
    //     $response = $this->put(route('subscription.update', $this->restaurant), $this->restaurant_data);

    //     $response->assertRedirect(route('login'));

    //     $this->test_データベースが変更されていないか確認する();
    // }

    // public function test_ログイン済みの一般ユーザーは管理者側の店舗を更新できない()
    // {
    //     $response = $this->actingAs($this->user)->put(route('subscription.update', $this->restaurant), $this->restaurant_data);

    //     $response->assertRedirect(route('login'));

    //     $this->test_データベースが変更されていないか確認する();
    // }

    // public function test_ログイン済みの管理者は管理者側の店舗を更新できる()
    // {
    //     $response = $this->actingAs($this->admin, 'admin')->put(route('subscription.update', $this->restaurant), $this->restaurant_data);

    //     $response->assertRedirect(route('subscription.show', $this->restaurant));

    //     foreach ($this->category_ids as $category_id) {
    //         $this->assertDatabaseHas('category_restaurant', [
    //             'category_id' => $category_id,
    //             'restaurant_id' => $this->restaurant->id,
    //         ]);
    //     }

    //     foreach ($this->regular_holiday_ids as $regular_holiday_id) {
    //         $this->assertDatabaseHas('regular_holiday_restaurant', [
    //             'regular_holiday_id' => $regular_holiday_id,
    //             'restaurant_id' => $this->restaurant->id,
    //         ]);
    //     }
    // }

    // // cancelアクション
    // public function test_未ログインのユーザーは管理者側の店舗を削除できない()
    // {
    //     $response = $this->delete(route('subscription.destroy', $this->restaurant));

    //     $response->assertRedirect(route('login'));

    //     $this->assertModelExists($this->restaurant);
    // }

    // public function test_ログイン済みの一般ユーザーは管理者側の店舗を削除できない()
    // {
    //     $response = $this->actingAs($this->user)->delete(route('subscription.destroy', $this->restaurant));

    //     $response->assertRedirect(route('login'));

    //     $this->assertModelExists($this->restaurant);
    // }

    // public function test_ログイン済みの管理者は管理者側の店舗を削除できる()
    // {
    //     $response = $this->actingAs($this->admin, 'admin')->delete(route('subscription.destroy', $this->restaurant));

    //     $response->assertRedirect(route('subscription.index'));
    //     $this->assertModelMissing($this->restaurant);
    // }

    // // destroyアクション
    // public function test_未ログインのユーザーは管理者側の店舗を削除できない()
    // {
    //     $response = $this->delete(route('subscription.destroy', $this->restaurant));

    //     $response->assertRedirect(route('login'));

    //     $this->assertModelExists($this->restaurant);
    // }

    // public function test_ログイン済みの一般ユーザーは管理者側の店舗を削除できない()
    // {
    //     $response = $this->actingAs($this->user)->delete(route('subscription.destroy', $this->restaurant));

    //     $response->assertRedirect(route('login'));

    //     $this->assertModelExists($this->restaurant);
    // }

    // public function test_ログイン済みの管理者は管理者側の店舗を削除できる()
    // {
    //     $response = $this->actingAs($this->admin, 'admin')->delete(route('subscription.destroy', $this->restaurant));

    //     $response->assertRedirect(route('subscription.index'));
    //     $this->assertModelMissing($this->restaurant);
    // }
}
