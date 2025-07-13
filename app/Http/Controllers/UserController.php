<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function saveFcmToken(Request $request)
{
    $request->validate([
        'fcm_token' => 'required|string'
    ]);

    $user = Auth::user();
    $user->fcm_token = $request->fcm_token;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Token FCM sauvegardé avec succès'
    ]);
}
}
