<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

/**
 * Shop basket API routes.
 * All routes start with /api/shop/basket.
 */
// Only for SE sorry.
Route::middleware('support:se')->group(function (): void {
	Route::get('/Shop', [Shop\CatalogController::class, 'getAlbumCatalog']);
	Route::group(['prefix' => '/Shop/Order'], function (): void {
		Route::get('/List', [Shop\OrderController::class, 'list']);
		Route::get('/{order_id}', [Shop\OrderController::class, 'get']);
		Route::post('/{order_id}', [Shop\OrderController::class, 'markAsPaid']);
		Route::put('/{order_id}', [Shop\OrderController::class, 'markAsDelivered']);
		Route::delete('/', [Shop\OrderController::class, 'forget']);
	});
	Route::group(['prefix' => '/Shop/Basket'], function (): void {
		Route::get('/', [Shop\BasketController::class, 'get']);
		Route::post('/Photo', [Shop\BasketController::class, 'addPhoto']);
		Route::post('/Album', [Shop\BasketController::class, 'addAlbum']);
		Route::delete('/item', [Shop\BasketController::class, 'removeItem']);
		Route::delete('/', [Shop\BasketController::class, 'delete']);
	});
	Route::group(['prefix' => '/Shop/Checkout'], function (): void {
		Route::get('/Options', [Shop\CheckoutController::class, 'options']);
		Route::post('/Offline', [Shop\CheckoutController::class, 'offline']);
		Route::post('/Create-session', [Shop\CheckoutController::class, 'createSession']);
		Route::post('/Process', [Shop\CheckoutController::class, 'process']);
		Route::get('/Finalize/{provider}/{transaction_id}', [Shop\CheckoutController::class, 'finalize'])->withoutMiddleware(['content_type:json', 'accept_content_type:json'])->name('shop.checkout.return');
		Route::get('/Cancel/{transaction_id}', [Shop\CheckoutController::class, 'cancel'])->name('shop.checkout.cancel');
	});
	Route::group(['prefix' => '/Shop/Management'], function (): void {
		Route::get('/Options', [Admin\ShopManagementController::class, 'options']);
		Route::get('/List', [Admin\ShopManagementController::class, 'list']);
		Route::post('/Purchasable/Photo', [Admin\ShopManagementController::class, 'setPhotoPurchasable']);
		Route::post('/Purchasable/Album', [Admin\ShopManagementController::class, 'setAlbumPurchasable']);
		Route::put('/Purchasable/Price', [Admin\ShopManagementController::class, 'updatePurchasablePrices']);
		Route::delete('/Purchasables', [Admin\ShopManagementController::class, 'deletePurchasables']);
	});
});