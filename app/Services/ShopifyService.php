<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    /**
     * 
     */
    private $api_scopes, $api_key, $api_secret;
    /**
     * 
     */
    public function __construct()
    {
        $this->api_scopes = implode(',', config('shopify.api_scopes'));
        $this->api_key = config('shopify.shopify_api_key');
        $this->api_secret = config('shopify.shopify_api_secret');
    }
    /**
     * Content-Type: application/json
     * X-Shopify-Access-Token: value
     */
    public function makeAnAPICallToShopify($method, $endpoint, $url_params = null, $headers, $requestBody = null)
    {
        try {
            $client = new Client();
            $response = null;
            if ($method == 'GET' || $method == 'DELETE') {
                $response = $client->request($method, $endpoint, ['headers' => $headers]);
            } else {
                $response = $client->request($method, $endpoint, ['headers' => $headers, 'json' => $requestBody]);
            }
            return [
                'statusCode' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), true)
            ];
        } catch (Exception $e) {
            return [
                'statusCode' => $e->getCode(),
                'message' => $e->getMessage(),
                'body' => null
            ];
        }
    }

    /**
     * 
     */
    public function makeAPOSTCallToShopify($payload, $endpoint, $headers = NULL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers === NULL ? [] : $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $aHeaderInfo = curl_getinfo($ch);
        $curlHeaderSize = $aHeaderInfo['header_size'];
        $sBody = trim(mb_substr($result, $curlHeaderSize));

        return ['statusCode' => $httpCode, 'body' => $sBody];
    }

    /**
     * 
     */
    public function validateRequestFromShopify($request)
    {
        try {
            $arr = [];
            $hmac = $request['hmac'];
            unset($request['hmac']);
            foreach ($request as $key => $value) {
                $key = str_replace("%", "%25", $key);
                $key = str_replace("&", "%26", $key);
                $key = str_replace("=", "%3D", $key);
                $value = str_replace("%", "%25", $value);
                $value = str_replace("&", "%26", $value);
                $arr[] = $key . "=" . $value;
            }
            $str = implode('&', $arr);
            $ver_hmac =  hash_hmac('sha256', $str, config('shopify.shopify_api_secret'), false);

            return $ver_hmac === $hmac;
        } catch (Exception $e) {
            dd($e->getMessage());
            Log::info('Problem with verify hmac from request');
            Log::info($e->getMessage() . ' ' . $e->getLine());
            return false;
        }
    }

    /**
     * 
     */
    public function getShopDetailsFromShopify($shop, $accessToken)
    {
        try {
            $endpoint = getShopifyURLForStore('shop.json', ['myshopify_domain' => $shop]);
            $headers = getShopifyHeadersForStore(['access_token' => $accessToken]);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
            if ($response['statusCode'] == 200) {
                $body = $response['body'];
                Log::info($body);
                if (!is_array($body)) $body = json_decode($body, true);
                return $body['shop'] ?? null;
            } else {
                Log::info('Response recieved for shop details');
                Log::info($response);
                return null;
            }
        } catch (Exception $e) {
            Log::info('Problem getting the shop details from shopify');
            Log::info($e->getMessage() . ' ' . $e->getLine());
            return null;
        }
    }
    /**
     * 
     */
    public function requestAccessTokenFromShopifyForThisStore($shop, $code)
    {
        try {
            $endpoint = 'https://' . $shop . '/admin/oauth/access_token';
            $headers = ['Content-Type: application/json'];
            $requestBody = json_encode([
                'client_id' => $this->api_key,
                'client_secret' => $this->api_secret,
                'code' => $code
            ]);
            $response = $this->makeAPOSTCallToShopify($requestBody, $endpoint, $headers);
            if ($response['statusCode'] == 200) {
                $body = $response['body'];
                if (!is_array($body)) $body = json_decode($body, true);
                if (is_array($body) && isset($body['access_token']) && $body['access_token'] !== null)
                    return $body['access_token'];
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Write some code here that will use the Guzzle library to fetch the shop object from Shopify API
     * If it succeeds with 200 status then that means its valid and we can return true;        
     */

    public function checkIfAccessTokenIsValid($storeDetails)
    {
        try {
            if ($storeDetails !== null && isset($storeDetails->access_token) && strlen($storeDetails->access_token) > 0) {
                $token = $storeDetails->access_token;
                $endpoint = getShopifyURLForStore('shop.json', $storeDetails);
                $headers = getShopifyHeadersForStore($storeDetails);
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers, null);
                return $response['statusCode'] === 200;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function buildShopifyAuthorizeEndpoint($shop): string
    {
        return 'https://' . $shop .
            '/admin/oauth/authorize?client_id=' . $this->api_key .
            '&scope=' . $this->api_scopes .
            '&redirect_uri=' . route('app_install_redirect');
    }
}
