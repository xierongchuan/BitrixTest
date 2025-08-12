<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\Bitrix\BitrixApiService;

class BpPhoneContactsController extends Controller
{
    public function handle(Request $request, BitrixApiService $bitrix): JsonResponse
    {
        $data = (object) $request->validate([
            'phone' => [
                'required',
                'string',
                'min:7',
                'max:25'
            ]
        ]);

        // Удаление символов кроме главного символа плюс
        $phone = preg_replace('/\D+/', '', $data->phone);

        try {
            $ids = $bitrix->findContactsByPhone($phone);
        } catch (\Throwable $e) {
            Log::error('BP Phone Contacts Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'errors' => ['server' => 'Internal error'],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'contact_ids' => array_values($ids),
        ]);
    }
}
