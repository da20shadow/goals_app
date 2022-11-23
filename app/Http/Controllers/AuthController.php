<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'username' => ['required','string','min:3','max:45','unique:users,username'],
            'email' => ['required','unique:users,email','email'],
            'password' => ['required','min:8','max:75','string','confirmed'],
        ]);

        $user = User::create([
            'username' => $fields['username'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
        ]);

        $token = $user->createToken('laravelGoalsAppAccessToken')->plainTextToken;

        return response()->json(['user' => $user,'token' => $token],201);
    }

    public function login(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','min:8','string'],
        ]);
        //Check Email
        $user = User::where('email',$fields['email'])->first();
        //Check password
        if (!$user || !Hash::check($fields['password'],$user->password)) {
            return response()->json(['message'=>'Bad credentials!'],401);
        }

        $token = $user->createToken('laravelGoalsAppAccessToken')->plainTextToken;

        return response()->json(['user' => $user,'token' => $token],201);
    }

    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();
        return response()->json(['user' => 'Successfully logged out']);
    }
}
