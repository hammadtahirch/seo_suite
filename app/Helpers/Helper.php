<?php

/**
 * 
 */
function getShopifyURLForShop($endpoint, $shop)
{
    return checkIfShopIsPrivate($shop) ?
        'https://' . $shop['api_key'] . ':' . $shop['api_secret_key'] . '@' . $shop['myshopify_domain'] . '/admin/api/' . config('shopify.shopify_api_version') . '/' . $endpoint
        :
        'https://' . $shop['myshopify_domain'] . '/admin/api/' . config('shopify.shopify_api_version') . '/' . $endpoint;
}

/**
 * 
 */
function getShopifyHeadersForShop($shop, $method = 'GET')
{
    return $method == 'GET' ? [
        'Content-Type' => 'application/json',
        'X-Shopify-Access-Token' => $shop['access_token']
    ] : [
        'Content-Type: application/json',
        'X-Shopify-Access-Token: ' . $shop['access_token']
    ];
}

/**
 * 
 */
function getGraphQLHeadersForShop($shop)
{
    return checkIfShopIsPrivate($shop) ? [
        'Content-Type' => 'application/json',
        'X-Shopify-Access-Token' => $shop['api_secret_key'],
        'X-GraphQL-Cost-Include-Fields' => true
    ] : [
        'Content-Type' => 'application/json',
        'X-Shopify-Access-Token' => $shop['access_token'],
        'X-GraphQL-Cost-Include-Fields' => true
    ];
}

/**
 * 
 */
function checkIfShopIsPrivate($shop)
{
    return isset($shop['api_key']) && isset($shop['api_secret_key'])
        && $shop['api_key'] !== null && $shop['api_secret_key'] !== null
        && strlen($shop['api_key']) > 0 && strlen($shop['api_secret_key']) > 0;
}

/**
 * 
 */
function determineIfAppIsEmbedded()
{
    return config('shopify.app_embedded') == 'true' || config('shopify.app_embedded') == true;
}
