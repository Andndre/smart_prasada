/**
 * Petunjuk arah ke objek interaktif yang panelnya belum pernah dibuka (§8 A2).
 *
 * Kenapa perlu: AMBANG_PENGAMATAN = 100%, jadi satu objek yang terlewat di balik
 * punden menahan siswa di fase eksplorasi selamanya — tanpa error, tanpa petunjuk.
 * Macet berarti memanggil fasilitator, dan itu menabrak kriteria "tanpa intervensi
 * teknis pengembang".
 *
 * Memandu, tidak mengunci: murni visual. Tidak ada aturan lain yang boleh
 * bergantung pada nilai balik di sini.
 *
 * Sumber "sudah diamati" adalah `PhaseManager.objekDiamati` yang sama dengan pemicu
 * transisi fase — jangan membuat pelacakan kedua yang bisa melenceng darinya.
 *
 * Sengaja tidak mengimpor Three.js supaya bisa diuji `npm run test:js`.
 */

/**
 * Objek belum-diamati yang paling dekat dengan kepala siswa.
 *
 * @param {{nama: string, x: number, y: number, z: number}[]} kandidat posisi dunia
 * @param {{x: number, y: number, z: number}} kepala posisi kamera di ruang dunia
 * @param {Set<string>} diamati nama mesh yang panelnya sudah pernah dibuka
 * @returns {{nama: string, x: number, y: number, z: number}|null}
 */
export function targetTerdekat(kandidat, kepala, diamati) {
    let terpilih = null;
    let terdekat = Infinity;

    for (const objek of kandidat) {
        if (diamati.has(objek.nama)) continue;
        const jarak =
            (objek.x - kepala.x) ** 2 + (objek.y - kepala.y) ** 2 + (objek.z - kepala.z) ** 2;
        if (jarak < terdekat) {
            terdekat = jarak;
            terpilih = objek;
        }
    }

    return terpilih;
}

/** Simpangan dari pusat pandangan sebelum penanda muncul. Di bawah ini siswa sudah melihatnya. */
export const AMBANG_SIMPANGAN_DERAJAT = 18;

/**
 * Sudut penanda di sekeliling kursor, dari posisi target di ruang kamera
 * (x kanan, y atas, z ke belakang — konvensi Three.js).
 *
 * @returns {{sudut: number, tampil: boolean}} sudut radian, 0 = kanan kursor
 */
export function petunjukLayar(x, y, z, ambangDerajat = AMBANG_SIMPANGAN_DERAJAT) {
    const simpangan = (Math.atan2(Math.hypot(x, y), -z) * 180) / Math.PI;
    return { sudut: Math.atan2(y, x), tampil: simpangan > ambangDerajat };
}
