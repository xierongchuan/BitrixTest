# BitrixTest
Testing Bitrix24 API


### 1. Set up environment
```.env
BITRIX_API_URL={API_URL}
```

### 2. Seeding Contacts
```sh
php artisan bitrix:seed-contacts 50
```

### 3. Correct Contacts
```sh
php artisan bitrix:correct-names
```
