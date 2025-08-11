<?php

declare(strict_types=1);

namespace App\Services\Bitrix;

use GuzzleHttp\Client;

class BitrixWebhookService
{
    protected string $base;
    protected Client $http;

    public function __construct()
    {
        $this->base = rtrim(config('services.bitrix.api_url'), '/') . '/';
        $this->http = new Client(['timeout' => 10]);
    }

    public function call(string $method, array $params = [])
    {
        $url = $this->base . $method . '.json';
        $res = $this->http->post($url, ['json' => $params]);
        return (object)json_decode((string)$res->getBody(), true);
    }

    public function addContact(string $name, string $secondName, string $lastName): object
    {
        $response = $this->call('crm.contact.add', [
            'fields' => [
                'NAME' => $name,
                'SECOND_NAME' => $secondName,
                'LAST_NAME' => $lastName,
            ]
        ]);

        return $response;
    }

    public function updateContact(int $id, array $fields): object
    {
        return $this->call('crm.contact.update', [
            'id'     => $id,
            'fields' => $fields,
        ]);
    }
}
