<img
    src="{{ asset('images/app_logo.png') }}"
    alt="{{ config('app.name', 'Madiun Terpadu') }}"
    {{ $attributes->merge(['class' => 'rounded-full object-cover']) }}
    onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='block';"
/>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;" {{ $attributes }}>
    <path d="M7 19H4.815a1.83 1.83 0 0 1-1.57-.881 1.785 1.785 0 0 1-.004-1.784L7.196 9.5" />
    <path d="M11 19h8.203a1.83 1.83 0 0 0 1.556-.89 1.784 1.784 0 0 0 0-1.775l-1.226-2.12" />
    <path d="m14 16-3 3 3 3" />
    <path d="M8.294 4.887a1.83 1.83 0 0 1 1.57-.887h4.272a1.83 1.83 0 0 1 1.57.887l3.955 6.848" />
    <path d="m17 7-3-3 3-3" />
    <path d="m2 12 3 3 3-3" />
</svg>
