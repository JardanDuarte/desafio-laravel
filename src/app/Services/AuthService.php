<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.meli.base_url');
    }

    public function getAccessToken(): ?string
    {
        try {
            $response = Http::get("{$this->baseUrl}/traymeli/sellers/252254392");

            if ($response->status() === 429) {
                Log::warning('Limite de requisições excedido');
                echo "[WARNING] Limite de requisições excedido\n";
                return null;
            }

            if (!$response->successful()) {
                Log::error('Erro de autenticação API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                echo "[ERROR] Erro de autenticação da API verifique o log para mais detalhes \n";
                return null;
            }

            $data = $response->json();

            if (($data['inactive_token'] ?? 1) === 1) {
                Log::warning('Token Inativo');
                echo "[WARNING] Token Inativo\n";
                return null;
            }

            return $data['access_token'] ?? null;

        } catch (Exception $e) {
            Log::error('Erro de exceção', [
                'message' => $e->getMessage()
            ]);

            echo "[ERROR] Erro de exceção na autenticação da API verifique o log para mais detalhes \n";

            return null;
        }
    }
}