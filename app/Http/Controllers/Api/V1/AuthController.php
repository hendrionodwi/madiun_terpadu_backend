<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\UpdateProfileRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Models\WargaProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    /** Update the authenticated user's basic profile and role profile details. */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $avatarPath = $user->avatar;
        if ($request->hasFile('avatar')) {
            $newAvatarPath = $request->file('avatar')->store('avatars', 'public');
            if ($avatarPath && ! str_starts_with($avatarPath, 'http')) {
                Storage::disk('public')->delete($avatarPath);
            }
            $avatarPath = $newAvatarPath;
        }

        DB::transaction(function () use ($user, $validated, $avatarPath): void {
            $user->update([
                'name' => $validated['name'] ?? $user->name,
                'phone_number' => array_key_exists('phone_number', $validated)
                    ? $validated['phone_number']
                    : $user->phone_number,
                'avatar' => $avatarPath,
            ]);

            if ($user->isWarga()) {
                $user->wargaProfile()->updateOrCreate([], array_filter([
                    'alamat' => $validated['alamat'] ?? null,
                    'rt' => $validated['rt'] ?? null,
                    'rw' => $validated['rw'] ?? null,
                    'kelurahan' => $validated['kelurahan'] ?? null,
                    'kecamatan' => $validated['kecamatan'] ?? null,
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                ], fn (mixed $v): bool => $v !== null));
            } elseif ($user->isPetugasLapangan()) {
                $user->petugasLapanganProfile()->updateOrCreate([], array_filter([
                    'status_tugas' => $validated['status_tugas'] ?? null,
                    'jenis_kendaraan' => $validated['jenis_kendaraan'] ?? null,
                    'plat_nomor' => $validated['plat_nomor'] ?? null,
                ], fn (mixed $v): bool => $v !== null));
            } elseif ($user->isPengelolaTps3r() && ! empty($validated['nama_tps3r'])) {
                $user->pengelolaTps3rProfile()->updateOrCreate([], [
                    'nama_tps3r' => $validated['nama_tps3r'],
                    'alamat' => $validated['alamat'] ?? $user->pengelolaTps3rProfile?->alamat,
                ]);
            } elseif ($user->isPemrakarsa() && (! empty($validated['nama_instansi']) || ! empty($validated['penanggung_jawab']))) {
                $user->pemrakarsaProfile()->updateOrCreate([], array_filter([
                    'nama_instansi' => $validated['nama_instansi'] ?? null,
                    'penanggung_jawab' => $validated['penanggung_jawab'] ?? null,
                    'alamat_instansi' => $validated['alamat'] ?? null,
                ], fn (mixed $v): bool => $v !== null));
            }
        });

        $user->refresh()->load($this->profileRelationFor($user->role));

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => new UserResource($user),
        ], Response::HTTP_OK);
    }

    /**
     * Send password reset link to email for unified Web/Mobile recovery.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::broker()->sendResetLink(['email' => $validated['email']]);

        return response()->json([
            'success' => true,
            'message' => 'Jika email terdaftar dalam sistem, tautan atur ulang kata sandi telah dikirim.',
        ], Response::HTTP_OK);
    }

    /**
     * Reset user password with valid recovery token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker()->reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diatur ulang. Silakan login menggunakan kata sandi baru.',
        ], Response::HTTP_OK);
    }

    /**
     * Update authenticated user's password and revoke other device tokens.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $currentTokenId = $user->currentAccessToken()?->id;
        $user->tokens()
            ->when($currentTokenId !== null, fn ($q) => $q->where('id', '!=', $currentTokenId))
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui.',
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
