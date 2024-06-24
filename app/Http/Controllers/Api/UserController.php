<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function loginUser(LoginRequest $request)
    {
        $credentials = $request->validated(); 
        $user = User::where('phone', $credentials['phone'])->first();
        if (! $user && bcrypt($credentials['password']) === $user->password) {
            
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        Auth::login($user);
        $token = $this->generateNewToken(Auth::user());

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token,

        ], 200);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $user->update($request->validated());

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => new UserResource($user),
        ], 200);
    }

    protected function generateNewToken(User $user)
    {
        if (!config('custom.enable_multiple_logins')) {
            $user->tokens()->delete();
        }

        return $user->createToken(config('app.name'))->accessToken;
    }
}
