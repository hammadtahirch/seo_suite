<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shopify\AppController;
use App\Http\Controllers\Shopify\DashboardController;
use App\Http\Controllers\Shopify\WebhooksController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// /shopify/auth
Route::prefix('shopify/auth')->group(function () {
    Route::get('/', [AppController::class, 'startInstallation']);
    Route::get('redirect', [AppController::class, 'handleRedirect'])->name('app_install_redirect');
    Route::get('complete', [AppController::class, 'completeInstallation'])->name('app_install_complete');
});



Route::prefix('webhook')->group(function () {
    Route::any('order/created', [WebhooksController::class, 'orderCreated']);
    Route::any('order/updated', [WebhooksController::class, 'orderUpdated']);
    Route::any('product/created', [WebhooksController::class, 'productCreated']);
    Route::any('app/uninstall', [WebhooksController::class, 'appUninstalled']);
    Route::any('shop/updated', [WebhooksController::class, 'shopUpdated']);
});
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
//Testing scripts
Route::get('configure/webhooks/{id}', [WebhooksController::class, 'configureWebhooks']);
Route::get('delete/webhooks/{id}', [WebhooksController::class, 'deleteWebhooks']);
