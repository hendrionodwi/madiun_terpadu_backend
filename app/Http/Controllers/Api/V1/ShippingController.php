<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shipping\QuoteShippingRequest;
use App\Http\Requests\Api\V1\Shipping\SearchDestinationRequest;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
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
            (int) $validated['origin_id'],
            (int) $validated['destination_id'],
            (int) $validated['weight_grams'],
            isset($validated['couriers']) ? (string) $validated['couriers'] : null,
        );

        return response()->json([
            'success' => true,
            'message' => 'Pilihan ongkir berhasil dihitung.',
            'data' => $quotes,
        ], Response::HTTP_OK);
    }
}
