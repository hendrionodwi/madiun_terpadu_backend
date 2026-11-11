<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $this->moveDocuments('public', 'local');
    }

    public function down(): void
    {
        $this->moveDocuments('local', 'public');
    }

    private function moveDocuments(string $sourceDiskName, string $targetDiskName): void
    {
        $sourceDisk = Storage::disk($sourceDiskName);
        $targetDisk = Storage::disk($targetDiskName);

        DB::table('public_documents')
            ->select(['id', 'file_path'])
            ->orderBy('id')
            ->chunkById(100, function (Collection $documents) use ($sourceDisk, $targetDisk): void {
                foreach ($documents as $document) {
                    $path = (string) $document->file_path;

                    if ($path === '' || ! $sourceDisk->exists($path)) {
                        continue;
                    }

                    if (! $targetDisk->exists($path)) {
                        $contents = $sourceDisk->get($path);

                        if (! $targetDisk->put($path, $contents)) {
                            continue;
                        }
                    }

                    $sourceDisk->delete($path);
                }
            });
    }
};
