<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Services\Bitrix\BitrixWebhookService;

Route::get('/', function () {
    return 'Hii!';
});

Route::get('/contact/list', function () {
    $client = new BitrixWebhookService();
    $response = $client->call(
        'crm.contact.list',
        ['filter' => [], 'select' => ['ID','NAME', 'SECOND_NAME', 'LAST_NAME']]
    );
    return $response;
});
