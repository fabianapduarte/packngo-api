<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Trip;
use App\Models\User;
use App\Models\Trip_participant;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnumTravelStatus
{
    const PROGRESS = 'progress';
    const FINISHED = 'finished';
    const PLANNED = 'planned';
}

class TripController extends Controller
{
    public function addTrip(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:2,100',
            'destination' => 'required|string|max:100',
            'startDate' => 'required|date|after:today',
            'endDate' => 'required|date|after_or_equal:startDate',
            'image' => 'sometimes|image'
        ], [
            'title.between' => 'O título deve ter entre 2 e 100 caracteres.',
            'destination.max' => 'O destino não pode ter mais de 100 caracteres.',
            'startDate.after' => 'A data de início deve ser posterior à data atual.',
            'endDate.after_or_equal' => 'A data de término deve ser igual ou após a data de início.'
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->toJson()], 400);
        }

        $user = JWTAuth::parseToken()->authenticate();

        $imageName = null;

        if ($request->file('image') != null) {
            $imageName = (string) Uuid::uuid4() . '.' . $request->file('image')->extension();
            $request->image->move(storage_path('app/public/'), $imageName);
        }

        $trip = Trip::create([
            'title' => $request->get('title'),
            'code' => str()->random(6),
            'destination' => $request->get('destination'),
            'start_date' => $request->get('startDate'),
            'end_date' => $request->get('endDate'),
            'image_path' => $imageName
        ]);
        $trip_participant = Trip_participant::create([
            'id_user' => $user->id,
            'id_trip' => $trip->id,
        ]);

        return response()->json(compact('trip'), 201);
    }

    public function joinTrip(string $id): JsonResponse
    {
        $trip = Trip::findOrFail($id);
        $user = JWTAuth::parseToken()->authenticate();

        Trip_participant::create([
            'id_user' => $user->id,
            'id_trip' => $trip->id
        ]);
        return response()->json(['trip_id' => $trip->id], 200);
    }

    public function getTrips()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $tripIds = Trip_participant::where('id_user', $user->id)
            ->whereNull('deleted_at')
            ->pluck('id_trip');

        $trips = Trip::whereIn('id', $tripIds)->get();
        $trips = $this->setStatusForTrips($trips);
        return response()->json($trips);
    }

    public function showTrip(string $id)
    {
        $trip = Trip::findOrFail($id);
        $participants = $this->getParticipants($trip->id);
        $trip->participants = $participants;
        return response()->json($trip);
    }

    public function fetchTrip(string $code)
    {
        $trip = Trip::where('code', $code)->first();
        $participants = $this->getParticipants($trip->id);
        $trip->participants = $participants;
        $trip = $this->setStatus($trip);
        return response()->json($trip);
    }

    protected function getParticipants(string $id)
    {
        $usersIds = Trip_participant::where('id_trip', $id)->pluck('id_user');
        $users = User::whereIn('id', $usersIds)->select('name', 'image_path')->get();
        return $users;
    }

    public function updateTrip(Request $request, string $id)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'string|max:255',
                'destination' => 'string|max:255',
                'start_date' => 'date',
                'end_date' => 'date|after_or_equal:start_date',
                'imagePreview' => 'string|nullable',
            ]);

            $trip = Trip::findOrFail($id);

            $trip->update($validatedData);

            return response()->json(['message' => 'Trip updated successfully.', 'trip' => $trip], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Trip not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    public function deleteTrip(string $id)
    {
        $trip = Trip::findOrFail($id);
        $trip->delete();

        return response()->json(['message' => 'Viagem deletada com sucesso.']);
    }

    public function leaveTrip(string $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $tripParticipant = Trip_participant::where('id_trip', $id)
            ->where('id_user', $user->id)
            ->first();

        $tripParticipant->delete();

        return response()->json(['message' => 'Viagem deixada com sucesso.']);
    }


    #utils
    public function setStatus(object $data)
    {
        if ($data) {
            $status = null;
            $now = new \DateTime();

            $startDate = new \DateTime($data['start_date']);
            $endDate = new \DateTime($data['end_date']);

            if ($now < $startDate) {
                $status = EnumTravelStatus::PLANNED;
            } elseif ($now > $endDate) {
                $status = EnumTravelStatus::FINISHED;
            } else {
                $status = EnumTravelStatus::PROGRESS;
            }

            $tripArray = $data->toArray();
            $tripArray['status'] = $status;

            return $tripArray;
        }
    }

    public function setStatusForTrips($trips)
    {
        $now = new \DateTime();

        return $trips->map(function ($trip) use ($now) {
            $startDate = new \DateTime($trip->start_date);
            $endDate = new \DateTime($trip->end_date);

            if ($now < $startDate) {
                $trip->status = EnumTravelStatus::PLANNED;
            } elseif ($now > $endDate) {
                $trip->status = EnumTravelStatus::FINISHED;
            } else {
                $trip->status = EnumTravelStatus::PROGRESS;
            }

            return $trip;
        });
    }
}
