@php($title = 'Import Buku - Staff')
<x-staff-layout :title="$title">
    <section class="px-2 sm:px-4 pb-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-[#7c1d0f]">Import Excel Buku</h1>
                <p class="text-sm text-gray-700 mt-1">Unggah file Excel untuk menambah atau memperbarui data buku.</p>
            </div>
            <a href="{{ route('staff.kelola-buku') }}" class="text-sm font-semibold text-[#a63a2d] hover:underline">← Kembali ke daftar</a>
        </div>

        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-3 rounded">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 text-red-800 p-3 rounded">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('import_errors') && count(session('import_errors'))>0)
                <div class="bg-amber-50 text-amber-900 p-3 rounded">
                    <div class="font-semibold mb-1 text-sm">Detail kesalahan:</div>
                    <ul class="list-disc pl-5 text-xs">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('staff.kelola-buku.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="flex flex-col gap-3">
                    <label class="text-sm font-semibold text-gray-700">File Spreadsheet (.csv)</label>
                    <input type="file" name="file" accept=".csv,text/csv" class="w-full border rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-500">Gunakan file CSV dengan header: Judul; Penulis; Kategori; Tahun Terbit; Stok; Deskripsi. Anda bisa mengekspor dari Excel sebagai CSV (semicolon).</p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-[#a63a2d] text-white px-5 py-2 rounded-lg font-semibold hover:brightness-95">Upload</button>
                    <a href="{{ route('staff.kelola-buku.export') }}" class="text-sm text-[#a63a2d] font-semibold hover:underline">Unduh Data Buku (CSV untuk Excel)</a>
                </div>
            </form>
        </div>
    </section>
</x-staff-layout>
