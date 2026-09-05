<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create the pivot table
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'product_id']);
        });

        // 2. Migrate existing data from products table to category_product
        DB::table('products')->whereNotNull('category_id')->orderBy('id')->chunk(100, function ($products) {
            $pivotData = [];
            foreach ($products as $product) {
                $pivotData[] = [
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                ];
            }
            DB::table('category_product')->insert($pivotData);
        });

        // 3. Drop category_id from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Add category_id back to products table
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnDelete();
        });

        // 2. Try to restore data (using the first category found for each product)
        DB::table('products')->orderBy('id')->chunk(100, function ($products) {
            foreach ($products as $product) {
                $firstCategory = DB::table('category_product')
                    ->where('product_id', $product->id)
                    ->first();
                
                if ($firstCategory) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['category_id' => $firstCategory->category_id]);
                }
            }
        });

        // 3. Drop the pivot table
        Schema::dropIfExists('category_product');
    }
};
