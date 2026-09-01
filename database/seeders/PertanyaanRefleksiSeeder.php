<?php

namespace Database\Seeders;

use App\Enums\NilaiKarakter;
use App\Models\PertanyaanRefleksi;
use App\Models\VirtualMuseum;
use Illuminate\Database\Seeder;

/**
 * Pertanyaan refleksi cadangan untuk museum uji.
 *
 * CADANGAN, bukan naskah final. Kosakata NilaiKarakter masih placeholder (6
 * dimensi Profil Pelajar Pancasila) sampai daftar dari Pardi dkk. (2017) masuk,
 * jadi pemasangan nilai di bawah ikut sementara. Yang permanen hanya bentuknya:
 * satu pertanyaan menggali tepat satu nilai, supaya jawabannya bisa dianalisis
 * per nilai.
 *
 * Ada karena museum tanpa pertanyaan membuat modul refleksi — modul kelima
 * blueprint — tidak bisa diuji sama sekali di gladi bersih. Halaman refleksi
 * memang menjelaskan diri saat kosong, tapi penjelasan bukan data penelitian.
 *
 * Museum yang sudah punya pertanyaan DILEWATI, bukan ditimpa: begitu tim materi
 * mengarang lewat CRUD admin, karangan mereka menang selamanya dan seeder ini
 * berhenti menyentuh museum itu. Kalau ia menimpa, satu `db:seed` yang tidak
 * disengaja menghapus pekerjaan yang tidak ada salinannya.
 */
class PertanyaanRefleksiSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->getPertanyaanData() as $namaMuseum => $daftar) {
            $museum = VirtualMuseum::where('nama', $namaMuseum)->first();

            if (! $museum) {
                $this->command?->warn("Museum '{$namaMuseum}' tidak ditemukan. Melewati pertanyaan refleksinya.");

                continue;
            }

            if (PertanyaanRefleksi::where('museum_id', $museum->museum_id)->exists()) {
                $this->command?->line("  Museum '{$namaMuseum}' sudah punya pertanyaan — dilewati.");

                continue;
            }

