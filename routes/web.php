<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

if (app()->environment('local')) {
    Route::get('/_styleguide', function () {
        return view('styleguide');
    })->name('styleguide');
}
