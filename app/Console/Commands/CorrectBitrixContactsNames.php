<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Bitrix\BitrixWebhookService;

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
        $response = $bitrix->call(
            'crm.contact.list',
            ['filter' => [], 'select' => ['ID','NAME', 'SECOND_NAME', 'LAST_NAME']]
        );

        $contacts = $response->result;

        // При отсутствии Second Name берём его из Name Отправка скорректированных контавтов к Bitrix 24
        foreach ($contacts as $key => $contact) {
            if (empty($contact['SECOND_NAME'])) {
                $fullName = explode(" ", $contact['NAME']);
                $contact['NAME'] = $fullName[0];
                $contact['SECOND_NAME'] = $fullName[1];
            }

            $response = $bitrix->updateContact(
                (int) $contact['ID'],
                [
                    'NAME' => $contact['NAME'],
                    'SECOND_NAME' => $contact['SECOND_NAME'],
                    'LAST_NAME' => $contact['LAST_NAME'],
                ]
            );

            $this->info("[$key] Скорректирован контакт ID: {$contact['ID']}");

            if (!$bitrix->isResponseSuccessful($response)) {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
