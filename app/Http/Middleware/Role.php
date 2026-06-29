<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowed = collect($roles)
            ->flatMap(fn(string $role) => explode(',', $role))
            ->map(trim(...))
            ->filter()
            ->values()
            ->all();

        if (!$request->user() || !$request->user()->hasRole($allowed)) {
            abort(403, 'Unauthorized access.');
        }
        return $next($request);
    }
}
