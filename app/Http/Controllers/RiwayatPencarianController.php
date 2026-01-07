<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LogPencarian;

class RiwayatPencarianController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['data' => []], 401);
        }

        $riwayat = LogPencarian::where('pengguna_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'keyword' => $item->kata_kunci,
                    'created_at' => $item->created_at->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'data' => $riwayat,
        ]);
    }

    public function clear()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        LogPencarian::where('pengguna_id', $user->id)->delete();

        return response()->json([
            'message' => 'Riwayat pencarian berhasil dihapus',
        ]);
    }
}
