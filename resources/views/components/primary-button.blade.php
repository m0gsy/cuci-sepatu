<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-brand border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition-colors duration-150']) }}>
    {{ $slot }}
</button>
