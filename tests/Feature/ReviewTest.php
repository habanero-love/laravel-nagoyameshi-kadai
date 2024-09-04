<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Review;
use App\Models\Restaurant;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user, $user_subscription, $restaurant, $review;

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

        // ダミーデータ生成
        $this->restaurant = Restaurant::factory()->create();
        $this->review = Review::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->user_subscription->id
        ]);
    }

    // indexアクション
    public function test_未ログインのユーザーは会員側のレビュー一覧ページにアクセスできない()
    {
        $response = $this->get(route('restaurants.reviews.index', $this->restaurant));
        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員は会員側のレビュー一覧ページにアクセスできる()
    {
        $response = $this->actingAs($this->user)->get(route('restaurants.reviews.index', $this->restaurant));
        $response->assertStatus(200);
    }

    public function test_ログイン済みの有料会員は会員側のレビュー一覧ページにアクセスできる()
    {
        $response = $this->actingAs($this->user_subscription)->get(route('restaurants.reviews.index', $this->restaurant));
        $response->assertStatus(200);
    }

    public function test_ログイン済みの管理者は会員側のレビュー一覧ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('restaurants.reviews.index', $this->restaurant));
        $response->assertRedirect(route('admin.home'));
    }

    // createアクション
    public function test_未ログインのユーザーは会員側のレビュー投稿ページにアクセスできない()
    {
        $response = $this->get(route('restaurants.reviews.create', $this->restaurant));
        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員は会員側のレビュー投稿ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('restaurants.reviews.create', $this->restaurant));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_ログイン済みの有料会員は会員側のレビュー投稿ページにアクセスできる()
    {
        $response = $this->actingAs($this->user_subscription)->get(route('restaurants.reviews.create', $this->restaurant));
        $response->assertStatus(200);
    }

    public function test_ログイン済みの管理者は会員側のレビュー投稿ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('restaurants.reviews.create', $this->restaurant));
        $response->assertRedirect(route('admin.home'));
    }


    // storeアクション
    public function test_未ログインのユーザーはレビューを投稿できない()
    {
        $response = $this->post(route('restaurants.reviews.store', $this->restaurant), $this->review->toArray());
        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員はレビューを投稿できない()
    {
        $response = $this->actingAs($this->user)->post(route('restaurants.reviews.store', $this->restaurant), $this->review->toArray());
        $response->assertRedirect(route('subscription.create'));
    }
    public function test_ログイン済みの有料会員はレビューを投稿できる()
    {
        $response = $this->actingAs($this->user_subscription)->post(route('restaurants.reviews.store', $this->restaurant), $this->review->toArray());
        $response->assertRedirect(route('restaurants.reviews.index', $this->restaurant));
    }
    public function test_ログイン済みの管理者はレビューを投稿できない()
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('restaurants.reviews.store', $this->restaurant), $this->review->toArray());
        $response->assertRedirect(route('admin.home'));
    }


    // editアクション
    public function test_未ログインのユーザーは会員側のレビュー編集ページにアクセスできない()
    {
        $response = $this->get(route('restaurants.reviews.edit', [$this->restaurant, $this->review]));
        $response->assertRedirect(route('login'));
    }
    public function test_ログイン済みの無料会員はレビュー編集ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('restaurants.reviews.edit', [$this->restaurant, $this->review]));
        $response->assertRedirect(route('subscription.create'));
    }
    public function test_ログイン済みの有料会員は会員側の他人のレビュー編集ページにアクセスできない()
    {
        $review_other = Review::factory()->create(['restaurant_id' => $this->restaurant->id, 'user_id' => User::factory()->create()->id]);

        $response = $this->actingAs($this->user_subscription)->get(route('restaurants.reviews.edit', [$this->restaurant, $review_other]));
        $response->assertRedirect(route('restaurants.reviews.index', $this->restaurant));
    }
    public function test_ログイン済みの有料会員は会員側の自身のレビュー編集ページにアクセスできる()
    {
        $response = $this->actingAs($this->user_subscription)->get(route('restaurants.reviews.edit', [$this->restaurant, $this->review]));
        $response->assertStatus(200);
    }
    public function test_ログイン済みの管理者は会員側のレビュー編集ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('restaurants.reviews.edit', [$this->restaurant, $this->review]));
        $response->assertRedirect(route('admin.home'));
    }

    // updateアクション
    public function test_未ログインのユーザーはレビューを更新できない()
    {
        $response = $this->put(route('restaurants.reviews.update', [$this->restaurant, $this->review]), [
            'content' => '更新成功！'
        ]);

        $response->assertRedirect(route('login'));
    }
    public function test_ログイン済みの無料会員はレビューを更新できない()
    {
        $response = $this->actingAs($this->user)->put(route('restaurants.reviews.update', [$this->restaurant, $this->review]), [
            'content' => '更新成功！'
        ]);

        $response->assertRedirect(route('subscription.create'));
    }
    public function test_ログイン済みの有料会員は他人のレビューを更新できない()
    {
        $review_other = Review::factory()->create(['restaurant_id' => $this->restaurant->id, 'user_id' => User::factory()->create()->id]);

        $response = $this->actingAs($this->user_subscription)->put(route('restaurants.reviews.update', [$this->restaurant, $review_other]), [
            'content' => '更新成功！'
        ]);

        $response->assertRedirect(route('restaurants.reviews.index', $this->restaurant));

        $this->assertDatabaseMissing('reviews', [
            'id' => $this->review->id,
            'content' => '更新成功！'
        ]);
    }
    public function test_ログイン済みの有料会員は自身のレビューを更新できる()
    {
        $response = $this->actingAs($this->user_subscription)->put(route('restaurants.reviews.update', [$this->restaurant, $this->review]), [
            'content' => '更新成功！'
        ]);

        $response->assertRedirect(route('restaurants.reviews.index', $this->restaurant));

        $this->assertDatabaseHas('reviews', [
            'id' => $this->review->id,
            'content' => '更新成功！'
        ]);
    }
    public function test_ログイン済みの管理者はレビューを更新できない()
    {
        $response = $this->actingAs($this->admin, 'admin')->put(route('restaurants.reviews.update', [$this->restaurant, $this->review]), [
            'content' => '更新成功！'
        ]);

        $response->assertRedirect(route('admin.home'));
    }

    // destroyアクション
    public function test_未ログインのユーザーはレビューを削除できない()
    {
        $response = $this->delete(route('restaurants.reviews.destroy', [$this->restaurant, $this->review]));
        $response->assertRedirect(route('login'));
    }
    public function test_ログイン済みの無料会員はレビューを削除できない()
    {
        $response = $this->actingAs($this->user)->delete(route('restaurants.reviews.destroy', [$this->restaurant, $this->review]));
        $response->assertRedirect(route('subscription.create'));
    }
    public function test_ログイン済みの有料会員は他人のレビューを削除できない()
    {
        $review_other = Review::factory()->create(['restaurant_id' => $this->restaurant->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->user_subscription)->delete(route('restaurants.reviews.destroy', [$this->restaurant, $review_other]));

        $response->assertRedirect(route('restaurants.reviews.index', $this->restaurant));

        $this->assertDatabaseHas('reviews', [
            'id' => $review_other->id
        ]);
    }
    public function test_ログイン済みの有料会員は自身のレビューを削除できる()
    {
        $response = $this->actingAs($this->user_subscription)->delete(route('restaurants.reviews.destroy', [$this->restaurant, $this->review]));

        $response->assertRedirect(route('restaurants.reviews.index', $this->restaurant));

        $this->assertDatabaseMissing('reviews', [
            'id' => $this->review->id
        ]);
    }
    public function test_ログイン済みの管理者はレビューを削除できない()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete(route('restaurants.reviews.destroy', [$this->restaurant, $this->review]));
        $response->assertRedirect(route('admin.home'));
    }
}
