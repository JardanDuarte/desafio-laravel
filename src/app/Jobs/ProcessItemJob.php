<?php

namespace App\Jobs;

use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $id,
        private string $token
    ) {}

    public function handle(ItemService $itemService): void
    {
        Log::info("Processando o item {$this->id}");

        $data = $itemService->getItemDetails($this->id, $this->token);

        if (!$data) {
            Log::warning("Falha ao buscar o item {$this->id}");
            echo  "[WARNING] Falha ao processar o item {$this->id}\n";
            return;
        }

        Item::updateOrCreate(
            ['id' => $data['id']],
            [
                'title' => $data['title'],
                'status' => $data['status'],
                'ml_created' => $data['ml_created'],
                'ml_updated' => $data['ml_updated'],
                'processing_status' => 'processed',
            ]
        );

        Log::info("Item {$this->id} Salvo com sucesso");
        echo "[INFO] Item {$this->id} salvo com sucesso\n";
    }
}