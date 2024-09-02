<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Restaurant;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user,$restaurant;

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

         // ファクトリを実行してレストランを生成
         $this->restaurant = Restaurant::factory()->create();
    }

    // indexアクション
    public function test_未ログインのユーザーは会員側の店舗一覧ページにアクセスできる()
    {
        $response = $this->get(route('restaurants.index'));

        $response->assertStatus(200);
    }

    public function test_ログイン済みの一般ユーザーは会員側の店舗一覧ページにアクセスできる()
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('restaurants.index'));

        $response->assertStatus(200);
    }

    public function test_ログイン済みの管理者は会員側の店舗一覧ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('restaurants.index'));

        $response->assertRedirect('/admin/home');
    }

    // showアクション
    public function test_未ログインのユーザーは会員側の店舗詳細ページにアクセスできる()
    {
        $response = $this->get(route('restaurants.show',$this->restaurant));

        $response->assertStatus(200);
    }

    public function test_ログイン済みの一般ユーザーは会員側の店舗詳細ページにアクセスできる()
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('restaurants.show',$this->restaurant));

        $response->assertStatus(200);
    }

    public function test_ログイン済みの管理者は会員側の店舗詳細ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('restaurants.show',$this->restaurant));

        $response->assertRedirect('/admin/home');
    }
}
