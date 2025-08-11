<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Bitrix\BitrixWebhookService;
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
    public function handle(BitrixWebhookService $bitrix)
    {
        // Получаем список всех контактов с отсутствующим SECOND_NAME
        $contacts = $bitrix->getContacts(
            ['ID','NAME','SECOND_NAME','LAST_NAME'],
            ['SECOND_NAME' => '']
        );

        // Клачтер для команды batch(отправка изменений чанками до 50шт разом)
        $cluster = [];

        // При отсутствии Second Name берём его из Name
        foreach ($contacts as $key => $contact) {
            if (empty($contact['SECOND_NAME'])) {
                $fullName = explode(" ", $contact['NAME']);
                $contact['NAME'] = $fullName[0];
                $contact['SECOND_NAME'] = $fullName[1];
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
