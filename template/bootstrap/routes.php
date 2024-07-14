<?php

use App\Controllers\HelloWorldController;
use App\Framework\Support\Route;

Route::any('/', [HelloWorldController::class, 'index'])->name('test');
