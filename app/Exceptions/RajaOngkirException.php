<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class RajaOngkirException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $statusCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function notConfigured(): self
    {
        return new self('Integrasi ongkir belum diaktifkan atau belum dikonfigurasi.');
    }

    public static function unavailable(?int $statusCode, Throwable $previous): self
    {
        return new self(
            'Layanan perhitungan ongkir sedang tidak dapat dihubungi.',
            $statusCode,
            $previous,
        );
    }

    public static function malformedResponse(): self
    {
        return new self('Layanan ongkir mengembalikan data yang tidak valid.');
    }

    /** @return array<string, int|string|null> */
    public function context(): array
    {
        return [
            'shipping_provider' => 'rajaongkir',
            'upstream_status' => $this->statusCode,
        ];
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if (! $request->is('api/*') && ! $request->expectsJson()) {
            return back()->withErrors([
                'shipping_source' => $this->getMessage(),
            ])->withInput();
        }

        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
