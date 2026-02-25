<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\TokenValidator;

class SearchService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.meli.base_url');
    }

    public function getAllItemIds(?string $token): array
    {
        if (!app(TokenValidator::class)->validate($token)) {
            echo "[WARNING] Token nulo ou inválido.\n";
            return [];
        }

        $allIds = [];

        for ($offset = 0; $offset <= 25; $offset += 5) {
            $response = Http::withToken($token)
            ->get("{$this->baseUrl}/mercadolibre/sites/MLB/search", [
                'seller_id' => 252254392,
                'offset' => $offset,
                'limit' => 5
            ]);
            
            if (!$response->successful()) {
                Log::error('Erro ao buscar os anúncios na API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                echo "[ERROR] Erro ao buscar os anúncios na API verifique o log para mais detalhes.\n";
                continue;
            }

            $data = $response->json();
            $allIds = array_merge($allIds, $data['results'] ?? []);
        }

        return $allIds;
    }
}