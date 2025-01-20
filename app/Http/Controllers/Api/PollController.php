<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $idTrip): JsonResponse
    {
        $trip = Trip::findOrFail($idTrip);
        $this->authorize('isParticipant', $trip);

        return response()->json();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $idTrip, Request $request): JsonResponse
    {
        $trip = Trip::findOrFail($idTrip);
        $this->authorize('isParticipant', $trip);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:2,100',
            'options' => 'required|array|min:2',
        ], [
            'title.between' => 'O título deve ter entre 2 e 100 caracteres.',
            'options.min' => 'A enquete deve ter no mínimo duas opções',
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->toJson()], 400);
        }

        $pollCreated = Poll::create([
            'title' => $request->get('title'),
            'id_trip' => $trip->id,
        ]);

        $optionsRequest = $request->get('options');
        $options = [];
        foreach ($optionsRequest as $option) {
            array_push($options, [
                'option' => $option,
                'id_poll' => $pollCreated->id,
            ]);
        }

        $pollCreated->options()->createMany($options);

        return response()->json(['message' => 'Enquete criada com sucesso.'], 201);
    }

    public function vote(string $idTrip, string $idPoll, string $idOption): JsonResponse
    {
        return response()->json();
    }
}
