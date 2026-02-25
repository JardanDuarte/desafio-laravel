<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\TokenValidator;
use Exception;

class ItemService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.meli.base_url');
    }

    public function getItemDetails(string $id, ?string $token): ?array
    {
        if (!app(TokenValidator::class)->validate($token)) {
            echo "[WARNING] Token nulo ou inválido.\n";
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->get("{$this->baseUrl}/mercadolibre/items/{$id}");

            if ($response->status() === 401) {
                Log::error("Não autorizado ao buscar o item {$id}");
                echo "[ERROR] Não autorizado ao buscar o item {$id}\n";
                return null;
            }

            if ($response->status() === 404) {
                Log::warning("Item {$id} não encontrado (404)");
                echo "[WARNING] Item {$id} não encontrado (404)\n";
                return null;
            }

            if (!$response->successful()) {
                Log::error("Erro inesperado ao buscar o item {$id}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                echo "[ERROR] Erro inesperado ao buscar o item {$id} verifique o log para mais detalhes\n";
                return null;
            }

            $data = $response->json();

            return [
                'id' => $data['id'],
                'title' => $data['title'],
                'status' => $data['status'],
                'ml_created' => $data['created'],
                'ml_updated' => $data['updated'],
            ];

        } catch (Exception $e) {
            Log::error("Exceção ao buscar item {$id}", [
                'message' => $e->getMessage(),
            ]);

            echo "[ERROR] Exceção ao buscar item {$id} verifique o log para mais detalhes\n";

            return null;
        }
    }
}