<?php

/**
 * Translation keys for E-Learning pages
 * English
 */

return [
    /*
  |--------------------------------------------------------------------------
  | General / Layout
  |--------------------------------------------------------------------------
  */
    'back' => 'Back',
    'profile_picture' => 'Profile Picture',
    'progress' => 'Progress',
    'bab' => 'Chapter',
    'era' => 'Era',
    'materi' => 'Material',
    'selesai' => 'Completed',
    'terkunci' => 'Locked',
    'tersedia' => 'Available',
    'tidak_tersedia' => 'Not Available',
    'lihat' => 'View',
    'mulai' => 'Start',
    'lihat_hasil' => 'View Results',
    'kembali' => 'Back',

    /*
  |--------------------------------------------------------------------------
  | Ebook Page
  |--------------------------------------------------------------------------
  */
    'ebook' => [
        'memuat_ebook' => 'Loading e-book...',
        'klik_untuk_mulai' => 'Click to start reading',
        'ebook_tampil_fullscreen' => 'E-book will display in fullscreen',
        'mulai_membaca' => 'Start Reading',
        'sebelumnya' => 'Previous',
        'selanjutnya' => 'Next',
        'halaman' => 'Page',
        'dari' => 'of',
    ],

    /*
  |--------------------------------------------------------------------------
  | Era Materi Page
  |--------------------------------------------------------------------------
  */
    'era_materi' => [
        'daftar_materi_era' => 'List of materials in this era',
        'materi_lainnya' => 'Other Materials',
        'progress_era' => 'Era Progress',
        'belum_ada_materi' => 'No Materials Yet',
        'materi_segment_available_soon' => 'Materials for this era will be available soon.',
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
        'persiapan_pre_test' => 'Pre Test Preparation',
        'akan_mengerjakan_pretest' => 'You will be taking the Pre Test for material :materi. Ready to start?',
        'panduan_mengisi_pretest' => 'Pre Test Answer Guide',
        'pretest_tidak_tersedia' => 'Pre Test Not Available',
        'materi_tidak_memiliki_pretst' => 'This material does not have a pre test',

        // E-book section
        'ebook_tidak_tersedia' => 'E-Book Not Available',
        'materi_tidak_memiliki_ebook' => 'This material does not have an e-book',
        'ebook_terkunci' => 'E-Book Locked',
        'selesaikan_pretese_terlebih_dulu' => 'Complete the pre-test first to access the e-book',
        'harus_baca_seluruh_halaman' => 'You must read all pages in the e-book to complete it and proceed to the next level.',
        'baca_ebook' => 'Read E-Book',
        'file_tidak_ditemukan' => 'File not found',
        'file_error' => 'File Error',

        // Virtual Living Museum section
        'museum_tidak_tersedia' => ':appName Not Available',
        'materi_tidak_memiliki_museum' => 'This material does not have :appName',
        'museum_terkunci' => ':appName Locked',
        'baca_semua_ebook_terlebih_dulu' => 'Read all e-books first to access :appName',
        'kunjungi' => 'Visit',

        // Post-test section
        'post_test_tidak_tersedia' => 'Post Test Not Available',
        'materi_tidak_memiliki_posttest' => 'This material does not have a post test',
        'posttest_terkunci' => 'Post Test Locked',
        'kunjungi_semua_museum' => 'Visit all :appName first to access the post-test',

        // Recap section
        'rekap' => 'Summary',
        'nilai_pretest' => 'Pre Test Score',
        'nilai_posttest' => 'Post Test Score',
        'tugas' => 'Assignment',
        'beberapa_tugas_harus_diselesaikan' => 'Here are some assignments you need to complete.',
        'perkembangan_anda' => 'Your Progress',
        'siap_ke_materi_berikutnya' => 'Ready for the Next Material?',
    ],

    /*
  |--------------------------------------------------------------------------
  | Pre-test Page
  |--------------------------------------------------------------------------
  */
    'pretest' => [
        'pretest_selesai' => 'Pre-test Completed!',
        'telah_menyelesaikan_pretest' => 'You have completed the pre-test for this material.',
        'nilai' => 'Score',
        'benar' => 'Correct',
        'evaluasi_jawaban_anda' => 'Your Answer Evaluation',
        'soal' => 'Question',
        'benar' => 'Correct',
        'salah' => 'Wrong',

        // Instructions
        'instruksi_pretest' => 'Pre-test Instructions',
        'pretest_terdiri_dari' => 'Pre-test consists of :count multiple choice questions',
        'pilih_satu_jawaban' => 'Choose the most appropriate answer for each question',
        'setelah_selesai_anjutkan_materi' => 'After finishing, you can proceed to the learning material',
        'mulai_pretest' => 'Start Pre-test',
        'progres' => 'Progress',
        'dari' => 'of',
        'selainnya' => 'Next',
        'terjadi_kesalahan' => 'An error occurred:',
        'sebelumnya' => 'Previous',
        'selesai' => 'Finish',
        'mohon_jawab_semua' => 'Please answer all questions. You have only answered :answered out of :total questions.',
        'lanjutkan_ke_materi' => 'Continue to Material',
    ],

    /*
  |--------------------------------------------------------------------------
  | Post-test Page
  |--------------------------------------------------------------------------
  */
    'posttest' => [
        'posttest_selesai' => 'Post-test Completed!',
        'telah_menyelesaikan_seluruh_materi' => 'Congratulations! You have completed all the learning material.',
        'nilai' => 'Score',
        'jawaban_benar' => 'Correct',
        'jawaban_salah' => 'Wrong',
        'evaluasi_jawaban_anda' => 'Your Answer Evaluation',
        'soal' => 'Question',
        'kembali_ke_daftar_materi' => 'Back to Material List',
        'lihat_materi' => 'View Material',

        // Instructions
        'instruksi_posttest' => 'Post-test Instructions',
        'posttest_terdiri_dari' => 'Post-test consists of :count multiple choice questions',
        'soal_menguji_pemahaman' => 'This test evaluates your understanding after studying the material',
        'setelah_selesai_materi_completed' => 'After completion, this material will be marked as completed',
        'mulai_posttest' => 'Start Post-test',
        'progres' => 'Progress',
        'dari' => 'of',
        'selainnya' => 'Next',
        'sebelumnya' => 'Previous',
        'selesai' => 'Finish',
        'mohon_jawab_semua' => 'Please answer all questions. You have only answered :answered out of :total questions.',
    ],

    /*
  |--------------------------------------------------------------------------
  | Tugas Page
  |--------------------------------------------------------------------------
  */
    'tugas' => [
        'tugas' => 'Assignment',
        'daftar_tugas' => 'Assignment List',
        'tidak_ada_tugas' => 'No Assignments',
        'materi_tidak_memiliki_tugas' => 'This material does not have any assignments to work on',
        'kembali_ke_materi' => 'Back to Material',
    ],
];
