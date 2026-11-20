<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\StoreShippingCourierRequest;
use App\Http\Requests\Web\Admin\UpdateShippingSettingsRequest;
use App\Models\MasterData;
use App\Services\RajaOngkirService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminShippingSettingsController extends Controller
{
    public function index(RajaOngkirService $rajaOngkirService): View
    {
        $databaseSettings = MasterData::query()
            ->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $hasDatabaseSettings = $databaseSettings->isNotEmpty();
        $couriers = $hasDatabaseSettings
            ? $databaseSettings->map(static fn (MasterData $courier): array => [
                'code' => $courier->code,
                'name' => $courier->name,
                'description' => $courier->description,
                'is_active' => $courier->is_active,
            ])->values()->all()
            : $this->fallbackCourierCatalog($rajaOngkirService);

        return view('dashboard.admin.shipping-settings', [
            'couriers' => $couriers,
            'activeCourierCodes' => $rajaOngkirService->allowedCourierCodes(),
            'settingsSource' => $hasDatabaseSettings ? 'database' : 'environment',
        ]);
    }

    public function update(UpdateShippingSettingsRequest $request, RajaOngkirService $rajaOngkirService): RedirectResponse
    {
        /** @var list<string> $selectedCourierCodes */
        $selectedCourierCodes = $request->validated()['couriers'];

        DB::transaction(function () use ($selectedCourierCodes): void {
            $this->materializeFallbackCouriers();

            MasterData::query()
                ->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)
                ->update(['is_active' => false]);
            MasterData::query()
                ->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)
                ->whereIn('code', $selectedCourierCodes)
                ->update(['is_active' => true]);
        });

        $rajaOngkirService->clearCostCache();

        return redirect()
            ->route('dashboard.admin.shipping-settings.index')
            ->with('status', 'Daftar kurir global berhasil diperbarui.');
    }

    public function store(StoreShippingCourierRequest $request, RajaOngkirService $rajaOngkirService): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $this->materializeFallbackCouriers();

            $nextSortOrder = (int) MasterData::query()
                ->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)
                ->max('sort_order') + 1;

            MasterData::query()->create([
                'group' => RajaOngkirService::COURIER_SETTINGS_GROUP,
                'code' => $validated['code'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?: null,
                'unit' => 'courier',
                'is_active' => true,
                'sort_order' => $nextSortOrder,
            ]);
        });

        $rajaOngkirService->clearCostCache();

        return redirect()
            ->route('dashboard.admin.shipping-settings.index')
            ->with('status', 'Kurir baru berhasil ditambahkan dan diaktifkan.');
    }

    public function destroy(string $code, RajaOngkirService $rajaOngkirService): RedirectResponse
    {
        DB::transaction(function () use ($code): void {
            $this->materializeFallbackCouriers();

            $courier = MasterData::query()
                ->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)
                ->where('code', mb_strtolower(trim($code)))
                ->lockForUpdate()
                ->firstOrFail();
            $totalCouriers = MasterData::query()
                ->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)
                ->count();
            $activeCouriers = MasterData::query()
                ->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)
                ->where('is_active', true)
                ->count();

            if ($totalCouriers <= 1 || ($courier->is_active && $activeCouriers <= 1)) {
                throw ValidationException::withMessages([
                    'couriers' => 'Minimal satu kurir aktif harus tetap tersedia.',
                ]);
            }

            $courier->delete();
        });

        $rajaOngkirService->clearCostCache();

        return redirect()
            ->route('dashboard.admin.shipping-settings.index')
            ->with('status', 'Kurir berhasil dihapus dari katalog.');
    }

    /**
     * @return list<array{code: string, name: string, description: string|null, is_active: bool}>
     */
    private function fallbackCourierCatalog(RajaOngkirService $rajaOngkirService): array
    {
        $catalog = RajaOngkirService::courierCatalog();

        foreach (RajaOngkirService::fallbackCourierCodes() as $code) {
            $catalog[$code] ??= [
                'name' => mb_strtoupper(str_replace(['_', '-'], ' ', $code)),
                'description' => 'Kurir tambahan dari konfigurasi awal RajaOngkir.',
            ];
        }

        $activeCourierCodes = $rajaOngkirService->allowedCourierCodes();
        $couriers = [];

        foreach ($catalog as $code => $courier) {
            $couriers[] = [
                'code' => $code,
                'name' => $courier['name'],
                'description' => $courier['description'],
                'is_active' => in_array($code, $activeCourierCodes, true),
            ];
        }

        return $couriers;
    }

    private function materializeFallbackCouriers(): void
    {
        if (MasterData::query()->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)->exists()) {
            return;
        }

        $activeCourierCodes = RajaOngkirService::fallbackCourierCodes();
        $catalog = RajaOngkirService::courierCatalog();

        foreach ($activeCourierCodes as $code) {
            $catalog[$code] ??= [
                'name' => mb_strtoupper(str_replace(['_', '-'], ' ', $code)),
                'description' => 'Kurir tambahan dari konfigurasi awal RajaOngkir.',
            ];
        }

        $sortOrder = 1;
        foreach ($catalog as $code => $courier) {
            MasterData::query()->create([
                'group' => RajaOngkirService::COURIER_SETTINGS_GROUP,
                'code' => $code,
                'name' => $courier['name'],
                'description' => $courier['description'],
                'unit' => 'courier',
                'is_active' => in_array($code, $activeCourierCodes, true),
                'sort_order' => $sortOrder,
            ]);
            $sortOrder++;
        }
    }
}
