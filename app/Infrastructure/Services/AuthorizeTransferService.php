<?php

namespace App\Infrastructure\Services;

use App\Domains\Transfer\Contracts\AuthorizeTransferServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthorizeTransferService implements AuthorizeTransferServiceInterface
{
    public function authorize(): bool
    {
        $url = config('services.transfer.authorizer');

        if (!is_string($url) || $url === '') {
            Log::warning('Transfer authorizer URL is not configured');
            return false;
        }

        try {
            $response = Http::timeout(5)->get($url);
        } catch (\Throwable $e) {
            Log::warning('Transfer authorizer request failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }

        if (!$response->successful()) {
            Log::warning('Transfer authorizer returned non-successful response', [
                'status' => $response->status(),
            ]);
            return false;
        }

        return data_get($response->json(), 'data.authorization') === true;
    }
}
