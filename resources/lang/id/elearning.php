<?php

/**
 * Translation keys for E-Learning pages
 * Bahasa Indonesia
 */

return [
    /*
  |--------------------------------------------------------------------------
  | General / Layout
  |--------------------------------------------------------------------------
  */
    'back' => 'Kembali',
    'profile_picture' => 'Foto Profil',
    'progress' => 'Progres',
    'bab' => 'Bab',
    'era' => 'Era',
    'materi' => 'Materi',
    'selesai' => 'Selesai',
    'terkunci' => 'Terkunci',
    'tersedia' => 'Tersedia',
    'tidak_tersedia' => 'Tidak Tersedia',
    'lihat' => 'Lihat',
    'mulai' => 'Mulai',
    'lihat_hasil' => 'Lihat Hasil',
    'kembali' => 'Kembali',

    /*
  |--------------------------------------------------------------------------
  | Ebook Page
  |--------------------------------------------------------------------------
  */
    'ebook' => [
        'memuat_ebook' => 'Memuat e-book...',
        'klik_untuk_mulai' => 'Klik untuk mulai membaca',
        'ebook_tampil_fullscreen' => 'E-book akan tampil fullscreen',
        'mulai_membaca' => 'Mulai Membaca',
        'sebelumnya' => 'Sebelumnya',
        'selanjutnya' => 'Selanjutnya',
        'halaman' => 'Hal.',
        'dari' => 'dari',
    ],

    /*
  |--------------------------------------------------------------------------
  | Era Materi Page
  |--------------------------------------------------------------------------
  */
    'era_materi' => [
        'daftar_materi_era' => 'Daftar materi pada era',
        'materi_lainnya' => 'Materi Lainnya',
        'progress_era' => 'Progress Era',
        'belum_ada_materi' => 'Belum Ada Materi',
        'materi_segment_available_soon' => 'Materi pada era ini akan segera tersedia.',
    ],

    /*
  |--------------------------------------------------------------------------
  | Materi Detail Page
  |--------------------------------------------------------------------------
  */
    'materi' => [
        'pre_test' => 'Pre Test',
        'e_book' => 'E-Book',
        'virtual_living_museum' => 'Virtual Living Museum',
        'post_test' => 'Post Test',

        // Pre-test section
        'persiapan_pre_test' => 'Persiapan Pre Test',
        'akan_mengerjakan_pretest' => 'Anda akan mengerjakan Pre Test materi :materi. Siap mulai?',
        'panduan_mengisi_pretest' => 'Panduan Mengisi Pre Test',
        'pretest_tidak_tersedia' => 'Pre Test Tidak Tersedia',
        'materi_tidak_memiliki_pretst' => 'Materi ini tidak memiliki pre test',

        // E-book section
        'ebook_tidak_tersedia' => 'E-Book Tidak Tersedia',
        'materi_tidak_memiliki_ebook' => 'Materi ini tidak memiliki e-book',
        'ebook_terkunci' => 'E-Book Terkunci',
        'selesaikan_pretese_terlebih_dulu' => 'Selesaikan pre-test terlebih dahulu untuk mengakses e-book',
        'harus_baca_seluruh_halaman' => 'Anda harus membaca seluruh halaman pada e-book untuk menyelesaikan e-book ini dan dapat melanjutkan ke tingkatan selanjutnya.',
        'baca_ebook' => 'Baca E-Book',
        'file_tidak_ditemukan' => 'File tidak ditemukan',
        'file_error' => 'File Error',

        // Virtual Living Museum section
        'museum_tidak_tersedia' => ':appName Tidak Tersedia',
        'materi_tidak_memiliki_museum' => 'Materi ini tidak memiliki :appName',
        'museum_terkunci' => ':appName Terkunci',
        'baca_semua_ebook_terlebih_dulu' => 'Baca semua e-book terlebih dahulu untuk mengakses :appName',
        'kunjungi' => 'Kunjungi',

        // Post-test section
        'post_test_tidak_tersedia' => 'Post Test Tidak Tersedia',
        'materi_tidak_memiliki_posttest' => 'Materi ini tidak memiliki post test',
        'posttest_terkunci' => 'Post Test Terkunci',
        'kunjungi_semua_museum' => 'Kunjungi semua :appName terlebih dahulu untuk mengakses post-test',

        // Recap section
        'rekap' => 'Rekap',
        'nilai_pretest' => 'Nilai Pre test',
        'nilai_posttest' => 'Nilai Post test',
        'tugas' => 'Tugas',
        'beberapa_tugas_harus_diselesaikan' => 'Berikut adalah beberapa tugas yang harus Anda selesaikan.',
        'perkembangan_anda' => 'Perkembangan Anda',
        'siap_ke_materi_berikutnya' => 'Siap Ke-Materi Berikutnya?',
    ],

    /*
  |--------------------------------------------------------------------------
  | Pre-test Page
  |--------------------------------------------------------------------------
  */
    'pretest' => [
        'pretest_selesai' => 'Pre-test Selesai!',
        'telah_menyelesaikan_pretest' => 'Anda telah menyelesaikan pre-test untuk materi ini.',
        'nilai' => 'Nilai',
        'benar' => 'Benar',
        'salah' => 'Salah',
        'evaluasi_jawaban_anda' => 'Evaluasi Jawaban Anda',
        'soal' => 'Soal',

        // Instructions
        'instruksi_pretest' => 'Instruksi Pre-test',
        'pretest_terdiri_dari' => 'Pre-test terdiri dari :count soal pilihan ganda',
        'pilih_satu_jawaban' => 'Pilih satu jawaban yang paling tepat untuk setiap soal',
        'setelah_selesai_anjutkan_materi' => 'Setelah selesai, Anda dapat melanjutkan ke materi pembelajaran',
        'mulai_pretest' => 'Mulai Pre-test',
        'progres' => 'Progres',
        'dari' => 'dari',
        'selainnya' => 'Selanjutnya',
        'terjadi_kesalahan' => 'Terjadi kesalahan:',
        'sebelumnya' => 'Sebelumnya',
        'selesai' => 'Selesai',
        'mohon_jawab_semua' => 'Mohon jawab semua pertanyaan. Anda baru menjawab :answered dari :total soal.',
        'lanjutkan_ke_materi' => 'Lanjut ke Materi',
    ],

    /*
  |--------------------------------------------------------------------------
  | Post-test Page
  |--------------------------------------------------------------------------
  */
    'posttest' => [
        'posttest_selesai' => 'Post-test Selesai!',
        'telah_menyelesaikan_seluruh_materi' => 'Selamat! Anda telah menyelesaikan seluruh materi pembelajaran ini.',
        'nilai' => 'Nilai',
        'jawaban_benar' => 'Benar',
        'jawaban_salah' => 'Salah',
        'evaluasi_jawaban_anda' => 'Evaluasi Jawaban Anda',
        'soal' => 'Soal',
        'kembali_ke_daftar_materi' => 'Kembali ke Daftar Materi',
        'lihat_materi' => 'Lihat Materi',

        // Instructions
        'instruksi_posttest' => 'Instruksi Post-test',
        'posttest_terdiri_dari' => 'Post-test terdiri dari :count soal pilihan ganda',
        'soal_menguji_pemahaman' => 'Soal ini menguji pemahaman Anda setelah mempelajari materi',
        'setelah_selesai_materi_completed' => 'Setelah selesai, materi ini akan dianggap completed',
        'mulai_posttest' => 'Mulai Post-test',
        'progres' => 'Progres',
        'dari' => 'dari',
        'selainnya' => 'Selanjutnya',
        'sebelumnya' => 'Sebelumnya',
        'selesai' => 'Selesai',
        'mohon_jawab_semua' => 'Mohon jawab semua pertanyaan. Anda baru menjawab :answered dari :total soal.',
    ],

    /*
  |--------------------------------------------------------------------------
  | Tugas Page
  |--------------------------------------------------------------------------
  */
    'tugas' => [
        'tugas' => 'Tugas',
        'daftar_tugas' => 'Daftar Tugas',
        'tidak_ada_tugas' => 'Tidak Ada Tugas',
        'materi_tidak_memiliki_tugas' => 'Materi ini tidak memiliki tugas untuk dikerjakan',
        'kembali_ke_materi' => 'Kembali ke Materi',
    ],
];
