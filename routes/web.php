<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/fix', function () {
    try {
        Artisan::call('key:generate');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('storage:link');
        return '✅ Artisan commands executed successfully.';
    } catch (\Exception $e) {
        return '❌ Error: ' . $e->getMessage();
    }
});
