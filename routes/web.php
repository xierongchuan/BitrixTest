<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Services\Bitrix\BitrixApiService;

Route::get('/', function () {
    return 'Hii!';
});

Route::get('/contact/list', function () {
    $client = new BitrixApiService();
    $response = $client->call(
        'crm.contact.list',
        ['filter' => [], 'select' => ['ID','NAME', 'SECOND_NAME', 'LAST_NAME', 'PHONE']]
    );
    return $response;
});
