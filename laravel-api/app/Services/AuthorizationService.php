<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class AuthorizationService
{
    public function authorize(): bool
    {
        $response = Http::get('https://util.devi.tools/api/v2/authorize');

        if (!$response->successful()) {
            return false;
        }

        return $response->json('data.authorization') === true;
    }
}
