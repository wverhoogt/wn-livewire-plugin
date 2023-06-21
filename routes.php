<?php namespace Verbant;

use App;
use Route;

Route::get('/vendor/livewire/{file}', function ($file) {
  $c = collect(explode('/', $file));
  $fn = $c->last();
  $getPath = $file;
  if (in_array(pathinfo($fn, PATHINFO_EXTENSION), ['js', 'map'])) {
    $getPath = public_path("/vendor/livewire/livewire/dist/${fn}");
  }
  if (file_exists($getPath)) {
    return file_get_contents($getPath);
  } else {
    App::abort(404);
  }
})->where('file', '.*');
// Route::group(['prefix' => 'livewire'], function() {

// });