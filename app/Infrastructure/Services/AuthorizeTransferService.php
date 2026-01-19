<?php

namespace App\Infrastructure\Services;

use App\Domains\Transfer\Contracts\AuthorizeTransferServiceInterface;
use Illuminate\Support\Facades\Http;

class AuthorizeTransferService implements AuthorizeTransferServiceInterface
{
    public function authorize(): bool
    {
        $url = config('services.transfer.authorizer');

        if (!is_string($url) || $url === '') {
            return false;
        }

        try {
            $response = Http::timeout(5)->get($url);
        } catch (\Throwable $e) {
            return false;
        }

        if (!$response->successful()) {
            return false;
        }

        return data_get($response->json(), 'data.authorization') === true;
    }
}
