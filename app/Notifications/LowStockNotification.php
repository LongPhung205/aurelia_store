<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ProductVariant;

class LowStockNotification extends Notification
{
    use Queueable;

    public $variant;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProductVariant $variant)
    {
        $this->variant = $variant;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $productName = $this->variant->product->name ?? 'Sản phẩm';
        return [
            'type' => 'low_stock',
            'product_variant_id' => $this->variant->id,
            'message' => "Sắp hết hàng: {$productName} (" . ($this->variant->sku ?: 'No SKU') . ") - Còn {$this->variant->stock_quantity} sản phẩm",
            'url' => route('admin.inventory.index', ['search' => $this->variant->sku])
        ];
    }
}
