<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsEnseignant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isEnseignant()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Réservé aux enseignants.',
            ], 403);
        }

        return $next($request);
    }
}
