<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Jobs\ConfigureWebhooks;
use App\Jobs\DeleteWebhooks;
use App\Services\ShopifyService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhooksController extends Controller
{

    /**
     * 
     */
    private $shopifyService;

    /**
     * 
     */
    public function __construct(ShopifyService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    /**
     * 
     */
    public function configureWebhooks($shop)
    {
        try {
            ConfigureWebhooks::dispatchNow($shop);
            print_r('Done');
        } catch (Exception $e) {
            Log::info($e->getMessage() . ' ' . $e->getLine());
        }
    }

    /**
     * 
     */
    public function deleteWebhooks($shop)
    {
        try {
            DeleteWebhooks::dispatch($shop);
            print_r('Done');
        } catch (Exception $e) {
            Log::info($e->getMessage() . ' ' . $e->getLine());
        }
    }

    /**
     * 
     */
    public function orderCreated(Request $request)
    {
        Log::info('Received webhook for event order created');
        Log::info($request->all());
        return response()->json(['status' => true], 200);
    }

    /**
     * 
     */
    public function orderUpdated(Request $request)
    {
        Log::info('Received webhook for event order updated');
        Log::info($request->all());
        return response()->json(['status' => true], 200);
    }

    public function productCreated(Request $request)
    {
        Log::info('Received webhook for event product created');
        Log::info($request->all());
        return response()->json(['status' => true], 200);
    }

    /**
     * 
     */
    public function appUninstalled(Request $request)
    {
        Log::info('Received webhook for event app removed');
        Log::info($request->all());
        return response()->json(['status' => true], 200);
    }

    /**
     * 
     */
    public function shopUpdated(Request $request)
    {
        Log::info('Received webhook for event shop updated');
        Log::info($request->all());
        return response()->json(['status' => true], 200);
    }

    /**
     * 
     */
    public function returnCustomerData(Request $request)
    {
        try {
            $validRequest = $this->shopifyService->validateRequestFromShopify($request->all());
            $response = $validRequest ?
                ['status' => true, 'message' => 'Not Found', 'code' => 200] :
                ['status' => false, 'message' => 'Unauthorized!', 'code' => 401];
        } catch (Exception $e) {
            $response = ['status' => false, 'message' => 'Unauthorized!', 'code' => 401];
        }
        return response()->json($response, $response['code']);
    }

    /**
     * 
     */
    public function deleteCustomerData(Request $request)
    {
        try {
            $validRequest = $this->shopifyService->validateRequestFromShopify($request->all());
            $response = $validRequest ?
                ['status' => true, 'message' => 'Not Found', 'code' => 200] :
                ['status' => false, 'message' => 'Unauthorized!', 'code' => 401];
        } catch (Exception $e) {
            $response = ['status' => false, 'message' => 'Unauthorized!', 'code' => 401];
        }
        return response()->json($response, $response['code']);
    }

    /**
     * 
     */
    public function deleteShopData(Request $request)
    {
        try {
            $validRequest = $this->shopifyService->validateRequestFromShopify($request->all());
            $response = $validRequest ?
                ['status' => true, 'message' => 'Success', 'code' => 200] :
                ['status' => false, 'message' => 'Unauthorized!', 'code' => 401];
        } catch (Exception $e) {
            $response = ['status' => false, 'message' => 'Unauthorized!', 'code' => 401];
        }
        return response()->json($response, $response['code']);
    }
}
