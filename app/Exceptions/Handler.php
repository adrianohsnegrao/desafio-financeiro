<?php

namespace App\Exceptions;

use App\Domains\Transfer\Exceptions\TransferException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (TransferException $e, $request) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        });
    }
}
