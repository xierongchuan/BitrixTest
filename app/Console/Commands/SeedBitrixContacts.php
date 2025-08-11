<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Bitrix\BitrixWebhookService;
use Faker\Factory as Faker;

class SeedBitrixContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:seed-contacts {count=50}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gen test contacts and sent to Bitrix24 API';

    /**
     * Execute the console command.
     */
    public function handle(BitrixWebhookService $bitrix)
    {
        $faker = Faker::create('ru_RU');

        $contacts = collect(range(1, 50))->map(function ($i) use ($faker, $bitrix) {
            $firstName  = $faker->firstName;
            $lastName   = $faker->lastName;
            $secondName = $faker->middleName;

            $contact = (random_int(0, 1) === 0)
                ? ['NAME' => $firstName, 'SECOND_NAME' => $secondName, 'LAST_NAME' => $lastName]
                : ['NAME' => "$firstName $secondName", 'SECOND_NAME' => '', 'LAST_NAME' => $lastName];

            $response = $bitrix->addContact($contact['NAME'], $contact['SECOND_NAME'], $contact['LAST_NAME']);

            $this->info("[$i] Добавлен контакт ID: {$response->result}");

            return $response;
        });

        unset($contacts);

        return Command::SUCCESS;
    }
}
