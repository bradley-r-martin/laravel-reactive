<?php

Route::post('/reactive','Sihq\Reactive\Http\Controllers\Reactive\Reactive@route')->middleware('web');
Route::post('/reactive/signed-transfer','Sihq\Reactive\Http\Controllers\Reactive\Transfer@request');
Route::put('/reactive/signed-transfer','Sihq\Reactive\Http\Controllers\Reactive\Transfer@stage');

Route::get('/files/{file?}','Sihq\Reactive\Http\Controllers\Reactive\Transfer@serve')->where('file', '.*');
