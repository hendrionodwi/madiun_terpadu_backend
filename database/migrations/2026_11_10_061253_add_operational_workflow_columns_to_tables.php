<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pickup_schedules', function (Blueprint $table) {
            $table->string('kode_jadwal', 40)->nullable()->unique()->after('id');
            $table->decimal('estimasi_berat_kg', 8, 2)->nullable()->after('jenis_sampah');
        });

        DB::table('pickup_schedules')
            ->select(['id', 'tanggal_penjemputan'])
            ->orderBy('id')
            ->eachById(function (object $record): void {
                $datePart = $record->tanggal_penjemputan
                    ? str_replace('-', '', substr((string) $record->tanggal_penjemputan, 0, 10))
                    : date('Ymd');
                DB::table('pickup_schedules')
                    ->where('id', $record->id)
                    ->update(['kode_jadwal' => 'JMP-'.$datePart.'-'.strtoupper(Str::random(6))]);
            });

        Schema::table('waste_weighings', function (Blueprint $table) {
            $table->string('client_reference_id', 100)->nullable()->after('pickup_schedule_id');
            $table->unique(['petugas_id', 'client_reference_id'], 'waste_weighings_petugas_client_ref_unique');
        });

        Schema::table('tps3r_waste_deposits', function (Blueprint $table) {
            $table->string('client_reference_id', 100)->nullable()->after('nomor_resi');
            $table->decimal('berat_bruto_kg', 10, 2)->nullable()->after('berat_kg');
            $table->decimal('berat_tara_kg', 10, 2)->nullable()->after('berat_bruto_kg');
            $table->unique(['petugas_id', 'client_reference_id'], 'tps3r_deposits_petugas_client_ref_unique');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->string('prioritas', 30)->default('sedang')->after('status');
            $table->string('route_code', 60)->nullable()->after('assigned_petugas_id');
            $table->timestamp('target_selesai_at')->nullable()->after('assigned_at');
        });

        Schema::table('environmental_reports', function (Blueprint $table) {
            $table->string('nomor_laporan', 50)->nullable()->unique()->after('public_id');
            $table->string('nomor_bap', 50)->nullable()->unique()->after('status');
            $table->json('monitoring_data')->nullable()->after('metode_pengelolaan');
            $table->string('file_path')->nullable()->change();
        });

        DB::table('environmental_reports')
            ->select(['id', 'tahun'])
            ->orderBy('id')
            ->eachById(function (object $record): void {
                DB::table('environmental_reports')
                    ->where('id', $record->id)
                    ->update(['nomor_laporan' => 'LAP-'.($record->tahun ?? date('Y')).'-'.strtoupper(Str::random(6))]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('environmental_reports', function (Blueprint $table) {
            $table->dropUnique(['nomor_laporan']);
            $table->dropUnique(['nomor_bap']);
            $table->dropColumn(['nomor_laporan', 'nomor_bap', 'monitoring_data']);
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['prioritas', 'route_code', 'target_selesai_at']);
        });

        Schema::table('tps3r_waste_deposits', function (Blueprint $table) {
            $table->dropUnique('tps3r_deposits_petugas_client_ref_unique');
            $table->dropColumn(['client_reference_id', 'berat_bruto_kg', 'berat_tara_kg']);
        });

        Schema::table('waste_weighings', function (Blueprint $table) {
            $table->dropUnique('waste_weighings_petugas_client_ref_unique');
            $table->dropColumn('client_reference_id');
        });

        Schema::table('pickup_schedules', function (Blueprint $table) {
            $table->dropUnique(['kode_jadwal']);
            $table->dropColumn(['kode_jadwal', 'estimasi_berat_kg']);
        });
    }
};
