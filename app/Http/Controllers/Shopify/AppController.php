<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Jobs\ConfigureWebhooks;
use App\Services\ShopifyService;
use App\Services\ShopService;
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
    private $shopService;

    /**
     * 
     */
    public function __construct(ShopifyService $shopifyService, ShopService $shopService)
    {
        $this->shopifyService = $shopifyService;
        $this->shopService = $shopService;
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
                    $shopDetails = $this->shopService->getShopByDomain($request->shop);
                    if ($shopDetails !== null && $shopDetails !== false) {
                        //shop record exists and now determine whether the access token is valid or not
                        //if not then forward them to the re-installation flow
                        //if yes then redirect them to the login page.

                        $validAccessToken = $this->shopifyService->checkIfAccessTokenIsValid($shopDetails);

                        if ($validAccessToken) {
                            //Token is valid for Shopify API calls so redirect them to the login page.

                            /**
                             * Handle whether the app will render in Embed mode
                             */
                            $is_embedded = determineIfAppIsEmbedded();
                            if ($is_embedded) {
                                return Redirect::to('https://' . $request->shop . '/admin/apps/seo_suite/dashboard');
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
                    $accessToken = $this->shopifyService->requestAccessTokenFromShopifyForThisShop($shop, $code);
                    if ($accessToken !== false && $accessToken !== null) {
                        $shopDetails = $this->shopifyService->getShopDetailsFromShopify($shop, $accessToken);
                        $shopDetails = $this->shopService->saveShopDetailsToDatabase($shopDetails, $accessToken);
                        if ($shopDetails) {
                            ConfigureWebhooks::dispatch($shop);
                            //At this point the installation process is complete.
                            $is_embedded = determineIfAppIsEmbedded();
                            if ($is_embedded) {
                                // redirect to dashboard page.
                                return Redirect::to('https://' . $request->shop . '/admin/apps/seo_suite/dashboard');
                            }
                        } else {
                            Log::info('Problem during saving shop details into the db');
                            Log::info($shopDetails);
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
