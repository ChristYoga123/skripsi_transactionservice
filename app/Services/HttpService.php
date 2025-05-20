<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HttpService
{
    private static $instance = null;
    private $userServiceUrl;
    private $kursusServiceUrl;
    private $token;

    private function __construct()
    {
        $this->userServiceUrl = env('USER_SERVICE_URL');
        $this->kursusServiceUrl = env('KURSUS_SERVICE_URL');
        $this->token = request()->bearerToken();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getUserService()
    {
        return Http::withToken($this->token)->baseUrl($this->userServiceUrl);
    }

    public function getKursusService()
    {
        return Http::withToken($this->token)->baseUrl($this->kursusServiceUrl);
    }
} 