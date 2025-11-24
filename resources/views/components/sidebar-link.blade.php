@props(['href' => '#', 'active' => false, 'icon' => null])

@php
$baseClasses = 'flex items-center w-full py-2 px-4 text-sm font-medium rounded-md transition-colors duration-200 ease-in-out';
$activeClasses = 'border-l-4 border-green-500 text-green-700 bg-green-50';
$inactiveClasses = 'text-gray-700 hover:bg-gray-100 hover:text-gray-900';

$classes = $active ? $activeClasses : $inactiveClasses;
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses . ' ' . $classes]) }}>
    @if ($icon)
        <i class="{{ $icon }} mr-3"></i>
    @endif
    {{ $slot }}
</a>
