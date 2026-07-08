<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'g_number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'total_price',
        'discount_percent',
        'is_supply',
        'is_realization',
        'promo_code_discount',
        'warehouse_name',
        'country_name',
        'oblast_okrug_name',
        'region_name',
        'income_id',
        'sale_id',
        'odid',
        'spp',
        'for_pay',
        'finished_price',
        'price_with_disc',
        'nm_id',
        'subject',
        'category',
        'brand',
        'is_storno',
    ];

    protected $casts = [
        'barcode' => 'integer',
        'discount_percent' => 'integer',
        'is_supply' => 'boolean',
        'is_realization' => 'boolean',
        'income_id' => 'integer',
        'odid' => 'integer',
        'nm_id' => 'integer',
        'date' => 'date',
        'last_change_date' => 'date',
        'total_price' => 'decimal:2',
        'promo_code_discount' => 'decimal:2',
        'spp' => 'decimal:2',
        'for_pay' => 'decimal:2',
        'finished_price' => 'decimal:2',
        'price_with_disc' => 'decimal:2',
        'is_storno' => 'boolean',
    ];
}
