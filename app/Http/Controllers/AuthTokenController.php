<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthTokenController extends Controller
{
    /**
     * Create a new API token for the authenticated user.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], Response::HTTP_UNAUTHORIZED);
        }
        $token = $request->user()->createToken('api-token');

        return response()->json([
            'token' => $token->plainTextToken
        ]);
    }

    /**
     * Revoke the user's API token.
     */
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
