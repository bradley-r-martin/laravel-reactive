<?php


Route::post('/reactive','Sihq\Http\Controllers\Reactive\Reactive@parse')->middleware('web');
Route::post('/reactive/signed-transfer','Sihq\Http\Controllers\Reactive\Transfer@store');
Route::put('/reactive/signed-transfer','Sihq\Http\Controllers\Reactive\Transfer@stage');