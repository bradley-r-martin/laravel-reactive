<?php


Route::post('/reactive','Sihq\Http\Controllers\Reactive\Reactive@parse')->middleware('web');
Route::post('/reactive/signed-transfer','Sihq\Http\Controllers\Reactive\Transfer@store');
Route::put('/reactive/signed-transfer','Sihq\Http\Controllers\Reactive\Transfer@stage');


Route::get('{slug?}',function(){
    return view('sihq::app');
})->where('slug', '(?!api|reactive).*(?<!js|css|json|html|txt|xml|webmanifest|jpg|gif|png|svg)')->middleware('web');
