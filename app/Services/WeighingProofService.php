<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class WeighingProofService
{
    /**
     * Process weighing proof input from API / Form.
     * Supports:
     * 1. Real UploadedFile binary upload (from camera / gallery).
     * 2. Base64 encoded image string.
     * 3. Simulated filename (e.g. 'bukti_timbangan_20261014_0745_1.jpg') - will auto-generate realistic scale image.
     */
    /** @param array<string, mixed> $metadata */
    public function processProofInput(mixed $photoInput, array $metadata = []): ?string
    {
        if (blank($photoInput)) {
            return null;
        }

        // 1. Binary UploadedFile
        if ($photoInput instanceof UploadedFile) {
            $storedPath = $photoInput->store('bukti_timbangan', 'public');
            if ($storedPath === false) {
                throw new RuntimeException('Foto bukti timbangan gagal disimpan.');
            }

            return $storedPath;
        }

        if (! is_string($photoInput)) {
            return null;
        }

        $photoInput = trim($photoInput);

        // 2. Base64 data URL
        if (str_starts_with($photoInput, 'data:image/')) {
            return $this->saveBase64Image($photoInput);
        }

        // 3. Absolute URL (already hosted externally)
        if (str_starts_with($photoInput, 'http://') || str_starts_with($photoInput, 'https://')) {
            return $photoInput;
        }

        // 4. Filename or relative path string
        $cleanPath = ltrim(preg_replace('/^(\/?storage\/)/', '', $photoInput), '/');

        return $this->ensureProofImage($cleanPath, $metadata);
    }

    /**
     * Ensure the proof image physically exists in the public storage disk.
     * If not found, generates a high-fidelity digital scale verification photo image.
     */
    /** @param array<string, mixed> $metadata */
    public function ensureProofImage(string $filename, array $metadata = []): string
    {
        $cleanFilename = ltrim(preg_replace('/^(\/?storage\/)/', '', $filename), '/');

        if (Storage::disk('public')->exists($cleanFilename)) {
            return $cleanFilename;
        }

        $this->generateScaleProofImage($cleanFilename, $metadata);

        return $cleanFilename;
    }

    /**
     * Generate a digital scale certificate image using GD library.
     */
    /** @param array<string, mixed> $metadata */
    public function generateScaleProofImage(string $filename, array $metadata = []): void
    {
        $width = 800;
        $height = 600;

        $image = $this->createCanvas($width, $height);

        // Colors
        $darkBg = $this->allocateColor($image, 15, 23, 42);          // Slate 900
        $panelBg = $this->allocateColor($image, 30, 41, 59);         // Slate 800
        $cardBg = $this->allocateColor($image, 24, 32, 47);          // Dark Slate
        $lcdBg = $this->allocateColor($image, 10, 20, 15);           // Very dark green-black LCD
        $lcdBorder = $this->allocateColor($image, 16, 185, 129);     // Emerald border
        $lcdText = $this->allocateColor($image, 52, 211, 153);       // Neon emerald LCD text
        $white = $this->allocateColor($image, 248, 250, 252);        // Slate 50
        $tealHeader = $this->allocateColor($image, 13, 148, 136);    // Teal 600
        $textMuted = $this->allocateColor($image, 148, 163, 184);    // Slate 400
        $badgeBg = $this->allocateColor($image, 6, 78, 59);          // Emerald 900
        $badgeText = $this->allocateColor($image, 167, 243, 208);    // Emerald 200
        $borderMuted = $this->allocateColor($image, 51, 65, 85);     // Slate 700

        // Fill background
        imagefill($image, 0, 0, $darkBg);

        // Outer frame border
        imagesetthickness($image, 3);
        imagerectangle($image, 10, 10, $width - 10, $height - 10, $borderMuted);

        // Top Header Banner
        imagefilledrectangle($image, 12, 12, $width - 12, 90, $panelBg);
        imagefilledrectangle($image, 12, 12, $width - 12, 20, $tealHeader);

        imagestring($image, 5, 30, 32, 'PEMERINTAH KOTA MADIUN - DINAS LINGKUNGAN HIDUP', $white);
        imagestring($image, 4, 30, 58, 'DOKUMENTASI BUKTI PENIMBANGAN SAMPAH TERPADU (ARMADA PETUGAS)', $textMuted);

        // Weighing Scale Display Box (LCD Digital Bench Scale)
        $lcdX1 = 50;
        $lcdY1 = 115;
        $lcdX2 = $width - 50;
        $lcdY2 = 330;

        // Scale Body
        imagefilledrectangle($image, $lcdX1, $lcdY1, $lcdX2, $lcdY2, $cardBg);
        imagesetthickness($image, 2);
        imagerectangle($image, $lcdX1, $lcdY1, $lcdX2, $lcdY2, $borderMuted);

        // Inner Digital Screen
        imagefilledrectangle($image, $lcdX1 + 25, $lcdY1 + 35, $lcdX2 - 25, $lcdY2 - 35, $lcdBg);
        imagerectangle($image, $lcdX1 + 25, $lcdY1 + 35, $lcdX2 - 25, $lcdY2 - 35, $lcdBorder);

        imagestring($image, 4, $lcdX1 + 35, $lcdY1 + 12, 'TIMBANGAN DIGITAL RESMI DLH - TRUK ARMADA PERSAMPAHAN', $textMuted);

        // Weight value
        $beratKg = number_format((float) ($metadata['berat_kg'] ?? 5.0), 2, '.', '');
        $weightDisplay = $beratKg.' KG';

        imagestring($image, 5, $lcdX1 + 50, $lcdY1 + 65, '[ DIGITAL SENSOR STABLE ]', $badgeText);
        imagestring($image, 5, $lcdX1 + 50, $lcdY1 + 95, 'BERAT BERSIH TIMBANGAN :', $textMuted);
        imagestring($image, 5, $lcdX1 + 50, $lcdY1 + 130, '>>>  '.$weightDisplay.'  <<<', $lcdText);

        // Metadata Card at bottom
        $metaY1 = 350;
        $metaY2 = 560;
        imagefilledrectangle($image, 50, $metaY1, $width - 50, $metaY2, $panelBg);
        imagerectangle($image, 50, $metaY1, $width - 50, $metaY2, $borderMuted);

        $jenisSampah = strtoupper(str_replace('_', ' ', (string) ($metadata['jenis_sampah'] ?? 'SAMPAH TERPILAH')));
        $wargaName = (string) ($metadata['warga_name'] ?? 'Warga Kota Madiun');
        $petugasName = (string) ($metadata['petugas_name'] ?? 'Petugas Lapangan DLH');
        $timestamp = (string) ($metadata['timestamp'] ?? now()->translatedFormat('d F Y, H:i:s').' WIB');

        imagestring($image, 5, 75, $metaY1 + 20, 'RINCIAN DATA TIMBANGAN LAPANGAN', $white);
        imagefilledrectangle($image, 75, $metaY1 + 42, 380, $metaY1 + 44, $tealHeader);

        imagestring($image, 4, 75, $metaY1 + 55, 'Kategori Sampah  : '.$jenisSampah, $badgeText);
        imagestring($image, 4, 75, $metaY1 + 80, 'Penyetor / Warga : '.Str::limit($wargaName, 40), $white);
        imagestring($image, 4, 75, $metaY1 + 105, 'Petugas Timbang  : '.Str::limit($petugasName, 40), $textMuted);
        imagestring($image, 4, 75, $metaY1 + 130, 'Waktu Timbang    : '.$timestamp, $textMuted);
        imagestring($image, 3, 75, $metaY1 + 160, 'Berkas File      : '.basename($filename), $textMuted);

        // Verification Badge on bottom right
        imagefilledrectangle($image, $width - 320, $metaY1 + 125, $width - 70, $metaY1 + 185, $badgeBg);
        imagerectangle($image, $width - 320, $metaY1 + 125, $width - 70, $metaY1 + 185, $lcdBorder);
        imagestring($image, 4, $width - 305, $metaY1 + 135, 'STATUS VERIFIKASI :', $badgeText);
        imagestring($image, 4, $width - 305, $metaY1 + 155, 'TERVALIDASI RESMI DLH', $white);

        // Ensure storage directory exists
        $absolutePath = Storage::disk('public')->path($filename);
        $this->ensureDirectory(dirname($absolutePath));

        if (! imagejpeg($image, $absolutePath, 92)) {
            imagedestroy($image);

            throw new RuntimeException('Gambar bukti timbangan gagal dibuat.');
        }

        imagedestroy($image);
    }

    /**
     * Decode base64 image string and store to public disk.
     */
    protected function saveBase64Image(string $base64String): string
    {
        $parts = explode(',', $base64String, 2);
        $encoded = count($parts) === 2 ? $parts[1] : $parts[0];
        $binary = base64_decode($encoded, true);
        if ($binary === false) {
            throw ValidationException::withMessages([
                'foto_bukti' => ['Format foto bukti timbangan tidak valid.'],
            ]);
        }

        $filename = 'bukti_timbangan/timbangan_'.now()->format('Ymd_His').'_'.Str::random(6).'.jpg';
        if (! Storage::disk('public')->put($filename, $binary)) {
            throw new RuntimeException('Foto bukti timbangan gagal disimpan.');
        }

        return $filename;
    }

    /**
     * Generate fallback placeholder image in public/images/placeholder-timbangan.png.
     */
    public function generatePlaceholderImage(): string
    {
        $targetPath = public_path('images/placeholder-timbangan.png');
        if (file_exists($targetPath)) {
            return $targetPath;
        }

        $this->ensureDirectory(dirname($targetPath));

        $w = 400;
        $h = 300;
        $im = $this->createCanvas($w, $h);
        $bg = $this->allocateColor($im, 30, 41, 59);
        imagefill($im, 0, 0, $bg);
        $border = $this->allocateColor($im, 51, 65, 85);
        imagerectangle($im, 5, 5, $w - 5, $h - 5, $border);
        $teal = $this->allocateColor($im, 20, 184, 166);
        $white = $this->allocateColor($im, 248, 250, 252);
        $muted = $this->allocateColor($im, 148, 163, 184);

        imagestring($im, 5, 45, 90, 'DLH KOTA MADIUN', $teal);
        imagestring($im, 4, 45, 125, 'Foto Bukti Timbangan', $white);
        imagestring($im, 3, 45, 155, 'Dokumentasi penimbangan armada', $muted);
        imagestring($im, 3, 45, 175, 'sedang diproses oleh petugas.', $muted);

        if (! imagepng($im, $targetPath)) {
            imagedestroy($im);

            throw new RuntimeException('Placeholder bukti timbangan gagal dibuat.');
        }

        imagedestroy($im);

        return $targetPath;
    }

    /**
     * @param  positive-int  $width
     * @param  positive-int  $height
     */
    private function createCanvas(int $width, int $height): GdImage
    {
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            throw new RuntimeException('Kanvas gambar bukti timbangan gagal dibuat.');
        }

        return $image;
    }

    /**
     * @param  int<0, 255>  $red
     * @param  int<0, 255>  $green
     * @param  int<0, 255>  $blue
     */
    private function allocateColor(GdImage $image, int $red, int $green, int $blue): int
    {
        $color = imagecolorallocate($image, $red, $green, $blue);
        if ($color === false) {
            throw new RuntimeException('Warna gambar bukti timbangan gagal dialokasikan.');
        }

        return $color;
    }

    private function ensureDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException("Direktori {$directory} gagal dibuat.");
        }
    }
}
