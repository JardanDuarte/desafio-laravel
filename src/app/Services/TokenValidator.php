<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TokenValidator
{
    public function validate(?string $token): bool
    {
        if (empty($token)) {
            Log::warning('Token nulo ou inválido.');
            return false;
        }

        return true;
    }
}