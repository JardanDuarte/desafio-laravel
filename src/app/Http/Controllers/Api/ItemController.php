<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Item::query()
            ->orderBy('ml_created', 'desc')
            ->get();

        return response()->json([
            'data' => $items,
            'total' => $items->count(),
        ]);
    }
}