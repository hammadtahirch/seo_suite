<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Services\ShopifyService;
use App\Services\ShopService;
use App\Traits\FunctionTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConfigureWebhooks implements ShouldQueue
{

    /**
     * traits
     */
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 
     */
    private $shop;

    /**
     * 
     */
    private $shopifyService;

    /**
     * 
     */
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
        try {
            $shopObject = $this->shopService->getShopByDomain($this->shop);

            $endpoint = getShopifyURLForShop('webhooks.json', $shopObject);
            $headers = getShopifyHeadersForShop($shopObject);
            $webhooks_config = config('shopify.webhook_events');
            foreach ($webhooks_config as $topic => $url) {
                $body = [
                    'webhook' => [
                        'topic' => $topic,
                        'address' => config('app.url') . 'webhook/' . $url,
                        'format' => 'json'
                    ]
                ];
                $response = $this->shopifyService->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $body);
                Log::info('Response for topic ' . $topic);
                Log::info($response['body']);
                //You can write a logic to save this in the database table.
            }
        } catch (Exception $e) {
            Log::info('here in configure webhooks ' . $e->getMessage() . ' ' . $e->getLine());
        }
    }
}
