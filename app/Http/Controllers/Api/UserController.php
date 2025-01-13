<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user);
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|between:2,100',
            'password' => 'sometimes|string|confirmed|min:8',
            'image_path' => ['sometimes', 'file', 'max:2000', 'mimes:png,jpeg,jpg'],
        ]);

        if ($request->has('email')) {
            return response()->json(['error' => 'Email cannot be updated.'], 400);
        }

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->toJson()], 400);
        }

        $user->update($validator->validated());

        return response()->json(['user' => $user], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
