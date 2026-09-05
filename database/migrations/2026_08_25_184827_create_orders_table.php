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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Thông tin khách hàng
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('address');
            $table->integer('province_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->string('ward_code')->nullable();
            
            // Thông tin tiền
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            
            // Thông tin GHN
            $table->string('shipping_order_code')->nullable()->comment('Mã vận đơn GHN');
            $table->string('shipping_status')->default('pending')->comment('Trạng thái giao hàng GHN');
            
            // Trạng thái chung
            $table->string('status')->default('pending'); // pending, processing, shipping, completed, cancelled
            $table->string('payment_method')->default('cod'); // cod, payos
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, failed

            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
