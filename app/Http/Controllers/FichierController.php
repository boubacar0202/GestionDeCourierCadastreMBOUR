<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FichierController extends Controller
{
    public function show(string $path): StreamedResponse
    {
        $path = str_replace('\\', '/', $path);

        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(404);
        }

        $autorise = false;
        foreach (['courrierarrivee/', 'courrierdepart/', 'service/', 'lotissement/', 'pointDensifier/', 'numerisation/', 'mutations/'] as $prefixe) {
            if (str_starts_with($path, $prefixe)) {
                $autorise = true;
                break;
            }
        }

        $racine = realpath(storage_path('app/public'));
        $complet = realpath(storage_path('app/public/'.$path));

        if (
            ! $autorise
            || $racine === false
            || $complet === false
            || ! str_starts_with($complet, $racine.DIRECTORY_SEPARATOR)
            || ! Storage::disk('public')->exists($path)
        ) {
            abort(404);
        }

        return Storage::disk('public')->response($path, basename($complet), [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
