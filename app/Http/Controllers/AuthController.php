<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function googleLogin(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            // Verify Google token
            $response = Http::get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'access_token' => $request->token,
            ]);

            if (!$response->successful()) {
                return response()->json(['error' => 'Invalid Google token'], 401);
            }

            $googleUser = $response->json();

            // Create or update user
            $user = User::updateOrCreate(
                ['google_id' => $googleUser['sub']],
                [
                    'name' => $googleUser['name'] ?? '',
                    'email' => $googleUser['email'],
                    'avatar' => $googleUser['picture'] ?? null,
                ]
            );

            // Create API token
            $token = $user->createToken('chrome-extension')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication failed'], 500);
        }
    }

    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true]);
    }
}
