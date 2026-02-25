<?php

namespace App\Jobs;

use App\Services\AuthService;
use App\Services\SearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\TokenValidator;


class CaptureItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(AuthService $authService, SearchService $searchService): void
    {
        Log::info('Iniciando CaptureItemsJob');
        echo "[INFO] Iniciando CaptureItemsJob\n";

        $token = $authService->getAccessToken();

        if (!app(TokenValidator::class)->validate($token)) {
            echo "[WARNING] Token nulo ou inválido.\n";
            return;
        }

        $ids = $searchService->getAllItemIds($token);

        foreach ($ids as $id) {
            ProcessItemJob::dispatch($id, $token);
        }

        Log::info('O CaptureItemsJob despachou todos os trabalhos de itens.');
    }
}