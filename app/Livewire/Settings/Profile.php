<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use App\Models\MasterData;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Title('Informasi Pribadi & Profil')]
class Profile extends Component
{
    use ProfileValidationRules;
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $phone_number = '';

    public mixed $avatar = null;

    public ?string $existingAvatarUrl = null;

    public string $nip = '';

    public string $jabatan = '';

    public string $bidang = '';

    public string $jenis_kendaraan = '';

    public string $plat_nomor = '';

    public string $alamat = '';

    public string $rt = '';

    public string $rw = '';

    public string $kelurahan = '';

    public string $kecamatan = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $user->loadMissing([
            'adminDlhProfile',
            'petugasLapanganProfile',
            'wargaProfile',
        ]);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone_number = (string) ($user->phone_number ?? '');
        $this->existingAvatarUrl = $user->avatar_url;

        if ($user->isAdminDlh()) {
            $this->nip = (string) ($user->adminDlhProfile?->nip ?? '');
            $this->jabatan = (string) ($user->adminDlhProfile?->jabatan ?? '');
            $this->bidang = (string) ($user->adminDlhProfile?->bidang ?? '');
        } elseif ($user->isPetugasLapangan()) {
            $this->jenis_kendaraan = (string) ($user->petugasLapanganProfile?->jenis_kendaraan ?? '');
            $this->plat_nomor = (string) ($user->petugasLapanganProfile?->plat_nomor ?? '');
        } elseif ($user->isWarga()) {
            $this->alamat = (string) ($user->wargaProfile?->alamat ?? '');
            $this->rt = (string) ($user->wargaProfile?->rt ?? '');
            $this->rw = (string) ($user->wargaProfile?->rw ?? '');
            $this->kelurahan = (string) ($user->wargaProfile?->kelurahan ?? '');
            $this->kecamatan = (string) ($user->wargaProfile?->kecamatan ?? '');
        }
    }

    /**
     * Active district names for citizen domicile select.
     *
     * @return list<string>
     */
    #[Computed]
    public function districts(): array
    {
        if (! Auth::user()?->isWarga()) {
            return [];
        }

        return MasterData::query()
            ->where('group', 'districts')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = array_merge($this->profileRules($user->id), [
            'phone_number' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($user->isAdminDlh()) {
            $rules['nip'] = ['nullable', 'string', 'max:40'];
            $rules['jabatan'] = ['nullable', 'string', 'max:120'];
            $rules['bidang'] = ['nullable', 'string', 'max:120'];
        } elseif ($user->isPetugasLapangan()) {
            $rules['jenis_kendaraan'] = ['nullable', 'string', 'max:100'];
            $rules['plat_nomor'] = ['nullable', 'string', 'max:30'];
        } elseif ($user->isWarga()) {
            $rules['alamat'] = ['nullable', 'string', 'max:500'];
            $rules['rt'] = ['nullable', 'string', 'max:10'];
            $rules['rw'] = ['nullable', 'string', 'max:10'];
            $rules['kelurahan'] = ['nullable', 'string', 'max:100'];
            $rules['kecamatan'] = ['nullable', 'string', 'max:100'];
        }

        $validated = $this->validate($rules);

        $newAvatarPath = null;
        $oldAvatar = $user->avatar;

        if ($this->avatar instanceof TemporaryUploadedFile) {
            $newAvatarPath = $this->avatar->store('avatars', 'public');
        }

        DB::transaction(function () use ($user, $validated, $newAvatarPath): void {
            $user->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] !== '' ? $validated['phone_number'] : null,
            ]);

            if ($newAvatarPath !== null) {
                $user->avatar = $newAvatarPath;
            }

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            if ($user->isAdminDlh()) {
                $user->adminDlhProfile()->updateOrCreate([], [
                    'nip' => $validated['nip'] ?: null,
                    'jabatan' => $validated['jabatan'] ?: null,
                    'bidang' => $validated['bidang'] ?: ($user->adminDlhProfile?->bidang ?? 'Operasional'),
                ]);
            } elseif ($user->isPetugasLapangan()) {
                $user->petugasLapanganProfile()->updateOrCreate([], [
                    'jenis_kendaraan' => $validated['jenis_kendaraan'] ?: null,
                    'plat_nomor' => $validated['plat_nomor'] ?: null,
                ]);
            } elseif ($user->isWarga()) {
                $user->wargaProfile()->updateOrCreate([], [
                    'alamat' => $validated['alamat'] ?: null,
                    'rt' => $validated['rt'] ?: null,
                    'rw' => $validated['rw'] ?: null,
                    'kelurahan' => $validated['kelurahan'] ?: null,
                    'kecamatan' => $validated['kecamatan'] ?: null,
                ]);
            }
        });

        if ($newAvatarPath !== null && $oldAvatar && ! str_starts_with($oldAvatar, 'http')) {
            Storage::disk('public')->delete($oldAvatar);
        }

        $this->avatar = null;
        $this->existingAvatarUrl = $user->fresh()?->avatar_url;

        session()->flash('profile_status', 'Informasi pribadi dan profil berhasil diperbarui.');
        Flux::toast(variant: 'success', text: __('Informasi pribadi berhasil diperbarui.'));
    }

    /**
     * Remove the authenticated user's uploaded profile photo.
     */
    public function removeAvatar(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $oldAvatar = $user->avatar;

        if ($oldAvatar && ! str_starts_with($oldAvatar, 'http')) {
            Storage::disk('public')->delete($oldAvatar);
        }

        $user->update(['avatar' => null]);
        $this->avatar = null;
        $this->existingAvatarUrl = null;

        session()->flash('profile_status', 'Foto profil dikembalikan ke inisial akun.');
        Flux::toast(variant: 'success', text: __('Foto profil dihapus.'));
    }
}
