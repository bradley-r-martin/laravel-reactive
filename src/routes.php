<?php

Route::get('{slug?}', function(){
    return view('laravel-reactive::app');
})->where('slug', '(?!api|reactive).*(?<!js|css|json|html|txt|xml|webmanifest|jpg|gif|png|svg)')->middleware('web');


Route::post('/reactive','Sihq\LaravelReactive\LaravelReactive@route')->middleware('web');
