<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->text('note')->nullable()->after('admin_id')->comment('Ghi chú đối soát');
            $table->timestamp('reconciled_at')->nullable()->after('note')->comment('Thời gian đối soát');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['admin_id', 'note', 'reconciled_at']);
        });
    }
};
