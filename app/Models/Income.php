<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'income_id',
        'number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'quantity',
        'total_price',
        'date_close',
        'warehouse_name',
        'nm_id',
    ];

    protected $casts = [
        'income_id' => 'integer',
        'barcode' => 'integer',
        'quantity' => 'integer',
        'total_price' => 'decimal:2',
        'date' => 'date',
        'last_change_date' => 'date',
        'date_close' => 'date',
        'nm_id' => 'integer',
    ];
}
