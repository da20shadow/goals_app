<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user_id = auth()->user()->getAuthIdentifier();
        $profile = User::where('id',$user_id)->first();
        return response()->json([
            'username' => $profile['username'],
            'email' => $profile['email'],
        ]);
    }

    /**
     * Display the specified resource.
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $publicProfile = User::where('id',$id)->first();
        return response()->json([
            'username' => $publicProfile['username']
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $user_id = auth()->user()->getAuthIdentifier();

        $fields = $request->all();

        if (isset($fields['new_email'])){
            $inputEmail = $request->validate(['new_email' => ['unique:users,email','email']]);
            User::where('id',$user_id)
                ->update(['email' => $inputEmail['email']]);
            return response()->json(['message' => 'Successfully updated email!']);
        }

        if (isset($fields['new_password'])){
            $inputPassword = $request->validate(['new_password' => ['min:8','max:75','string','confirmed']]);
            User::where('id',$user_id)
                ->update(['password' => bcrypt($inputPassword['new_password'])]);
            return response()->json(['message' => 'Successfully updated password!']);
        }

        return response()->json(['message' => 'An error occur! Please, try again!']);
    }

    /**
     * Remove the specified resource from storage.
     * @return JsonResponse
     */
    public function destroy(): JsonResponse
    {
        $user_id = auth()->user()->getAuthIdentifier();
        User::where('id',$user_id)
            ->delete();

        return response()->json(['message' => 'Successfully Deleted Account!']);
    }
}
