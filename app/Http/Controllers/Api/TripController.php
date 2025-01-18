<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Trip;
use App\Models\User;
use App\Models\Trip_participant;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class TripController extends Controller
{

    public function addTrip(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:2,100',
            'destination' => 'required|string|max:100',
            'startDate' => 'required|date|after:today',
            'endDate' => 'required|date|after_or_equal:startDate'
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

        $trip = Trip::create([
            'title' => $request->get('title'),
            'destination' => $request->get('destination'),
            'start_date' => $request->get('startDate'),
            'end_date' => $request->get('endDate'),
            'image_path' => $request->get('imagePreview')
        ]);
        $trip_participant = Trip_participant::create([
            'id_user' => $user->id,
            'id_trip' => $trip->id,
        ]);

        return response()->json(compact('trip'), 201);
    }

    public function joinTrip(string $id, Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $trip = Trip_participant::create([
            'id_user' => $user->id,
            'id_trip' => $id
        ]);
        return response()->json(compact('trip'), 201);
    }

    public function getTrips()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $tripIds = Trip_participant::where('id_user', $user->id)
            ->whereNull('deleted_at')
            ->pluck('id_trip');

        $trips = Trip::whereIn('id', $tripIds)->get();
        return response()->json($trips);
    }

    public function showTrip(string $id)
    {
        $trip = Trip::findOrFail($id);
        return response()->json($trip);
    }

    public function getParticipants(string $id)
    {
        $usersIds = Trip_participant::where('id_trip', $id)->pluck('id_user');
        $usersNames = User::whereIn('id', $usersIds)->pluck('name');
        return response()->json($usersNames);
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

    public function deleteTrip(Request $request, string $id)
    {
        $trip = Trip::findOrFail($id);
        $trip->deleted_at = $request->get('deleted_at');
        $trip->save();

        return response()->json(['message' => 'Viagem deletada com sucesso.']);
    }
}
