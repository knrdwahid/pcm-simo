<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Aum;
use App\Models\Category;
use App\Models\Official;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@pcmsimo.or.id')->first() ?? User::first();

        // 1. Categories
        $categories = [
            [
                'name' => 'Berita Cabang',
                'slug' => 'berita-cabang',
                'color' => '#006837',
                'description' => 'Warta kegiatan dan kabar terkini seputar PCM Simo dan ranting.',
            ],
            [
                'name' => 'Tabligh & Tarjih',
                'slug' => 'tabligh-tarjih',
                'color' => '#0A2540',
                'description' => 'Kajian keislaman, fiqih ibadah, tuntunan ibadah, dan fatwa tarjih.',
            ],
            [
                'name' => 'Pendidikan & AUM',
                'slug' => 'pendidikan-aum',
                'color' => '#D97706',
                'description' => 'Informasi sekolah, pesantren, klinik PKU, dan amal usaha Muhammadiyah.',
            ],
            [
                'name' => 'Aisyiyah & Ortom',
                'slug' => 'aisyiyah-ortom',
                'color' => '#059669',
                'description' => 'Aktivitas PCA Simo, Pemuda Muhammadiyah, Nasyiatul Aisyiyah, IPM, dan HW.',
            ],
            [
                'name' => 'Opini & Inspirasi',
                'slug' => 'opini-inspirasi',
                'color' => '#4338CA',
                'description' => 'Tulisan gagasan, telaah dakwah pencerahan, dan refleksi keumatan.',
            ],
        ];

        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[$cat['slug']] = Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 2. Articles
        $articles = [
            [
                'category_id' => $catModels['berita-cabang']->id,
                'user_id' => $admin?->id,
                'title' => 'Musycab PCM Simo Sukses Digelar: Meneguhkan Dakwah Pencerahan Menuju Simo Berkemajuan',
                'slug' => 'musycab-pcm-simo-sukses-digelar-meneguhkan-dakwah-pencerahan',
                'excerpt' => 'Musyawarah Cabang Muhammadiyah Simo Periode Muktamar ke-48 berlangsung khidmat di Gedung Dakwah Muhammadiyah Simo dengan menetapkan arah kepemimpinan baru.',
                'content' => "Musyawarah Cabang (Musycab) Muhammadiyah dan 'Aisyiyah Kecamatan Simo telah sukses diselenggarakan dengan penuh kehangatan dan semangat ukhuwah islamiyah. Acara yang dihadiri oleh ratusan utusan dari pimpinan ranting se-Kecamatan Simo ini menandai tonggak sejarah baru dalam penguatan gerakan dakwah amar ma'ruf nahi munkar di kawasan lereng Gunung Merbabu bagian timur.\n\nDalam sambutannya, Ketua PCM Simo menyampaikan pentingnya revitalisasi cabang dan ranting sebagai ujung tombak gerakan Muhammadiyah. 'Cabang dan ranting adalah denyut nadi persyarikatan. Keberadaan amal usaha di bidang pendidikan dan kesehatan di Simo harus terus diperkuat agar senantiasa memberikan manfaat nyata bagi seluruh lapisan masyarakat tanpa membedakan latar belakang,' tegasnya.\n\nMusycab kali ini menghasilkan beberapa rekomendasi strategis, antara lain percepatan digitalisasi administrasi ranting, penguatan kemandirian ekonomi umat melalui Baitut Tamwil Muhammadiyah (BTM), dan pengembangan sarana pelayanan Klinik Pratama PKU Muhammadiyah Simo menjadi rumah sakit pratama yang semakin representatif.",
                'image_url' => 'https://images.unsplash.com/photo-1577962917302-cd874c4e31d2?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => true,
                'views' => 1420,
                'published_at' => now()->subDays(2),
            ],
            [
                'category_id' => $catModels['pendidikan-aum']->id,
                'user_id' => $admin?->id,
                'title' => 'SD Muhammadiyah Simo Raih Juara Umum Olimpiade Sains & Al-Qur\'an Tingkat Kabupaten Boyolali',
                'slug' => 'sd-muhammadiyah-simo-raih-juara-umum-olimpiade-sains-al-quran',
                'excerpt' => 'Prestasi gemilang kembali ditorehkan oleh siswa-siswi SD Muhammadiyah Simo yang berhasil memborong 7 piala emas dalam ajang kompetisi tahunan.',
                'content' => "Keluarga besar perguruan Muhammadiyah Simo patut berbangga hati. Pada perhelatan Olimpiade Sains dan Seni Al-Qur'an Sekolah Dasar se-Kabupaten Boyolali, kontingen SD Muhammadiyah Simo berhasil dinobatkan sebagai Juara Umum setelah menyabet medali emas di cabang Matematika Terapan, IPA Terpadu, Tartil Al-Qur'an, dan Hifdzil Qur'an Juz 30.\n\nKepala SD Muhammadiyah Simo menyatakan rasa syukur yang mendalam atas kerja keras para guru pembimbing dan ketekunan siswa. 'Pencapaian ini membuktikan bahwa integrasi kurikulum nasional dengan nilai-nilai tauhid dan kemuhammadiyahan mampu melahirkan generasi yang unggul secara intelektual sekaligus anggun secara akhlak,' ujarnya di hadapan wali murid saat apel penyambutan piala kejuaraan.",
                'image_url' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => true,
                'views' => 980,
                'published_at' => now()->subDays(4),
            ],
            [
                'category_id' => $catModels['tabligh-tarjih']->id,
                'user_id' => $admin?->id,
                'title' => 'Kajian Rutin Ahad Pagi Masjid At-Taqwa Simo: Fiqih Ibadah Praktis Sesuai HPT Muhammadiyah',
                'slug' => 'kajian-rutin-ahad-pagi-masjid-at-taqwa-simo-fiqih-ibadah-praktis',
                'excerpt' => 'Kajian Ahad Pagi yang diselenggarakan Majelis Tabligh PCM Simo membedah tuntunan shalat dan thaharah berlandaskan Himpunan Putusan Tarjih.',
                'content' => "Masjid Besar At-Taqwa Simo kembali dipadati ribuan jamaah dari berbagai ranting di wilayah Kecamatan Simo pada Ahad pagi. Pengajian rutin yang diampu oleh ustadz dari Majelis Tarjih dan Tajdid PDM Boyolali kali ini mengupas secara tuntas mengenai tata cara bersuci dan pelaksanaan shalat yang merujuk langsung kepada sunnah Rasulullah SAW sebagaimana tercantum dalam Himpunan Putusan Tarjih (HPT) Muhammadiyah.\n\nAntusiasme jamaah terlihat dari banyaknya pertanyaan interaktif seputar problematika ibadah harian. Majelis Tabligh PCM Simo juga menyediakan sarapan pagi gratis dan pemeriksaan kesehatan tensi/gula darah cuma-cuma bekerjasama dengan relawan medis Klinik PKU Muhammadiyah Simo.",
                'image_url' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => false,
                'views' => 745,
                'published_at' => now()->subDays(6),
            ],
            [
                'category_id' => $catModels['aisyiyah-ortom']->id,
                'user_id' => $admin?->id,
                'title' => 'PCA Simo Resmikan Program Ketahanan Pangan Keluarga & Pelatihan UMKM Ibu-Ibu Ranting',
                'slug' => 'pca-simo-resmikan-program-ketahanan-pangan-keluarga',
                'excerpt' => 'Pimpinan Cabang Aisyiyah Simo menginisiasi kebun bibit sayur dan pendampingan izin halal bagi produk olahan pangan binaan Aisyiyah.',
                'content' => "Pimpinan Cabang 'Aisyiyah (PCA) Simo terus bergerak nyata memberdayakan kaum perempuan dan memperkuat ketahanan keluarga. Melalui Majelis Ekonomi dan Ketenagakerjaan, PCA Simo meresmikan program 'Lumbung Hidup Aisyiyah' sekaligus menggelar lokakarya sertifikasi produk halal bagi puluhan pelaku UMKM binaan di Pendopo Gedung Dakwah Simo.\n\nKetua PCA Simo menyampaikan bahwa kemandirian ekonomi keluarga adalah pilar penting kesejahteraan masyarakat. Dengan pendampingan legalitas usaha dan pemasaran digital, diharapkan produk-produk lokal karya warga persyarikatan dapat menembus pasar yang lebih luas.",
                'image_url' => 'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => false,
                'views' => 560,
                'published_at' => now()->subDays(8),
            ],
            [
                'category_id' => $catModels['pendidikan-aum']->id,
                'user_id' => $admin?->id,
                'title' => 'Klinik Pratama PKU Muhammadiyah Simo Buka Layanan Fisioterapi & USG 4D Bagi Masyarakat',
                'slug' => 'klinik-pratama-pku-muhammadiyah-simo-buka-layanan-fisioterapi',
                'excerpt' => 'Sebagai wujud kepedulian di bidang kesehatan, Klinik PKU Simo kini menambah fasilitas penunjang medis modern dengan dokter spesialis berpengalaman.',
                'content' => "Klinik Pratama PKU Muhammadiyah Simo terus meningkatkan mutu layanannya. Mulai bulan ini, masyarakat Simo dan sekitarnya dapat memanfaatkan poli fisioterapi modern untuk rehabilitasi stroke, cidera tulang, serta fasilitas pemeriksaan ultrasonografi (USG) 4 Dimensi untuk ibu hamil dengan tarif yang sangat terjangkau.\n\nDirektur Klinik menegaskan komitmen PKU Muhammadiyah untuk melayani umat dengan hati dan semangat *Penolong Kesengsaraan Oemoem*. Layanan ini juga menerima pasien pemegang BPJS Kesehatan secara optimal tanpa diskriminasi.",
                'image_url' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => false,
                'views' => 890,
                'published_at' => now()->subDays(10),
            ],
        ];

        foreach ($articles as $art) {
            Article::updateOrCreate(['slug' => $art['slug']], $art);
        }

        // 3. Officials (Pimpinan Cabang Muhammadiyah Simo)
        $officials = [
            [
                'name' => 'Drs. H. Sudarsono, M.Ag.',
                'position' => 'Ketua Pimpinan Cabang',
                'period' => '2022 - 2027',
                'image_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
                'sort_order' => 1,
            ],
            [
                'name' => 'H. Ahmad Fauzi, S.Pd.I.',
                'position' => 'Wakil Ketua Bidang Tabligh & Tarjih',
                'period' => '2022 - 2027',
                'image_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
                'sort_order' => 2,
            ],
            [
                'name' => 'Bambang Sugiarto, S.Pd., M.Pd.',
                'position' => 'Sekretaris PCM Simo',
                'period' => '2022 - 2027',
                'image_url' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80',
                'sort_order' => 3,
            ],
            [
                'name' => 'H. Joko Prasetyo, S.E.',
                'position' => 'Bendahara PCM Simo',
                'period' => '2022 - 2027',
                'image_url' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80',
                'sort_order' => 4,
            ],
            [
                'name' => 'Ir. Muhammad Arifin',
                'position' => 'Ketua Majelis Dikdasmen & PNF',
                'period' => '2022 - 2027',
                'image_url' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80',
                'sort_order' => 5,
            ],
            [
                'name' => 'dr. H. Wahyu Hidayat',
                'position' => 'Ketua Majelis Pembina Kesehatan Umum (MPKU)',
                'period' => '2022 - 2027',
                'image_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
                'sort_order' => 6,
            ],
        ];

        foreach ($officials as $off) {
            Official::updateOrCreate(['name' => $off['name']], $off);
        }

        // 4. Amal Usaha Muhammadiyah (AUM) Simo
        $aums = [
            [
                'name' => 'SD Muhammadiyah Program Khusus Simo',
                'category' => 'Pendidikan',
                'address' => 'Jl. Simo - Bangak Km. 1, Pelem, Simo, Boyolali',
                'leader' => 'Suyanto, S.Pd.',
                'phone' => '(0276) 3294101',
                'image_url' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=800&q=80',
                'description' => 'Sekolah dasar unggulan berkarakter islami dengan program tahfidz dan bilingual class.',
            ],
            [
                'name' => 'SMP Muhammadiyah 2 Simo',
                'category' => 'Pendidikan',
                'address' => 'Jl. Raya Simo - Klego No. 15, Simo, Boyolali',
                'leader' => 'Dra. Sri Wahyuni',
                'phone' => '(0276) 3294202',
                'image_url' => 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=800&q=80',
                'description' => 'Mencetak kader persyarikatan berprestasi di bidang akademik, sains, dan seni budaya.',
            ],
            [
                'name' => 'Klinik Pratama PKU Muhammadiyah Simo',
                'category' => 'Kesehatan',
                'address' => 'Jl. Simo - Karanggede, Simo, Boyolali',
                'leader' => 'dr. Retno Wulandari',
                'phone' => '(0276) 3294303',
                'image_url' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=800&q=80',
                'description' => 'Pelayanan rawat inap, IGD 24 Jam, laboratorium, poli gigi, bersalin, dan farmasi.',
            ],
            [
                'name' => 'Masjid Besar At-Taqwa Muhammadiyah Simo',
                'category' => 'Masjid & Sosial',
                'address' => 'Pusat Kecamatan Simo (Kompleks Gedung Dakwah), Boyolali',
                'leader' => 'H. Sukirno, B.A.',
                'phone' => '(0276) 3294404',
                'image_url' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=800&q=80',
                'description' => 'Pusat pembinaan ruhaniyah, kajian Ahad pagi, baitul mal, dan kegiatan pemuda islam.',
            ],
            [
                'name' => 'Panti Asuhan Yatim Muhammadiyah (PAYM) Simo',
                'category' => 'Sosial',
                'address' => 'Desa Simo, RT 04 / RW 02, Kec. Simo, Boyolali',
                'leader' => 'Hj. Siti Mariyam',
                'phone' => '(0276) 3294505',
                'image_url' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=800&q=80',
                'description' => 'Lembaga kesejahteraan sosial anak yang mengasuh dan membina anak-anak yatim dan dhuafa.',
            ],
        ];

        foreach ($aums as $aum) {
            Aum::updateOrCreate(['name' => $aum['name']], $aum);
        }
    }
}
