<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Services\ShopifyService;
use App\Services\ShopService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteWebhooks implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $shop;
    private $shopifyService;
    private $shopService;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shop)
    {
        $this->shop = $shop;
        $this->shopifyService = app(ShopifyService::class);
        $this->shopService = app(ShopService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shop = $this->shopService->getShopByDomain($this->shop);
        $endpoint = getShopifyURLForShop('webhooks.json', $shop);
        $headers = getShopifyHeadersForShop($shop);
        $response = $this->shopifyService->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
        $webhooks = $response['body']['webhooks'];
        foreach ($webhooks as $webhook) {
            $endpoint = getShopifyURLForShop('webhooks/' . $webhook['id'] . '.json', $shop);
            $headers = getShopifyHeadersForShop($shop);
            $response = $this->shopifyService->makeAnAPICallToShopify('DELETE', $endpoint, null, $headers);
            Log::info('Response for deleting webhooks');
            Log::info($response);
        }
    }
}
