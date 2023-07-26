<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Jobs\ConfigureWebhooks;
use App\Services\ShopifyService;
use App\Services\StoreService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class AppController extends Controller
{

    /**
     * 
     */
    private $shopifyService;

    /**
     * 
     */
    private $storeService;

    /**
     * 
     */
    public function __construct(ShopifyService $shopifyService, StoreService $storeService)
    {
        $this->shopifyService = $shopifyService;
        $this->storeService = $storeService;
    }

    /**
     * Three scenarios can happen
     * New installation
     * Re-installation
     * Opening the app
     */

    public function startInstallation(Request $request)
    {
        try {
            $validRequest = $this->shopifyService->validateRequestFromShopify($request->all());
            if ($validRequest) {
                $shop = $request->has('shop'); //Check if shop parameter exists on the request.

                if ($shop) {
                    $storeDetails = $this->storeService->getStoreByDomain($request->shop);
                    if ($storeDetails !== null && $storeDetails !== false) {
                        //store record exists and now determine whether the access token is valid or not
                        //if not then forward them to the re-installation flow
                        //if yes then redirect them to the login page.

                        $validAccessToken = $this->shopifyService->checkIfAccessTokenIsValid($storeDetails);
                        if ($validAccessToken) {
                            //Token is valid for Shopify API calls so redirect them to the login page.

                            /**
                             * Handle whether the app will render in Embed mode
                             */
                            $is_embedded = determineIfAppIsEmbedded();
                            if ($is_embedded) {
                                return redirect()->route('dashboard');
                            }
                        } else {
                            return Redirect::to($this->shopifyService->buildShopifyAuthorizeEndpoint($request->shop));
                        }
                    } else {
                        return Redirect::to($this->shopifyService->buildShopifyAuthorizeEndpoint($request->shop));
                    }
                } else throw new Exception('Shop parameter not present in the request');
            } else throw new Exception('Request is not valid!');
        } catch (Exception $e) {
            Log::info($e->getMessage() . ' ' . $e->getLine());
            dd($e->getMessage() . ' ' . $e->getLine());
        }
    }

    /**
     * 
     */
    public function handleRedirect(Request $request)
    {
        try {
            $validRequest = $this->shopifyService->validateRequestFromShopify($request->all());
            if ($validRequest) {
                if ($request->has('shop') && $request->has('code')) {
                    $shop = $request->shop;
                    $code = $request->code;
                    $accessToken = $this->shopifyService->requestAccessTokenFromShopifyForThisStore($shop, $code);
                    if ($accessToken !== false && $accessToken !== null) {
                        $shopDetails = $this->shopifyService->getShopDetailsFromShopify($shop, $accessToken);
                        $storeDetails = $this->storeService->saveStoreDetailsToDatabase($shopDetails, $accessToken);
                        if ($storeDetails) {
                            //At this point the installation process is complete.
                            $is_embedded = determineIfAppIsEmbedded();
                            if ($is_embedded) {
                                // redirect to dashboard page.
                                return redirect()->route('dashboard');
                            }
                        } else {
                            Log::info('Problem during saving shop details into the db');
                            Log::info($storeDetails);
                            dd('Problem during installation. please check logs.');
                        }
                    } else throw new Exception('Invalid Access Token ' . $accessToken);
                } else throw new Exception('Code / Shop param not present in the URL');
            } else throw new Exception('Request is not valid!');
        } catch (Exception $e) {
            Log::info($e->getMessage() . ' ' . $e->getLine());
            dd($e->getMessage() . ' ' . $e->getLine());
        }
    }
}
