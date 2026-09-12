<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ImportSkirtsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:skirts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import skirts from json file and copy images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jsonPath = base_path('chan_vay.json');
        if (!File::exists($jsonPath)) {
            $this->error("File chan_vay.json not found!");
            return;
        }

        $jsonContent = File::get($jsonPath);
        $products = json_decode($jsonContent, true);

        if (!$products) {
            $this->error("Invalid JSON data!");
            return;
        }

        // Find or create category
        $category = Category::where('name', 'like', '%Đầm Thiết Kế Dự Tiệc Công Sở%')->first();
        if (!$category) {
            $category = Category::create([
                'name' => 'Đầm Thiết Kế Dự Tiệc Công Sở',
                'slug' => Str::slug('Đầm Thiết Kế Dự Tiệc Công Sở'),
                'is_active' => true,
            ]);
            $this->info("Created category Đầm Thiết Kế Dự Tiệc Công Sở.");
        }

        // Create storage directory if it doesn't exist
        $storageDir = storage_path('app/public/products');
        if (!File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        foreach ($products as $item) {
            $name = $item['name'] ?? '';
            $code = $item['code'] ?? '';
            $priceStr = $item['price'] ?? '0';
            $localImage = $item['local_image'] ?? '';

            // Extract numeric price (e.g. 370.000 ₫ -> 370000)
            $price = (int) preg_replace('/[^0-9]/', '', $priceStr);
            $slug = Str::slug($name) . '-' . Str::random(5);

            $this->info("Importing: {$name}");

            // Create Product
            $product = Product::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => $slug,
                    'short_description' => '',
                    'description' => '',
                    'status' => 'active',
                ]
            );

            // Attach Category
            $product->categories()->syncWithoutDetaching([$category->id]);

            // Copy Image
            $imageUrl = null;
            if ($localImage) {
                $sourcePath = base_path($localImage);
                if (File::exists($sourcePath)) {
                    $fileName = basename($localImage);
                    $destPath = $storageDir . '/' . $fileName;
                    File::copy($sourcePath, $destPath);
                    $imageUrl = 'products/' . $fileName;

                    // Create Product Image
                    ProductImage::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'image_url' => $imageUrl
                        ],
                        [
                            'display_order' => 1
                        ]
                    );
                } else {
                    $this->warn("Image not found: {$sourcePath}");
                }
            }

            // Create Variant
            ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'sku' => $code],
                [
                    'color_id' => null,
                    'size_id' => null,
                    'price' => $price,
                    'stock_quantity' => 100,
                    'is_active' => true,
                    'thumbnail_url' => $imageUrl,
                ]
            );
        }

        $this->info('Import completed successfully!');
    }
}
