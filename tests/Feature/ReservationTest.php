<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Restaurant;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin, $user, $user_subscription, $restaurant, $reservation;

    protected function setUp(): void
    {
        parent::setUp();

        // 一般ユーザーを生成
        $this->user = User::factory()->create();

        // 有料会員を生成
        $this->user_subscription = User::factory()->create();
        $this->user_subscription->newSubscription('premium_plan', 'price_1PuVTqA0Has0zR6r4lRJ6FoQ')->create('pm_card_visa');

        // 管理者ユーザーを生成
        $this->seed(\Database\Seeders\AdminSeeder::class);
        $this->admin = Admin::where('email', 'admin@example.com')->first();

        // レストランを生成
        $this->restaurant = Restaurant::factory()->create();

        // 予約を生成
        $this->reservation = Reservation::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->user_subscription->id,
        ]);
    }

    // Indexアクションのテスト
    public function test_未ログインユーザーは会員側の予約一覧ページにアクセスできない()
    {
        $response = $this->get(route('reservations.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員は会員側の予約一覧ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('reservations.index'));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_ログイン済みの有料会員は会員側の予約一覧ページにアクセスできる()
    {
        $response = $this->actingAs($this->user_subscription)->get(route('reservations.index'));
        $response->assertStatus(200);
    }

    public function test_管理者は会員側の予約一覧ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('reservations.index'));
        $response->assertRedirect(route('admin.home'));
    }

    // Createアクションのテスト
    public function test_未ログインユーザーは予約ページにアクセスできない()
    {
        $response = $this->get(route('restaurants.reservations.create', [$this->restaurant, $this->reservation]));
        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員は予約ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('restaurants.reservations.create', [$this->restaurant, $this->reservation]));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_ログイン済みの有料会員は予約ページにアクセスできる()
    {
        $response = $this->actingAs($this->user_subscription)->get(route('restaurants.reservations.create', [$this->restaurant, $this->reservation]));
        $response->assertStatus(200);
    }

    public function test_管理者は予約ページにアクセスできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('restaurants.reservations.create', [$this->restaurant, $this->reservation]));
        $response->assertRedirect(route('admin.home'));
    }

    // Storeアクションのテスト
    public function test_未ログインユーザーは予約できない()
    {
        $response = $this->post(route('restaurants.reservations.store', [$this->restaurant, $this->reservation]), $this->reservation->toArray());
        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員は予約できない()
    {
        $response = $this->actingAs($this->user)->post(route('restaurants.reservations.store', [$this->restaurant, $this->reservation]), $this->reservation->toArray());
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_ログイン済みの有料会員は予約できる()
    {
        $response = $this->actingAs($this->user_subscription)->post(route('restaurants.reservations.store', [$this->restaurant, $this->reservation]), array_merge($this->reservation->toArray(), [
            'reservation_date' => '2024-09-10',
            'reservation_time' => '18:00',
        ]));
        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->user_subscription->id,
        ]);
    }

    public function test_管理者は予約できない()
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('restaurants.reservations.store', [$this->restaurant, $this->reservation]), $this->reservation->toArray());
        $response->assertRedirect(route('admin.home'));
    }

    // Destroyアクションのテスト
    public function test_未ログインユーザーは予約をキャンセルできない()
    {
        $response = $this->delete(route('reservations.destroy', $this->reservation));
        $response->assertRedirect(route('login'));
    }

    public function test_ログイン済みの無料会員は予約をキャンセルできない()
    {
        $response = $this->actingAs($this->user)->delete(route('reservations.destroy', $this->reservation));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_ログイン済みの有料会員は他人の予約をキャンセルできない()
    {
        $reservation_other = Reservation::factory()->create(['restaurant_id' => $this->restaurant->id, 'user_id' => User::factory()->create()->id]);

        $response = $this->actingAs($this->user_subscription)->delete(route('reservations.destroy', $reservation_other));
        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('reservations', ['id' => $reservation_other->id]);
    }

    public function test_ログイン済みの有料会員は自身の予約をキャンセルできる()
    {
        $response = $this->actingAs($this->user_subscription)->delete(route('reservations.destroy', $this->reservation));
        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseMissing('reservations', ['id' => $this->reservation->id]);
    }

    public function test_管理者は予約をキャンセルできない()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete(route('reservations.destroy', $this->reservation));
        $response->assertRedirect(route('admin.home'));
    }
}
