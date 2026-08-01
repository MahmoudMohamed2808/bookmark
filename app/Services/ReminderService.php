<?php

namespace App\Services;

use App\Models\Bookmark;
use App\Models\Reminder;
use App\Models\User;
use App\Notifications\ReminderDueNotification;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class ReminderService
{
    /**
     * Schedule a reminder (or scheduled review) for a bookmark.
     */
    public function schedule(Bookmark $bookmark, CarbonInterface $remindAt, string $type = 'reminder'): Reminder
    {
        $bookmark->forceFill(['reminder_at' => $remindAt])->save();

        return $bookmark->reminders()->create([
            'type' => $type,
            'remind_at' => $remindAt,
            'is_notified' => false,
        ]);
    }

    /**
     * Push a reminder's due time forward and reset its notified state.
     */
    public function snooze(Reminder $reminder, CarbonInterface $newTime): void
    {
        $reminder->forceFill([
            'remind_at' => $newTime,
            'is_notified' => false,
            'notified_at' => null,
        ])->save();
    }

    /**
     * Dispatch notifications for every due reminder and mark them notified.
     */
    public function sendDueNotifications(): void
    {
        $due = Reminder::query()
            ->where('is_notified', false)
            ->where('remind_at', '<=', now())
            ->with(['bookmark.user'])
            ->get();

        foreach ($due as $reminder) {
            $this->notify($reminder);
        }
    }

    public function notify(Reminder $reminder): void
    {
        $user = $reminder->bookmark->user;

        if ($user === null) {
            return;
        }

        DB::transaction(function () use ($reminder, $user): void {
            $fresh = Reminder::query()
                ->whereKey($reminder->id)
                ->where('is_notified', false)
                ->lockForUpdate()
                ->first();

            if ($fresh === null) {
                return;
            }

            $fresh->forceFill([
                'is_notified' => true,
                'notified_at' => now(),
            ])->save();

            $user->notify(new ReminderDueNotification($fresh, $fresh->bookmark));
        });
    }
}
