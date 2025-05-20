<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Http\Controllers\Helpers\ResponseFormatterController;

class OrderController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::query()
            ->latest()
            ->limit(40)
            ->get();

        return ResponseFormatterController::success($transaksi, 'Data Seluruh Transaksi Berhasil Diambil');
    }
}
