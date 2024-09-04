<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Billable;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory, Billable;

    protected $fillable = [
        'score',
        'content',
        'restaurant_id',
        'user_id',
    ];

    // リレーションを設定
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
