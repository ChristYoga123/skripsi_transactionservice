<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Transaksi;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Helpers\ResponseFormatterController;

class TransactionController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::query()
            ->latest()
            ->get();

        return ResponseFormatterController::success($transaksi, 'Data Seluruh Transaksi Berhasil Diambil');
    }
    
    public function daftar($slug)
    {   
        $kursus = Http::kursus()->get("/kelas/{$slug}");
        $user = Http::user()->get('/auth/me');

        if($kursus->notFound())
        {
            return ResponseFormatterController::error('Kursus tidak ditemukan', 404);
        }

        if($kursus->json()['data']['tipe'] === 'premium')
        {
            return ResponseFormatterController::error('Kursus ini adalah kursus premium, silahkan beli kursus terlebih dahulu', 400);
        }

        // Validasi checkout
        [$hasil, $errorMessage] = $this->checkoutValidation($kursus->json()['data']);
        if(!$hasil)
        {
            return ResponseFormatterController::error($errorMessage);
        }

        DB::beginTransaction();
        try
        {
            Transaksi::create([
                'order_id' => 'LCRS-' . Str::random(6),
                'student_id' => $user->json()['data']['id'],
                'kursus_id' => $kursus->json()['data']['id'],
                'status' => 'success',
                'total_harga' => 0,
            ]);

            Http::kursus()->withToken(request()->bearerToken())->post('/kelas/' . $kursus->json()['data']['slug'] . '/enroll');

            DB::commit();
            return ResponseFormatterController::success(null, 'Berhasil mendaftar kursus');
        }catch(Exception $e)
        {
            DB::rollBack();
            Log::error('Daftar error: ' . $e->getMessage());
            return ResponseFormatterController::error('Terjadi kesalahan saat mendaftar kursus' . $e->getMessage(), 500);
        }
    }

    /**
     * Validasi checkout/daftar untuk kursus
     * 
     * @param Kursus $kursus
     * @return bool true jika validasi berhasil, false jika gagal
     */
    private function checkoutValidation($kursus)
    {
        $errors = [
            'admin' => 'Admin tidak bisa membeli course',
            'creator' => 'Anda tidak bisa membeli course yang anda buat sendiri',
            'registered' => 'Anda sudah terdaftar di course ini. Silahkan masuk ke dashboard untuk mempelajari course ini',
        ];
        
        $hasil = true; 
        $errorMessage = '';
        
        // Periksa kondisi error
        if ($this->checkIsAdmin()) {
            $hasil = false;
            $errorMessage = $errors['admin'];
        } elseif ($this->checkIfCourseIsCreatedByUser($kursus)) {
            $hasil = false;
            $errorMessage = $errors['creator'];
        } elseif ($this->checkIsRegistered($kursus)) {
            $hasil = false;
            $errorMessage = $errors['registered'];
        }
        
        return [$hasil, $errorMessage];
    }

    private function checkIfCourseIsCreatedByUser($kursus)
    {
        $isCreator = Http::kursus()
            ->get('/kelas/' . $kursus['slug'] . '/is-created-by-user')
            ->json()['data'];
        
        if($isCreator['is_mentor'] === true)
        {
            return ResponseFormatterController::error('Anda tidak bisa membeli course yang anda buat sendiri', 400);
        }
    }

    private function checkIsRegistered($kursus)
    {
        $isEnrolled = Http::kursus()
            ->get('/kelas/' . $kursus['slug'] . '/is-joined-by-user')
            ->json()['data'];
        
        if($isEnrolled['is_enrolled'] === true)
        {
            return ResponseFormatterController::error('Anda sudah terdaftar di course ini. Silahkan masuk ke dashboard untuk mempelajari course ini', 400);
        }
    }

    private function checkIsAdmin()
    {
        $isAdmin = Http::user()
            ->get('/auth/is-admin')
            ->json()['data'];
        
        if($isAdmin['is_admin'] === true)
        {
            return ResponseFormatterController::error('Admin tidak bisa membeli course', 400);
        }
    }
}
