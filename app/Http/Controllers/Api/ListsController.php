<?php

namespace App\Http\Controllers\Api;

use App\Models\Lists;
use App\Models\Trip;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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

    public function deleteItem(string $idTrip, string $idItem)
    {
        $trip = Trip::findOrFail($idTrip);
        $item = Lists::findOrFail($idItem);

        $this->authorize('isParticipant', $trip);

        $item->delete();

        return response()->json(['message' => 'Item deletado com sucesso'], 200);
    }

    public function checkItem(string $idTrip, string $idItem)
    {
        $trip = Trip::findOrFail($idTrip);
        $item = Lists::findOrFail($idItem);

        $this->authorize('isParticipant', $trip);

        $item->update([
            'is_checked' => !$item->is_checked,
        ]);

        return response()->json($item, 200);
    }
}
