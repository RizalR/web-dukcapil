@props(['profile'])

<article {{ $attributes->merge(['class' => 'card group overflow-hidden']) }}>
    <div class="relative aspect-[4/5] overflow-hidden bg-slate-100">
        <div class="absolute inset-x-0 top-0 z-10 h-24 bg-gradient-to-b from-ink/10 to-transparent" aria-hidden="true"></div>
        <img
            class="size-full object-cover object-top transition duration-500 group-hover:scale-[1.02]"
            src="{{ $profile->photoUrl() }}"
            alt="Foto resmi {{ $profile->name }}, {{ $profile->role->label() }}"
            loading="lazy"
            decoding="async"
        >
    </div>
    <div class="border-t-4 border-ocean p-5 sm:p-6">
        <p class="text-xs font-bold tracking-[0.14em] text-ocean uppercase">{{ $profile->role->label() }}</p>
        <h3 class="mt-2 text-xl font-bold leading-7 text-ink">{{ $profile->name }}</h3>
    </div>
</article>
