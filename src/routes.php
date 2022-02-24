<?php

Route::post('/reactive','Sihq\Reactive\Http\Controllers\Reactive\Reactive@route')->middleware('web');
Route::get('/reactive/signed-transfer','Sihq\Reactive\Http\Controllers\Reactive\Transfer@request');
Route::post('/reactive/signed-transfer','Sihq\Reactive\Http\Controllers\Reactive\Transfer@transfer');