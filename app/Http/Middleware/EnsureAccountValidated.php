<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountValidated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $roleReconnu = $user && in_array($user->role, ['admin', 'user'], true);

        if (! $user || ! $user->is_validated || ! $roleReconnu) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = ($user && ! $user->is_validated)
                ? 'Votre compte est en attente de validation par un administrateur.'
                : 'Ce compte n\'est pas autorisé. Contactez un administrateur.';

            return redirect()->route('login')->with('status', $message);
        }

        return $next($request);
    }
}
