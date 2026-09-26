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
                'description' => 'Warta kegiatan dan kabar terkini seputar PCM Simo dan pimpinan ranting.',
            ],
            [
                'name' => 'Tabligh & Tarjih',
                'slug' => 'tabligh-tarjih',
                'color' => '#0A2540',
                'description' => 'Kajian keislaman, fiqih ibadah, tuntunan sunnah, dan dakwah kultural.',
            ],
            [
                'name' => 'Pendidikan & AUM',
                'slug' => 'pendidikan-aum',
                'color' => '#D97706',
                'description' => 'Informasi sekolah, pesantren, klinik PKU, dan amal usaha Muhammadiyah di Simo.',
            ],
            [
                'name' => 'Aisyiyah & Ortom',
                'slug' => 'aisyiyah-ortom',
                'color' => '#059669',
                'description' => 'Aktivitas PCA Simo, Pemuda Muhammadiyah, Nasyiah, KOKAM, IPM, dan HW.',
            ],
            [
                'name' => 'Opini & Inspirasi',
                'slug' => 'opini-inspirasi',
                'color' => '#4338CA',
                'description' => 'Tulisan gagasan, refleksi keumatan, dan dakwah pencerahan Islam berkemajuan.',
            ],
        ];

        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[$cat['slug']] = Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 2. Articles (10 Berita Otentik Seputar PCM Simo Boyolali)
        $articles = [
            [
                'category_id' => $catModels['tabligh-tarjih']->id,
                'user_id' => $admin?->id,
                'title' => 'Gedung Dakwah PCM Simo Dipadati Ribuan Jamaah: Pengajian Ahad Pagi Hadirkan Dakwah Wayang Golek Pitutur',
                'slug' => 'gedung-dakwah-pcm-simo-dipadati-ribuan-jamaah-pengajian-ahad-pagi-wayang-golek-pitutur',
                'excerpt' => 'Ribuan jamaah dan warga persyarikatan memadati Gedung Dakwah PCM Simo di Jl. Singoprono Utara untuk mengikuti Pengajian Ahad Pagi dengan media dakwah kultural Wayang Golek Pitutur.',
                'content' => "<p>Suasana hangat dan penuh keceriaan menyelimuti kompleks Gedung Dakwah Pimpinan Cabang Muhammadiyah (PCM) Simo di Jl. Singoprono Utara, Ngaliyan, Pelem, Simo, Boyolali pada perhelatan Pengajian Rutin Ahad Pagi. Sejak pukul 06.00 WIB, ribuan warga, simpatisan persyarikatan, serta rombongan jamaah dari berbagai pimpinan ranting se-Kecamatan Simo telah berbondong-bondong memadati aula utama hingga halaman gedung dakwah.</p>\n\n<p>Pengajian kali ini menghadirkan nuansa istimewa dengan penampilan dakwah kultural bertajuk <strong>'Wayang Golek Pitutur'</strong> yang dibawakan oleh Ki Ustaz Pujiono, anggota Majelis Tabligh PWM Jawa Tengah sekaligus Mudir Ponpes Muhammadiyah Manafiul Ulum Sambi. Melalui lakon carangan yang sarat petuah hikmah, Ki Dalang menyampaikan pesan-pesan penting mengenai kesalehan sosial, birrul walidain, keteladanan akhlak Nabi Muhammad SAW, serta tanggung jawab menjaga kelestarian lingkungan hidup.</p>\n\n<p>Ketua PCM Simo, <strong>H. Sholikin, S.Pd.</strong>, dalam sambutannya menyampaikan rasa syukur dan terima kasih yang mendalam atas ghirah jamaah yang tak pernah surut. <em>'Pendekatan dakwah yang komunikatif, menggembirakan, dan sarat nilai luhur seperti Wayang Golek Pitutur ini sangat efektif menjangkau seluruh lapisan masyarakat—mulai dari generasi sepuh hingga anak-anak. Muhammadiyah hadir menggembirakan umat dengan pencerahan yang membumi,'</em> tutur beliau.</p>\n\n<p>Selain siraman rohani, panitia pengajian bersama relawan medis Klinik PKU Muhammadiyah Simo juga menyediakan layanan pemeriksaan kesehatan gratis berupa cek tensi darah dan gula darah, serta pembagian ratusan porsi sarapan pagi berkah sumbangan dari warga ranting secara bergiliran.</p>",
                'image_url' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => true,
                'views' => 1845,
                'published_at' => now()->subDays(1),
            ],
            [
                'category_id' => $catModels['pendidikan-aum']->id,
                'user_id' => $admin?->id,
                'title' => 'Kecamatan Simo Sukses Jadi Tuan Rumah PORSIMU SD/MI Muhammadiyah se-Boyolali, Cetak Generasi Unggul dan Berkarakter',
                'slug' => 'kecamatan-simo-sukses-jadi-tuan-rumah-porsimu-sd-mi-muhammadiyah-se-boyolali',
                'excerpt' => 'Ajang bergengsi Pekan Olahraga, Sains, dan Al-Islam Kemuhammadiyahan (PORSIMU) sukses digelar di Simo, diikuti ribuan santri dan atlet cilik dari berbagai sekolah Muhammadiyah se-Boyolali.',
                'content' => "<p>Kecamatan Simo dipercaya menjadi pusat kemeriahan perhelatan akbar <strong>Pekan Olahraga, Sains, dan Seni Al-Islam Kemuhammadiyahan (PORSIMU)</strong> tingkat Kabupaten Boyolali. Ajang kompetisi tahunan yang diinisiasi Forum Komunikasi Kepala Sekolah (FKKS) SD/MI Muhammadiyah ini diikuti oleh ribuan siswa dari puluhan kontingen sekolah se-Boyolali.</p>\n\n<p>Kegiatan resmi dibuka oleh Ketua Pimpinan Cabang Muhammadiyah (PCM) Simo, <strong>H. Sholikin, S.Pd.</strong>, bertempat di lapangan perguruan Muhammadiyah Simo. Dalam pidato pembukaannya, beliau menegaskan bahwa PORSIMU bukan sekadar ajang perebutan medali dan piala, melainkan sarana silaturahmi akbar dan pembentukan integritas karakter generasi penerus bangsa.</p>\n\n<p><em>'Di lapangan dan di ruang ujian sains ini, anak-anak kita belajar tentang kejujuran, disiplin, sportivitas, serta ukhuwah islamiyah. Menang atau kalah adalah dinamika, namun akhlak mulia dan kecintaan pada Al-Qur'an adalah mahkota utama,'</em> pesan H. Sholikin di hadapan para kepala sekolah dan official kontingen.</p>\n\n<p>Berbagai cabang lomba dipertandingkan dengan antusiasme tinggi, antara lain Futsal, Bulu Tangkis, Tenis Meja, Lari Cepat, Olimpiade Matematika dan IPA Terpadu, Olimpiade ISMUBA (Al-Islam, Kemuhammadiyahan, Bahasa Arab), serta lomba seni paduan suara dan tilawah Al-Qur'an. Kontingen tuan rumah dari Simo berhasil menorehkan prestasi gemilang di sejumlah cabang bergengsi.</p>",
                'image_url' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => true,
                'views' => 1520,
                'published_at' => now()->subDays(3),
            ],
            [
                'category_id' => $catModels['berita-cabang']->id,
                'user_id' => $admin?->id,
                'title' => 'Konsolidasi PCM Simo dan Pimpinan Ranting: Perkuat Dakwah Akar Rumput dan Digitalisasi Administrasi Organisasi',
                'slug' => 'konsolidasi-pcm-simo-dan-pimpinan-ranting-perkuat-dakwah-akar-rumput',
                'excerpt' => 'Pimpinan Cabang Muhammadiyah Simo menggelar rapat konsolidasi bersama jajaran pimpinan ranting se-Kecamatan Simo guna menyelaraskan program dakwah dan tata kelola aset persyarikatan.',
                'content' => "<p>Guna memperkokoh barisan dakwah di tingkat akar rumput, jajaran Pimpinan Cabang Muhammadiyah (PCM) Simo menyelenggarakan Rapat Koordinasi dan Konsolidasi Cabang bertempat di Aula Gedung Dakwah Muhammadiyah Simo. Pertemuan strategis ini dihadiri oleh utusan Pimpinan Ranting Muhammadiyah (PRM) dari seluruh desa di wilayah Simo, antara lain Ranting Pelem, Walen, Sumber, Bendungan, Blagung, Wates, Pentur, Kedunglengkong, dan Tapan.</p>\n\n<p>Agenda utama pertemuan mencakup evaluasi program kerja majelis, pemetaan potensi wakaf persyarikatan, serta percepatan sistem pendataan anggota berbasis digital. Jajaran pimpinan menegaskan komitmen agar tertib administrasi dan legalitas hukum seluruh tanah wakaf masjid, mushola, dan gedung sekolah Muhammadiyah di Simo dapat terselesaikan dengan rapi.</p>\n\n<p>Jajaran pimpinan PCM Simo menekankan bahwa ranting adalah pilar sejati denyut nadi persyarikatan. <em>'Muhammadiyah hidup dan mengakar karena pengorbanan para aktivis ranting yang tak kenal lelah mengurusi jamaah dan memakmurkan masjid. PCM hadir untuk mendampingi, memfasilitasi, dan memastikan seluruh program sinergis bergerak satu arah,'</em> ungkap perwakilan sekretariat PCM Simo.</p>",
                'image_url' => 'https://images.unsplash.com/photo-1577962917302-cd874c4e31d2?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => false,
                'views' => 930,
                'published_at' => now()->subDays(5),
            ],
            [
                'category_id' => $catModels['pendidikan-aum']->id,
                'user_id' => $admin?->id,
                'title' => 'Kiprah SMA Muhammadiyah 1 Simo: Asah Kemandirian Siswa Melalui Kemah Hizbul Wathan dan Prestasi Akademik',
                'slug' => 'kiprah-sma-muhammadiyah-1-simo-asah-kemandirian-siswa-melalui-kemah-hw',
                'excerpt' => 'Sebagai salah satu amal usaha pendidikan menengah kebanggaan di Boyolali Utara, SMA Muhammadiyah 1 Simo terus mencetak generasi kader yang tangguh, terampil, dan religius.',
                'content' => "<p><strong>SMA Muhammadiyah 1 Simo (SMAMSA)</strong> terus mengukuhkan posisinya sebagai institusi pendidikan menengah rujukan bagi masyarakat di kawasan Simo dan sekitarnya. Memadukan kurikulum nasional modern dengan pembinaan Al-Islam dan Kemuhammadiyahan yang komprehensif, sekolah ini konsisten menorehkan prestasi baik di bidang sains maupun kepanduan.</p>\n\n<p>Baru-baru ini, Qabilah Gerakan Kepanduan Hizbul Wathan (HW) SMA Muhammadiyah 1 Simo sukses menyelenggarakan Perkemahan Akbar Taruna HW di lereng perbukitan Simo. Selama tiga hari kegiatan, ratusan pandu pengenal dan penghela dilatih ketahanan fisik, survival alam, tali-temali, pertolongan pertama pada kecelakaan (P3K), serta kepemimpinan berwawasan kebangsaan.</p>\n\n<p>Kepala SMA Muhammadiyah 1 Simo menegaskan bahwa kegiatan kepanduan HW dan beladiri Tapak Suci Putera Muhammadiyah merupakan wadah pembentukan disiplin dan mental mandiri siswa. <em>'Lulusan kami tidak hanya dipersiapkan untuk menembus seleksi perguruan tinggi negeri dan swasta favorit, tetapi juga memiliki jiwa korsa keislaman dan kepedulian sosial yang tinggi di tengah masyarakat,'</em> jelas beliau.</p>",
                'image_url' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => false,
                'views' => 1105,
                'published_at' => now()->subDays(7),
            ],
            [
                'category_id' => $catModels['aisyiyah-ortom']->id,
                'user_id' => $admin?->id,
                'title' => 'PCPM dan KOKAM Cabang Simo Gelar Apel Kesiapsiagaan: Siap Kawal Dakwah dan Tanggap Bencana Bersama MDMC',
                'slug' => 'pcpm-dan-kokam-cabang-simo-gelar-apel-kesiapsiagaan-kawal-dakwah',
                'excerpt' => 'Komando Kesiapsiagaan Angkatan Muda Muhammadiyah (KOKAM) Cabang Simo menggelar apel konsolidasi dan latihan SAR tanggap darurat bencana bersama MDMC Boyolali.',
                'content' => "<p>Pimpinan Cabang Pemuda Muhammadiyah (PCPM) Simo bersama puluhan personil <strong>KOKAM (Komando Kesiapsiagaan Angkatan Muda Muhammadiyah) Cabang Simo</strong> menggelar Apel Kesiapsiagaan Kader dan Latihan Tanggap Darurat Bencana di kompleks perguruan Muhammadiyah Simo.</p>\n\n<p>Komandan KOKAM Cabang Simo menegaskan bahwa KOKAM memiliki tugas mulia sebagai benteng persyarikatan dan penjaga keutuhan NKRI. Dalam sesi pembekalan, para kader muda mendapatkan materi navigasi darat, manajemen dapur umum, serta evakuasi medis darurat yang dipandu langsung oleh instruktur dari Muhammadiyah Disaster Management Center (MDMC) Kabupaten Boyolali.</p>\n\n<p>Selain kesiapsiagaan kebencanaan, kader Pemuda Muhammadiyah di tingkat ranting juga giat menggerakkan aksi sosial kemasyarakatan, salah satunya program donor darah berkala di Ranting Walen dan pengamanan arus lalu lintas pengajian akbar di sepanjang jalan protokol Simo.</p>",
                'image_url' => 'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => false,
                'views' => 840,
                'published_at' => now()->subDays(9),
            ],
            [
                'category_id' => $catModels['aisyiyah-ortom']->id,
                'user_id' => $admin?->id,
                'title' => 'PCA Simo Gulirkan Gerakan Lumbung Hidup dan Pendampingan Sertifikasi Halal bagi UMKM Warga Ranting',
                'slug' => 'pca-simo-gulirkan-gerakan-lumbung-hidup-dan-pendampingan-sertifikasi-halal',
                'excerpt' => 'Pimpinan Cabang Aisyiyah Simo melalui Majelis Ekonomi mendorong kemandirian keluarga lewat budidaya pekarangan hijau dan fasilitasi legalitas produk UMKM lokal.',
                'content' => "<p>Gerakan dakwah perempuan berkemajuan terus dibuktikan secara nyata oleh <strong>Pimpinan Cabang 'Aisyiyah (PCA) Simo</strong>. Bertempat di Pendopo Gedung Dakwah Simo, Majelis Ekonomi dan Ketenagakerjaan PCA Simo menggelar Pelatihan Pengelolaan Pekarangan Rumah Berkah ('Lumbung Hidup') serta Klinik Konsultasi Sertifikasi Halal bagi para pelaku UMKM rumahan.</p>\n\n<p>Program Lumbung Hidup 'Aisyiyah membagikan ratusan bibit sayuran, cabai, dan tanaman obat keluarga (toga) kepada perwakilan ibu-ibu Pimpinan Ranting 'Aisyiyah (PRA) se-Kecamatan Simo. Program ini bertujuan memperkuat ketahanan pangan hewani dan nabati keluarga dari halaman rumah sendiri di tengah fluktuasi harga kebutuhan pokok.</p>\n\n<p>Ketua PCA Simo menyatakan bahwa perempuan memiliki peranan strategis dalam menopang ekonomi keluarga sakinah. <em>'Melalui pendampingan NIB (Nomor Induk Berusaha) dan sertifikasi halal gratis dari Halal Center Muhammadiyah, kami ingin produk kue basah, keripik, dan aneka olahan warga Simo bisa naik kelas dan dipasarkan secara luas,'</em> ungkapnya.</p>",
                'image_url' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => false,
                'views' => 760,
                'published_at' => now()->subDays(11),
            ],
            [
                'category_id' => $catModels['pendidikan-aum']->id,
                'user_id' => $admin?->id,
                'title' => 'SD Muhammadiyah Program Khusus Simo Wisuda Puluhan Siswa Penghafal Al-Qur\'an Juz 30 dan 29',
                'slug' => 'sd-muhammadiyah-program-khusus-simo-wisuda-puluhan-siswa-penghafal-al-quran',
                'excerpt' => 'Rona haru dan bahagia terpancar dari wali murid saat putra-putri SD Muhammadiyah Simo sukses menuntaskan uji publik hafalan Al-Qur\'an Juz 30 dan 29 dengan predikat mutqin.',
                'content' => "<p>Sebanyak 48 siswa <strong>SD Muhammadiyah Program Khusus Simo</strong> resmi diwisuda dalam agenda Khotmil Qur'an dan Wisuda Tahfidz yang berlangsung khidmat di Gedung Pertemuan Simo. Para siswa yang diwisuda telah melalui tahapan munaqosyah ketat pada hafalan Juz 30, Juz 29, serta tartil tajwid dengan predikat sangat memuaskan.</p>\n\n<p>Dalam sesi uji publik yang disaksikan langsung oleh para orang tua, dewan penguji membacakan potongan ayat secara acak dan langsung disambung dengan lancar oleh para wisudawan cilik. Momen haru memuncak ketika para siswa turun panggung untuk menyematkan mahkota simbolis dan memeluk kedua orang tua masing-masing sebagai wujud bakti anak saleh.</p>\n\n<p>Kepala SD Muhammadiyah Simo menyampaikan bahwa program tahfidz merupakan kurikulum unggulan yang dipadukan dengan pembiasaan shalat dhuha berjamaah dan penanaman adab islami. <em>'Kami ingin mencetak ilmuwan dan calon pemimpin masa depan yang di dalam dadanya terpatri ayat-ayat suci Al-Qur'an,'</em> tutur beliau.</p>",
                'image_url' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => false,
                'views' => 1240,
                'published_at' => now()->subDays(13),
            ],
            [
                'category_id' => $catModels['berita-cabang']->id,
                'user_id' => $admin?->id,
                'title' => 'Lazismu Kantor Layanan Simo Salurkan Paket Sembako Berkah dan Beasiswa Sang Surya untuk Santri Dhuafa',
                'slug' => 'lazismu-kantor-layanan-simo-salurkan-paket-sembako-berkah-dan-beasiswa',
                'excerpt' => 'Sebagai wujud filantropi Islam berkemajuan, KL Lazismu Simo mendistribusikan amanah donatur berupa ratusan paket sembako dhuafa dan bantuan beasiswa pendidikan.',
                'content' => "<p><strong>Kantor Layanan (KL) Lazismu Simo</strong> kembali menyalurkan dana Zakat, Infak, dan Sedekah (ZIS) dari para muzakki dan donatur melalui program 'Kado Ramadhan & Paket Sembako Berkah Dhuafa' serta penyerahan Beasiswa Mentari-Sang Surya bagi santri berprestasi dari keluarga prasejahtera se-Kecamatan Simo.</p>\n\n<p>Penyaluran bantuan dilakukan secara jemput bola langsung ke rumah-rumah penerima manfaat di desa-desa terpencil dengan dibantu oleh armada KOKAM dan relawan muda Muhammadiyah Simo. Langkah ini dilakukan agar bantuan benar-benar sampai kepada yang berhak tanpa membebani biaya transportasi bagi para lansia dhuafa.</p>\n\n<p>Ketua Badan Pengurus KL Lazismu Simo menyampaikan apresiasi kepada seluruh donatur dan agniya di wilayah Simo. <em>'Kepercayaan umat adalah amanah tertinggi bagi kami. Melalui Lazismu, dana zakat dan sedekah dikelola secara transparan dan ditransformasikan menjadi program berdaya guna—mulai dari beasiswa pendidikan anak yatim hingga pemberdayaan modal usaha mikro,'</em> pungkasnya.</p>",
                'image_url' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => false,
                'views' => 670,
                'published_at' => now()->subDays(15),
            ],
            [
                'category_id' => $catModels['pendidikan-aum']->id,
                'user_id' => $admin?->id,
                'title' => 'Klinik Pratama PKU Muhammadiyah Simo Tingkatkan Layanan Fisioterapi dan Siap Layani Pasien BPJS Kesehatan',
                'slug' => 'klinik-pratama-pku-muhammadiyah-simo-tingkatkan-layanan-fisioterapi-dan-bpjs',
                'excerpt' => 'Fasilitas penunjang medis modern, dokter jaga 24 jam, dan poli rawat inap terus disempurnakan demi mewujudkan pelayanan kesehatan umat yang prima di kawasan Simo.',
                'content' => "<p><strong>Klinik Pratama PKU Muhammadiyah Simo</strong> terus berbenah dan meningkatkan sarana prasarana kesehatan bagi masyarakat Kecamatan Simo dan sekitarnya. Fasilitas medis yang terletak di jalur strategis Simo ini kini resmi memperluas poli pelayanan dengan menghadirkan layanan Fisioterapi rehabilitasi medik serta instalasi USG 4D untuk pemeriksaan kandungan ibu hamil.</p>\n\n<p>Sebagai fasilitas kesehatan tingkat pertama yang melayani peserta BPJS Kesehatan, Klinik PKU Muhammadiyah Simo beroperasi dengan dokter dan perawat berpengalaman serta didukung layanan ambulans siaga 24 jam siap jemput pasien gawat darurat tanpa dipungut biaya bagi masyarakat dhuafa.</p>\n\n<p>Direktur operasional klinik menegaskan bahwa semangat pelayanan PKU berakar dari teologi <em>Penolong Kesengsaraan Oemoem</em> yang digagas K.H. Ahmad Dahlan. <em>'Kami ingin setiap warga yang datang berobat merasakan ketenangan, keramahan, dan kesembuhan dengan biaya yang sangat terjangkau serta pelayanan berstandar islami,'</em> tegasnya.</p>",
                'image_url' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => false,
                'views' => 1080,
                'published_at' => now()->subDays(18),
            ],
            [
                'category_id' => $catModels['opini-inspirasi']->id,
                'user_id' => $admin?->id,
                'title' => 'Meneladani Teologi Al-Ma\'un di Bumi Simo: Membumikan Islam Berkemajuan Lewat Amal Usaha Nyata',
                'slug' => 'meneladani-teologi-al-maun-di-bumi-simo-membumikan-islam-berkemajuan',
                'excerpt' => 'Refleksi mendalam tentang gerakan persyarikatan di tingkat cabang: bahwa dakwah Muhammadiyah bukan sekadar narasi mimbar, melainkan karya nyata mencerdaskan dan menyehatkan bangsa.',
                'content' => "<p>Ketika K.H. Ahmad Dahlan mengulang-ulang pengajaran Surat Al-Ma'un kepada santri-santrinya di Kauman Yogyakarta lebih dari satu abad silam, beliau tidak sedang mengajarkan teori hafalan semata. Beliau mengajarkan sebuah tindakan nyata: bagaimana iman kepada Allah SWT harus mewujud dalam kepedulian konkret kepada kaum tertindas, fakir miskin, dan anak-anak yatim.</p>\n\n<p>Semangat Al-Ma'un itulah yang hari ini terus hidup dan berdenyut di bumi <strong>Simo, Kabupaten Boyolali</strong>. Di balik hijaunya hamparan pedesaan Simo, berdirilah gedung-gedung dakwah, sekolah-sekolah Muhammadiyah dari jenjang dasar hingga menengah atas, panti asuhan yatim, serta klinik kesehatan PKU yang setiap hari melayani masyarakat tanpa pamrih.</p>\n\n<p>Para pimpinan cabang, pimpinan ranting, 'Aisyiyah, dan pemuda di Simo mewakafkan waktu, tenaga, dan pikiran mereka bukan untuk mencari popularitas politik, melainkan demi membumikan risalah Islam Berkemajuan. Kehadiran portal digital dan sistem informasi PCM Simo ini menjadi babak baru untuk merajut sinergi, mencatat sejarah kebaikan, dan menerangi peradaban umat menuju Simo yang maju, mandiri, dan berkah.</p>",
                'image_url' => 'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?auto=format&fit=crop&w=1200&q=80',
                'status' => 'published',
                'is_featured' => false,
                'views' => 895,
                'published_at' => now()->subDays(20),
            ],
        ];

        foreach ($articles as $art) {
            Article::updateOrCreate(['slug' => $art['slug']], $art);
        }

        // 3. Officials (Pimpinan Cabang Muhammadiyah Simo Periode 2022 - 2027)
        $officials = [
            [
                'name' => 'H. Sholikin, S.Pd.',
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
                'name' => 'SMA Muhammadiyah 1 Simo (SMAMSA)',
                'category' => 'Pendidikan',
                'address' => 'Jl. Singoprono No. 03, Ngaliyan, Pelem, Simo, Boyolali',
                'leader' => 'Drs. H. Sriyanto',
                'phone' => '(0276) 3294101',
                'image_url' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80',
                'description' => 'Sekolah menengah atas unggulan persyarikatan di Boyolali Utara dengan program pembinaan Hizbul Wathan, Tapak Suci, dan persiapan perguruan tinggi favorit.',
            ],
            [
                'name' => 'SD Muhammadiyah Program Khusus Simo',
                'category' => 'Pendidikan',
                'address' => 'Jl. Simo - Bangak Km. 1, Pelem, Simo, Boyolali',
                'leader' => 'Suyanto, S.Pd.',
                'phone' => '(0276) 3294102',
                'image_url' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=800&q=80',
                'description' => 'Sekolah dasar unggulan berkarakter islami dengan program tahfidzul qur\'an, pembiasaan adab harian, dan kelas bilingual.',
            ],
            [
                'name' => 'SMP Muhammadiyah 2 Simo',
                'category' => 'Pendidikan',
                'address' => 'Jl. Raya Simo - Klego No. 15, Simo, Boyolali',
                'leader' => 'Dra. Sri Wahyuni',
                'phone' => '(0276) 3294202',
                'image_url' => 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=800&q=80',
                'description' => 'Mencetak kader persyarikatan yang religius dan berprestasi di bidang akademik, sains, olahraga, dan seni budaya.',
            ],
            [
                'name' => 'Klinik Pratama PKU Muhammadiyah Simo',
                'category' => 'Kesehatan',
                'address' => 'Jl. Raya Simo - Karanggede, Simo, Boyolali',
                'leader' => 'dr. Retno Wulandari',
                'phone' => '(0276) 3294303',
                'image_url' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=800&q=80',
                'description' => 'Pelayanan rawat jalan BPJS, instalasi gawat darurat 24 jam, poli fisioterapi, pemeriksaan USG 4D, bersalin, dan mobil ambulans siaga umat.',
            ],
            [
                'name' => 'Gedung Dakwah Muhammadiyah & Masjid At-Taqwa Simo',
                'category' => 'Masjid & Dakwah',
                'address' => 'Jl. Singoprono Utara, Ngaliyan, Pelem, Kec. Simo, Kab. Boyolali',
                'leader' => 'H. Sholikin, S.Pd.',
                'phone' => '(0276) 3294404',
                'image_url' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=800&q=80',
                'description' => 'Pusat sekretariat PCM Simo, koordinasi majelis dan ortom, pengajian rutin Ahad pagi Wayang Golek Pitutur, serta sentra dakwah persyarikatan.',
            ],
            [
                'name' => 'Kantor Layanan Lazismu Simo',
                'category' => 'Sosial & Filantropi',
                'address' => 'Kompleks Gedung Dakwah PCM Simo, Jl. Singoprono Utara, Simo, Boyolali',
                'leader' => 'Drs. Suparno',
                'phone' => '(0276) 3294505',
                'image_url' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=800&q=80',
                'description' => 'Lembaga amil zakat, infak, dan sedekah terpercaya yang mengelola program beasiswa mentari, santunan dhuafa, dan tanggap kebencanaan.',
            ],
        ];

        foreach ($aums as $aum) {
            Aum::updateOrCreate(['name' => $aum['name']], $aum);
        }
    }
}
