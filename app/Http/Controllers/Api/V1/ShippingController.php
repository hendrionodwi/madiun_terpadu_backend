<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shipping\QuoteShippingRequest;
use App\Http\Requests\Api\V1\Shipping\SearchDestinationRequest;
use App\Models\User;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ShippingController extends Controller
{
    public function __construct(public RajaOngkirService $rajaOngkirService) {}

    public function destinations(SearchDestinationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $destinations = $this->rajaOngkirService->searchDomesticDestinations(
            (string) $validated['search'],
            (int) ($validated['limit'] ?? 10),
            (int) ($validated['offset'] ?? 0),
        );

        return response()->json([
            'success' => true,
            'message' => 'Daftar tujuan pengiriman berhasil diambil.',
            'data' => $destinations,
        ], Response::HTTP_OK);
    }

    public function quotes(QuoteShippingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $quotes = $this->rajaOngkirService->calculateDomesticCosts(
            $this->resolveOriginId($validated),
            (int) $validated['destination_id'],
            (int) $validated['weight_grams'],
        );

        return response()->json([
            'success' => true,
            'message' => 'Pilihan ongkir berhasil dihitung.',
            'data' => $quotes,
        ], Response::HTTP_OK);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveOriginId(array $validated): int
    {
        if (isset($validated['origin_id'])) {
            return (int) $validated['origin_id'];
        }

        $seller = User::query()
            ->with('pengelolaTps3rProfile')
            ->whereKey((int) $validated['seller_id'])
            ->where('is_active', true)
            ->first();
        $originId = $seller?->isPengelolaTps3r() === true
            ? $seller->pengelolaTps3rProfile?->rajaongkir_origin_id
            : null;

        if ($originId === null) {
            throw ValidationException::withMessages([
                'seller_id' => 'TPS3R belum mengatur lokasi asal pengiriman luar kota.',
            ]);
        }

        return $originId;
    }
}
