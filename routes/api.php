<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BpPhoneContactsController;

Route::post('/bp/phone_contacts', [BpPhoneContactsController::class, 'handle']);
