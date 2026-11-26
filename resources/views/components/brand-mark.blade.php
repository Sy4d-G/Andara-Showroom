@props(['theme' => 'light', 'storeName' => 'Andara Motor'])

@php
    $isDark = $theme === 'dark';
@endphp

<span class="inline-flex items-center">
    <img
        src="{{ asset('images/logo/Andara Motor Speed Logo.png') }}"
        alt="{{ $storeName }}"
        width="2172"
        height="724"
        decoding="async"
        class="{{ $isDark ? 'h-9 w-auto rounded bg-white px-2.5 py-1 object-contain' : 'h-10 w-auto object-contain sm:h-11' }}"
    >
</span>
