<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function () {
    $runner = app(\App\Services\AgentService::class);

    return $runner->run("search for How do i fix flat bike tire");
});
