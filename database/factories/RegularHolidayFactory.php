<?php

namespace Database\Factories;

use App\Models\RegularHoliday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegularHoliday>
 */
class RegularHolidayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = RegularHoliday::class;

    public function definition(): array
    {
        return [
            'day' => 'テスト',
        ];
    }
}
