<?php

use App\Models\Bookmark;
use App\Models\Reminder;
use App\Models\User;
use App\Notifications\ReminderDueNotification;
use App\Services\ReminderService;
use Illuminate\Support\Facades\Notification;

it('schedules a reminder and stamps the bookmark', function () {
    $bookmark = Bookmark::factory()->create();
    $remindAt = now()->addDay()->startOfSecond();

    $reminder = app(ReminderService::class)->schedule($bookmark, $remindAt, 'reminder');

    expect($reminder->bookmark_id)->toBe($bookmark->id)
        ->and($reminder->type)->toBe('reminder')
        ->and($reminder->remind_at->equalTo($remindAt))->toBeTrue()
        ->and($bookmark->fresh()->reminder_at->equalTo($remindAt))->toBeTrue();
});

it('snoozes a reminder and resets its notified state', function () {
    $reminder = Reminder::factory()->notified()->create();
    $newTime = now()->addWeek()->startOfSecond();

    app(ReminderService::class)->snooze($reminder, $newTime);

    $reminder->refresh();

    expect($reminder->remind_at->equalTo($newTime))->toBeTrue()
        ->and($reminder->is_notified)->toBeFalse()
        ->and($reminder->notified_at)->toBeNull();
});

it('sends notifications for due reminders and marks them notified', function () {
    Notification::fake();

    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);

    $due = Reminder::factory()->due()->create(['bookmark_id' => $bookmark->id]);
    $notDue = Reminder::factory()->create([
        'bookmark_id' => $bookmark->id,
        'remind_at' => now()->addDay(),
    ]);
    $alreadyNotified = Reminder::factory()->due()->notified()->create(['bookmark_id' => $bookmark->id]);

    app(ReminderService::class)->sendDueNotifications();

    Notification::assertSentTo($user, ReminderDueNotification::class);

    expect($due->fresh()->is_notified)->toBeTrue()
        ->and($due->fresh()->notified_at)->not->toBeNull()
        ->and($notDue->fresh()->is_notified)->toBeFalse()
        ->and($alreadyNotified->fresh()->is_notified)->toBeTrue();
});

it('does not notify twice for the same reminder', function () {
    Notification::fake();

    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);
    $reminder = Reminder::factory()->due()->create(['bookmark_id' => $bookmark->id]);

    $service = app(ReminderService::class);
    $service->sendDueNotifications();
    $service->sendDueNotifications();

    Notification::assertSentToTimes($user, ReminderDueNotification::class, 1);
});
