<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $this->extractApiKey($request);

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key required',
                'message' => 'Please provide a valid API key in the X-API-Key header or api_key parameter',
            ], 401);
        }

        $apiKeyModel = ApiKey::where('key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$apiKeyModel) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'The provided API key is invalid or has been deactivated',
            ], 401);
        }

        // Check origin restrictions
        $origin = $request->header('Origin') ?? $request->header('Referer');
        if ($origin && !$apiKeyModel->isOriginAllowed($origin)) {
            return response()->json([
                'error' => 'Origin not allowed',
                'message' => 'Your domain is not authorized to use this API key',
            ], 403);
        }

        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $origin ?? '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Key, Content-Type');
        }

        // Attach API key to request for later use
        $request->merge(['authenticated_api_key' => $apiKeyModel]);

        // Record usage asynchronously (optional)
        dispatch(function () use ($apiKeyModel) {
            $apiKeyModel->recordUsage();
        })->afterResponse();

        $response = $next($request);

        // Set CORS headers on response
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'X-API-Key, Content-Type');
        }

        return $response;
    }

    /**
     * Extract API key from request
     */
    private function extractApiKey(Request $request): ?string
    {
        // Check header first (preferred method)
        $apiKey = $request->header('X-API-Key');

        // Fallback to query parameter
        if (!$apiKey) {
            $apiKey = $request->query('api_key');
        }

        return $apiKey;
    }
}
