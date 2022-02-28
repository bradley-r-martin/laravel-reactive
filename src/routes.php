<?php


Route::post('/reactive-x','Sihq\Reactive\Http\Controllers\Reactive\ReactiveX@parse')->middleware('web');


Route::post('/reactive','Sihq\Reactive\Http\Controllers\Reactive\Reactive@route')->middleware('web');
Route::post('/reactive/signed-transfer','Sihq\Reactive\Http\Controllers\Reactive\Transfer@request');
Route::put('/reactive/signed-transfer','Sihq\Reactive\Http\Controllers\Reactive\Transfer@stage');