<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppBotController;

Route::get('/webhook', [WhatsAppBotController::class, 'verify']);
