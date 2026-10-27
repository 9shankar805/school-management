<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Bus Tracking Channels
|--------------------------------------------------------------------------
|
| Public channel — no auth required so the parent app (and any subscriber)
| can listen without Sanctum overhead. This matches the BusLocationUpdated
| event's broadcastOn() return value.
|
| Client usage (Laravel Echo):
|   Echo.channel('bus.' + vehicleId).listen('BusLocationUpdated', (e) => { ... })
|
*/
Broadcast::channel('bus.{vehicleId}', function () {
    // Public channel — allow all subscribers.
    return true;
});
