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

    // updateアクション
    public function test_未ログインのユーザーはお支払い方法を更新できない()
    {
        $response = $this->put(route('subscription.update'));

        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員はお支払い方法を更新できない()
    {
        $response = $this->actingAs($this->user)->put(route('subscription.update'));

        $response->assertRedirect(route('subscription.create'));
    }

    public function test_ログイン済みの有料会員はお支払い方法を更新できる()
    {
        $request_parameter = [
            'paymentMethodId' => 'pm_card_mastercard'
        ];

        $default_payment_method_id = $this->user_subscription->defaultPaymentMethod()->id;

        $response = $this->actingAs($this->user_subscription)->put(route('subscription.update'), $request_parameter);

        $response->assertRedirect(route('home'));

        $update_payment_method_id = $this->user_subscription->defaultPaymentMethod()->id;

        $this->assertNotSame($default_payment_method_id, $update_payment_method_id);
    }

    public function test_ログイン済みの管理者はお支払い方法を更新できない()
    {
        $response = $this->actingAs($this->admin, 'admin')->put(route('subscription.update'));

        $response->assertRedirect(route('admin.home'));
    }

    // cancelアクション
    public function test_未ログインのユーザーは有料プラン解約ページにアクセスできない()
    {
        $response = $this->get(route('subscription.cancel'));

        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員は有料プラン解約ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('subscription.cancel'));

        $response->assertRedirect(route('subscription.create'));
    }

    public function test_ログイン済みの有料会員は有料プラン解約ページにアクセスできる()
    {

        $response = $this->actingAs($this->user_subscription)->get(route('subscription.cancel'));

        $response->assertStatus(200);
    }

    public function test_ログイン済みの管理者は有料プラン解約ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('subscription.cancel'));

        $response->assertRedirect(route('admin.home'));
    }

    // destroyアクション
    public function test_未ログインのユーザーは有料プランを解約できない()
    {
        $response = $this->delete(route('subscription.destroy'));

        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員は有料プランを解約できない()
    {
        $response = $this->actingAs($this->user)->delete(route('subscription.destroy'));

        $response->assertRedirect(route('subscription.create'));
    }

    public function test_ログイン済みの有料会員は有料プランを解約できる()
    {

        $response = $this->actingAs($this->user_subscription)->delete(route('subscription.destroy'));

        $response->assertRedirect(route('home'));

        $this->assertFalse($this->user_subscription->subscribed('premium_plan'));
    }

    public function test_ログイン済みの管理者は有料プランを解約できない()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete(route('subscription.destroy'));

        $response->assertRedirect(route('admin.home'));
    }
}
