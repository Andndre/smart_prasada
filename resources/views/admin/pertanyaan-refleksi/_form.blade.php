<div>
    <label for="nilai_karakter" class="mb-2 block text-sm font-medium text-gray-700">Nilai Karakter</label>
    <select id="nilai_karakter" name="nilai_karakter" required
        class="block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
        <option value="">— pilih nilai —</option>
        @foreach (\App\Enums\NilaiKarakter::options() as $value => $label)
            <option value="{{ $value }}"
                @selected(old('nilai_karakter', $soal?->nilai_karakter?->value) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    {{-- Satu pertanyaan menggali satu nilai — berbeda dari objek museum yang boleh
         membawa beberapa nilai sekaligus. Lihat komentar di migration. --}}
    <p class="mt-1 text-xs text-gray-500">Satu pertanyaan menggali satu nilai, supaya jawabannya bisa dianalisis per nilai.</p>
    @error('nilai_karakter')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="pertanyaan" class="mb-2 block text-sm font-medium text-gray-700">Pertanyaan</label>
    <textarea id="pertanyaan" name="pertanyaan" rows="4" required
        class="block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500"
        placeholder="Contoh: Punden berundak ini dibangun bersama-sama oleh warga desa. Di mana kamu bisa menerapkan sikap itu dalam keseharianmu?">{{ old('pertanyaan', $soal?->pertanyaan) }}</textarea>
    <p class="mt-1 text-xs text-gray-500">Pertanyaan terbuka, tanpa jawaban benar atau salah.</p>
    @error('pertanyaan')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="urutan" class="mb-2 block text-sm font-medium text-gray-700">Urutan</label>
    <input type="number" id="urutan" name="urutan" min="0" value="{{ old('urutan', $soal?->urutan ?? 0) }}"
        class="block w-32 rounded-md border border-gray-300 px-3 py-2 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
    @error('urutan')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
