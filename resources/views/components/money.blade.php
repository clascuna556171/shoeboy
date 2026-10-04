@props(['value' => 0])

<span {{ $attributes }}>₱{{ number_format((float) $value, 2) }}</span>
