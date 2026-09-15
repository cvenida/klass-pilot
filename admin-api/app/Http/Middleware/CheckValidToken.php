<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckValidToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Authorization token is missing or empty.',
                'code' => 401,
            ], 200);
        }

        $tokenParts = explode('.', $token);

        if (count($tokenParts) !== 3) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid token structure.',
                'code' => 401,
            ], 200);
        }

        $payloadJson = base64_decode(strtr($tokenParts[1], '-_', '+/'));
        $payload = json_decode($payloadJson, true);

        if (!$payload) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to decode token payload.',
                'code' => 401,
            ], 200);
        }

        if (isset($payload['exp']) && time() >= $payload['exp']) {
            return response()->json([
                'status' => false,
                'message' => 'Token has expired.',
                'code' => 401,
            ], 200);
        }

        // 2. Extract User ID (supports standard claims 'sub' or custom 'user_id'/'id')
        $userId = $payload['sub'] ?? $payload['user_id'] ?? $payload['id'] ?? null;

        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'User identifier not found in token payload.',
                'code' => 401,
            ], 200);
        }

        // 3. Check if user exists in database
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User associated with token does not exist.',
                'code' => 401,
            ], 200);
        }

        // Authenticate user for current request lifecycle
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}