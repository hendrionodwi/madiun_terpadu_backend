<?php

namespace App\Services;

use App\Exceptions\RajaOngkirException;
use App\Models\MasterData;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

final class RajaOngkirService
{
    public const COURIER_SETTINGS_GROUP = 'shipping_couriers';

    /**
     * @return array<string, array{name: string, description: string}>
     */
    public static function courierCatalog(): array
    {
        return [
            'jne' => [
                'name' => 'JNE',
                'description' => 'Jalur Nugraha Ekakurir untuk pengiriman antarkota.',
            ],
            'jnt' => [
                'name' => 'J&T Express',
                'description' => 'Layanan reguler J&T Express melalui RajaOngkir.',
            ],
            'sicepat' => [
                'name' => 'SiCepat',
                'description' => 'Pilihan layanan SiCepat yang tersedia pada tujuan.',
            ],
            'anteraja' => [
                'name' => 'AnterAja',
                'description' => 'Layanan pengiriman AnterAja untuk tujuan yang didukung.',
            ],
            'pos' => [
                'name' => 'POS Indonesia',
                'description' => 'Jaringan pengiriman POS Indonesia melalui RajaOngkir.',
            ],
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     label: string,
     *     province_name: string,
     *     city_name: string,
     *     district_name: string,
     *     subdistrict_name: string,
     *     zip_code: string
     * }>
     */
    public function searchDomesticDestinations(string $search, int $limit = 10, int $offset = 0): array
    {
        $normalizedSearch = trim($search);

        if (mb_strlen($normalizedSearch) < 3) {
            throw new InvalidArgumentException('Kata pencarian tujuan minimal 3 karakter.');
        }

        if ($limit < 1 || $limit > 100) {
            throw new InvalidArgumentException('Batas hasil pencarian harus antara 1 dan 100.');
        }

        if ($offset < 0) {
            throw new InvalidArgumentException('Offset pencarian tidak boleh negatif.');
        }

        $this->ensureConfigured();

        $cacheKey = 'rajaongkir:destinations:'.hash('sha256', mb_strtolower($normalizedSearch).'|'.$limit.'|'.$offset);

        return $this->remember(
            $cacheKey,
            (int) config('services.rajaongkir.destination_cache_seconds', 604800),
            function () use ($normalizedSearch, $limit, $offset): array {
                $data = $this->fetchData(fn (): Response => $this->client()->get(
                    'destination/domestic-destination',
                    [
                        'search' => $normalizedSearch,
                        'limit' => $limit,
                        'offset' => $offset,
                    ],
                ));

                return $this->normalizeDestinations($data);
            },
        );
    }

    /**
     * @return list<array{
     *     courier_code: string,
     *     courier_name: string,
     *     service: string,
     *     description: string,
     *     cost: int,
     *     etd: string
     * }>
     */
    public function calculateDomesticCosts(
        int $originId,
        int $destinationId,
        int $weightGrams,
        ?string $couriers = null,
    ): array {
        if ($originId < 1 || $destinationId < 1) {
            throw new InvalidArgumentException('Asal dan tujuan pengiriman wajib dipilih.');
        }

        if ($weightGrams < 1) {
            throw new InvalidArgumentException('Berat pengiriman minimal 1 gram.');
        }

        $allowedCouriers = $this->allowedCourierCodes();
        $requestedCouriers = $couriers === null
            ? $allowedCouriers
            : $this->normalizeCourierCodes($couriers);
        if ($requestedCouriers === [] || array_diff($requestedCouriers, $allowedCouriers) !== []) {
            throw new InvalidArgumentException('Kurir pengiriman tidak valid.');
        }
        $selectedCouriers = implode(':', $requestedCouriers);

        $this->ensureConfigured();

        $cacheKey = 'rajaongkir:costs:'.hash(
            'sha256',
            $originId.'|'.$destinationId.'|'.$weightGrams.'|'.$selectedCouriers,
        );

        return $this->remember(
            $cacheKey,
            (int) config('services.rajaongkir.cost_cache_seconds', 900),
            function () use ($originId, $destinationId, $weightGrams, $selectedCouriers): array {
                $data = $this->fetchData(fn (): Response => $this->client()
                    ->asForm()
                    ->post('calculate/domestic-cost', [
                        'origin' => $originId,
                        'destination' => $destinationId,
                        'weight' => $weightGrams,
                        'courier' => $selectedCouriers,
                    ]));

                return $this->normalizeCosts($data);
            },
        );
    }

    /**
     * @return array{
     *     delivered: bool,
     *     status: string,
     *     description: string,
     *     receiver: string|null,
     *     delivered_at: string|null
     * }
     */
    public function trackWaybill(string $trackingNumber, string $courier, ?string $lastPhoneNumber = null): array
    {
        $normalizedTrackingNumber = trim($trackingNumber);
        $normalizedCourier = mb_strtolower(trim($courier));
        $normalizedPhoneNumber = $lastPhoneNumber === null
            ? null
            : preg_replace('/\D+/', '', $lastPhoneNumber);

        if ($normalizedTrackingNumber === '' || mb_strlen($normalizedTrackingNumber) > 100) {
            throw new InvalidArgumentException('Nomor resi pengiriman tidak valid.');
        }

        if ($normalizedCourier === '' || preg_match('/^[a-z0-9_-]+$/', $normalizedCourier) !== 1) {
            throw new InvalidArgumentException('Kurir pengiriman tidak valid.');
        }

        if ($normalizedPhoneNumber !== null && mb_strlen($normalizedPhoneNumber) !== 5) {
            throw new InvalidArgumentException('Validasi nomor telepon penerima harus berisi 5 digit terakhir.');
        }

        $this->ensureConfigured();

        $parameters = [
            'awb' => $normalizedTrackingNumber,
            'courier' => $normalizedCourier,
        ];

        if ($normalizedPhoneNumber !== null) {
            $parameters['last_phone_number'] = $normalizedPhoneNumber;
        }

        $data = $this->fetchPayload(fn (): Response => $this->client()
            ->withQueryParameters($parameters)
            ->post('track/waybill'));

        return $this->normalizeTracking($data);
    }

    /** @return list<string> */
    public function allowedCourierCodes(): array
    {
        if (! Schema::hasTable((new MasterData)->getTable())) {
            return $this->configuredCourierCodes();
        }

        $databaseSettings = MasterData::query()
            ->where('group', self::COURIER_SETTINGS_GROUP)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['code', 'is_active']);

        if ($databaseSettings->isEmpty()) {
            return $this->configuredCourierCodes();
        }

        return $this->supportedCourierCodes(
            $databaseSettings
                ->where('is_active', true)
                ->pluck('code')
                ->map(static fn (mixed $code): string => mb_strtolower(trim((string) $code)))
                ->values()
                ->all(),
        );
    }

