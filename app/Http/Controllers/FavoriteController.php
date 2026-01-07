<?php

namespace App\Http\Controllers;

class FavoriteController extends Controller
{
    public function index()
    {
        $books = \App\Models\Buku::dummyData();
        $favorites = session('favorites', []);

        $favoriteBooks = [];
        foreach ($favorites as $slug) {
            if (isset($books[$slug])) {
                $favoriteBooks[] = $books[$slug];
            }
        }

        return view('favorit', [
            'favorites' => $favorites,
            'favoriteBooks' => $favoriteBooks,
        ]);
    }

    public function toggle(string $slug)
    {
        $books = \App\Models\Buku::dummyData();
        if (! isset($books[$slug])) {
            abort(404);
        }

        $favorites = session()->get('favorites', []);

        if (in_array($slug, $favorites, true)) {
            $favorites = array_values(array_diff($favorites, [$slug]));
            session()->put('favorites', $favorites);

            return redirect()->back()->with('success', 'Buku dihapus dari favorit!');
        }

        $favorites[] = $slug;
        session()->put('favorites', $favorites);

        return redirect()->back()->with('success', 'Buku ditambahkan ke favorit!');
    }
}
