<x-app-layout>
    <div class="py-6">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-bold text-gray-900">Tambah Pertanyaan Refleksi</h1>

            <form method="POST" action="{{ route('admin.pertanyaan-refleksi.store', $museum->museum_id) }}"
                class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                @csrf
                @include('admin.pertanyaan-refleksi._form', ['soal' => null])

                <div class="flex gap-3">
                    <button type="submit"
                        class="rounded-md bg-purple-600 px-5 py-2 font-semibold text-white hover:bg-purple-700">Simpan</button>
                    <a href="{{ route('admin.pertanyaan-refleksi', $museum->museum_id) }}"
                        class="rounded-md border border-gray-300 px-5 py-2 text-gray-700 hover:bg-gray-50">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