    public function isCourierAllowed(string $courier): bool
    {
        return in_array(mb_strtolower(trim($courier)), $this->allowedCourierCodes(), true);
    }

    private function ensureConfigured(): void
    {
        $enabled = (bool) config('services.rajaongkir.enabled', false);
        $apiKey = trim((string) config('services.rajaongkir.api_key'));
        $baseUrl = trim((string) config('services.rajaongkir.base_url'));

        if (! $enabled || $apiKey === '' || $baseUrl === '') {
            throw RajaOngkirException::notConfigured();
        }
    }

    /** @return list<string> */
    private function normalizeCourierCodes(string $couriers): array
    {
        $normalizedCouriers = mb_strtolower(trim($couriers));
        if ($normalizedCouriers === '' || preg_match('/^[a-z0-9_:-]+$/', $normalizedCouriers) !== 1) {
            return [];
        }

        return array_values(array_unique(array_filter(explode(':', $normalizedCouriers))));
    }

    /**
     * @param  array<array-key, string>  $couriers
     * @return list<string>
     */
    private function supportedCourierCodes(array $couriers): array
    {
        return self::normalizeCourierCodeList($couriers);
    }

    /** @return list<string> */
    private function configuredCourierCodes(): array
    {
        return self::fallbackCourierCodes();
    }

    /** @return list<string> */
    public static function fallbackCourierCodes(): array
    {
        $couriers = mb_strtolower(trim((string) config('services.rajaongkir.couriers', '')));

        if ($couriers === '' || preg_match('/^[a-z0-9_:-]+$/', $couriers) !== 1) {
            return [];
        }

        return self::normalizeCourierCodeList(explode(':', $couriers));
    }

    /**
     * @param  array<array-key, string>  $couriers
     * @return list<string>
     */
    private static function normalizeCourierCodeList(array $couriers): array
    {
        return array_values(array_unique(array_filter(
            array_map(static fn (string $code): string => mb_strtolower(trim($code)), $couriers),
            static fn (string $code): bool => preg_match('/^[a-z0-9_-]+$/', $code) === 1,
        )));
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.rajaongkir.base_url'), '/').'/')
            ->acceptJson()
            ->withHeaders([
                'key' => (string) config('services.rajaongkir.api_key'),
            ])
            ->connectTimeout((int) config('services.rajaongkir.connect_timeout', 3))
            ->timeout((int) config('services.rajaongkir.timeout', 10));
    }

    /**
     * @param  Closure(): Response  $request
     * @return list<array<string, mixed>>
     */
    private function fetchData(Closure $request): array
    {
        $data = $this->fetchPayload($request);

        return array_values(array_filter($data, 'is_array'));
    }

