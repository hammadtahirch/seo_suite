<?php

return [
    'shopify_api_key' => env('SHOPIFY_API_KEY'),
    'shopify_api_secret' => env('SHOPIFY_API_SECRET'),
    'shopify_api_version' => env('SHOPIFY_API_VERSION'),
    'app_embedded' => env('APP_EMBEDDED', false),
    'api_scopes' => [
        'write_orders',
        'write_fulfillments',
        'write_customers',
        'write_products',
        'read_third_party_fulfillment_orders',
        'write_third_party_fulfillment_orders',
        'write_assigned_fulfillment_orders',
        'read_assigned_fulfillment_orders',
        'read_merchant_managed_fulfillment_orders',
        'write_merchant_managed_fulfillment_orders'
    ],
    'webhook_events' => [
        'orders/create' => 'order/created', //When the shop receives an order
        'orders/updated' => 'order/updated', //When an order is updated
        'products/create' => 'product/created', //When products are created
        'app/uninstalled' => 'app/uninstall', //To know when the app has been removed. 
        'shop/update' => 'shop/updated', //To keep latest data in the shops table
    ],
    'default_permissions' => [
        'write-products', 'read-products',
        'write-orders', 'read-orders',
        'write-customers', 'read-customers',
        'write-members', 'read-members'
    ]
];
