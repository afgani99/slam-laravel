<button {{ $attributes->merge(['type' => 'submit', 'class' => 'slam-primary-btn']) }}>
    {{ $slot }}
</button>
