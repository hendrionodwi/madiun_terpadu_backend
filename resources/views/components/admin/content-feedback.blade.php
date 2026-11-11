@if (session('status'))
    <div class="civic-alert-success" role="status">
        <flux:icon.check-circle class="size-5 shrink-0" />
        <span>{{ session('status') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="civic-alert-danger" role="alert">
        <flux:icon.exclamation-triangle class="mt-0.5 size-5 shrink-0" />
        <div>
            <p class="font-bold">Periksa kembali data berikut:</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
