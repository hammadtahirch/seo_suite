<?php

namespace App\Services;

use App\Models\Store;
use Exception;
use Illuminate\Support\Facades\Log;

class StoreService
{
    /**
     * 
     */
    public function saveStoreDetailsToDatabase($shopDetails, $accessToken)
    {
        try {
            $payload = [
                'access_token' => $accessToken,
                'myshopify_domain' => $shopDetails['myshopify_domain'],
                'id' => $shopDetails['id'],
                'email' => $shopDetails['email'],
                'name' => $shopDetails['name'],
                'phone' => $shopDetails['phone'],
                'address1' => $shopDetails['address1'],
                'address2' => $shopDetails['address2'],
                'zip' => $shopDetails['zip']
            ];
            $store_db = Store::updateOrCreate(['myshopify_domain' => $shopDetails['myshopify_domain']], $payload);
            return $store_db;
        } catch (Exception $e) {
            Log::info($e->getMessage() . ' ' . $e->getLine());
            return false;
        }
    }

    /**
     * 
     */
    public function getStoreByDomain($shop)
    {
        return Store::where('myshopify_domain', $shop)->first();
    }
}
