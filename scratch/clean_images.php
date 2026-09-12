<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = 0;
foreach (\App\Models\ProductImage::all() as $img) {
    if (!\Storage::disk('public')->exists($img->image_url)) {
        echo "Deleting image ID: {$img->id} (Path: {$img->image_url})\n";
        $img->delete();
        $count++;
    }
}
echo "Deleted {$count} missing images.\n";
