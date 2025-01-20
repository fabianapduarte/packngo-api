<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Http\Controllers\Controller;
use App\Models\EventParticipant;
use App\Models\Trip;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;
use Tymon\JWTAuth\Facades\JWTAuth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $idTrip): JsonResponse
    {
        $trip = Trip::findOrFail($idTrip);
        $this->authorize('isParticipant', $trip);

        $events = Event::where('id_trip', $idTrip)->get();

        $eventsIds = [];
        foreach ($events as $event) {
            array_push($eventsIds, $event->id);
        }

        $allEventsParticipants = EventParticipant::whereIn('id_event', $eventsIds)->get();
        $participantsByEvent = $allEventsParticipants
            ->groupBy('id_event')
            ->map(function ($group) {
                return $group->pluck('id_user')->toArray();
            });
        $countsByEvent = $allEventsParticipants->countBy('id_event');

        $categories = Category::pluck('name', 'id');

        foreach ($events as $event) {
            $totalParticipants = $countsByEvent->has($event->id)
                ? $countsByEvent->get($event->id) : 0;
            $event->individual_cost = $totalParticipants > 0
                ? ($event->share_cost ? $event->cost / $totalParticipants : $event->cost) : 0;

            $participants = isset($participantsByEvent[$event->id])
                ? User::whereIn('id', $participantsByEvent[$event->id])->select('name', 'image_path', 'id')->get()
                : collect();
            $event->participants = $participants;

            $categoryId = $event->id_category;
            $event->category_name = $categories[$categoryId];
        }

        return response()->json($events, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $idTrip, Request $request): JsonResponse
    {
        $trip = Trip::findOrFail($idTrip);
        $this->authorize('isParticipant', $trip);

        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->toJson()], 400);
        }

        $eventCreated = Event::create([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'destination' => $request->get('destination'),
            'start_datetime' => $request->get('startDatetime'),
            'end_datetime' => $request->get('endDatetime'),
            'cost' => $request->get('cost'),
            'share_cost' => $request->get('shareCost'),
            'id_category' => $request->get('idCategory'),
            'id_trip' => $idTrip,
        ]);

        $user = JWTAuth::parseToken()->authenticate();

        EventParticipant::create([
            'id_event' => $eventCreated->id,
            'id_user' => $user->id,
        ]);

        return response()->json($eventCreated, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $idTrip, string $idEvent): JsonResponse
    {
        $trip = Trip::findOrFail($idTrip);
        $this->authorize('isParticipant', $trip);

        $event = Event::findOrFail($idEvent);

        return response()->json($event, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $idTrip, string $idEvent, Request $request): JsonResponse
    {
        $trip = Trip::findOrFail($idTrip);
        $event = Event::findOrFail($idEvent);

        $this->authorize('isParticipant', $trip);

        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->toJson()], 400);
        }

        $event->update([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'destination' => $request->get('destination'),
            'start_datetime' => $request->get('startDatetime'),
            'end_datetime' => $request->get('endDatetime'),
            'cost' => $request->get('cost'),
            'share_cost' => $request->get('shareCost'),
            'id_category' => $request->get('idCategory'),
        ]);

        return response()->json($event, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $idTrip, string $idEvent): JsonResponse
    {
        $trip = Trip::findOrFail($idTrip);
        $event = Event::findOrFail($idEvent);

        $this->authorize('isParticipant', $trip);

        EventParticipant::where('id_event', $idEvent)->delete();
        $event->delete();

        return response()->json(['message' => 'Evento apagado com sucesso!'], 200);
    }

    public function joinEvent(string $idTrip, string $idEvent): JsonResponse
    {
        $trip = Trip::findOrFail($idTrip);
        $event = Event::findOrFail($idEvent);

        $this->authorize('isParticipant', $trip);

        $user = JWTAuth::parseToken()->authenticate();

        $eventParticipant = EventParticipant::where('id_event', $event->id)->where('id_user', $user->id)->count();

        if ($eventParticipant > 0) {
            return response()->json(['message' => 'Você já está participando do evento.'], 400);
        }

        EventParticipant::create([
            'id_event' => $event->id,
            'id_user' => $user->id,
        ]);

        return response()->json(['message' => 'Sua participação no evento foi confirmada com sucesso.'], 200);
    }

    public function leaveEvent(string $idTrip, string $idEvent): JsonResponse
    {
        $trip = Trip::findOrFail($idTrip);
        $event = Event::findOrFail($idEvent);

        $this->authorize('isParticipant', $trip);

        $user = JWTAuth::parseToken()->authenticate();

        $eventParticipant = EventParticipant::where('id_event', $event->id)->where('id_user', $user->id);

        if ($eventParticipant->count() == 0) {
            return response()->json(['message' => 'Você já cancelou sua participação no evento.'], 400);
        }

        $eventParticipant->delete();

        return response()->json(['message' => 'Sua participação no evento foi cancelada com sucesso.'], 200);
    }

    protected function validateRequest(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'title' => 'required|string|between:2,100',
            'description' => 'sometimes|string|max:100|nullable',
            'destination' => 'required|string|max:100',
            'startDatetime' => 'required|date_format:Y-m-d H:i|after:today',
            'endDatetime' => 'required|date_format:Y-m-d H:i|after:startDatetime',
            'cost' => 'required|decimal:0,2|min:0',
            'shareCost' => 'required|boolean',
            'idCategory' => 'required|numeric|min:1|max:7'
        ], [
            'title.between' => 'O título deve ter entre 2 e 100 caracteres.',
            'description.max' => 'O destino não pode ter mais de 100 caracteres.',
            'destination.max' => 'O destino não pode ter mais de 100 caracteres.',
            'startDatetime.after' => 'A data de início deve ser posterior à data atual.',
            'endDatetime.after' => 'A data de término deve ser igual ou após a data de início.',
            'cost.min' => 'O valor do custo do evento não pode ser menor que 0.',
            'idCategory.min' => 'Categoria inválida.',
            'idCategory.max' => 'Categoria inválida.',
        ]);
    }
}
