@props(['name', 'class' => 'w-6 h-6', 'stroke' => 'currentColor'])

@php
    $icons = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l9.5-9.5 9.5 9.5M4.5 9.75v10.5a.75.75 0 00.75.75H9.75v-6h4.5v6h4.5a.75.75 0 00.75-.75V9.75"/>',
        'anggota' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0v.75H4.5v-.75z"/>',
        'buku' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75A2.25 2.25 0 016.75 4.5h9a2.25 2.25 0 012.25 2.25v12.75H6.75A2.25 2.25 0 014.5 17.25V6.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75h9M9 9.75h9"/>',
        'grafik' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7.5 15l3-6 3 4.5 3-7.5"/>',
        'user' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0v.75H4.5v-.75z"/>',
        'setting' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317a1.25 1.25 0 012.35 0l.29.87a1.25 1.25 0 001.17.86h.92a1.25 1.25 0 011.16.83l.3.93a1.25 1.25 0 00.59.7l.8.46a1.25 1.25 0 01.45 1.7l-.45.78a1.25 1.25 0 000 1.23l.45.78a1.25 1.25 0 01-.45 1.7l-.8.46a1.25 1.25 0 00-.59.7l-.3.93a1.25 1.25 0 01-1.16.83h-.92a1.25 1.25 0 00-1.17.86l-.29.87a1.25 1.25 0 01-2.35 0l-.29-.87a1.25 1.25 0 00-1.17-.86h-.92a1.25 1.25 0 01-1.16-.83l-.3-.93a1.25 1.25 0 00-.59-.7l-.8-.46a1.25 1.25 0 01-.45-1.7l.45-.78a1.25 1.25 0 000-1.23l-.45-.78a1.25 1.25 0 01.45-1.7l.8-.46a1.25 1.25 0 00.59-.7l.3-.93a1.25 1.25 0 011.16-.83h.92a1.25 1.25 0 001.17-.86l.29-.87z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5z"/>',
        'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 9l3-3m0 0l3 3m-3-3v12"/>',
        'email' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 016 4.5h12a2.25 2.25 0 012.25 2.25v10.5A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25V6.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5l7.5 6 7.5-6"/>',
        'notification' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.25 18.75a2.25 2.25 0 11-4.5 0"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 8.25a6 6 0 1112 0c0 1.848.5 3.663 1.452 5.242.45.75-.078 1.758-.984 1.758H5.532c-.906 0-1.434-1.008-.984-1.758A10.45 10.45 0 006 8.25z"/>',
        'arrow-down' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25L12 15.75 4.5 8.25"/>',
        'search' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18.75a7.5 7.5 0 006.15-3.1z"/>',
        'edit' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a1.875 1.875 0 112.651 2.651L7.132 18.52a4.5 4.5 0 01-1.897 1.13l-2.26.677.678-2.26a4.5 4.5 0 011.13-1.897L16.862 3.487z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L16.875 4.5"/>',
        'delete' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12M9.75 7.5V5.25A1.5 1.5 0 0111.25 3.75h1.5a1.5 1.5 0 011.5 1.5V7.5m-7.5 0h9l-.72 11.09a1.5 1.5 0 01-1.5 1.41H8.22a1.5 1.5 0 01-1.5-1.41L6 7.5z"/>',
        'reminder' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'book-open' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c-1.148-.717-2.618-1.125-4.125-1.125-1.507 0-2.977.408-4.125 1.125v11.25c1.148-.717 2.618-1.125 4.125-1.125 1.507 0 2.977.408 4.125 1.125V6.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c1.148-.717 2.618-1.125 4.125-1.125 1.507 0 2.977.408 4.125 1.125v11.25c-1.148-.717-2.618-1.125-4.125-1.125-1.507 0-2.977.408-4.125 1.125V6.75z"/>',
    ];

    $svg = $icons[$name] ?? $icons['home'];
@endphp

<svg {{ $attributes->merge(['class' => $class]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $stroke }}" stroke-width="1.8">
    {!! $svg !!}
</svg>