    /**
     * @param  Closure(): Response  $request
     * @return array<string, mixed>
     */
    private function fetchPayload(Closure $request): array
    {
        try {
            $response = $request();
            $response->throw();
        } catch (ConnectionException|RequestException $exception) {
            $statusCode = $exception instanceof RequestException
                ? $exception->response->status()
                : null;

            throw RajaOngkirException::unavailable($statusCode, $exception);
        }

        $data = $response->json('data');
        if (! is_array($data)) {
            throw RajaOngkirException::malformedResponse();
        }

        return $data;
    }

    /**
     * @param  list<array<string, mixed>>  $data
     * @return list<array{
     *     id: int,
     *     label: string,
     *     province_name: string,
     *     city_name: string,
     *     district_name: string,
     *     subdistrict_name: string,
     *     zip_code: string
     * }>
     */
    private function normalizeDestinations(array $data): array
    {
        $destinations = array_map(static fn (array $destination): array => [
            'id' => (int) ($destination['id'] ?? 0),
            'label' => trim((string) ($destination['label'] ?? '')),
            'province_name' => trim((string) ($destination['province_name'] ?? '')),
            'city_name' => trim((string) ($destination['city_name'] ?? '')),
            'district_name' => trim((string) ($destination['district_name'] ?? '')),
            'subdistrict_name' => trim((string) ($destination['subdistrict_name'] ?? '')),
            'zip_code' => trim((string) ($destination['zip_code'] ?? '')),
        ], $data);

        return array_values(array_filter(
            $destinations,
            static fn (array $destination): bool => $destination['id'] > 0 && $destination['label'] !== '',
        ));
    }

    public function clearCostCache(): void
    {
        Cache::flush();
    }

    private function isRelevantRetailService(string $service, string $description): bool
    {
        $normalized = mb_strtoupper($service.' '.$description);

        if (str_contains($normalized, 'DANGEROUS') || str_contains($normalized, 'VALUABLE')) {
            return false;
        }

        if (preg_match('/JTR[<>]/', $normalized) === 1) {
            return false;
        }

        if (mb_strtoupper(trim($service)) === 'DOK') {
            return false;
        }

        return true;
    }

    /**
     * @param  list<array<string, mixed>>  $data
     * @return list<array{
     *     courier_code: string,
     *     courier_name: string,
     *     service: string,
     *     description: string,
     *     cost: int,
     *     etd: string
     * }>
     */
    private function normalizeCosts(array $data): array
    {
        $allowedCouriers = $this->allowedCourierCodes();

        $costs = array_map(static fn (array $cost): array => [
            'courier_code' => mb_strtolower(trim((string) ($cost['code'] ?? ''))),
            'courier_name' => trim((string) ($cost['name'] ?? '')),
            'service' => trim((string) ($cost['service'] ?? '')),
            'description' => trim((string) ($cost['description'] ?? '')),
            'cost' => (int) ($cost['cost'] ?? 0),
            'etd' => trim((string) ($cost['etd'] ?? '')),
        ], $data);

        return array_values(array_filter(
            $costs,
            function (array $cost) use ($allowedCouriers): bool {
                if ($cost['courier_code'] === '' || $cost['service'] === '' || $cost['cost'] < 0) {
                    return false;
                }

                if (! in_array($cost['courier_code'], $allowedCouriers, true)) {
                    return false;
                }

                return $this->isRelevantRetailService($cost['service'], $cost['description']);
            },
        ));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     delivered: bool,
     *     status: string,
     *     description: string,
     *     receiver: string|null,
     *     delivered_at: string|null
     * }
     */
    private function normalizeTracking(array $data): array
    {
        $deliveryStatus = is_array($data['delivery_status'] ?? null) ? $data['delivery_status'] : [];
        $summary = is_array($data['summary'] ?? null) ? $data['summary'] : [];
        $manifest = array_values(array_filter(
            is_array($data['manifest'] ?? null) ? $data['manifest'] : [],
            'is_array',
        ));
        $lastManifest = $manifest === [] ? [] : $manifest[array_key_last($manifest)];
        $status = trim((string) ($deliveryStatus['status'] ?? $summary['status'] ?? ''));
        $podDate = trim((string) ($deliveryStatus['pod_date'] ?? ''));
        $podTime = trim((string) ($deliveryStatus['pod_time'] ?? ''));
        $delivered = filter_var($data['delivered'] ?? false, FILTER_VALIDATE_BOOL)
            || mb_strtoupper($status) === 'DELIVERED';

        return [
            'delivered' => $delivered,
            'status' => $status,
            'description' => trim((string) ($lastManifest['manifest_description'] ?? $status)),
            'receiver' => ($receiver = trim((string) ($deliveryStatus['pod_receiver'] ?? ''))) !== ''
                ? $receiver
                : null,
            'delivered_at' => $podDate !== ''
                ? trim($podDate.' '.$podTime)
                : null,
        ];
    }

    /**
     * @template T of list<array<string, mixed>>
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    private function remember(string $key, int $seconds, Closure $callback): array
    {
        if ($seconds <= 0) {
            return $callback();
        }

        return Cache::remember($key, $seconds, $callback);
    }
}
