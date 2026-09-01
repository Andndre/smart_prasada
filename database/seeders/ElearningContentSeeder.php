<?php

namespace Database\Seeders;

use App\Models\Ebook;
use App\Models\Materi;
use App\Models\Posttest;
use App\Models\Pretest;
use App\Models\SitusPeninggalan;
use App\Models\User;
use App\Models\VirtualMuseum;
use App\Models\VirtualMuseumObject;
use Illuminate\Database\Seeder;

class ElearningContentSeeder extends Seeder
{
    /**
     * Seed all elearning content for testing the complete flow.
     * Includes: Pretest, Ebook, Posttest, Situs, Virtual Museum
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (! $admin) {
            $this->command->warn('No admin user found. Skipping situs/virtual museum creation that requires user_id.');
            $admin = User::first();
        }

        // Get first materi to seed content (or skip if none)
        $materis = Materi::with('era')->get();

        if ($materis->isEmpty()) {
            $this->command->warn('No materi found. Please run MateriHierarchySeeder first.');

            return;
        }

        // Content data organized by era and materi topic
        $contentData = $this->getContentData();

        foreach ($materis as $materi) {
            $eraKode = $materi->era?->kode ?? 'A';
            $bab = $materi->bab ?? 1;
            $topicKey = $this->findMatchingTopic($materi->judul, $contentData, $eraKode, $bab);

            $this->seedPretest($materi, $topicKey);
            $this->seedEbook($materi, $topicKey);
            $this->seedPosttest($materi, $topicKey);
        }

        $this->seedSitus($admin);
    }

    private function getContentData(): array
    {
        return [
            // Era A - Prasejarah
            'Punden Berundak' => [
                'pretest' => [
                    ['q' => 'Apa yang dimaksud dengan punden berundak?', 'a' => 'Punden Berundak', 'b' => 'Sarkofagus', 'c' => 'Dolmen', 'd' => 'Menhir', 'e' => 'Arca', 'answer' => 'A'],
                    ['q' => 'Punden berundak berfungsi sebagai?', 'a' => 'Tempat tinggal', 'b' => 'Simpananan air', 'c' => 'Situs pemakaman', 'd' => 'Tempat ibadah Hindu', 'e' => 'Sarang burung', 'answer' => 'C'],
                    ['q' => 'Punden berundak ditemukan di daerah?', 'a' => 'Jawa Timur', 'b' => 'Bali', 'c' => 'Sumatera', 'd' => 'Kalimantan', 'e' => 'Sulawesi', 'answer' => 'B'],
                    ['q' => 'Bentuk punden berundak menyerupai?', 'a' => 'Lingkaran', 'b' => 'Segitiga', 'c' => 'Piramida kecil berundak', 'd' => 'Kotak', 'e' => 'Silinder', 'answer' => 'C'],
                    ['q' => 'Periode pembuatan punden berundak?', 'a' => '2000 SM', 'b' => '1000 SM - 800 M', 'c' => 'Abad ke-14', 'd' => 'Abad ke-18', 'e' => 'Abad ke-5', 'answer' => 'B'],
                ],
                'ebook' => ['judul' => 'Modul Punden Berundak', 'path' => 'ebooks/punden-berundak-modul.pdf'],
                'posttest' => [
                    ['q' => 'Berdasarkan penemuan, punden berundak berasal dari masa?', 'a' => 'Prasejarah', 'b' => 'Hindu-Buddha', 'c' => 'Majapahit', 'd' => 'Kolonial', 'e' => 'Modern', 'answer' => 'A'],
                    ['q' => 'Punden berundak di Bali banyak ditemukan di?', 'a' => 'Dataran tinggi Gianyar', 'b' => 'Pesisir pantai', 'c' => 'Gunung berapi', 'd' => 'Hutan dalam', 'e' => 'Sungai', 'answer' => 'A'],
                    ['q' => 'Struktur punden berundak terdiri dari berapa tingkatan?', 'a' => '2-3 tingkat', 'b' => '5-6 tingkat', 'c' => '7-8 tingkat', 'd' => '10+ tingkat', 'e' => '1 tingkat', 'answer' => 'C'],
                    ['q' => 'Bahan utama pembuatan punden berundak?', 'a' => 'Batu kapur', 'b' => 'Besi', 'c' => 'Kayu', 'd' => 'Tanah liat', 'e' => 'Logam', 'answer' => 'A'],
                    ['q' => 'Fungsi祭坛 punden berundak terkait dengan kepercayaan?', 'a' => 'Animisme', 'b' => 'Hindu', 'c' => 'Buddha', 'd' => 'Kristen', 'e' => 'Islam', 'answer' => 'A'],
                ],
            ],
            'Sarkofagus' => [
                'pretest' => [
                    ['q' => 'Sarkofagus adalah?', 'a' => 'Punden berundak', 'b' => 'Tempat menyimpan mayat', 'c' => 'Prasasti', 'd' => 'Candi', 'e' => 'Arca', 'answer' => 'B'],
                    ['q' => 'Bentuk sarkofagus menyerupai?', 'a' => 'Lingkaran', 'b' => 'Kotak batu', 'c' => 'Segitiga', 'd' => 'Silinder', 'e' => 'Layang-layang', 'answer' => 'B'],
                    ['q' => 'Sarkofagus di Bali memiliki ukiran?', 'a' => 'Gajah', 'b' => 'Wajah manusia', 'c' => 'Burung', 'd' => 'Ikan', 'e' => 'Kuda', 'answer' => 'B'],
                    ['q' => 'Periode sarkofagus di Bali?', 'a' => '1000 SM', 'b' => '500 SM - 500 M', 'c' => 'Abad ke-10', 'd' => 'Abad ke-15', 'e' => 'Abad ke-20', 'answer' => 'B'],
                    ['q' => 'Sarkofagus ditemukan bersama dengan?', 'a' => 'Punden berundak', 'b' => 'Menhir', 'c' => 'Dolmen', 'd' => 'Candi', 'e' => 'Prasasti', 'answer' => 'A'],
                ],
                'ebook' => ['judul' => 'Modul Sarkofagus', 'path' => 'ebooks/sarkofagus-modul.pdf'],
                'posttest' => [
                    ['q' => 'Sarkofagus termasuk artefak?', 'a' => 'Prasejarah', 'b' => 'Hindu-Buddha', 'c' => 'Majapahit', 'd' => 'Kolonial', 'e' => 'Modern', 'answer' => 'A'],
                    ['q' => 'Bahan sarkofagus?', 'a' => 'Batu kapur', 'b' => 'Batu andesit', 'c' => 'Granit', 'd' => 'Marmer', 'e' => 'Besi', 'answer' => 'B'],
                    ['q' => 'Sarkofagus Yeh Mengening memiliki ciri?', 'a' => 'Bentuk manusia', 'b' => 'Bentuk rumah', 'c' => 'Bentuk animal', 'd' => 'Bentuk tanaman', 'e' => 'Bentuk abstrak', 'answer' => 'A'],
                    ['q' => 'Fungsi sarkofagus dalam masyarakat prasejarah?', 'a' => 'Tempat tinggal', 'b' => 'Pemakaman', 'c' => 'Simpananan', 'd' => 'Peribadatan', 'e' => 'Penerangan', 'answer' => 'B'],
                    ['q' => 'Lokasi utama penemuan sarkofagus di Bali?', 'a' => 'Kuta', 'b' => 'Bedulu', 'c' => 'Denpasar', 'd' => 'Singaraja', 'e' => 'Sanur', 'answer' => 'B'],
                ],
            ],
            'Arca Megalitik' => [
                'pretest' => [
                    ['q' => 'Arca megalitik adalah?', 'a' => 'Patung batu besar', 'b' => 'Punden berundak', 'c' => 'Sarkofagus', 'd' => 'Prasasti', 'e' => 'Candi', 'answer' => 'A'],
                    ['q' => 'Arca megalitik di Bali dikenal dengan?', 'a' => 'Arca Dwarapala', 'b' => 'Arca Pandang', 'c' => 'Arca Payung', 'd' => 'Arca Singa', 'e' => 'Arca Wanita', 'answer' => 'B'],
                    ['q' => 'Ciri khas arca megalitik?', 'a' => 'Berukuran kecil', 'b' => 'Berukuran besar', 'c' => 'Terbuat dari kayu', 'd' => 'Berwarna cerah', 'e' => 'Bergerak', 'answer' => 'B'],
                    ['q' => 'Fungsi arca megalitik?', 'a' => 'Dekorasi', 'b' => 'Penanda kuburan', 'c' => 'Mainan', 'd' => 'Peralatan dapur', 'e' => 'Perhiasan', 'answer' => 'B'],
                    ['q' => 'Periode arca megalitik?', 'a' => 'Abad ke-20', 'b' => 'Abad ke-10', 'c' => 'Prasejarah', 'd' => 'Kolonial', 'e' => 'Kerajaan', 'answer' => 'C'],
                ],
                'ebook' => ['judul' => 'Modul Arca Megalitik', 'path' => 'ebooks/arca-megalitik-modul.pdf'],
                'posttest' => [
                    ['q' => 'Arca megalitik termasuk dalam?', 'a' => 'Peninggalan Hindu', 'b' => 'Peninggalan Buddha', 'c' => 'Peninggalan Prasejarah', 'd' => 'Peninggalan Kolonial', 'e' => 'Peninggalan Modern', 'answer' => 'C'],
                    ['q' => 'Bahan arca megalitik?', 'a' => 'Batu kapur', 'b' => 'Batu andesit', 'c' => 'Kayu', 'd' => 'Logam', 'e' => 'Tanah liat', 'answer' => 'B'],
                    ['q' => 'Tempat penemuan arca megalitik di Bali?', 'a' => 'Kuta Beach', 'b' => 'Gunung Agung', 'c' => 'Daerah dataran tinggi', 'd' => 'Pesisir', 'e' => 'Sungai', 'answer' => 'C'],
                    ['q' => 'Arca Dwarapala ditemukan di?', 'a' => 'Bedulu', 'b' => 'Tampaksiring', 'c' => 'Kintamani', 'd' => 'Jembrana', 'e' => 'Klungkung', 'answer' => 'B'],
                    ['q' => 'Perbedaan arca megalitik dengan arca Hindu-Buddha?', 'a' => 'Tidak ada perbedaan', 'b' => 'Ukuran lebih besar dan lebih tua', 'c' => 'Berwarna', 'd' => 'Dapat bergerak', 'e' => 'Terbuat dari emas', 'answer' => 'B'],
                ],
            ],
            'Menhir' => [
                'pretest' => [
                    ['q' => 'Menhir adalah?', 'a' => 'Tiang batu besar', 'b' => 'Sarkofagus', 'c' => 'Punden', 'd' => 'Candi', 'e' => 'Dolmen', 'answer' => 'A'],
                    ['q' => 'Bentuk menhir?', 'a' => 'Kotak', 'b' => 'Silinder/Pipih', 'c' => 'Bulat', 'd' => 'Kerucut', 'e' => 'Layang-layang', 'answer' => 'B'],
                    ['q' => 'Fungsi menhir?', 'a' => 'Tempat tinggal', 'b' => 'Penanda ritual', 'c' => 'Penyimpanan', 'd' => 'Perhiasan', 'e' => 'Pertahanan', 'answer' => 'B'],
                    ['q' => 'Menhir banyak ditemukan di?', 'a' => 'Bali', 'b' => 'Jawa', 'c' => 'Sumatera', 'd' => 'Sulawesi', 'e' => 'Kalimantan', 'answer' => 'A'],
                    ['q' => 'Periode menhir?', 'a' => '500 M', 'b' => '2000 SM', 'c' => 'Abad ke-15', 'd' => 'Abad ke-10', 'e' => '1945', 'answer' => 'B'],
                ],
                'ebook' => ['judul' => 'Modul Menhir', 'path' => 'ebooks/menhir-modul.pdf'],
                'posttest' => [
                    ['q' => 'Menhir termasuk peninggalan?', 'a' => 'Prasejarah', 'b' => 'Hindu-Buddha', 'c' => 'Majapahit', 'd' => 'Kolonial', 'e' => 'Modern', 'answer' => 'A'],
                    ['q' => 'Perbedaan menhir dan tiang biasa?', 'a' => 'Tidak ada', 'b' => 'Ukuran dan umur', 'c' => 'Bahan', 'd' => 'Warna', 'e' => 'Bentuk', 'answer' => 'B'],
                    ['q' => 'Lokasi penemuan menhir di Bali?', 'a' => 'Kuta', 'b' => 'Bedulu', 'c' => 'Singaraja', 'd' => 'Denpasar', 'e' => 'Nusa Dua', 'answer' => 'B'],
                    ['q' => 'Orientasi menhir sering dikaitkan dengan?', 'a' => 'Arah angin', 'b' => 'Arah matahari', 'c' => 'Arah air', 'd' => 'Arah laut', 'e' => 'Arah gunung', 'answer' => 'B'],
                    ['q' => 'Fungsi ritual menhir?', 'a' => 'Hiburan', 'b' => 'Komunikasi dengan roh leluhur', 'c' => 'Pemakaman', 'd' => 'Penyimpanan', 'e' => 'Perang', 'answer' => 'B'],
                ],
            ],
            'Dolmen' => [
                'pretest' => [
                    ['q' => 'Dolmen adalah?', 'a' => 'Meja batu', 'b' => 'Sarkofagus', 'c' => 'Menhir', 'd' => 'Punden', 'e' => 'Candi', 'answer' => 'A'],
                    ['q' => 'Ciri dolmen?', 'a' => 'Tiang tunggal', 'b' => 'Lempengan batu sebagai meja', 'c' => 'Bentuk bulat', 'd' => 'Terbuat dari kayu', 'e' => 'Bergerak', 'answer' => 'B'],
                    ['q' => 'Fungsi dolmen?', 'a' => 'Tempat tinggal', 'b' => 'Meja altar ritual', 'c' => 'Penyimpanan', 'd' => 'Perhiasan', 'e' => 'Pertahanan', 'answer' => 'B'],
                    ['q' => 'Dolmen di Bali ditemukan di?', 'a' => 'Kuta', 'b' => 'Bedulu', 'c' => 'Ubud', 'd' => 'Sanur', 'e' => 'Jimbaran', 'answer' => 'B'],
                    ['q' => 'Periode dolmen?', 'a' => 'Abad ke-20', 'b' => 'Abad ke-14', 'c' => 'Prasejarah', 'd' => 'Kolonial', 'e' => 'Kerajaan', 'answer' => 'C'],
                ],
                'ebook' => ['judul' => 'Modul Dolmen', 'path' => 'ebooks/dolmen-modul.pdf'],
                'posttest' => [
                    ['q' => 'Dolmen termasuk peninggalan?', 'a' => 'Prasejarah', 'b' => 'Hindu-Buddha', 'c' => 'Majapahit', 'd' => 'Kolonial', 'e' => 'Modern', 'answer' => 'A'],
                    ['q' => 'Perbedaan dolmen dan punden berundak?', 'a' => 'Tidak ada', 'b' => 'Dolmen berupa meja batu, punden berupa piramida', 'c' => 'Dolmen lebih besar', 'd' => 'Dolmen berwarna', 'e' => 'Dolmen dari kayu', 'answer' => 'B'],
                    ['q' => 'Bahan utama dolmen?', 'a' => 'Kayu', 'b' => 'Batu kapur', 'c' => 'Besi', 'd' => 'Tanah', 'e' => 'Logam', 'answer' => 'B'],
                    ['q' => 'Fungsi altar dolmen?', 'a' => 'Makan', 'b' => 'Persembahan ritual', 'c' => 'Tidur', 'd' => 'Bermain', 'e' => 'Berbicara', 'answer' => 'B'],
                    ['q' => 'Penelitian dolmen di Bali dilakukan oleh?', 'a' => 'Van Hövell', 'b' => 'Sutherland', 'c' => 'Korn', 'd' => 'Soekarno', 'e' => 'Habibie', 'answer' => 'C'],
                ],
            ],
            // Era B - Hindu-Buddha
            'Arca Hindu-Buddha' => [
                'pretest' => [
                    ['q' => 'Arca Hindu-Buddha berfungsi sebagai?', 'a' => 'Dekorasi rumah', 'b' => 'PerObject peribadatan', 'c' => 'Peralatan makan', 'd' => 'Mainan anak', 'e' => 'Perhiasan', 'answer' => 'B'],
                    ['q' => 'Contoh arca Hindu di Bali?', 'a' => 'Arca Buddha', 'b' => 'Arca Wisnu', 'c' => 'Arca Tara', 'd' => 'Arca Amitabha', 'e' => 'Arca Maitreya', 'answer' => 'B'],
                    ['q' => 'Arca Ganesha adalah arca?', 'a' => 'Buddha', 'b' => 'Hindu', 'c' => 'Prasejarah', 'd' => 'Kolonial', 'e' => 'Modern', 'answer' => 'B'],
                    ['q' => 'Ciri arca Buddha?', 'a' => 'Bertopi', 'b' => 'Bhumi Sparsha mudra', 'c' => 'Memiliki gajah', 'd' => 'Berwarna cerah', 'e' => 'Berpose menari', 'answer' => 'B'],
                    ['q' => 'Bahan pembuatan arca?', 'a' => 'Kayu', 'b' => 'Batu andesit', 'c' => 'Kain', 'd' => 'Kertas', 'e' => 'Plastik', 'answer' => 'B'],
                ],
                'ebook' => ['judul' => 'Modul Arca Hindu-Buddha', 'path' => 'ebooks/arca-hindu-buddha-modul.pdf'],
                'posttest' => [
                    ['q' => 'Arca Durga di Bali ditemukan di?', 'a' => 'Bedulu', 'b' => 'Tampaksiring', 'c' => 'Kuta', 'd' => 'Ubud', 'e' => 'Singaraja', 'answer' => 'B'],
                    ['q' => 'Mudra adalah?', 'a' => 'Bahan arca', 'b' => 'Posisi tangan', 'c' => 'Warna arca', 'd' => 'Ukuran arca', 'e' => 'Tempat arca', 'answer' => 'B'],
                    ['q' => 'Arca Buddha Dhyana Mudra memiliki arti?', 'a' => 'Takut', 'b' => 'Meditasi', 'c' => 'Tawa', 'd' => 'Marah', 'e' => 'Tidur', 'answer' => 'B'],
                    ['q' => 'Jumlah arca utama di Pura Besakih?', 'a' => '5', 'b' => '10', 'c' => '17', 'd' => '25', 'e' => '50', 'answer' => 'C'],
                    ['q' => 'Periode pembuatan arca Hindu-Buddha di Bali?', 'a' => '1000 SM', 'b' => 'Abad ke-8-14', 'c' => 'Abad ke-20', 'd' => '1945', 'e' => '1800', 'answer' => 'B'],
                ],
            ],
            'Candi' => [
                'pretest' => [
                    ['q' => 'Candi adalah?', 'a' => 'Rumah', 'b' => 'Bangunan ibadah Hindu/Buddha', 'c' => 'Sungai', 'd' => 'Gunung', 'e' => 'Hutan', 'answer' => 'B'],
                    ['q' => 'Candi terkenal di Bali?', 'a' => 'Candi Prambanan', 'b' => 'Candi Borobudur', 'c' => 'Candi Penataran', 'd' => 'Candi Sukuh', 'e' => 'Candi Gunung Kawi', 'answer' => 'E'],
                    ['q' => 'Fungsi candi?', 'a' => 'Tempat tinggal', 'b' => 'Simpananan barang', 'c' => 'Tempat pemujaan', 'd' => 'Kantor', 'e' => 'Sekolah', 'answer' => 'C'],
                    ['q' => 'Candi di Bali建造 oleh?', 'a' => 'Kerajaan Majapahit', 'b' => 'Kerajaan Sunda', 'c' => 'Kerajaan Mataran', 'd' => 'Kerajaan Singasari', 'e' => 'Belanda', 'answer' => 'C'],
                    ['q' => 'Bahan utama candi?', 'a' => 'Kayu', 'b' => 'Batu bata merah', 'c' => 'Kaca', 'd' => 'Besi', 'e' => 'Kain', 'answer' => 'B'],
                ],
                'ebook' => ['judul' => 'Modul Candi di Bali', 'path' => 'ebooks/candi-modul.pdf'],
                'posttest' => [
                    ['q' => 'Candi Gunung Kawi terletak di?', 'a' => 'Gianyar', 'b' => 'Tampaksiring', 'c' => 'Karangasem', 'd' => 'Tabanan', 'e' => 'Jembrana', 'answer' => 'B'],
                    ['q' => 'Candi yang memiliki tebing batu besar?', 'a' => 'Candi Prambanan', 'b' => 'Candi Borobudur', 'c' => 'Candi Gunung Kawi', 'd' => 'Candi Sukuh', 'e' => 'Candi Penataran', 'answer' => 'C'],
                    ['q' => 'Periode pembangunan candi di Bali?', 'a' => '1000 SM', 'b' => 'Abad ke-11-14', 'c' => 'Abad ke-20', 'd' => '1800', 'e' => '1945', 'answer' => 'B'],
                    ['q' => 'Candi di Bali kebanyakan beragama?', 'a' => 'Buddha', 'b' => 'Hindu', 'c' => 'Kristen', 'd' => 'Islam', 'e' => 'Konghucu', 'answer' => 'B'],
                    ['q' => 'Arca yang ditemukan di Candi Gunung Kawi?', 'a' => 'Ganesha', 'b' => 'Shiva', 'c' => 'Vishnu', 'd' => 'Brahma', 'e' => 'Semua benar', 'answer' => 'E'],
                ],
            ],
            'Prasasti' => [
                'pretest' => [
                    ['q' => 'Prasasti adalah?', 'a' => 'Patung', 'b' => 'Tulisan pada batu', 'c' => 'Candi', 'd' => 'Arca', 'e' => 'Situs', 'answer' => 'B'],
                    ['q' => 'Prasasti digunakan untuk?', 'a' => 'Dekorasi', 'b' => 'Mencatat informasi', 'c' => 'Mainan', 'd' => 'Peralatan', 'e' => 'Perhiasan', 'answer' => 'B'],
                    ['q' => 'Prasasti Sukuh menggunakan bahasa?', 'a' => 'Jawa Kuno', 'b' => 'Bali Kuno', 'c' => 'Sanskerta', 'd' => 'Melayu', 'e' => 'Bugis', 'answer' => 'C'],
                    ['q' => 'Prasasti terkenal di Bali?', 'a' => 'Prasasti Canggal', 'b' => 'Prasasti Sukuh', 'c' => 'Prasasti Kalasan', 'd' => 'Prasasti Sojomerto', 'e' => 'Prasasti Gandasuli', 'answer' => 'B'],
                    ['q' => 'Bahan prasasti?', 'a' => 'Kayu', 'b' => 'Batu', 'c' => 'Kain', 'd' => 'Kertas', 'e' => 'Logam', 'answer' => 'B'],
                ],
                'ebook' => ['judul' => 'Modul Prasasti', 'path' => 'ebooks/prasasti-modul.pdf'],
                'posttest' => [
                    ['q' => 'Prasasti Sukuh menceritakan tentang?', 'a' => 'Perang', 'b' => 'Keagamaan dan politik', 'c' => 'Pertanian', 'd' => 'Perdagangan', 'e' => 'Olahraga', 'answer' => 'B'],
                    ['q' => 'Huruf yang digunakan prasasti Bali?', 'a' => 'Aksara Jawa', 'b' => 'Aksara Pallawa', 'c' => 'Aksara Latin', 'd' => 'Aksara Arab', 'e' => 'Aksara China', 'answer' => 'A'],
                    ['q' => 'Periode prasasti di Bali?', 'a' => '1000 SM', 'b' => 'Abad ke-9-15', 'c' => 'Abad ke-20', 'd' => '1800', 'e' => '1945', 'answer' => 'B'],
                    ['q' => 'Prasasti sering ditemukan di?', 'a' => 'Laut', 'b' => 'Sungai', 'c' => 'Kaki bukit', 'd' => 'Hutan', 'e' => 'Sawah', 'answer' => 'C'],
                    ['q' => 'Informasi dalam prasasti meliputi?', 'a' => 'Hanya nama', 'b' => 'Sejarah, agama, Donation', 'c' => 'Hanya angka', 'd' => 'Hanya gambar', 'e' => 'Hanya cuaca', 'answer' => 'B'],
                ],
            ],
            // Era C - Majapahit
            'Periode Majapahit' => [
                'pretest' => [
                    ['q' => 'Majapahit adalah kerajaan?', 'a' => 'Bali', 'b' => 'Jawa', 'c' => 'Sumatera', 'd' => 'Kalimantan', 'e' => 'Sulawesi', 'answer' => 'B'],
                    ['q' => 'Kerajaan Majapahit влияние на Bali?', 'a' => 'Tidak ada', 'b' => 'Sangat besar', 'c' => 'Kecil', 'd' => 'Hanya ekonomi', 'e' => 'Hanya militer', 'answer' => 'B'],
                    ['q' => 'Peninggalan Majapahit di Bali?', 'a' => 'Candi Prambanan', 'b' => 'Candi Borobudur', 'c' => 'Situs Tawangmangu', 'd' => 'Pura Kehen', 'e' => 'Monas', 'answer' => 'D'],
                    ['q' => 'Pura Kehen terletak di?', 'a' => 'Gianyar', 'b' => 'Bangli', 'c' => 'Karangasem', 'd' => 'Tabanan', 'e' => 'Badung', 'answer' => 'B'],
                    ['q' => 'Majapahit влияние на seni?', 'a' => 'Negatif', 'b' => 'Sangat positif', 'c' => 'Tidak berpengaruh', 'd' => 'Hanya arsitektur', 'e' => 'Hanya sastra', 'answer' => 'B'],
                ],
                'ebook' => ['judul' => 'Modul Pengaruh Majapahit di Bali', 'path' => 'ebooks/majapahit-modul.pdf'],
                'posttest' => [
                    ['q' => 'Periode Majapahit dimulai tahun?', 'a' => '1200', 'b' => '1293', 'c' => '1400', 'd' => '1500', 'e' => '1600', 'answer' => 'B'],
                    ['q' => 'Raja terkenal Majapahit?', 'a' => 'Hayam Wuruk', 'b' => 'Sisingamangaraja', 'c' => 'Sultan Agung', 'd' => 'Pattimura', 'e' => 'Diponegoro', 'answer' => 'A'],
                    ['q' => 'Peninggalan arsitektur Majapahit di Bali?', 'a' => 'Pura Tanah Lot', 'b' => 'Pura Kehen', 'c' => 'Pura Besakih', 'd' => 'Pura Uluwatu', 'e' => 'Pura Ulun Danu', 'answer' => 'B'],
                    ['q' => 'Pengaruh Majapahit pada bahasa?', 'a' => 'Menghilangkan bahasa lokal', 'b' => 'Memperkaya kosakata', 'c' => 'Tidak berubah', 'd' => 'Menggantikan sepenuhnya', 'e' => 'Menghapus bahasa', 'answer' => 'B'],
                    ['q' => 'Tari Kecak berasal dari pengaruh?', 'a' => 'Majapahit', 'b' => 'Kolonial', 'c' => 'India', 'd' => 'China', 'e' => 'Eropa', 'answer' => 'A'],
                ],
            ],
            // Era D - Gelgel dan Sembilan Kerajaan
            'Wayang Kamasan' => [
                'pretest' => [
                    ['q' => 'Wayang Kamasan berasal dari?', 'a' => 'Jawa', 'b' => 'Bali', 'c' => 'Sumatra', 'd' => 'Kalimantan', 'e' => 'Sulawesi', 'answer' => 'B'],
                    ['q' => 'Wayang Kamasan menggunakan teknik?', 'a' => 'Tari', 'b' => 'Lukis', 'c' => 'Ukir', 'd' => 'Anyam', 'e' => 'Pahat', 'answer' => 'B'],
                    ['q' => 'Tema wayang Kamasan?', 'a' => 'Ramayana', 'b' => 'Mahabharata', 'c' => 'Bharatayuddha', 'd' => ' Semua benar', 'e' => ' Hanya Ramayana', 'answer' => 'D'],
                    ['q' => 'Lokasi Wayang Kamasan?', 'a' => 'Denpasar', 'b' => 'Klungkung', 'c' => 'Gianyar', 'd' => 'Singaraja', 'e' => 'Badung', 'answer' => 'B'],
                    ['q' => 'Warna khas wayang Kamasan?', 'a' => 'Merah dan kuning', 'b' => 'Hitam dan putih', 'c' => 'Hijau dan biru', 'd' => 'Coklat dan oranye', 'e' => 'Ungu dan pink', 'answer' => 'B'],
                ],
                'ebook' => ['judul' => 'Modul Wayang Kamasan', 'path' => 'ebooks/wayang-kamasan-modul.pdf'],
                'posttest' => [
                    ['q' => 'Wayang Kamasan adalah?', 'a' => 'Wayang kulit', 'b' => 'Wayang lukis', 'c' => 'Wayang golek', 'd' => 'Wayang klitik', 'e' => 'Wayang suweg', 'answer' => 'B'],
                    ['q' => 'Periode pembuatan wayang Kamasan?', 'a' => 'Abad ke-10', 'b' => 'Abad ke-16', 'c' => 'Abad ke-20', 'd' => '1800', 'e' => '1945', 'answer' => 'B'],
                    ['q' => 'Bahan wayang Kamasan?', 'a' => 'Kulit', 'b' => 'Kayu', 'c' => 'Kain', 'd' => 'Tali', 'e' => 'Logam', 'answer' => 'B'],
                    ['q' => 'Perbedaan wayang Kamasan dengan wayang kulit?', 'a' => 'Teknik pembuatan', 'b' => 'Tidak ada', 'c' => 'Bahan', 'd' => 'Cerita', 'e' => 'Warna', 'answer' => 'A'],
                    ['q' => 'Wayang Kamasan disimpan di?', 'a' => 'Museum Puri Lukisan', 'b' => 'Museum Le Mayeur', 'c' => 'Museum Bali', 'd' => ' Semua benar', 'e' => 'Tidak ada', 'answer' => 'D'],
                ],
            ],
            // Era E - Kolonial Belanda
            'Masa Kolonial Belanda' => [
                'pretest' => [
                    ['q' => 'Belanda mulai menjajah Bali tahun?', 'a' => '1600', 'b' => '1700', 'c' => '1846', 'd' => '1900', 'e' => '1945', 'answer' => 'C'],
                    ['q' => 'PerPerangan Bali tegen Belanda?', 'a' => 'Perang Diponegoro', 'b' => 'Perang Puputan', 'c' => 'Perang Bali', 'd' => ' Semua benar', 'e' => 'Tidak ada', 'answer' => 'D'],
                    ['q' => 'Puputan adalah?', 'a' => 'Tarian', 'b' => 'Perperangan sampai mati', 'c' => 'Upacara', 'd' => 'Musik', 'e' => 'Pesta', 'answer' => 'B'],
                    ['q' => 'Perang Puputan Badung terjadi tahun?', 'a' => '1846', 'b' => '1906', 'c' => '1915', 'd' => '1942', 'e' => '1950', 'answer' => 'B'],
                    ['q' => 'Peninggalan arsitektur Kolonial di Bali?', 'a' => 'Candi', 'b' => 'Pura', 'c' => 'Gedung art deco', 'd' => 'Situs prasejarah', 'e' => 'Monumen', 'answer' => 'C'],
                ],
                'ebook' => ['judul' => 'Modul Masa Kolonial Belanda di Bali', 'path' => 'ebooks/kolonial-modul.pdf'],
                'posttest' => [
                    ['q' => 'Perang Puputan Margala terjadi tahun?', 'a' => '1846', 'b' => '1891', 'c' => '1906', 'd' => '1914', 'e' => '1945', 'answer' => 'B'],
                    ['q' => 'Perjanjian Sanur签订了 tahun?', 'a' => '1846', 'b' => '1884', 'c' => '1904', 'd' => '1914', 'e' => '1945', 'answer' => 'C'],
                    ['q' => 'Dampak kolonisasi terhadap arsitektur Bali?', 'a' => 'Hilangnya pura', 'b' => 'Pengaruh art deco', 'c' => 'Tidak ada', 'd' => 'Penghancuran', 'e' => 'Penggantian', 'answer' => 'B'],
                    ['q' => 'Gedung art deco banyak ditemukan di?', 'a' => 'Singaraja', 'b' => 'Denpasar', 'c' => 'Kuta', 'd' => 'Ubud', 'e' => 'Nusa Dua', 'answer' => 'A'],
                    ['q' => 'Peninggalan dokumenter kolonial di Bali?', 'a' => 'Pura', 'b' => 'Brosur dan foto', 'c' => 'Candi', 'd' => 'Arca', 'e' => 'Prasasti', 'answer' => 'B'],
                ],
            ],
            // Era F - Pasca Kemerdekaan
            'Masa Pasca-Kemerdekaan' => [
                'pretest' => [
                    ['q' => 'Bali resmi menjadi bagian Indonesia tahun?', 'a' => '1945', 'b' => '1950', 'c' => '1960', 'd' => '1970', 'e' => '1980', 'answer' => 'B'],
                    ['q' => 'Peristiwa penting pasca kemerdekaan di Bali?', 'a' => 'Perang Puputan', 'b' => 'Peristiwa 1965', 'c' => 'Reformasi', 'd' => 'Semua benar', 'e' => 'Tidak ada', 'answer' => 'D'],
                    ['q' => 'Peninggalan arsitektur pasca kemerdekaan?', 'a' => 'Candi', 'b' => 'Pura', 'c' => 'Monumen Perjuangan', 'd' => 'Situs prasejarah', 'e' => 'Gedung kolonial', 'answer' => 'C'],
                    ['q' => 'Monumen Perjuangan Rakyat Bali terletak di?', 'a' => 'Denpasar', 'b' => 'Gianyar', 'c' => 'Singaraja', 'd' => 'Badung', 'e' => 'Tabanan', 'answer' => 'A'],
                    ['q' => 'Perkembangan seni pasca kemerdekaan?', 'a' => 'Terhenti', 'b' => 'Berkembang pesat', 'c' => 'Menurun', 'd' => 'Berubah drastis', 'e' => 'Hilangnya seni', 'answer' => 'B'],
                ],
                'ebook' => ['judul' => 'Modul Masa Pasca-Kemerdekaan', 'path' => 'ebooks/pascakemerdekaan-modul.pdf'],
                'posttest' => [
                    ['q' => 'Musium Le Mayeur merupakan peninggalan?', 'a' => 'Kolonial', 'b' => 'Pasca kemerdekaan', 'c' => 'Prasejarah', 'd' => 'Hindu-Buddha', 'e' => 'Majapahit', 'answer' => 'B'],
                    ['q' => 'Perkembangan pariwisata Bali dimulai tahun?', 'a' => '1945', 'b' => '1960-an', 'c' => '1980-an', 'd' => '2000-an', 'e' => '2020', 'answer' => 'B'],
                    ['q' => 'Peristiwa 1965 di Bali melibatkan?', 'a' => 'Hanya politik', 'b' => 'G30S', 'c' => 'Tsunami', 'd' => 'Gempa', 'e' => 'Tsunami dan gempa', 'answer' => 'B'],
                    ['q' => 'Peninggalan seni rupa modern di Bali?', 'a' => 'Candi', 'b' => 'Museum', 'c' => 'Pura', 'd' => 'Arca', 'e' => 'Prasasti', 'answer' => 'B'],
                    ['q' => 'Warisan budaya pasca kemerdekaan?', 'a' => 'Hanya tradisi', 'b' => 'Tradisi dan modernisasi', 'c' => 'Hanya modern', 'd' => 'Tidak ada', 'e' => 'Hanya arsitektur', 'answer' => 'B'],
                ],
            ],
        ];
    }

    private function findMatchingTopic(string $judul, array $contentData, string $eraKode, int $bab): ?string
    {
        // Try exact match first
        foreach ($contentData as $topic => $data) {
            if (stripos($judul, $topic) !== false) {
                return $topic;
            }
        }

        // Try partial match
        $keywords = [
            'punden' => 'Punden Berundak',
            'sarkofagus' => 'Sarkofagus',
            'arca megalitik' => 'Arca Megalitik',
            'menhir' => 'Menhir',
            'dolmen' => 'Dolmen',
            'arca hindu' => 'Arca Hindu-Buddha',
            'arca buddha' => 'Arca Hindu-Buddha',
            'candi' => 'Candi',
            'prasasti' => 'Prasasti',
            'majapahit' => 'Periode Majapahit',
            'wayang' => 'Wayang Kamasan',
            'kolonial' => 'Masa Kolonial Belanda',
            'pasca' => 'Masa Pasca-Kemerdekaan',
        ];

        foreach ($keywords as $keyword => $topic) {
            if (stripos($judul, $keyword) !== false) {
                return $topic;
            }
        }

        // Default fallback based on era
        return match ($eraKode) {
            'A' => 'Punden Berundak',
            'B' => 'Arca Hindu-Buddha',
            'C' => 'Periode Majapahit',
            'D' => 'Wayang Kamasan',
            'E' => 'Masa Kolonial Belanda',
            'F' => 'Masa Pasca-Kemerdekaan',
            default => 'Punden Berundak',
        };
    }

    private function seedPretest(Materi $materi, ?string $topicKey): void
    {
        $contentData = $this->getContentData();
        $data = $contentData[$topicKey] ?? $contentData['Punden Berundak'];

        foreach ($data['pretest'] as $question) {
            Pretest::updateOrCreate(
                [
                    'materi_id' => $materi->materi_id,
                    'pertanyaan' => $question['q'],
                ],
                [
                    'pilihan_a' => $question['a'],
                    'pilihan_b' => $question['b'],
                    'pilihan_c' => $question['c'],
                    'pilihan_d' => $question['d'],
                    'pilihan_e' => $question['e'] ?? null,
                    'jawaban_benar' => $question['answer'],
                ]
            );
        }
    }

    private function seedEbook(Materi $materi, ?string $topicKey): void
    {
        $contentData = $this->getContentData();
        $data = $contentData[$topicKey] ?? $contentData['Punden Berundak'];
        $ebook = $data['ebook'] ?? ['judul' => 'Modul Umum', 'path' => 'ebooks/default-modul.pdf'];

        Ebook::updateOrCreate(
            ['materi_id' => $materi->materi_id],
            [
                'judul' => $ebook['judul'],
                'path_file' => $ebook['path'],
            ]
        );
    }

    private function seedPosttest(Materi $materi, ?string $topicKey): void
    {
        $contentData = $this->getContentData();
        $data = $contentData[$topicKey] ?? $contentData['Punden Berundak'];

        foreach ($data['posttest'] as $question) {
            Posttest::updateOrCreate(
                [
                    'materi_id' => $materi->materi_id,
                    'pertanyaan' => $question['q'],
                ],
                [
                    'pilihan_a' => $question['a'],
                    'pilihan_b' => $question['b'],
                    'pilihan_c' => $question['c'],
                    'pilihan_d' => $question['d'],
                    'pilihan_e' => $question['e'] ?? null,
                    'jawaban_benar' => $question['answer'],
                ]
            );
        }
    }

    /**
     * Situs, museum, dan objek yang benar-benar punya berkas GLB di storage.
     *
     * Dipisahkan dari getContentData() karena datanya bukan konten per-topik
     * yang bisa digenerate: tiap baris terikat pada satu berkas nyata. Objek
     * sengaja tidak membawa mesh_name — penautan ke node GLB dilakukan admin
     * lewat editor VR, dan menebaknya di sini hanya akan menghasilkan objek
     * yang diam-diam tidak pernah muncul di headset.
     *
     * @return array<int, array{materi: string, nama: string, alamat: string, deskripsi: string, lat: float, lng: float, museum: string, model: string, objek: array<int, array<string, string|list<string>|null>>}>
     */
    private function getSitusData(): array
    {
        return [
            [
                'materi' => 'Punden Berundak',
                'nama' => 'Pura Candi',
                'alamat' => 'Selulung, Kec. Kintamani, Kabupaten Bangli, Bali 80652',
                'deskripsi' => 'Kompleks pura di Desa Selulung yang menyimpan punden berundak bertingkat lima peninggalan tradisi megalitik.',
                'lat' => -8.19769754,
                'lng' => 115.26779916,
                'museum' => 'Punden Berundak di Pura Mehu',
                'model' => 'virtual-museum/models/1774095667_Punden_Atas-v1.glb',
                'objek' => [
                    [
                        'nama' => 'Punden Berundak Pelinggih I Ratu Gede Kanginan',
                        'gambar_real' => 'virtual-museum/objects/images/1774059085_gambar_real_pundenatas1.jpg',
                        'path_obj' => 'virtual-museum/objects/models/1773736570_obj_PundenAtas_1-v1.glb',
                        'deskripsi' => 'Punden Berundak Pelinggih I Ratu Gede Kanginan merupakan bangunan suci bertingkat yang terdiri dari lima tingkatan dengan denah dasar berbentuk segi empat. Bangunan ini terbuat dari batu padas yang dikombinasikan dengan batu bata dan direkatkan menggunakan tanah. Setiap tingkatan memiliki teras yang semakin mengecil ke arah atas, mencerminkan konsep kesakralan yang meningkat menuju puncak. Pada bagian puncak terdapat struktur berbentuk mahkota padmasana yang terbuat dari batu padas, serta sebuah menhir berbentuk lonjong yang dipercaya berkaitan dengan pemujaan leluhur. Pada sisi selatan bangunan terdapat tangga dari susunan batu padas yang menghubungkan tingkat pertama hingga tingkat keempat. Pola hias pada bangunan ini relatif sederhana, dengan penutup lantai berbentuk capon (sisi genta) serta hiasan bunga teratai dan simbar gantung pada bagian sudut teras.',
                        'path_audio' => 'virtual-museum/objects/audio/1775982250_audio_deskripsi3.mp3',
                    ],
                    [
                        'nama' => 'Punden Berundak Pelinggih I Ratu Gede Makarang',
                        'gambar_real' => 'virtual-museum/objects/images/1774059133_gambar_real_pundenatas2.jpg',
                        'path_obj' => 'virtual-museum/objects/models/1773736612_obj_PundenAtas_2-v1.glb',
                        'deskripsi' => 'Punden Berundak Pelinggih I Ratu Gede Makarang merupakan bangunan punden berundak yang terdiri dari empat tingkatan dengan denah dasar berbentuk segi empat. Bangunan ini disusun dari batu padas yang direkatkan menggunakan tanah dan memiliki teras pada setiap tingkatnya yang semakin mengecil ke arah atas. Pada bagian puncak bangunan terdapat sebuah menhir yang terbuat dari batu padas, yang diduga memiliki fungsi sebagai simbol pemujaan terhadap roh leluhur. Struktur bangunan ini memiliki bentuk yang sederhana tanpa ornamen atau pola hias yang rumit. Hiasan yang tampak hanya terdapat pada bagian penutup lantai teras yang diprofil berbentuk capon (sisi genta). Keberadaan bangunan ini menunjukkan kesinambungan tradisi megalitik dalam praktik keagamaan masyarakat Bali hingga masa sekarang.',
                        'path_audio' => 'virtual-museum/objects/audio/1775982296_audio_deskripsi4.mp3',
                    ],
                    [
                        'nama' => 'Punden Berundak di Pura Bale Agung',
                        'gambar_real' => 'virtual-museum/objects/images/1774239979_gambar_real_WhatsAppImage2026-03-23at12.25.05.jpeg',
                        'path_obj' => 'virtual-museum/objects/models/1774240301_obj_pundenmadya11-v1.glb',
                        'deskripsi' => 'Punden berundak Madya Petirtaan terdiri atas tiga tingkatan dengan ukuran yang semakin kecil ke arah atas. Pada bagian puncak bangunan terdapat sebuah lubang yang diduga sebagai tempat menhir. Oleh masyarakat setempat, bangunan ini disebut Madya Petirtaan dan digunakan sebagai tempat memohon air suci yang digunakan dalam berbagai upacara keagamaan.',
                        'path_audio' => 'virtual-museum/objects/audio/1775988611_audio_deskripsi5revisi.mp3',
                    ],
                ],
            ],

            [
                'materi' => 'Punden Berundak',
                'nama' => 'Pura Mihu',
                'alamat' => 'Selulung, Kec. Kintamani, Kabupaten Bangli, Bali 80652',
                'deskripsi' => 'Areal pura dengan dua punden berundak yang menghadap Gunung Penulisan sebagai arah pemujaan.',
                'lat' => -8.19831133,
                'lng' => 115.26741012,
                'museum' => 'Punden Berundak di Pura Candi',
                'model' => 'virtual-museum/models/1773755935_PundenBawah-v1.glb',
                'objek' => [
                    [
                        'nama' => 'Punden Berundak Pelinggih I Ratu Dukuh Jegir',
                        'gambar_real' => 'virtual-museum/objects/images/1774059698_gambar_real_pundenbawah1.jpg',
                        'path_obj' => 'virtual-museum/objects/models/1774062505_obj_pundenbawah2-v1.glb',
                        'deskripsi' => 'Punden berundak ini terdiri atas lima tingkatan yang semakin kecil ke arah atas dan memiliki bentuk dasar segi empat. Bangunan tersebut dibuat dari pasangan batu padas dengan perekat tanah dan terletak di halaman barat laut Punden Berundak Ratu Gede Kemulan. Pada bagian puncak bangunan terdapat batu tegak yang bentuknya menyerupai pegangan genta, yang menjadi simbol sakral dalam pemujaan. Ornamen pada bangunan ini relatif sederhana, terutama berupa profil capon (sisi genta) pada bagian kaki bangunan.',
                        'path_audio' => 'virtual-museum/objects/audio/1775990355_audio_deskripsi2revisi.mp3',
                    ],
                    [
                        'nama' => 'Punden Berundak Pelinggih I Ratu Gede Kemulan',
                        'gambar_real' => 'virtual-museum/objects/images/1774060055_gambar_real_pundenbawah2.jpg',
                        'path_obj' => 'virtual-museum/objects/models/1774062780_obj_pundenbawah1-v1.glb',
                        'deskripsi' => 'Punden berundak Pelinggih I Ratu Gede Kemulan memiliki lima tingkatan yang semakin mengecil ke arah puncak. Bangunan tersebut dibuat dari batu padas dengan perekat tanah dan memiliki bentuk dasar segi empat. Posisinya berada di areal Pura Candi dan menghadap ke selatan, dengan arah pemujaan menuju Gunung Penulisan di sebelah utara. Pada bagian puncak bangunan terdapat sebuah batu berbentuk kerucut yang merupakan menhir, yang berfungsi sebagai simbol pemujaan leluhur. Struktur bangunan juga dilengkapi dengan berbagai ornamen hias seperti motif simbar gantung, ceplok bunga, serta sulur-suluran berbentuk bunga teratai pada beberapa bagian dinding dan sudut bangunan.',
                        'path_audio' => 'virtual-museum/objects/audio/1775981635_audio_deskripsi1.mp3',
                    ],
                ],
            ],

            [
                'materi' => 'Arca Megalitik',
                'nama' => 'Pura Taulan',
                'alamat' => 'Selulung, Kec. Kintamani, Kabupaten Bangli, Bali',
                'deskripsi' => 'Pura yang menyimpan sepasang arca bercorak Tionghoa, bukti akulturasi budaya Bali dan Tionghoa.',
                'lat' => -8.20996209,
                'lng' => 115.26267737,
                'museum' => 'Arca Peninggalan di Pura Taulan',
                'model' => 'virtual-museum/models/1774140700_Kamasan-v1.glb',
                'objek' => [
                    [
                        'nama' => 'Arca Bercorak Tionghoa',
                        'gambar_real' => 'virtual-museum/objects/images/1774144553_gambar_real_purataulan.jpg',
                        'path_obj' => 'virtual-museum/objects/models/1774240235_obj_kawitankecil-v1.glb',
                        'deskripsi' => 'Arca bercorak Tionghoa di Pura Taulan merupakan sepasang arca yang menggambarkan figur laki-laki dan perempuan dengan proporsi tubuh yang seimbang serta dihiasi ornamen khas budaya Tionghoa. Arca laki-laki memiliki tinggi sekitar 124 cm dengan ciri mengenakan mahkota tinggi, kalung, serta busana dengan ikat pinggang yang disimpul di bagian depan. Pasangannya, arca perempuan, berukuran sedikit lebih kecil dengan tinggi sekitar 121 cm dan digambarkan mengenakan mahkota, perhiasan, serta kain panjang hingga pergelangan kaki. Kedua arca ini memperlihatkan gaya seni yang menunjukkan pengaruh budaya Tionghoa, namun ditempatkan dalam konteks religius masyarakat Bali. Keberadaan arca tersebut menjadi bukti adanya proses akulturasi budaya antara masyarakat Bali dan komunitas Tionghoa pada masa lampau. Selain arca tersebut, di kawasan pura juga ditemukan fragmen arca serta lingga semu yang menunjukkan adanya perpaduan unsur Hindu Siwa dan tradisi Buddhis-Tionghoa dalam praktik keagamaan masyarakat pada masa lampau.',
                        'path_audio' => 'virtual-museum/objects/audio/1775982372_audio_deskripsi6revisi.mp3',
                    ],
                ],
            ],

            [
                'materi' => 'Wayang Kamasan',
                'nama' => 'Museum Kerta Gosa',
                'alamat' => 'Jl. Kenanga No.11, Semarapura Kelod, Kec. Klungkung, Kabupaten Klungkung, Bali 80761',
                'deskripsi' => 'Bekas balai pengadilan Kerajaan Klungkung dengan langit-langit berhiaskan lukisan wayang gaya Kamasan.',
                'lat' => -8.53582524,
                'lng' => 115.40354081,
                'museum' => 'Galeri Virtual Wayang Kamasan',
                'model' => 'virtual-museum/models/1774229897_Kamasan-v1.glb',
                'objek' => [
                    [
                        'nama' => 'Ramayana',
                        'mesh_name' => 'lukisan-4',
                        'gambar_real' => 'virtual-museum/objects/images/1774234145_gambar_real_edit5.jpg',
                        'path_obj' => 'virtual-museum/objects/models/1774238816_obj_lukisan1-v1.glb',
                        'deskripsi' => 'Lukisan yang menggambarkan perjalanan rama dan rombongan keranya melawan pasukan rahwana yaitu raksasa kumbakarna di lautan menuju kerajaan kosala.',
                        'path_audio' => 'virtual-museum/objects/audio/1775988415_audio_deskripsi7.mp3',
                    ],
                    [
                        'nama' => 'Manasija',
                        'mesh_name' => 'lukisan-5',
                        'gambar_real' => 'virtual-museum/objects/images/1774238373_gambar_real_edit5Edited.jpg',
                        'path_obj' => 'virtual-museum/objects/models/1774238830_obj_lukisan2-v2.glb',
                        'deskripsi' => 'Lukisan yang bermakna kisah cinta yang abadi dari kedua pasangan yang setia.',
                        'path_audio' => 'virtual-museum/objects/audio/1775988432_audio_deskripsi7.mp3',
                    ],
                    [
                        'nama' => 'Bhagavad Gita',
                        'mesh_name' => 'lukisan-3',
                        'gambar_real' => 'virtual-museum/objects/images/1774238544_gambar_real_4edit.png',
                        'path_obj' => 'virtual-museum/objects/models/1774238844_obj_lukisan3-v3.glb',
                        'deskripsi' => 'Kisah yang menceritakan percakapan Sri Krisna dan Arjuna dalam medan perang kurusena yang dikutip dengan menggunakan nyanyian-nyanyian Bhagavan.',
                        'path_audio' => 'virtual-museum/objects/audio/1775988450_audio_deskripsi7.mp3',
                    ],
                    [
                        'nama' => 'Dewanagari',
                        'mesh_name' => 'lukisan-2',
                        'gambar_real' => 'virtual-museum/objects/images/1774238712_gambar_real_edit1.jpg',
                        'path_obj' => 'virtual-museum/objects/models/1774238859_obj_lukisan4-v4.glb',
                        'deskripsi' => 'Kisah kematian Dewanagari yang merupakan raja atau pemimpin asura',
                        'path_audio' => 'virtual-museum/objects/audio/1775988468_audio_deskripsi7.mp3',
                    ],
                    [
                        'nama' => 'Svarga Loka',
                        'mesh_name' => 'lukisan-1',
                        'gambar_real' => 'virtual-museum/objects/images/1774238791_gambar_real_edit1.jpg',
                        'path_obj' => 'virtual-museum/objects/models/1774238791_obj_lukisan5-v5.glb',
                        'deskripsi' => 'Pada lukisan tersebut memiliki makna atau cerita tentang svarga loka yang dihuni dewa dan wong.',
                        'path_audio' => 'virtual-museum/objects/audio/1775988482_audio_deskripsi7.mp3',
                    ],
                ],
            ],

            [
                'materi' => 'Menhir',
                'nama' => 'Pura Puseh Desa Adat Selulung Kintamani',
                'alamat' => 'Selulung, Kec. Kintamani, Kabupaten Bangli, Bali',
                'deskripsi' => 'Pura desa adat dengan menhir setinggi 74 cm di halaman luar yang masih dipandang sakral hingga kini.',
                'lat' => -8.20059721,
                'lng' => 115.2715472,
                'museum' => 'Menhir di Pura Puseh Adat Selullung',
                'model' => 'virtual-museum/models/1774232761_menhir-v1.glb',
                'objek' => [
                    [
                        'nama' => 'Menhir',
                        'gambar_real' => 'virtual-museum/objects/images/1774233000_gambar_real_ScreenshotFrom2026-03-2310-28-27.png',
                        'path_obj' => 'virtual-museum/objects/models/1774233000_obj_menhirreal-v1.glb',
                        'deskripsi' => 'Menhir merupakan sebuah batu tegak peninggalan tradisi megalitik yang terletak di halaman luar pura. Menhir ini memiliki tinggi sekitar 74 cm dengan lebar bagian atas dan bawah sekitar 29 cm. Dalam kepercayaan masyarakat prasejarah, menhir berfungsi sebagai simbol penghormatan kepada roh leluhur atau tokoh yang dimuliakan dalam komunitas. Masyarakat setempat hingga kini masih memandang menhir tersebut sebagai benda sakral yang berkaitan dengan permohonan keselamatan, kesejahteraan, dan kesuburan. Keberadaan menhir ini menunjukkan kesinambungan tradisi megalitik yang tetap dihormati dan diintegrasikan dalam praktik keagamaan masyarakat Bali hingga masa kini.',
                        'path_audio' => 'virtual-museum/objects/audio/1775988527_audio_deskripsi8.mp3',
                    ],
                ],
            ],

            // Satu-satunya scene yang penamaan nodenya lengkap dan rapi, karena itu
            // satu-satunya yang bisa memperagakan alur VR utuh: panel info, chip nilai
            // karakter, sampai aktivitas pemasangan. Empat scene lain memakai nama
            // default Blender (`Plane.057`, `Cube.001`) tanpa node yang mewakili satu
            // artefak, jadi mesh_name di sana mustahil sampai model diekspor ulang.
            //
            // Situs tersendiri, bukan museum kedua pada situs Pura Mihu, karena
            // guest/vr/maps.blade.php hanya menautkan `virtualMuseum->first()` — museum
            // kedua tidak akan pernah bisa dibuka dari peta.
            //
            // Namanya menyebut "Rintisan" dengan sengaja: geometrinya prosedural tanpa
            // tekstur dan punden utamanya hanya 2,2 m padahal aslinya ±5,7 m, sementara
            // Pura Mehu sudah punya museum bermodel asli. Dua pin dengan mutu berbeda
            // tanpa penanda akan tertukar. Hapus entri ini kalau uji lapangan tidak
            // ingin menampilkannya.
            [
                'materi' => 'Punden Berundak',
                'nama' => 'Punden Berundak Pura Mehu (Rintisan)',
                'alamat' => 'Selulung, Kec. Kintamani, Kabupaten Bangli, Bali',
                'deskripsi' => 'Model rintisan Punden Berundak Pura Mehu untuk gladi bersih. Geometrinya masih sketsa prosedural tanpa tekstur, tetapi seluruh objeknya sudah tertaut ke data sehingga panel informasi dan nilai karakter bisa dicoba utuh.',
                'lat' => -8.19831133,
                'lng' => 115.26741012,
                'museum' => 'Punden Berundak Pura Mehu (Rintisan)',
                'model' => 'virtual-museum/models/punden-berundak-pura-mehu.glb',
                'objek' => [
                    // Tiga deskripsi pertama dikutip apa adanya dari daftar-objek.txt
                    // milik pemodel. Empat sisanya menerangkan unsur arsitektur Bali
                    // secara umum plus ukuran terukur dari GLB — sengaja tidak
                    // mengarang klaim arkeologis khusus situs ini; naskah sebenarnya
                    // menunggu tim materi.
                    [
                        'nama' => 'Punden Berundak Utama',
                        'mesh_name' => 'Punden_Berundak_Utama',
                        'deskripsi' => 'Struktur teras batu bertingkat 5 undak yang mengerucut ke atas, terbuat dari batu padas, dengan alas berbentuk segi empat memanjang (±5,7 x 5,5 m) dan tinggi total ±3,1 m. Berfungsi sebagai sarana pemujaan leluhur dan Ida Sang Hyang Widhi Wasa pada masa megalitikum.',
                        'nilai_karakter' => ['religius', 'gotong_royong'],
                    ],
                    [
                        'nama' => 'Padma Kurung',
                        'mesh_name' => 'Padma_Kurung',
                        'deskripsi' => 'Relung/wadah di puncak punden berundak, berdinding tiga sisi (kanan, kiri, belakang) tanpa atap — bagian tersuci dari struktur, tempat bersemayamnya arwah leluhur.',
                        'nilai_karakter' => ['religius'],
                    ],
                    [
                        'nama' => 'Area Persembahyangan',
                        'mesh_name' => 'Area_Persembahyangan',
                        'deskripsi' => 'Pelataran batu datar di sekitar punden berundak, tempat pengunjung/umat melakukan persembahyangan.',
                        'nilai_karakter' => ['religius', 'gotong_royong'],
                    ],
                    [
                        'nama' => 'Punden Berundak Kedua',
                        'mesh_name' => 'Punden_Berundak_Kedua',
                        'deskripsi' => 'Punden berundak kedua di kompleks ini, berukuran lebih kecil daripada punden utama (sekitar 2,1 x 2,5 m pada model). Keberadaan lebih dari satu punden dalam satu areal menunjukkan bahwa pemujaan di situs ini tidak terpusat pada satu bangunan saja.',
                        'nilai_karakter' => ['religius'],
                    ],
                    [
                        'nama' => 'Candi Bentar',
                        'mesh_name' => 'Candi_Bentar',
                        'deskripsi' => 'Gerbang belah tanpa atap yang menandai pintu masuk ke areal suci. Bentuknya seperti satu bangunan yang dibelah dua dan digeser, sehingga pengunjung berjalan menembus celah di antaranya sebagai penanda perpindahan dari ruang luar ke ruang sakral.',
                        'nilai_karakter' => ['religius', 'kreatif'],
                    ],
                    [
                        'nama' => 'Bale Pesandekan',
                        'mesh_name' => 'Bale_Pesandekan',
                        'deskripsi' => 'Bangunan terbuka beratap di areal pura, tempat umat beristirahat serta menyiapkan sarana upacara sebelum persembahyangan dimulai.',
                        'nilai_karakter' => ['gotong_royong'],
                    ],
                    [
                        'nama' => 'Lingkungan Situs',
                        'mesh_name' => 'Lingkungan_Situs',
                        'deskripsi' => 'Kontur tanah, tumbuhan, dan batu lepas di sekeliling pelataran. Bagian ini bukan bangunan, tetapi ikut menjelaskan mengapa situs dibangun di titik tersebut.',
                    ],
                ],
            ],
        ];
    }

    private function seedSitus(User $admin): void
    {
        foreach ($this->getSitusData() as $data) {
            $materi = Materi::where('judul', $data['materi'])->first();

            if (! $materi) {
                $this->command->warn("Materi '{$data['materi']}' tidak ditemukan. Melewati situs {$data['nama']}.");

                continue;
            }

            $situs = SitusPeninggalan::updateOrCreate(
                ['nama' => $data['nama']],
                [
                    'materi_id' => $materi->materi_id,
                    'alamat' => $data['alamat'],
                    'deskripsi' => $data['deskripsi'],
                    'lat' => $data['lat'],
                    'lng' => $data['lng'],
                    'user_id' => $admin->id,
                ]
            );

            $museum = VirtualMuseum::updateOrCreate(
                ['situs_id' => $situs->situs_id],
                [
                    'nama' => $data['museum'],
                    'path_obj' => $data['model'],
                ]
            );

            foreach ($data['objek'] as $objek) {
                VirtualMuseumObject::updateOrCreate(
                    [
                        'museum_id' => $museum->museum_id,
                        'nama' => $objek['nama'],
                    ],
                    array_merge($objek, ['situs_id' => $situs->situs_id])
                );
            }
        }
    }
}
