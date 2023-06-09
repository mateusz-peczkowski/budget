<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::get('/config', 'ClosestExpensesAndIncomesController@config');
Route::get('/data', 'ClosestExpensesAndIncomesController@data');
Route::post('/change-status-to-paid', 'ClosestExpensesAndIncomesController@changeStatusToPaid');
