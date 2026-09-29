<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            return response()->json(['message' => 'Non authentifie.'], 401);
        }

        if (! $user->hasRole(...$roles)) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        return $next($request);
    }
}