<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Models\WargaProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Handle user login across multiple roles.
     *
     * AUT-F-002: Fitur Autentikasi API Multi-Role
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password yang Anda masukkan salah.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator DLH Kota Madiun.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($request->filled('role')) {
            $requestedRole = (string) $request->validated('role');
            if ($user->role->value !== $requestedRole) {
                return response()->json([
                    'success' => false,
                    'message' => "Akun Anda tidak memiliki hak akses sebagai {$requestedRole}.",
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $deviceName = (string) ($request->input('device_name') ?? 'mobile-device');
        $token = $user->createToken($deviceName, ["role:{$user->role->value}"])->plainTextToken;

        $user->load($this->profileRelationFor($user->role));

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Handle citizen (warga) self-registration.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => UserRole::Warga,
                'phone_number' => $validated['phone_number'] ?? null,
                'is_active' => true,
            ]);

            WargaProfile::create([
                'user_id' => $user->id,
                'nik' => $validated['nik'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'rt' => $validated['rt'] ?? null,
                'rw' => $validated['rw'] ?? null,
                'kelurahan' => $validated['kelurahan'] ?? null,
                'kecamatan' => $validated['kecamatan'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'saldo_poin' => 0,
            ]);

            return $user;
        });

        $deviceName = (string) ($request->input('device_name') ?? 'mobile-device');
        $token = $user->createToken($deviceName, ["role:{$user->role->value}"])->plainTextToken;

        $user->load('wargaProfile');

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran akun warga berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Get the authenticated user's profile and role details.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->load($this->profileRelationFor($user->role));

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil.',
            'data' => new UserResource($user),
        ], Response::HTTP_OK);
    }

    /**
     * Logout and revoke current Sanctum token.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Sesi telah diakhiri.',
        ], Response::HTTP_OK);
    }

    /**
     * Logout and revoke all tokens on all devices.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout dari seluruh perangkat berhasil.',
        ], Response::HTTP_OK);
    }

    /**
     * Get the relation string corresponding to the user's role.
     */
    protected function profileRelationFor(UserRole $role): string
    {
        return match ($role) {
            UserRole::Warga => 'wargaProfile',
            UserRole::PetugasLapangan => 'petugasLapanganProfile',
            UserRole::PengelolaTps3r => 'pengelolaTps3rProfile',
            UserRole::Pemrakarsa => 'pemrakarsaProfile',
            UserRole::AdminDlh => 'adminDlhProfile',
        };
    }
}
