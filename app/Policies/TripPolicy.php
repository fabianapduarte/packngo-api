<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\Trip_participant;
use App\Models\User;

class TripPolicy
{
    public function isParticipant(User $user, Trip $trip): bool
    {
        $tripParticipant = Trip_participant::where('id_trip', $trip->id)->where('id_user', $user->id)->first();
        return !is_null($tripParticipant);
    }
}
