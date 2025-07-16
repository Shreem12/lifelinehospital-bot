<?php
use Illuminate\Support\Facades\Artisan;

Route::get('/fix', function () {
    Artisan::call('key:generate');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('storage:link');
    return 'Fix commands run!';
});
