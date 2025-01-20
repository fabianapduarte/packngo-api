<?php

namespace App\Http\Controllers\Api;

use App\Models\Lists;
use App\Models\Trip;
use App\Models\User;
use App\Models\Trip_participant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ListsController extends Controller    
{
    public function getLists(string $id_trip)
    {
        $trip = Trip::findOrFail($id_trip);
        $lists = Lists::where('id_trip', $id_trip)->get();
        $this->authorize('isParticipant', $trip);

        return response()->json($lists);
    }

    public function addLists(Request $request) { 
        $user = JWTAuth::parseToken()->authenticate(); 
        $trip = Trip::findOrFail($request->get('id_trip')); 

        $this->authorize('isParticipant', $trip); 
        $validator = Validator::make($request->all(), [ 
            'title' => 'required|string|max:100', 
            'id_trip' => 'required|exists:trips,id' 
        ]); 
        if ($validator->fails()) { 
            return response()->json(["error" => $validator->errors()->toJson()], 400); 
        } 

        $list = Lists::create([ 
            'title' => $request->get('title'), 
            'is_checked' => false, 
            'id_trip' => $request->get('id_trip') 
        ]); 
        return response()->json(['message' => 'Item adicionado com sucesso'], 200); 
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
