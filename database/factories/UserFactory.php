<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => UserRole::Warga,
            'phone_number' => fake()->phoneNumber(),
            'avatar' => null,
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * State for role Warga.
     */
    public function warga(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Warga,
        ]);
    }

    /**
     * State for role Petugas Lapangan.
     */
    public function petugasLapangan(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::PetugasLapangan,
        ]);
    }

    /**
     * State for role Pengelola TPS3R.
     */
    public function pengelolaTps3r(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::PengelolaTps3r,
        ]);
    }

    /**
     * State for role Pemrakarsa.
     */
    public function pemrakarsa(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Pemrakarsa,
        ]);
    }

    /**
     * State for role Admin DLH.
     */
    public function adminDlh(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::AdminDlh,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
