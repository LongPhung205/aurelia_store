<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PayOS\PayOS;

class ConfirmPayOSWebhookCommand extends Command
{
    protected $signature = 'payos:confirm-webhook {url? : Webhook URL cần đăng ký với PayOS}';
    protected $description = 'Đăng ký và xác thực Webhook URL với cổng thanh toán PayOS';

    public function handle(): int
    {
        $url = $this->argument('url') ?: url('/payos/webhook');

        $this->info("Đang đăng ký Webhook URL với PayOS: {$url}");

        $clientId = config('services.payos.client_id');
        $apiKey = config('services.payos.api_key');
        $checksumKey = config('services.payos.checksum_key');

        if (! $clientId || ! $apiKey || ! $checksumKey) {
            $this->error('Thiếu cấu hình PAYOS_CLIENT_ID, PAYOS_API_KEY hoặc PAYOS_CHECKSUM_KEY trong file .env / Environment Variables.');
            return 1;
        }

        try {
            $payOS = app()->bound(PayOS::class) ? app(PayOS::class) : new PayOS($clientId, $apiKey, $checksumKey);
            $confirmedUrl = $payOS->confirmWebhook($url);

            $this->info("✓ Đăng ký thành công! PayOS đã xác nhận Webhook URL: {$confirmedUrl}");
            return 0;
        } catch (\Throwable $e) {
            $this->error("✗ Lỗi đăng ký Webhook với PayOS: " . $e->getMessage());
            return 1;
        }
    }
}
