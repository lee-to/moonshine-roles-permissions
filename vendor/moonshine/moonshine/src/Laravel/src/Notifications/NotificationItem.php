<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Notifications;

use DateTimeInterface;
use Illuminate\Notifications\DatabaseNotification;
use MoonShine\Laravel\Contracts\Notifications\NotificationButtonContract;
use MoonShine\Laravel\Contracts\Notifications\NotificationItemContract;

final readonly class NotificationItem implements NotificationItemContract
{
    public function __construct(
        private DatabaseNotification $notification,
    ) {
    }

    public function getId(): int|string|null
    {
        return $this->notification->id;
    }

    public function getReadRoute(): string
    {
        return route('moonshine.notifications.read', $this->notification);
    }

    public function getColor(): string
    {
        return $this->notification->data['color'] ?? 'green';
    }

    public function getMessage(): string
    {
        return $this->notification->data['message'] ?? '';
    }

    public function getDate(): DateTimeInterface
    {
        return $this->notification->created_at ?? now();
    }

    public function getButton(): ?NotificationButtonContract
    {
        if (empty($this->notification->data['button'])) {
            return null;
        }

        return new NotificationButton(
            $this->notification->data['button']['label'],
            $this->notification->data['button']['link'],
        );
    }

    public function getIcon(): string
    {
        return 'information-circle';
    }
}
