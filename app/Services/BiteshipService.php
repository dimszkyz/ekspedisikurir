<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BiteshipService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.biteship.base_url');
        $this->apiKey = config('services.biteship.api_key');
    }

    protected function client()
    {
        return Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json',
        ]);
    }

    public function getCourierRates($destination, $items)
    {
        return $this->client()->post("{$this->baseUrl}/rates/couriers", [
            "origin_area_id" => "ID-JKT-1", // contoh: Jakarta Pusat
            "destination_area_id" => $destination,
            "couriers" => "jne:jnt:sicepat:anteraja",
            "items" => $items
        ])->json();
    }

    public function createOrder($payload)
    {
        return $this->client()->post("{$this->baseUrl}/orders", $payload)->json();
    }
}
