/**
 * Kode responden untuk mode kiosk.
 *
 * Satu headset dipakai bergiliran ratusan orang lewat satu akun, jadi `kode_responden`
 * adalah satu-satunya hal yang membedakan mereka di `vr_event` dan `jawaban_refleksi`.
 * Kode yang basi — sesi berikutnya masih memakai kode responden sebelumnya — lebih
 * buruk daripada kode kosong: kosong itu jujur, basi itu salah diam-diam.
 *
 * Dua cara pengisian, keduanya harus tetap mungkin:
 *   1. Rentang berurutan dari peluncur (R041..R060) — "Responden berikutnya" satu ketukan.
 *   2. Ketik manual — untuk kode yang tidak berurutan.
 *
 * Berkas ini sengaja tidak mengimpor Three.js supaya bisa diuji `npm run test:js`.
 */

const POLA = /^(.*?)(\d+)$/;

/**
 * Kode berikutnya dalam deret, mempertahankan awalan dan lebar angka nol di depan.
 * Mengembalikan null kalau kode tidak berakhir dengan angka — berarti tidak berurutan
 * dan penambahan otomatis tidak masuk akal.
 */
export function kodeBerikutnya(kode) {
    const cocok = POLA.exec(kode ?? '');
    if (!cocok) return null;

    const [, awalan, angka] = cocok;
    const berikut = String(Number(angka) + 1).padStart(angka.length, '0');

    return awalan + berikut;
}

/** Apakah kode sudah melewati batas akhir rentang. Awalan yang berbeda dianggap di luar deret. */
export function melewatiAkhir(kode, kodeAkhir) {
    if (!kodeAkhir) return false;

    const sekarang = POLA.exec(kode ?? '');
    const akhir = POLA.exec(kodeAkhir);
    if (!sekarang || !akhir || sekarang[1] !== akhir[1]) return false;

    return Number(sekarang[2]) > Number(akhir[2]);
}

/**
 * Kode otomatis untuk responden berikutnya, atau null kalau fasilitator harus mengetik.
 * Null terjadi saat kode tidak berurutan, atau saat rentang sudah habis.
 */
export function kodeOtomatisBerikutnya(kode, kodeAkhir) {
    const berikut = kodeBerikutnya(kode);
    if (!berikut) return null;
    if (melewatiAkhir(berikut, kodeAkhir)) return null;

    return berikut;
}
