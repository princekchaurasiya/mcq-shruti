<button {{ $attributes->merge(['type' => 'submit', 'class' => 'login-btn']) }}>
    {{ $slot }}
</button>
