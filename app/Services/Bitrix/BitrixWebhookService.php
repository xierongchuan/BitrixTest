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

    // Получаем список всех контактов по 50шт(с обходом пагинации)
    public function getContacts(array $select = ['ID','NAME','SECOND_NAME','LAST_NAME'], array $filter = []): array
    {
        $start = 0;
        $all = [];

        while (true) {
            Log::info("Contact list start={$start}");

            $res = $this->call('crm.contact.list', [
                'filter' => $filter,
                'select' => $select,
                'start'  => (int) $start,
            ]);

            $total = (int) $res->total;

            // Надежно извлечём массив элементов из разных форм ответа
            $items = [];
            if (is_object($res) && isset($res->result) && is_array($res->result)) {
                $items = $res->result;
            } elseif (is_array($res) && isset($res['result']) && is_array($res['result'])) {
                $items = $res['result'];
            }

            $count = count($items);
            if ($count === 0 || $start >= $total) {
                break;
            }

            $all = array_merge($all, $items);
            $start += $count;
        }

        Log::info('Total contacts: ' . count($all));
        return $all;
    }

    // Получаем список всех контактов с проблемной ФИО
    public function getAllUncorrectedContacts(): array
    {
        $select = ['ID','NAME','SECOND_NAME','LAST_NAME'];

        $filters = [
            ['SECOND_NAME' => '', 'LAST_NAME' => ''],
            ['SECOND_NAME' => '', '!=LAST_NAME' => ''],
            ['LAST_NAME' => '', '!=SECOND_NAME' => ''],
        ];

        $acc = [];

        foreach ($filters as $filter) {
            $items = $this->getContacts($select, $filter);
            foreach ($items as $c) {
                $id = (int) ($c['ID'] ?? 0);
                if ($id === 0) {
                    continue;
                }
                $acc[$id] = $c;
            }
        }

        return array_values($acc);
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
