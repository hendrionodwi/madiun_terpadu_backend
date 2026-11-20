<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MarketplaceTransactionNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, scalar|null>  $context
     */
    public function __construct(
        public string $event,
        public string $title,
        public string $message,
        public string $orderNumber,
        public string $status,
        public string $statusLabel,
        public string $audience,
        public string $webUrl,
        public array $context = [],
    ) {}

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
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'category' => 'marketplace_transaction',
            'event' => $this->event,
            'title' => $this->title,
            'message' => $this->message,
            'order_number' => $this->orderNumber,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'audience' => $this->audience,
            'web_url' => $this->webUrl,
            'action_type' => 'marketplace_order',
            ...$this->context,
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
