<?php

namespace App\Http\Controllers\API\Authentication;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['email or password is incorret.'],
            ]);
        }

        $user->tokens()->delete();
        $expiresAt = now()->addMinutes(60);
        $tokenResult = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;
        return ResponseFormatter::success([
            'access_token' => $tokenResult,
            'token_type' => 'Bearer',
            'user' => $user
        ], 'Authenticated');
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'revoked');
    }

    public function user(Request $request)
    {
        $user = User::with('role.permissions')->find($request->user()->id);
        return ResponseFormatter::success($user, 'user');
    }
}
