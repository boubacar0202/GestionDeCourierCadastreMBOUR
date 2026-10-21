<?php

namespace App\Http\Controllers;

use App\Models\CodeAcces;
use Illuminate\Http\Request;

class CodeAccesController extends Controller
{
    //
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:100',
        ]);

        if (CodeAcces::correspond((string) $request->input('code'))) {
            return response()->json([
                'exists' => true,
                'success' => 'Code confirmé.',
            ]);
        }

        return response()->json([
            'exists' => false,
            'error' => 'Code invalide.'
        ]);
    }
}
