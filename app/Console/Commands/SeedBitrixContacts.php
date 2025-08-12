<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Bitrix\BitrixApiService;
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
    public function handle(BitrixApiService $bitrix)
    {
        $faker = Faker::create('ru_RU');
        $count = (int) $this->argument('count');

        $contacts = collect(range(1, $count))->map(function ($i) use ($faker, $bitrix) {
            $firstName  = $faker->firstName;
            $lastName   = $faker->lastName;
            $secondName = $faker->middleName;

            $rnd = random_int(0, 3);

            if ($rnd === 0) {
                $contact = ['NAME' => $firstName, 'SECOND_NAME' => $secondName, 'LAST_NAME' => $lastName];
            }
            if ($rnd === 1) {
                $contact = ['NAME' => "$firstName $secondName", 'SECOND_NAME' => '', 'LAST_NAME' => $lastName];
            }
            if ($rnd === 2) {
                $contact = ['NAME' => "$firstName $lastName", 'SECOND_NAME' => "$secondName", 'LAST_NAME' => ''];
            }
            if ($rnd === 3) {
                $contact = ['NAME' => "$firstName $secondName $lastName", 'SECOND_NAME' => '', 'LAST_NAME' => ''];
            }

            $response = $bitrix->addContact($contact['NAME'], $contact['SECOND_NAME'], $contact['LAST_NAME']);

            $this->info("[$i] Добавлен контакт ID: {$response->result}");

            return $response;
        });

        unset($contacts);

        return Command::SUCCESS;
    }
}
