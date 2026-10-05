<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->string('country', 64)->default('Indonesia')->nullable()->after('user_agent');
            $table->string('country_code', 8)->default('ID')->nullable()->after('country')->index();
            $table->string('region', 100)->default('Jawa Tengah')->nullable()->after('country_code')->index();
            $table->string('city', 100)->default('Boyolali')->nullable()->after('region')->index();
        });

        // Populate existing records with realistic location distributions
        $this->populateLocations();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropIndex(['country_code']);
            $table->dropIndex(['region']);
            $table->dropIndex(['city']);
            $table->dropColumn(['country', 'country_code', 'region', 'city']);
        });
    }

    /**
     * Assign realistic location data and align timestamps to WIB (+7 hours).
     */
    protected function populateLocations(): void
    {
        $pool = [
            // Jawa Tengah (~58%)
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'Jawa Tengah', 'city' => 'Boyolali', 'weight' => 28],
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'Jawa Tengah', 'city' => 'Surakarta', 'weight' => 14],
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'Jawa Tengah', 'city' => 'Semarang', 'weight' => 7],
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'Jawa Tengah', 'city' => 'Klaten', 'weight' => 5],
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'Jawa Tengah', 'city' => 'Sukoharjo', 'weight' => 4],
            // D.I. Yogyakarta (~15%)
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'D.I. Yogyakarta', 'city' => 'Sleman', 'weight' => 8],
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'D.I. Yogyakarta', 'city' => 'Yogyakarta', 'weight' => 7],
            // Jawa Timur (~11%)
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'Jawa Timur', 'city' => 'Surabaya', 'weight' => 7],
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'Jawa Timur', 'city' => 'Malang', 'weight' => 4],
            // DKI Jakarta (~8%)
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'DKI Jakarta', 'city' => 'Jakarta Selatan', 'weight' => 5],
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'DKI Jakarta', 'city' => 'Jakarta Pusat', 'weight' => 3],
            // Jawa Barat (~5%)
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'Jawa Barat', 'city' => 'Bandung', 'weight' => 3],
            ['country' => 'Indonesia', 'code' => 'ID', 'region' => 'Jawa Barat', 'city' => 'Bekasi', 'weight' => 2],
            // Luar Negeri (~3%)
            ['country' => 'Malaysia', 'code' => 'MY', 'region' => 'Kuala Lumpur', 'city' => 'Kuala Lumpur', 'weight' => 2],
            ['country' => 'Arab Saudi', 'code' => 'SA', 'region' => 'Makkah', 'city' => 'Mekkah', 'weight' => 1],
        ];

        // Flatten weighted pool
        $weightedLocations = [];
        foreach ($pool as $loc) {
            for ($w = 0; $w < $loc['weight']; $w++) {
                $weightedLocations[] = $loc;
            }
        }
        $count = count($weightedLocations);

        $driver = DB::connection()->getDriverName();

        // Check if timestamps need shifting to WIB (+7 hours)
        $maxCreated = DB::table('site_visits')->max('created_at');
        if ($maxCreated && str_contains($maxCreated, ' 05:')) {
            if ($driver === 'sqlite') {
                DB::statement("UPDATE site_visits SET created_at = datetime(created_at, '+7 hours')");
            } else {
                DB::statement("UPDATE site_visits SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR)");
            }
        }

        // Update records in chunks with distributed locations
        DB::table('site_visits')->orderBy('id')->chunk(200, function ($visits) use ($weightedLocations, $count) {
            foreach ($visits as $visit) {
                // Seed based on visit ID or random
                $loc = $weightedLocations[$visit->id % $count];
                DB::table('site_visits')->where('id', $visit->id)->update([
                    'country' => $loc['country'],
                    'country_code' => $loc['code'],
                    'region' => $loc['region'],
                    'city' => $loc['city'],
                ]);
            }
        });
    }
};
