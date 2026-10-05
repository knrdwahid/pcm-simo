<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan jenis entitas (Organisasi/Majelis, Ortom, Amal Usaha, Masjid)
     * agar seluruh data organisasi PCM Simo dapat dikelola dari dashboard.
     */
    public function up(): void
    {
        Schema::table('aums', function (Blueprint $table) {
            // organisasi (majelis & lembaga), ortom, amal_usaha, masjid
            $table->string('type')->default('amal_usaha')->after('name')->index();
            $table->string('icon')->nullable()->after('category');
            $table->string('website')->nullable()->after('phone');
            $table->integer('sort_order')->default(0)->after('description');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });

        // Tandai data masjid yang sudah ada
        DB::table('aums')
            ->where(function ($q) {
                $q->where('category', 'like', '%Masjid%')->orWhere('name', 'like', 'Masjid%');
            })
            ->update(['type' => 'masjid', 'icon' => 'mdi-mosque']);

        // Pindahkan data Majelis/Lembaga & Ortom yang sebelumnya hardcode di halaman publik
        $now = now();
        $seed = [
            ['organisasi', 'Majelis Tabligh & Tarjih', 'Majelis', 'mdi-book-open-page-variant-outline', 'Pembinaan muballigh, kajian Himpunan Putusan Tarjih (HPT), dan pengajian rutin ranting.'],
            ['organisasi', 'Majelis Dikdasmen & PNF', 'Majelis', 'mdi-school-outline', 'Pembinaan sekolah dan madrasah Muhammadiyah serta pendidikan non-formal di Simo.'],
            ['organisasi', 'Majelis Pembina Kesehatan Umum (MPKU)', 'Majelis', 'mdi-hospital-box-outline', 'Pengelolaan Klinik Pratama PKU Muhammadiyah Simo dan layanan kesehatan masyarakat.'],
            ['organisasi', 'Majelis Ekonomi, Bisnis & Kewirausahaan', 'Majelis', 'mdi-chart-line', 'Pemberdayaan potensi ekonomi persyarikatan dan kemandirian jamaah.'],
            ['organisasi', 'LAZISMU Kantor Layanan Simo', 'Lembaga', 'mdi-hand-heart-outline', 'Pengelolaan zakat, infaq, dan shadaqah secara profesional, amanah, dan terpercaya.'],
            ['organisasi', 'Lembaga Resiliensi Bencana (MDMC)', 'Lembaga', 'mdi-shield-alert-outline', 'Kesiapsiagaan, mitigasi, dan tanggap darurat bencana bersama relawan dan KOKAM.'],
            ['ortom', "Pimpinan Cabang 'Aisyiyah (PCA) Simo", 'Ortom', 'mdi-account-group-outline', 'Gerakan dakwah perempuan berkemajuan dan pembinaan keluarga sakinah.'],
            ['ortom', 'Pemuda Muhammadiyah (PC PM) Simo', 'Ortom', 'mdi-shield-account-outline', 'Kader pelopor gerakan dakwah pemuda Islam dan kesiapsiagaan KOKAM.'],
            ['ortom', "Nasyiatul 'Aisyiyah (PCNA) Simo", 'Ortom', 'mdi-flower-outline', 'Pembinaan remaja putri Islam dan program ketahanan keluarga muda.'],
            ['ortom', 'Ikatan Pelajar Muhammadiyah (PC IPM) Simo', 'Ortom', 'mdi-account-school-outline', 'Kaderisasi pelajar, pengembangan literasi, dan kepemimpinan di tingkat ranting/sekolah.'],
            ['ortom', 'Gerakan Kepanduan Hizbul Wathan (HW)', 'Ortom', 'mdi-compass-outline', 'Pendidikan karakter kepanduan berakhlak mulia dan cinta tanah air.'],
            ['ortom', 'Tapak Suci Putera Muhammadiyah (TSPM)', 'Ortom', 'mdi-sword-cross', 'Perguruan seni bela diri pencak silat berasaskan Islam dan berakhlak mulia.'],
        ];

        foreach ($seed as $i => [$type, $name, $category, $icon, $description]) {
            if (DB::table('aums')->where('name', $name)->exists()) {
                continue;
            }

            DB::table('aums')->insert([
                'name' => $name,
                'type' => $type,
                'category' => $category,
                'icon' => $icon,
                'description' => $description,
                'sort_order' => $i + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('aums')->whereIn('type', ['organisasi', 'ortom'])->delete();

        Schema::table('aums', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'icon', 'website', 'sort_order', 'is_active']);
        });
    }
};
