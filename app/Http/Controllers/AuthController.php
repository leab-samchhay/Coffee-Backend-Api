<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login (Request $request){
        $credentail = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        $user = User::where('email',$credentail['email'])->first();

        if(!$user || !Hash::check($credentail['password'],$user->password)){
            return response()->json([
                'message' => 'Invalid Email or password. Received Email: [' . $credentail['email'] . '], Pass: [' . $credentail['password'] . ']',
            ]);
        }

        $token = $user->createToken(
            'minipos-browser',
            ['*'],
            now()->addHour(8),
        )->plainTextToken;

        return response()->json([
            'token' => $token
        ]);
    }

    public function currentUser(Request $request){
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json(
            $request->user()->only(['id', 'name', 'email'])
        );
    }

    // public function logout(Request $request){
    //     if ($request->user()) {
    //         $request->user()->currentAccessToken()->delete();
    //         return response()->json([
    //             'message' => "loagu"
    //         ])->noContent();
    //     }

    //     return response()->json(['message' => 'Unauthenticated.'], 401);
    // }

    public function logout(Request $request){
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => 'Successfully logged out'
            ], 200);
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
