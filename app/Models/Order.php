<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
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
        'warehouse_name',
        'oblast',
        'income_id',
        'odid',
        'nm_id',
        'subject',
        'category',
        'brand',
        'is_cancel',
        'cancel_dt',
    ];

    protected $casts = [
        'barcode' => 'integer',
        'discount_percent' => 'integer',
        'income_id' => 'integer',
        'odid' => 'integer',
        'nm_id' => 'integer',
        'is_cancel' => 'boolean',
        'date' => 'datetime',
        'last_change_date' => 'date',
        'total_price' => 'decimal:2',
        'cancel_dt' => 'datetime',
    ];
}
