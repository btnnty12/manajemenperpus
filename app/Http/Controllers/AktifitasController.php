<?php

namespace App\Http\Controllers;

use App\Models\aktifitas;

class AktifitasController extends Controller
{
    public function destroy($id)
    {
        $aktifitas = aktifitas::findOrFail($id);
        $aktifitas->delete();

        return redirect()->back()->with('success', 'Aktifitas berhasil dihapus');
    }
}
