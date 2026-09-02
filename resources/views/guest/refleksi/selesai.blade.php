<x-app-layout tanpa-navigasi>
    <div class="bg-primary px-6 py-6 text-white">
        <div class="mx-auto max-w-7xl">
            <h1 class="text-lg font-bold">Refleksi</h1>
            <p class="text-sm opacity-90">Selesai</p>
        </div>
    </div>

    {{-- Kartu penutup meniru "state selesai" pretest/posttest: lingkaran centang hijau,
         judul, lalu satu tombol lanjut. --}}
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-7xl px-6 py-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                        <i class="fas fa-check text-2xl text-green-600"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900">Refleksi tersimpan</h3>
                    <p class="mb-6 text-gray-600">
                        Terima kasih. Jawabanmu sudah tercatat dan akan digunakan untuk penelitian.
                    </p>

                    {{-- Halaman netral, bukan daftar materi: di mode kiosk semua responden berbagi
                         satu akun, jadi mengarahkan ke daftar materi akan menampilkan progres akun
                         bersama itu ke setiap siswa. Langkah berikutnya ditentukan fasilitator,
                         jadi jangan merantainya ke post-test. --}}
                    <a href="{{ route('guest.home') }}"
                        class="inline-flex items-center rounded-lg bg-gray-100 px-6 py-3 font-medium text-gray-600 transition-colors hover:bg-gray-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
