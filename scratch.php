<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Route::get('/{slug}-p{id}.html', [\App\Http\Controllers\Client\PostController::class, 'show'])
    ->where('slug', '[a-zA-Z0-9\-]+')
    ->where('id', '[0-9]+')
    ->name('posts.show.test');

try {
    $request = Illuminate\Http\Request::create('/xu-huong-thu-dong-2026-su-tro-lai-cua-nhung-gam-mau-tram-am-1788602860-p1.html');
    $route = app('router')->getRoutes()->match($request);
    echo 'Matched Route: ' . $route->getName() . "\n";
    print_r($route->parameters());
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
