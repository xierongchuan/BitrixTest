<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Services\Bitrix\BitrixWebhookService;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's resources.
     */
    public function run(): void
    {
        $faker = Faker::create('ru_RU');
        $client = new BitrixWebhookService();

        $contacts = collect(range(1, 50))->map(function ($i) use ($faker, $client) {
            $firstName  = $faker->firstName;
            $lastName   = $faker->lastName;
            $secondName = $faker->secondName;

            $contact = (random_int(0, 1) === 0)
                ? ['NAME' => $firstName, 'SECOND_NAME' => $secondName, 'LAST_NAME' => $lastName]
                : ['NAME' => "$firstName $secondName", 'SECOND_NAME' => '', 'LAST_NAME' => $lastName];

            echo $i . '. Adding ' . $contact['NAME'] . "\n";

            $response = $client->addContact($contact['NAME'], $contact['SECOND_NAME'], $contact['LAST_NAME']);

            return $response;
        });

        unset($contacts);
    }
}
