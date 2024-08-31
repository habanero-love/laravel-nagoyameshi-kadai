<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    // 中間テーブルとのリレーションを設定
    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'category_restaurant')->withTimestamps();
    }
}
