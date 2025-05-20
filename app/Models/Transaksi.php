<?php

namespace App\Models;

use App\Enums\Transaksi\StatusEnum;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $casts = [
        'status' => StatusEnum::class,
    ];
}
