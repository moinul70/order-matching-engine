<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user details.
     */
    public function index(Request $request): JsonResponse
    {
        // $request->user() retrieves the user via the Sanctum token
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'balance' => (float) $user->balance, // Cast to float for Vue
            'created_at' => $user->created_at,
        ]);
    }
}
