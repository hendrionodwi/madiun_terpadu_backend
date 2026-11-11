<x-layouts::app :title="__('Siaran Pengumuman Portal')">
    @php
        $fieldClass = 'mt-1 min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
        $labelClass = 'text-xs font-bold text-slate-700 dark:text-slate-300';
    @endphp

    <div class="civic-page flex flex-col gap-5 p-2 md:p-4">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-civic-700 dark:text-civic-300">Konten publik</p>
                <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">Pengumuman</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Publikasikan informasi layanan tanpa memadati dashboard utama.</p>
            </div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="civic-button-secondary self-start sm:self-auto">
                <flux:icon.arrow-top-right-on-square class="size-4" />
                Lihat portal
            </a>
        </header>

        <x-admin.content-tabs :summary="$cmsSummary" />
        <x-admin.content-feedback />

        <div class="grid items-start gap-5 xl:grid-cols-[22rem_minmax(0,1fr)]">
            <section class="civic-card p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <span class="civic-icon-tile"><flux:icon.megaphone class="size-5" /></span>
                    <div>
                        <h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Buat pengumuman</h2>
                        <p class="text-xs text-slate-500">Isi singkat, tujuan jelas.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('dashboard.admin.broadcasts.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="title" class="{{ $labelClass }}">Judul</label>
                        <input id="title" name="title" value="{{ old('title') }}" required maxlength="200" class="{{ $fieldClass }}" placeholder="Penyesuaian jadwal penjemputan">
                    </div>
                    <div>
                        <label for="summary" class="{{ $labelClass }}">Ringkasan</label>
                        <textarea id="summary" name="summary" rows="2" maxlength="500" class="{{ $fieldClass }} py-3" placeholder="Inti informasi dalam satu kalimat">{{ old('summary') }}</textarea>
                    </div>
                    <div>
                        <label for="content" class="{{ $labelClass }}">Isi pengumuman</label>
                        <textarea id="content" name="content" rows="5" required class="{{ $fieldClass }} py-3" placeholder="Tuliskan informasi yang perlu diketahui warga">{{ old('content') }}</textarea>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <div>
                            <label for="kategori" class="{{ $labelClass }}">Kategori</label>
                            <select id="kategori" name="kategori" class="{{ $fieldClass }}">
                                @foreach (['informasi' => 'Informasi', 'jadwal' => 'Jadwal', 'himbauan' => 'Imbauan', 'penting' => 'Penting'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('kategori', 'informasi') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="priority" class="{{ $labelClass }}">Prioritas</label>
                            <select id="priority" name="priority" required class="{{ $fieldClass }}">
                                @foreach (['low' => 'Rendah', 'normal' => 'Normal', 'high' => 'Tinggi', 'urgent' => 'Mendesak'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="audience_role" class="{{ $labelClass }}">Audiens</label>
                            <select id="audience_role" name="audience_role" required class="{{ $fieldClass }}">
                                @foreach (['all' => 'Semua pengguna', 'warga' => 'Warga', 'petugas_lapangan' => 'Petugas lapangan', 'pengelola_tps3r' => 'Pengelola TPS3R', 'pemrakarsa' => 'Pemrakarsa'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('audience_role', 'all') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="status" class="{{ $labelClass }}">Status</label>
                            <select id="status" name="status" class="{{ $fieldClass }}">
                                @foreach (['draft' => 'Draf', 'scheduled' => 'Terjadwal', 'published' => 'Publikasikan', 'archived' => 'Arsip'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'published') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <div>
                            <label for="published_at" class="{{ $labelClass }}">Mulai tayang</label>
                            <input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at') }}" class="{{ $fieldClass }}">
                        </div>
                        <div>
                            <label for="expires_at" class="{{ $labelClass }}">Berakhir</label>
                            <input id="expires_at" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="{{ $fieldClass }}">
                        </div>
                    </div>
                    <label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-300">
                        <input type="hidden" name="show_on_portal" value="0">
                        <input type="checkbox" name="show_on_portal" value="1" @checked(old('show_on_portal', true)) class="size-4 rounded border-slate-300 text-civic-700 focus:ring-civic-600">
                        Tampilkan di portal publik
                    </label>
                    <button type="submit" class="civic-button-primary w-full">
                        <flux:icon.paper-airplane class="size-4" /> Simpan pengumuman
                    </button>
                </form>
            </section>

            <section id="broadcastList" data-ajax-zone="admin-broadcasts" class="civic-card overflow-hidden">
                <div class="civic-section-header">
                    <div>
                        <h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Daftar pengumuman</h2>
                        <p class="text-xs text-slate-500">{{ $broadcasts->total() }} konten tersimpan</p>
                    </div>
                    <span class="civic-status-success">{{ $cmsSummary['active_announcements'] ?? 0 }} aktif</span>
                </div>
                <div class="space-y-3 p-4">
                    @forelse ($broadcasts as $announcement)
                        @php
                            $statusClass = match ($announcement->status) {
                                'published' => 'civic-status-success',
                                'scheduled' => 'civic-status-info',
                                'archived' => 'civic-status-danger',
                                default => 'civic-status-warning',
                            };
                            $statusLabel = match ($announcement->status) {
                                'published' => 'Terbit',
                                'scheduled' => 'Terjadwal',
                                'archived' => 'Arsip',
                                default => 'Draf',
                            };
                        @endphp
                        <article class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $announcement->kategori ?? 'informasi' }}</span>
                                        @if ($announcement->show_on_portal)
                                            <span class="rounded-full bg-civic-50 px-2.5 py-1 text-xs font-bold text-civic-800 dark:bg-civic-950/50 dark:text-civic-200">Portal</span>
                                        @endif
                                    </div>
                                    <h3 class="mt-3 font-display text-base font-extrabold text-slate-950 dark:text-white">{{ $announcement->title }}</h3>
                                    <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $announcement->summary ?: $announcement->content }}</p>
                                    <p class="mt-2 text-xs text-slate-500">{{ $announcement->creator->name ?? 'Admin DLH' }} · {{ ($announcement->published_at ?? $announcement->created_at)?->translatedFormat('d M Y, H:i') }} WIB</p>
                                </div>
                                <form method="POST" action="{{ route('dashboard.admin.broadcasts.toggle', $announcement) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="civic-button-secondary whitespace-nowrap px-3 py-2 text-xs">
                                        <flux:icon.power class="size-4" /> {{ $announcement->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                            <details class="mt-4 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                                <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between px-3 text-sm font-bold text-civic-800 dark:text-civic-200">
                                    Edit pengumuman <flux:icon.chevron-down class="size-4" />
                                </summary>
                                <div class="border-t border-slate-200 p-3 dark:border-slate-700">
                                    <form method="POST" action="{{ route('dashboard.admin.broadcasts.update', $announcement) }}" class="grid gap-3 md:grid-cols-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="md:col-span-2"><label class="{{ $labelClass }}">Judul</label><input name="title" value="{{ $announcement->title }}" required maxlength="200" class="{{ $fieldClass }}"></div>
                                        <div class="md:col-span-2"><label class="{{ $labelClass }}">Ringkasan</label><textarea name="summary" rows="2" maxlength="500" class="{{ $fieldClass }} py-3">{{ $announcement->summary }}</textarea></div>
                                        <div class="md:col-span-2"><label class="{{ $labelClass }}">Isi</label><textarea name="content" rows="4" required class="{{ $fieldClass }} py-3">{{ $announcement->content }}</textarea></div>
                                        <div><label class="{{ $labelClass }}">Kategori</label><select name="kategori" class="{{ $fieldClass }}">@foreach (['informasi' => 'Informasi', 'jadwal' => 'Jadwal', 'himbauan' => 'Imbauan', 'penting' => 'Penting'] as $value => $label)<option value="{{ $value }}" @selected(($announcement->kategori ?? 'informasi') === $value)>{{ $label }}</option>@endforeach</select></div>
                                        <div><label class="{{ $labelClass }}">Status</label><select name="status" class="{{ $fieldClass }}">@foreach (['draft' => 'Draf', 'scheduled' => 'Terjadwal', 'published' => 'Terbit', 'archived' => 'Arsip'] as $value => $label)<option value="{{ $value }}" @selected($announcement->status === $value)>{{ $label }}</option>@endforeach</select></div>
                                        <div><label class="{{ $labelClass }}">Audiens</label><select name="audience_role" class="{{ $fieldClass }}">@foreach (['all' => 'Semua pengguna', 'warga' => 'Warga', 'petugas_lapangan' => 'Petugas lapangan', 'pengelola_tps3r' => 'Pengelola TPS3R', 'pemrakarsa' => 'Pemrakarsa'] as $value => $label)<option value="{{ $value }}" @selected(($announcement->audience_role ?? 'all') === $value)>{{ $label }}</option>@endforeach</select></div>
                                        <div><label class="{{ $labelClass }}">Prioritas</label><select name="priority" class="{{ $fieldClass }}">@foreach (['low' => 'Rendah', 'normal' => 'Normal', 'high' => 'Tinggi', 'urgent' => 'Mendesak'] as $value => $label)<option value="{{ $value }}" @selected($announcement->priority === $value)>{{ $label }}</option>@endforeach</select></div>
                                        <div><label class="{{ $labelClass }}">Mulai tayang</label><input type="datetime-local" name="published_at" value="{{ $announcement->published_at?->format('Y-m-d\TH:i') }}" class="{{ $fieldClass }}"></div>
                                        <div><label class="{{ $labelClass }}">Berakhir</label><input type="datetime-local" name="expires_at" value="{{ $announcement->expires_at?->format('Y-m-d\TH:i') }}" class="{{ $fieldClass }}"></div>
                                        <label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-200 px-3 text-sm font-semibold dark:border-slate-700 md:self-end"><input type="hidden" name="show_on_portal" value="0"><input type="checkbox" name="show_on_portal" value="1" @checked($announcement->show_on_portal) class="size-4 rounded border-slate-300 text-civic-700 focus:ring-civic-600"> Tampilkan di portal</label>
                                        <div class="md:col-span-2"><button type="submit" class="civic-button-primary">Simpan perubahan</button></div>
                                    </form>
                                    <form method="POST" action="{{ route('dashboard.admin.broadcasts.destroy', $announcement) }}" class="mt-3" onsubmit="return confirm('Hapus pengumuman ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex min-h-11 items-center gap-2 rounded-xl px-3 text-sm font-bold text-red-700 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-950/30"><flux:icon.trash class="size-4" /> Hapus pengumuman</button>
                                    </form>
                                </div>
                            </details>
                        </article>
                    @empty
                        <div class="civic-empty">Belum ada pengumuman. Buat konten pertama dari formulir.</div>
                    @endforelse
                </div>
                @if ($broadcasts->hasPages())
                    <div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $broadcasts->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-layouts::app>
