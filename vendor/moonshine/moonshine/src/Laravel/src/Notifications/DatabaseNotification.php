<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use MoonShine\Laravel\Contracts\Notifications\NotificationButtonContract;
use MoonShine\Support\Enums\Color;

final class DatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $message,
        protected ?NotificationButtonContract $button = null,
        protected null|string|Color $color = null
    ) {
        $this->color = $this->color instanceof Color ? $this->color->value : $this->color;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }


    /**
     * @return array{message: string, button: array, color: ?string}
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => $this->message,
            'button' => \is_null($this->button) ? [] : [
                'label' => $this->button->getLabel(),
                'link' => $this->button->getLink(),
            ],
            'color' => $this->color,
        ];
    }
}
