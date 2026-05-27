<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

test('authenticated user can view notifications', function () {
    $user = User::factory()->create();

    $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\Domains\Notifications\Notifications\LeadStatusChangedNotification',
        'data' => ['message' => 'Test notification'],
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('notifications.index'));

    $response->assertOk();
});

test('authenticated user can mark notification as read', function () {
    $user = User::factory()->create();

    $notification = $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\Domains\Notifications\Notifications\LeadStatusChangedNotification',
        'data' => ['message' => 'Test notification'],
    ]);

    expect(DatabaseNotification::find($notification->id)->read_at)->toBeNull();

    $response = $this
        ->actingAs($user)
        ->post(route('notifications.markAsRead', $notification->id));

    $response->assertRedirect();
    expect(DatabaseNotification::find($notification->id)->read_at)->not->toBeNull();
});

test('authenticated user can mark all notifications as read', function () {
    $user = User::factory()->create();

    $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\Domains\Notifications\Notifications\LeadStatusChangedNotification',
        'data' => ['message' => 'Test notification 1'],
    ]);

    $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\Domains\Notifications\Notifications\LeadAssignedNotification',
        'data' => ['message' => 'Test notification 2'],
    ]);

    expect($user->unreadNotifications()->count())->toBe(2);

    $response = $this
        ->actingAs($user)
        ->post(route('notifications.markAllAsRead'));

    $response->assertRedirect();
    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('unread count returns correct count', function () {
    $user = User::factory()->create();

    $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\Domains\Notifications\Notifications\LeadStatusChangedNotification',
        'data' => ['message' => 'Test notification'],
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('notifications.unreadCount'));

    $response->assertOk();
    $response->assertJson(['count' => 1]);
});
