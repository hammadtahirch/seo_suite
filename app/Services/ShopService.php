<?php

namespace App\Services;

use App\Models\Shop;
use Exception;
use Illuminate\Support\Facades\Log;

class ShopService
{
    /**
     * 
     */
    public function saveShopDetailsToDatabase($shopDetails, $accessToken)
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
            $shopObject = Shop::updateOrCreate(['myshopify_domain' => $shopDetails['myshopify_domain']], $payload);
            return $shopObject;
        } catch (Exception $e) {
            Log::info($e->getMessage() . ' ' . $e->getLine());
            return false;
        }
    }

    /**
     * 
     */
    public function getShopByDomain($shop)
    {
        return Shop::where('myshopify_domain', $shop)->first();
    }
}
