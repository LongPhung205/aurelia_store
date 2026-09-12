<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = 0;
foreach (\App\Models\ProductVariant::whereNotNull('thumbnail_url')->get() as $variant) {
    if (!\Storage::disk('public')->exists($variant->thumbnail_url)) {
        echo "Deleting missing thumbnail for variant ID: {$variant->id} (Path: {$variant->thumbnail_url})\n";
        $variant->update(['thumbnail_url' => null]);
        $count++;
    }
}
echo "Cleaned {$count} missing variant thumbnails.\n";
