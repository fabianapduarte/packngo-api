<?php

namespace App\Http\Controllers\Api;

use App\Models\Lists;
use App\Models\Trip;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ListsController extends Controller
{
    public function getList(string $id_trip)
    {
        $trip = Trip::findOrFail($id_trip);
        $lists = Lists::where('id_trip', $id_trip)->get();
        $this->authorize('isParticipant', $trip);

        return response()->json($lists);
    }

    public function addItem(string $id_trip, Request $request)
    {
        $trip = Trip::findOrFail($id_trip);

        $this->authorize('isParticipant', $trip);

        $validator = Validator::make($request->all(), [
            'item' => 'required|string|max:100'
        ]);
        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->toJson()], 400);
        }

        $newItem = Lists::create([
            'item' => $request->get('item'),
            'is_checked' => false,
            'id_trip' => $trip->id
        ]);

        return response()->json($newItem, 201);
    }

    public function deleteLists(string $id, string $id_trip)
    {
        $trip = Trip::findOrFail($idTrip);
        $user = JWTAuth::parseToken()->authenticate();
        $list = Lists::findOrFail($id);

        $this->authorize('isParticipant', $trip);

        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()->toJson()], 400);
        }

        //  ListsParticipant::where('id_lists', $id)->delete();
        $list->delete();
        return response()->json(['message' => 'Item deletado com sucesso'], 200);
    }
}
