<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Restaurant;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user, $user_subscription, $restaurant;

    // 共通のデータ
    public function setUp(): void
    {
        parent::setUp();

        // 一般ユーザーと有料会員を生成
        $this->user = User::factory()->create();
        $this->user_subscription = User::factory()->create();

        // 有料会員としてサブスクリプションを設定
        $this->user_subscription->newSubscription('premium_plan', 'price_1PuVTqA0Has0zR6r4lRJ6FoQ')->create('pm_card_visa');

        // 管理者シーダーを実行
        $this->seed(\Database\Seeders\AdminSeeder::class);
        $this->admin = Admin::where('email', 'admin@example.com')->first();

        // レストランを生成
        $this->restaurant = Restaurant::factory()->create();
    }

    // indexアクションのテスト
    public function test_未ログインユーザーはお気に入り一覧ページにアクセスできない()
    {
        $response = $this->get(route('favorites.index'));
        $response->assertRedirect('/login');
    }

    public function test_ログイン済みの無料会員はお気に入り一覧ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('favorites.index'));
        $response->assertRedirect('/subscription/create');
    }

    public function test_ログイン済みの有料会員はお気に入り一覧ページにアクセスできる()
    {
        $response = $this->actingAs($this->user_subscription)->get(route('favorites.index'));
        $response->assertStatus(200);
    }

    public function test_ログイン済みの管理者はお気に入り一覧ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('favorites.index'));
        $response->assertRedirect('/admin/home');
    }

    // storeアクションのテスト
    public function test_未ログインユーザーはお気に入りに追加できない()
    {
        $response = $this->post(route('favorites.store',$this->restaurant));
        $response->assertRedirect('/login');
    }

    public function test_ログイン済みの無料会員はお気に入りに追加できない()
    {
        $response = $this->actingAs($this->user)->post(route('favorites.store',$this->restaurant));
        $response->assertRedirect('/subscription/create');
    }

    public function test_ログイン済みの有料会員はお気に入りに追加できる()
    {
        $response = $this->actingAs($this->user_subscription)->post(route('favorites.store',$this->restaurant));
        $response->assertStatus(302);
    }

    public function test_ログイン済みの管理者はお気に入りに追加できない()
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('favorites.store',$this->restaurant));
        $response->assertRedirect('/admin/home');
    }

    // destroyアクションのテスト
    public function test_未ログインユーザーはお気に入りを解除できない()
    {
        $response = $this->delete(route('favorites.destroy',$this->restaurant));
        $response->assertRedirect('/login');
    }

    public function test_ログイン済みの無料会員はお気に入りを解除できない()
    {
        $response = $this->actingAs($this->user)->delete(route('favorites.destroy',$this->restaurant));
        $response->assertRedirect('/subscription/create');
    }

    public function test_ログイン済みの有料会員はお気に入りを解除できる()
    {
        $this->actingAs($this->user_subscription)->post(route('favorites.store',$this->restaurant));

        $response = $this->actingAs($this->user_subscription)->delete(route('favorites.destroy',$this->restaurant));
        $response->assertStatus(302);
    }

    public function test_ログイン済みの管理者はお気に入りを解除できない()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete(route('favorites.destroy',$this->restaurant));
        $response->assertRedirect('/admin/home');
    }
}