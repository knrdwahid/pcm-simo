<?php

use App\Models\Official;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sinkronisasi data resmi Pimpinan Cabang Muhammadiyah Simo Periode 2023 - 2028 (SK PDM Boyolali No. 100/KEP/III.0/D/2023).
     */
    public function up(): void
    {
        $officials = [
            [
                'name' => 'H. Sholihin, S.Pd',
                'position' => 'Ketua PCM Simo',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sayyaf, S.PdI',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 2,
            ],
            [
                'name' => 'Syarif Widodo, M.PdI',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 3,
            ],
            [
                'name' => 'Drs. Mukridin',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 4,
            ],
            [
                'name' => 'Drs. Qomarudin',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 5,
            ],
            [
                'name' => 'Mushowir, S.Ag., S.Kom',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 6,
            ],
            [
                'name' => 'Suryani, S.Si., S.H',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 7,
            ],
            [
                'name' => 'H. Suyono, S.H',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 8,
            ],
            [
                'name' => 'Drs. Suramto, M.Pd',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 9,
            ],
        ];

        Official::truncate();
        foreach ($officials as $item) {
            Official::create($item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
