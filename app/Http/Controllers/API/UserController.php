<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\APIServices\Users\GetUser;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;

class UserController extends Controller
{

    public function login(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Try to login user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // 3. Get authenticated user
        $user = Auth::user();

        
        // 4. Create Sanctum token
        $token = $user->createToken('api-token')->plainTextToken;

        $user = GetUser::execute($user->id);

        // 5. Return response
        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out'
        ]);
    }    

    public function me(Request $request)
    {
        return response()->json([
            'user' => GetUser::execute($request->user()->id)
        ]);
    }

    public function register(Request $request)
    {
        // 1. Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|string|max:15',
            'type' =>  'string',
        ]);

        // 2. Create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'type' => $request->type,
        ]);

        // 3. Create the related profile
        if (empty($validated['type']) || $validated['type'] === 'patient') {
            Patient::create([
                'user_id' => $user->id,
            ]);
        } elseif ($validated['type'] === 'doctor') {
            Doctor::create([
                'user_id' => $user->id,
            ]);
        }
        
        // 3. Generate token
        $token = $user->createToken('api-token')->plainTextToken;

        $user = GetUser::execute($user->id);

        // 4. Return response
        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }


}
