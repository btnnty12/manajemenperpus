<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Pengguna;
use App\Models\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['data' => [], 'unread_count' => 0]);
        }

        $query = Pesan::where(function ($q) use ($user) {
            $q->where('penerima_id', $user->id)
                ->orWhere('pengirim_id', $user->id);
        })->orderBy('created_at', 'desc');

        if ($request->has('only_inbox') && $request->boolean('only_inbox')) {
            $query->where('penerima_id', $user->id);
        }

        $messages = $query->limit(50)->get();

        $unread = Pesan::where('penerima_id', $user->id)->where('dibaca', false)->count();

        return response()->json([
            'data' => $messages,
            'unread_count' => $unread,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (! $user->isAdmin() && ! $user->isStaff()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'penerima_id' => 'required|exists:pengguna,id',
            'isi' => 'required|string',
        ]);

        $penerima = Pengguna::find($validated['penerima_id']);
        if (! $penerima || ! $penerima->isPengguna()) {
            return response()->json(['message' => 'Penerima harus berperan sebagai pengguna'], 422);
        }

        $msg = Pesan::create([
            'pengirim_id' => $user->id,
            'penerima_id' => $validated['penerima_id'],
            'isi' => $validated['isi'],
        ]);

        Notifikasi::create([
            'pengguna_id' => $validated['penerima_id'],
            'judul' => 'Pesan Baru',
            'pesan' => 'Anda menerima pesan baru dari '.$user->nama,
            'tipe' => 'info',
            'link' => '/notifikasi',
        ]);

        return response()->json(['data' => $msg], 201);
    }

    public function markRead($id)
    {
        $userId = Auth::id();
        $msg = Pesan::findOrFail($id);
        if ($msg->penerima_id !== $userId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $msg->update(['dibaca' => true]);

        return response()->json(['message' => 'OK']);
    }

    public function confirm($id)
    {
        $userId = Auth::id();
        $msg = Pesan::findOrFail($id);
        if ($msg->penerima_id !== $userId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $msg->update(['status' => 'confirmed', 'dibaca' => true]);

        Notifikasi::create([
            'pengguna_id' => $msg->pengirim_id,
            'judul' => 'Pesan Dikonfirmasi',
            'pesan' => 'Pesan Anda telah dikonfirmasi oleh '.Auth::user()->nama,
            'tipe' => 'success',
            'link' => '/notifikasi',
        ]);

        return response()->json(['message' => 'OK', 'data' => $msg]);
    }

    public function reply(Request $request, $id)
    {
        $user = Auth::user();
        $parent = Pesan::findOrFail($id);

        if ($parent->penerima_id !== $user->id && $parent->pengirim_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'isi' => 'required|string',
        ]);

        $targetId = $parent->pengirim_id === $user->id ? $parent->penerima_id : $parent->pengirim_id;

        $reply = Pesan::create([
            'pengirim_id' => $user->id,
            'penerima_id' => $targetId,
            'reply_to_id' => $parent->id,
            'isi' => $validated['isi'],
        ]);

        Notifikasi::create([
            'pengguna_id' => $targetId,
            'judul' => 'Balasan Pesan',
            'pesan' => 'Anda menerima balasan dari '.$user->nama,
            'tipe' => 'info',
            'link' => '/notifikasi',
        ]);

        return response()->json(['data' => $reply], 201);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $pesan = Pesan::findOrFail($id);

        // Validasi: hanya pengirim atau penerima yang bisa hapus
        if ($pesan->pengirim_id !== $user->id && $pesan->penerima_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $pesan->delete();

        return response()->json(['message' => 'Pesan berhasil dihapus']);
    }
}
