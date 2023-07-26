<?php

namespace App\Jobs;

use App\Models\Store;
use App\Services\ShopifyService;
use App\Traits\FunctionTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteWebhooks implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use FunctionTrait;

    private $store_id;
    private $shopifyService;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_id)
    {
        $this->store_id = $store_id;
        $this->shopifyService = app(ShopifyService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $store = Store::where('table_id', $this->store_id)->first();
        $endpoint = getShopifyURLForStore('webhooks.json', $store);
        $headers = getShopifyHeadersForStore($store);
        $response = $this->shopifyService->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
        $webhooks = $response['body']['webhooks'];
        foreach ($webhooks as $webhook) {
            $endpoint = getShopifyURLForStore('webhooks/' . $webhook['id'] . '.json', $store);
            $headers = getShopifyHeadersForStore($store);
            $response = $this->shopifyService->makeAnAPICallToShopify('DELETE', $endpoint, null, $headers);
            Log::info('Response for deleting webhooks');
            Log::info($response);
        }
    }
}
