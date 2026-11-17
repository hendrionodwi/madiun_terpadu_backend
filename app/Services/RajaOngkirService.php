<?php

namespace App\Services;

use App\Exceptions\RajaOngkirException;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

final class RajaOngkirService
{
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

        $selectedCouriers = mb_strtolower(trim($couriers ?? (string) config('services.rajaongkir.couriers', '')));
        if ($selectedCouriers === '' || preg_match('/^[a-z0-9_:-]+$/', $selectedCouriers) !== 1) {
            throw new InvalidArgumentException('Kurir pengiriman tidak valid.');
        }

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

    private function ensureConfigured(): void
    {
        $enabled = (bool) config('services.rajaongkir.enabled', false);
        $apiKey = trim((string) config('services.rajaongkir.api_key'));
        $baseUrl = trim((string) config('services.rajaongkir.base_url'));

        if (! $enabled || $apiKey === '' || $baseUrl === '') {
            throw RajaOngkirException::notConfigured();
        }
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

        return array_values(array_filter($data, 'is_array'));
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
            static fn (array $cost): bool => $cost['courier_code'] !== ''
                && $cost['service'] !== ''
                && $cost['cost'] >= 0,
        ));
    }

    /**
     * @param  Closure(): list<array<string, mixed>>  $callback
     * @return list<array<string, mixed>>
     */
    private function remember(string $key, int $seconds, Closure $callback): array
    {
        if ($seconds <= 0) {
            return $callback();
        }

        return Cache::remember($key, $seconds, $callback);
    }
}
