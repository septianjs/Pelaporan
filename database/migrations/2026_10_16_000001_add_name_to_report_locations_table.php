<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom `name` pada report_locations agar nama lokasi
     * dapat diisi/diubah sendiri oleh user per laporan, tanpa menghapus
     * relasi ke lokasi master (location_id) yang masih dipakai sebagai
     * urutan 5 titik pemantauan tetap dan nilai default/saran nama.
     *
     * Ditulis idempotent (aman dijalankan ulang) karena versi sebelumnya
     * memakai join-update yang tidak kompatibel dengan SQLite, yang bisa
     * meninggalkan kolom sudah ada tapi migration belum tercatat selesai.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('report_locations', 'name')) {
            Schema::table('report_locations', function (Blueprint $table) {
                $table->string('name', 150)->nullable()->after('location_id');
            });
        }

        // Backfill laporan lama: salin nama dari lokasi master agar data lama tetap tampil benar.
        // Pakai subquery SQL mentah (bukan join-update query builder) karena sintaks ini
        // portabel di SQLite maupun MySQL, sedangkan join-update Laravel diterjemahkan
        // berbeda di SQLite dan tidak bisa membaca kolom tabel lain di klausa SET.
        DB::statement('
            UPDATE report_locations
            SET name = (
                SELECT name FROM locations WHERE locations.id = report_locations.location_id
            )
            WHERE name IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('report_locations', 'name')) {
            Schema::table('report_locations', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};