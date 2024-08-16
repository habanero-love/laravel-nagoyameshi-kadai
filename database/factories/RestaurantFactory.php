<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use app\Models\Restaurant;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Restaurant>
 */
class RestaurantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }

    // テストメソッドごとに店舗のダミーデータを作成するパターン
public function test_sample_01()
{
    $restaurant = new Restaurant();
    $restaurant->name = 'テスト';
    $restaurant->description = 'テスト';
    $restaurant->lowest_price = 1000;
    $restaurant->highest_price = 5000;
    $restaurant->postal_code = '0000000';
    $restaurant->address = 'テスト';
    $restaurant->opening_time = '10:00:00';
    $restaurant->closing_time = '20:00:00';
    $restaurant->seating_capacity = 50;
    $restaurant->save();
}

public function test_sample_02()
{
    $restaurant = new Restaurant();
    $restaurant->name = 'テスト';
    $restaurant->description = 'テスト';
    $restaurant->lowest_price = 1000;
    $restaurant->highest_price = 5000;
    $restaurant->postal_code = '0000000';
    $restaurant->address = 'テスト';
    $restaurant->opening_time = '10:00:00';
    $restaurant->closing_time = '20:00:00';
    $restaurant->seating_capacity = 50;
    $restaurant->save();
}

// あらかじめ作成しておいた店舗用のファクトリを利用するパターン
public function test_sample_03()
{
    $restaurant = Restaurant::factory()->create();
}

public function test_sample_04()
{
    $restaurant = Restaurant::factory()->create();
}
}
