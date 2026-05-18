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
        $admin = User::where('role', '=', 'admin', true)->first();

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
            $this->seedSitus($materi, $topicKey, $admin);
        }
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
                'situs' => [
                    ['nama' => 'Situs Punden Berundak Gangkelit', 'alamat' => 'Desa Bedulu, Gianyar, Bali', 'lat' => -8.5093, 'lng' => 115.3056, 'deskripsi' => 'Situs punden berundak bersejarah di kawasan Bedulu.', 'masa' => null],
                    ['nama' => 'Situs Punden Berundak Yeh Ho', 'alamat' => 'Desa Pejem, Tabanan, Bali', 'lat' => -8.4234, 'lng' => 115.1234, 'deskripsi' => 'Situs peninggalan punden berundak di kawasan pesisir Tabanan.', 'masa' => null],
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
                'situs' => [
                    ['nama' => 'Situs Sarkofagus Yeh Mengening', 'alamat' => 'Desa Bedulu, Gianyar, Bali', 'lat' => -8.5123, 'lng' => 115.3067, 'deskripsi' => 'Situs penemuan sarkofagus dengan ukiran wajah manusia.', 'masa' => null],
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
                'situs' => [
                    ['nama' => 'Situs Arca Megalitik Taman Bung Karno', 'alamat' => 'Desa Tampaksiring, Gianyar, Bali', 'lat' => -8.4321, 'lng' => 115.2876, 'deskripsi' => 'Kawasan penemuan arca megalitik di Gianyar.', 'masa' => null],
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
                'situs' => [
                    ['nama' => 'Situs Menhir Galian', 'alamat' => 'Desa Galian, Gianyar, Bali', 'lat' => -8.5012, 'lng' => 115.2987, 'deskripsi' => 'Situs penemuan menhir di kawasan Galian.', 'masa' => null],
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
                'situs' => [
                    ['nama' => 'Situs Dolmen Malet', 'alamat' => 'Desa Malet, Gianyar, Bali', 'lat' => -8.4876, 'lng' => 115.3123, 'deskripsi' => 'Situs penemuan dolmen di kawasan Malet.', 'masa' => null],
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
                'situs' => [
                    ['nama' => 'Situs Arca Pura Besakih', 'alamat' => 'Desa Besakih, Rendang, Karangasem, Bali', 'lat' => -8.3764, 'lng' => 115.3167, 'deskripsi' => 'Kumpulan arca di kompleks Pura Besakih.', 'masa' => null],
                    ['nama' => 'Situs Arca Yeh Ganggi', 'alamat' => 'Desa Yeh Ganggi, Klungkung, Bali', 'lat' => -8.5234, 'lng' => 115.4012, 'deskripsi' => 'Situs penemuan arca bersejarah di Yeh Ganggi.', 'masa' => null],
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
                'situs' => [
                    ['nama' => 'Candi Gunung Kawi', 'alamat' => 'Desa Tampaksiring, Gianyar, Bali', 'lat' => -8.4376, 'lng' => 115.3123, 'deskripsi' => 'Candi kuno yang diukir di tebing batu.', 'masa' => null],
                    ['nama' => 'Candi Mengwi', 'alamat' => 'Desa Mengwi, Badung, Bali', 'lat' => -8.5234, 'lng' => 115.2345, 'deskripsi' => 'Situs candi bersejarah di Mengwi.', 'masa' => null],
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
                'situs' => [
                    ['nama' => 'Situs Prasasti Sukuh', 'alamat' => 'Desa Sukuh, Karangasem, Bali', 'lat' => -8.4234, 'lng' => 115.3567, 'deskripsi' => 'Lokasi penemuan prasasti Sukuh.', 'masa' => null],
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
                'situs' => [
                    ['nama' => 'Pura Kehen', 'alamat' => 'Desa Bangli, Bangli, Bali', 'lat' => -8.3723, 'lng' => 115.1234, 'deskripsi' => 'Pura bersejarah yang dibangun pada masa pengaruh Majapahit.', 'masa' => null],
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
                'situs' => [
                    ['nama' => 'Sentra Wayang Kamasan', 'alamat' => 'Desa Kamasan, Klungkung, Bali', 'lat' => -8.5234, 'lng' => 115.4123, 'deskripsi' => 'Pusat pembuatan dan penjualan wayang Kamasan.', 'masa' => null],
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
                'situs' => [
                    ['nama' => 'Gedung Art Deco Singaraja', 'alamat' => 'Kota Singaraja, Bali', 'lat' => -8.1234, 'lng' => 115.0876, 'deskripsi' => 'Kawasan gedung-gedung bergaya art deco peninggalan kolonial.', 'masa' => null],
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
                'situs' => [
                    ['nama' => 'Monumen Perjuangan Rakyat Bali', 'alamat' => 'Kota Denpasar, Bali', 'lat' => -8.6534, 'lng' => 115.2145, 'deskripsi' => 'Monumen bersejarah perjuangan rakyat Bali.', 'masa' => null],
                    ['nama' => 'Museum Le Mayeur', 'alamat' => 'Sanur, Denpasar, Bali', 'lat' => -8.6823, 'lng' => 115.2634, 'deskripsi' => 'Museum yang didirikan oleh pelukis Belgia Le Mayeur.', 'masa' => null],
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

    private function seedSitus(Materi $materi, ?string $topicKey, User $admin): void
    {
        $contentData = $this->getContentData();
        $data = $contentData[$topicKey] ?? $contentData['Punden Berundak'];

        if (! isset($data['situs'])) {
            // Create a default situs
            $situs = SitusPeninggalan::updateOrCreate(
                [
                    'materi_id' => $materi->materi_id,
                    'nama' => 'Situs '.$materi->judul,
                ],
                [
                    'alamat' => 'Bali',
                    'lat' => -8.5 + (rand(-50, 50) / 100),
                    'lng' => 115.3 + (rand(-50, 50) / 100),
                    'deskripsi' => 'Situs untuk materi '.$materi->judul,
                    'user_id' => $admin->id,
                ]
            );
        } else {
            foreach ($data['situs'] as $situsData) {
                $situs = SitusPeninggalan::updateOrCreate(
                    [
                        'materi_id' => $materi->materi_id,
                        'nama' => $situsData['nama'],
                    ],
                    [
                        'alamat' => $situsData['alamat'],
                        'lat' => $situsData['lat'],
                        'lng' => $situsData['lng'],
                        'deskripsi' => $situsData['deskripsi'],
                        'user_id' => $admin->id,
                    ]
                );

                // Create virtual museum for this situs
                $this->seedVirtualMuseum($situs);
            }
        }
    }

    private function seedVirtualMuseum(SitusPeninggalan $situs): void
    {
        // Create virtual museum
        $museum = VirtualMuseum::updateOrCreate(
            ['situs_id' => $situs->situs_id],
            [
                'nama' => 'Virtual Museum '.$situs->nama,
                'path_obj' => 'virtual-museum/'.$situs->situs_id.'/museum.glb',
            ]
        );

        // Create virtual museum objects
        $objects = [
            [
                'nama' => 'Objek AR '.$situs->nama.' 1',
                'gambar_real' => 'images/ar/placeholder-real.png',
                'path_obj' => 'virtual-museum/objects/'.bin2hex(random_bytes(4)).'.glb',
                'path_gambar_marker' => 'images/ar/placeholder-marker.png',
                'path_patt' => 'patterns/'.bin2hex(random_bytes(4)).'patt',
                'deskripsi' => 'Objek AR untuk '.$situs->nama,
            ],
            [
                'nama' => 'Objek AR '.$situs->nama.' 2',
                'gambar_real' => 'images/ar/placeholder-real2.png',
                'path_obj' => 'virtual-museum/objects/'.bin2hex(random_bytes(4)).'.glb',
                'path_gambar_marker' => 'images/ar/placeholder-marker2.png',
                'path_patt' => 'patterns/'.bin2hex(random_bytes(4)).'patt',
                'deskripsi' => 'Objek AR kedua untuk '.$situs->nama,
            ],
        ];

        foreach ($objects as $objectData) {
            VirtualMuseumObject::updateOrCreate(
                [
                    'museum_id' => $museum->museum_id,
                    'nama' => $objectData['nama'],
                ],
                array_merge($objectData, [
                    'situs_id' => $situs->situs_id,
                ])
            );
        }
    }
}