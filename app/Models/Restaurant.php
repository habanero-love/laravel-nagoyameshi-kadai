<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Restaurant extends Model
{
    use HasFactory,Sortable;

    protected $fillable = [
        'name',
        'image',
        'description',
        'lowest_price',
        'highest_price',
        'postal_code',
        'address',
        'opening_time',
        'closing_time',
        'seating_capacity',
    ];

    // リレーションを設定
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_restaurant')->withTimestamps();
    }
    public function regular_holidays()
    {
        return $this->belongsToMany(RegularHoliday::class, 'regular_holiday_restaurant')->withTimestamps();
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    public function favorite_users()
    {
        return $this->belongsToMany(User::class, 'restaurant_user')->withTimestamps();
    }

    // 独自の並べ替え機能を設定（リレーション先のカラム使用のため）
    public function ratingSortable($query, $direction) {
        return $query->withAvg('reviews', 'score')->orderBy('reviews_avg_score', $direction);
    }
    
    public function popularSortable($query) {
        return $query->withCount('reservations')->orderBy('reservations_count', 'desc');
    }
}