            foreach ($daftar as $urutan => [$nilai, $pertanyaan]) {
                PertanyaanRefleksi::create([
                    'museum_id' => $museum->museum_id,
                    'nilai_karakter' => $nilai,
                    'pertanyaan' => $pertanyaan,
                    'urutan' => $urutan + 1,
                ]);
            }
        }
    }

    /**
     * Pertanyaan digantung pada objek yang benar-benar ada di tiap museum, bukan
     * kalimat generik yang bisa dijawab tanpa pernah masuk VR — refleksi yang
     * tidak menuntut pengamatan tidak mengukur apa pun soal medianya.
     *
     * @return array<string, list<array{0: NilaiKarakter, 1: string}>>
     */
    private function getPertanyaanData(): array
    {
        return [
            'Punden Berundak di Pura Mehu' => [
                [NilaiKarakter::Religius, 'Punden berundak dibuat semakin mengecil ke arah puncak, dan bagian tersuci justru ada di tingkat paling atas. Menurutmu, apa yang ingin disampaikan orang dahulu lewat bentuk seperti itu?'],
                [NilaiKarakter::GotongRoyong, 'Bangunan ini disusun dari batu padas yang direkatkan dengan tanah, tanpa alat berat. Bayangkan berapa orang yang harus bekerja sama untuk menyelesaikannya. Apa yang kamu pelajari dari cara mereka bekerja?'],
                [NilaiKarakter::BernalarKritis, 'Kamu melihat dua punden dengan jumlah tingkatan dan hiasan yang berbeda di situs yang sama. Menurutmu apa yang bisa menjelaskan perbedaan itu?'],
            ],

            'Punden Berundak di Pura Candi' => [
                [NilaiKarakter::Religius, 'Punden Pelinggih I Ratu Gede Kemulan sengaja dibangun menghadap Gunung Penulisan. Apa arti memilih arah tertentu untuk sebuah tempat pemujaan menurutmu?'],
                [NilaiKarakter::Kreatif, 'Pada dinding dan sudut bangunan ada motif simbar gantung, ceplok bunga, dan sulur teratai yang dipahat pada batu keras. Bagian mana yang paling menarik perhatianmu, dan mengapa?'],
                [NilaiKarakter::BernalarKritis, 'Bangunan ini sudah berdiri ratusan tahun dan masih dipakai sampai sekarang. Menurutmu apa yang membuat sebuah peninggalan bisa bertahan selama itu?'],
            ],

            'Arca Peninggalan di Pura Taulan' => [
                [NilaiKarakter::BerkebinekaanGlobal, 'Sepasang arca di pura ini berpakaian dan berhias dengan gaya Tionghoa, tetapi ditempatkan di tempat suci masyarakat Bali. Apa yang hal itu ceritakan tentang hubungan antarbudaya pada masa lampau?'],
                [NilaiKarakter::Religius, 'Benda dari budaya lain bisa diterima menjadi bagian dari tempat ibadah. Menurutmu, sikap seperti apa yang membuat hal itu mungkin terjadi?'],
                [NilaiKarakter::BernalarKritis, 'Kalau kamu menemukan arca seperti ini tanpa ada yang menjelaskan asal-usulnya, petunjuk apa saja yang akan kamu cari untuk menebak dari mana ia berasal?'],
            ],

            'Galeri Virtual Wayang Kamasan' => [
                [NilaiKarakter::Kreatif, 'Lukisan gaya Kamasan menceritakan kisah panjang hanya lewat satu bidang gambar, tanpa tulisan. Dari lukisan yang kamu amati, bagaimana pelukisnya membuat ceritanya tetap bisa dibaca?'],
                [NilaiKarakter::BerkebinekaanGlobal, 'Ramayana dan Bhagavad Gita berasal dari India, tetapi tokoh-tokohnya di sini digambar dengan pakaian, wajah, dan hiasan khas Bali. Menurutmu mengapa cerita dari tempat lain bisa digambar ulang dengan cara sendiri?'],
                [NilaiKarakter::BernalarKritis, 'Kelima lukisan memakai warna, garis, dan pola hias yang mirip satu sama lain. Menurutmu apa gunanya seniman mengikuti pakem yang sama selama bergenerasi?'],
            ],

            // Satu-satunya museum yang objeknya benar-benar bisa dibuka panel infonya,
            // jadi pertanyaannya boleh menuntut pengamatan per objek — di empat museum
            // lain hal itu belum mungkin sampai modelnya diekspor ulang dengan nama node.
            'Punden Berundak Pura Mehu (Rintisan)' => [
                [NilaiKarakter::Religius, 'Di puncak punden terdapat Padma Kurung, relung berdinding tiga sisi tanpa atap. Mengapa menurutmu bagian tersuci justru dibuat terbuka ke langit?'],
                [NilaiKarakter::GotongRoyong, 'Selain punden, di areal ini ada Bale Pesandekan dan pelataran persembahyangan yang luas. Menurutmu untuk apa sebuah tempat suci menyediakan ruang sebesar itu bagi orang banyak?'],
                [NilaiKarakter::BernalarKritis, 'Kamu melewati Candi Bentar sebelum sampai ke punden. Gerbang itu dibelah dua dan tidak beratap. Apa yang ingin dirasakan orang yang berjalan menembusnya?'],
            ],

            'Menhir di Pura Puseh Adat Selullung' => [
                [NilaiKarakter::Religius, 'Menhir ini hanya sebuah batu tegak setinggi kurang lebih 74 cm, tanpa ukiran. Namun masyarakat masih menganggapnya sakral sampai hari ini. Menurutmu, apa yang membuat sebuah benda menjadi berharga bagi orang?'],
                [NilaiKarakter::GotongRoyong, 'Menhir ini terus dirawat dan dihormati oleh warga desa dari generasi ke generasi. Apa yang bisa kamu contoh dari cara mereka menjaga peninggalan bersama-sama?'],
                [NilaiKarakter::BernalarKritis, 'Tradisi megalitik jauh lebih tua daripada agama Hindu di Bali, tetapi menhir ini berdiri di halaman pura. Menurutmu bagaimana dua hal dari zaman yang berbeda bisa berdampingan?'],
            ],
        ];
    }
}
