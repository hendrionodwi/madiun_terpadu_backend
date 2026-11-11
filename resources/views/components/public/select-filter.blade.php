@props([
    'name',
    'value' => '',
    'options' => [],
    'placeholder' => 'Pilih opsi',
    'autoSubmit' => true,
    'heightClass' => 'h-11',
    'alignRight' => false,
])

@php
    $normalizedOptions = collect($options)
        ->map(function ($option, $key): array {
            if (is_array($option)) {
                return [
                    'value' => (string) ($option['value'] ?? ''),
                    'label' => (string) ($option['label'] ?? ''),
                ];
            }

            return [
                'value' => (string) $key,
                'label' => (string) $option,
            ];
        })
        ->values()
        ->all();

    $stringValue = (string) $value;
    $initialMatch = collect($normalizedOptions)->firstWhere('value', $stringValue);
    $initialLabel = $initialMatch['label'] ?? $placeholder;
@endphp

<div
    x-data="{
        open: false,
        selected: @js($stringValue),
        options: @js($normalizedOptions),
        placeholder: @js($placeholder),
        autoSubmit: @js((bool) $autoSubmit),
        get label() {
            const match = this.options.find((item) => item.value === this.selected);
            return match ? match.label : this.placeholder;
        },
        choose(val) {
            this.selected = val;
            this.open = false;
            if (this.autoSubmit) {
                this.$nextTick(() => {
                    const form = this.$refs.hiddenInput.form;
                    if (!form) {
                        return;
                    }
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                });
            }
        }
    }"
    x-on:select-filter-sync.window="if ($event.detail && $event.detail.name === @js($name)) { selected = String($event.detail.value ?? ''); }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
    data-select-filter
    :class="{ 'z-40': open, 'z-10': !open }"
    {{ $attributes->class('relative w-full') }}
>
    <input x-ref="hiddenInput" type="hidden" name="{{ $name }}" :value="selected">

    <button
        type="button"
        x-on:click="open = !open"
        aria-haspopup="listbox"
        :aria-expanded="open"
        :class="open ? 'is-open' : (selected !== '' ? 'is-selected' : '')"
        class="civic-select-trigger {{ $heightClass }} {{ str_contains($heightClass, 'text-') ? '' : 'text-sm' }} flex w-full cursor-pointer items-center justify-between gap-2 rounded-xl border px-3.5 text-left font-medium shadow-2xs transition focus:outline-none"
    >
        <span class="truncate" x-text="label">{{ $initialLabel }}</span>
        <svg
            class="size-4 shrink-0 text-slate-400 transition-transform duration-200"
            :class="{ 'rotate-180 text-civic-700': open }"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            aria-hidden="true"
        >
            <path d="m6 9 6 6 6-6" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-[0.98]"
        role="listbox"
        class="civic-select-menu {{ $alignRight ? 'right-0' : 'left-0' }} absolute top-full z-50 mt-2 max-h-64 w-full min-w-[13.5rem] overflow-y-auto rounded-2xl border p-1.5 shadow-xl"
    >
        <template x-for="(option, index) in options" :key="option.value + '-' + index">
            <button
                type="button"
                role="option"
                x-on:click="choose(option.value)"
                :aria-selected="selected === option.value"
                :class="selected === option.value
                    ? 'bg-civic-50 font-bold text-civic-800'
                    : 'font-medium text-slate-700 hover:bg-slate-100/80 hover:text-slate-950'"
                class="flex w-full cursor-pointer items-center justify-between gap-2 rounded-xl px-3 py-2 text-left text-sm transition"
            >
                <span class="truncate" x-text="option.label"></span>
                <svg
                    x-show="selected === option.value"
                    class="size-4 shrink-0 text-civic-700"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.4"
                    aria-hidden="true"
                >
                    <path d="m20 6-11 11-5-5" />
                </svg>
            </button>
        </template>
    </div>
</div>
