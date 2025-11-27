<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('catalog', [App\Http\Controllers\Api\CatalogController::class, 'index']);
    Route::get('books/{id}', [App\Http\Controllers\Api\CatalogController::class, 'show']);

    // Protected loan routes: require API token and appropriate role
    Route::post('loans/checkout', [App\Http\Controllers\Api\LoansController::class, 'checkout'])
        ->middleware([\App\Http\Middleware\ApiTokenAuth::class, \App\Http\Middleware\EnsureRole::class . ':patron']);

    Route::post('loans/{id}/return', [App\Http\Controllers\Api\LoansController::class, 'return'])
        ->middleware([\App\Http\Middleware\ApiTokenAuth::class, \App\Http\Middleware\EnsureRole::class . ':patron']);

    Route::post('loans/{id}/renew', [App\Http\Controllers\Api\LoansController::class, 'renew'])
        ->middleware([\App\Http\Middleware\ApiTokenAuth::class, \App\Http\Middleware\EnsureRole::class . ':patron']);

    Route::get('users/{user_id}/loans', [App\Http\Controllers\Api\LoansController::class, 'userLoans'])
        ->middleware([\App\Http\Middleware\ApiTokenAuth::class, \App\Http\Middleware\EnsureRole::class . ':patron']);
});
