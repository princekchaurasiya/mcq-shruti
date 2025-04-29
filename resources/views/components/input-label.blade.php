@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium mb-2']) }}>
    {{ $value ?? $slot }}
</label>
