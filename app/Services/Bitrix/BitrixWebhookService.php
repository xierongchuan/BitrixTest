<?php

declare(strict_types=1);

namespace App\Services\Bitrix;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

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

    public function isResponseSuccessful(object $response): bool
    {
        if (empty($response)) {
            Log::error('Bitrix: empty response.');
            return false;
        }

        if (isset($response->error)) {
            Log::error("Bitrix Error: {$response->error} - {$response->error_description}");
            return false;
        } elseif (!empty($response->result)) {
            Log::info("Bitrix: request was successful");
            return true;
        } else {
            Log::error("Bitrix: Unexpected response: " . json_encode($response));
            return false;
        }
    }
}
