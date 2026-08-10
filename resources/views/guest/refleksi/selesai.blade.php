<x-app-layout>
    <div class="py-16">
        <div class="mx-auto max-w-xl px-4 text-center sm:px-6 lg:px-8">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                <i class="fas fa-check text-2xl text-green-600"></i>
            </div>

            <h1 class="mt-6 text-2xl font-bold text-gray-900">Refleksi tersimpan</h1>
            <p class="mt-3 text-gray-600">
                Terima kasih. Jawabanmu sudah tercatat dan akan digunakan untuk penelitian.
            </p>

            {{-- Halaman netral, bukan daftar materi: di mode kiosk semua responden berbagi
                 satu akun, jadi mengarahkan ke daftar materi akan menampilkan progres akun
                 bersama itu ke setiap siswa. Langkah berikutnya ditentukan fasilitator,
                 jadi jangan merantainya ke post-test. --}}
            <a href="{{ route('guest.home') }}"
                class="mt-8 inline-flex items-center rounded-md border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Kembali ke beranda
            </a>
        </div>
    </div>
</x-app-layout>
