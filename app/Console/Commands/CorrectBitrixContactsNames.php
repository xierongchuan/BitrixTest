<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Bitrix\BitrixApiService;
use Illuminate\Support\Facades\Log;

class CorrectBitrixContactsNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:correct-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Correcting contacts names from Bitrix24';

    /**
     * Execute the console command.
     */
    public function handle(BitrixApiService $bitrix)
    {
        // Получаем список всех контактов с проблемной ФИО
        $contacts = $bitrix->getAllUncorrectedContacts();

        // Кластер для команды batch(отправка изменений чанками до 50шт разом)
        $cluster = [];

        // При отсутствии Second Name берём его из Name
        foreach ($contacts as $key => $contact) {
            $fullName = explode(" ", trim($contact['NAME']));

            if (count($fullName) <= 1) {
                continue;
            }

            if (
                empty($contact['SECOND_NAME']) &&
                isset($contact['LAST_NAME'])
            ) {
                $contact['NAME'] = $fullName[0];
                $contact['SECOND_NAME'] = $fullName[1];
            }

            if (
                isset($contact['SECOND_NAME']) &&
                empty($contact['LAST_NAME'])
            ) {
                $contact['NAME'] = $fullName[0];
                $contact['LAST_NAME'] = $fullName[1];
            }

            $correctedContact = [
                'id' => (int)$contact['ID'],
                'fields' => [
                    'NAME' => $contact['NAME'],
                    'SECOND_NAME' => $contact['SECOND_NAME'] ?? '',
                    'LAST_NAME' => $contact['LAST_NAME'] ?? '',
                ],
            ];

            $clusterKey = 'cluster_' . $contact['ID'];
            $cluster[$clusterKey] = 'crm.contact.update?' . http_build_query($correctedContact);
        }

        // Разбивка кластера на чанки по 50шт
        $chunks = array_chunk($cluster, 50, true);

        foreach ($chunks as $chunk) {
            $bitrix->call('batch', ['cmd' => $chunk]);
        }

        return Command::SUCCESS;
    }
}
