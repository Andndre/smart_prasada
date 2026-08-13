/**
 * Titik pandang pembuka (§8 B3): di mana siswa berdiri dan ke mana ia menghadap
 * pada frame pertama.
 *
 * Kesan pertama menentukan seluruh laporan keterpakaian: spawn lama selalu di titik
 * asal dunia menghadap −Z, jadi model bisa muncul di samping, di belakang, atau jauh
 * mengecil — terbaca sebagai model 3D mengambang, bukan sebagai tempat.
 *
 * Semuanya diturunkan dari bounding box model, tidak ada koordinat khusus satu
 * museum. Arah datangnya dipertahankan dari titik asal dunia — itu sisi yang sudah
 * dipilih seniman waktu menaruh model di Blender.
 *
 * Menempatkan, bukan mengunci: setelah frame pertama siswa bebas menoleh dan
 * bergerak. Nilai balik dipasang ke RIG, bukan ke kamera — di WebXR rotasi kepala
 * milik tracking dan `camera.rotation` apa pun akan tertimpa pose headset.
 *
 * Sengaja tidak mengimpor Three.js supaya bisa diuji `npm run test:js`.
 */

/** Sisa ruang di tepi bingkai. 1 = model menyentuh tepi pandangan persis. */
export const MARGIN_BINGKAI = 1.15;

/** Model kecil pun tidak boleh menempel di wajah. */
export const JARAK_MINIMUM = 2.5;

/**
 * @param {{minX: number, maxX: number, minY: number, maxY: number, minZ: number, maxZ: number}} kotak
 *        bounding box model di ruang dunia, setelah lantainya diturunkan ke y = 0
 * @param {number} fovDerajat sudut pandang vertikal kamera
 * @param {number} aspect lebar/tinggi viewport
 * @returns {{x: number, z: number, yaw: number, jarak: number}} posisi rig di lantai dan
 *          rotasi Y-nya, sudah menghadap pusat model
 */
export function titikPandangAwal(kotak, fovDerajat, aspect) {
    const pusatX = (kotak.minX + kotak.maxX) / 2;
    const pusatZ = (kotak.minZ + kotak.maxZ) / 2;
    const tinggi = kotak.maxY - kotak.minY;
    const lebar = Math.max(kotak.maxX - kotak.minX, kotak.maxZ - kotak.minZ);

    const fovVertikal = (fovDerajat * Math.PI) / 180;
    const fovMendatar = 2 * Math.atan(Math.tan(fovVertikal / 2) * Math.max(aspect, 0.1));

    // Jarak terjauh dari keduanya: yang lebih dekat memotong model di salah satu sumbu.
    const jarak = Math.max(
        JARAK_MINIMUM,
        ((tinggi / 2) * MARGIN_BINGKAI) / Math.tan(fovVertikal / 2),
        ((lebar / 2) * MARGIN_BINGKAI) / Math.tan(fovMendatar / 2),
    );

    // Datang dari arah titik asal dunia. Model yang pusatnya tepat di titik asal tidak
    // punya arah datang — mundur ke +Z lalu menghadap −Z, arah spawn yang lama.
    const panjang = Math.hypot(pusatX, pusatZ);
    const arahX = panjang < 0.01 ? 0 : pusatX / panjang;
    const arahZ = panjang < 0.01 ? -1 : pusatZ / panjang;

    return {
        x: pusatX - arahX * jarak,
        z: pusatZ - arahZ * jarak,
        // Kamera menghadap −Z lokal; putar sampai −Z menunjuk kembali ke pusat model.
        yaw: Math.atan2(-arahX, -arahZ),
        jarak,
    };
}
