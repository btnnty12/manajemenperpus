<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Activity;

class AktivitasController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $activities = Activity::where('pengguna_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('aktivitas', [
            'activities' => $activities,
            'user' => $user,
        ]);
    }

    public function api()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['data' => []], 401);
        }

        $activities = Activity::where('pengguna_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $activities,
        ]);
    }
}
