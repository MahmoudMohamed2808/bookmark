<?php

namespace App\Notifications;

use App\Models\Bookmark;
use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReminderDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Reminder $reminder,
        public readonly Bookmark $bookmark,
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'bookmark_id' => $this->bookmark->id,
            'bookmark_title' => $this->bookmark->title,
            'bookmark_url' => $this->bookmark->url,
            'reminder_type' => $this->reminder->type,
        ];
    }
}
