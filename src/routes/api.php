<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemController;

Route::get('/items', [ItemController::class, 'index']);