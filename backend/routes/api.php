// Store routes
Route::prefix('user/store')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/items', 'App\Http\Controllers\API\User\StoreController@getItems');
    Route::post('/purchase', 'App\Http\Controllers\API\User\StoreController@purchase');
    Route::get('/history', 'App\Http\Controllers\API\User\StoreController@getPurchaseHistory');
}); 