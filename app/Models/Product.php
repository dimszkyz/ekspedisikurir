<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function getActivePriceAttribute()
    {
        return $this->sale_price && $this->sale_price < $this->regular_price
            ? $this->sale_price
            : $this->regular_price;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->regular_price && $this->sale_price && $this->regular_price > $this->sale_price) {
            return round((($this->regular_price - $this->sale_price) / $this->regular_price) * 100);
        }

        return 0;
    }
}
