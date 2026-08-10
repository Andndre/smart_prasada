/**
 * Alur pembelajaran empat fase: orientasi -> eksplorasi -> interaksi -> refleksi.
 * Wajib per proposal hal. 22 ("state management untuk mengatur transisi antar fase
 * pembelajaran").
 *
 * ==========================================================================
 * FASE MELACAK DAN MEMANDU — FASE TIDAK PERNAH MENGUNCI.
 * ==========================================================================
 * Tidak ada satu pun kode di sini yang boleh memblokir teleport, panel info, atau
 * genggam. Kalau fase mengunci, setiap pemicu transisi berubah jadi titik macet
 * permanen: di museum uji punden berjarak 4 m dari titik spawn dan keempat panel
 * bisa dibuka sambil berdiri diam, jadi siswa yang tidak pernah merasa perlu
 * teleport akan tersangkut di orientasi tanpa pesan error apa pun. Siswa macet
 * memanggil fasilitator, dan itu intervensi yang dilarang kriteria TKT 6.
 *
 * Jangan "memperbaiki" modul ini menjadi penguncian.
 *
 * Berkas ini sengaja tidak mengimpor Three.js supaya logikanya bisa diuji dengan
 * `npm run test:js` tanpa perkakas tambahan.
 */

export const FASE = {
    Orientasi: 'orientasi',
    Eksplorasi: 'eksplorasi',
    Interaksi: 'interaksi',
    Refleksi: 'refleksi',
};

export const ALASAN = {
    Selesai: 'selesai',
    DilewatiPerangkat: 'dilewati_perangkat',
    DilewatiTanpaSlot: 'dilewati_tanpa_slot',
};

const URUTAN = [FASE.Orientasi, FASE.Eksplorasi, FASE.Interaksi, FASE.Refleksi];

/**
 * Bagian objek yang harus diamati sebelum eksplorasi dianggap tuntas.
 * 1 = seluruhnya. Turunkan ke misal 0.7 kalau uji internal menunjukkan 100% terlalu
 * memaksa untuk museum berobjek banyak.
 */
export const AMBANG_PENGAMATAN = 1;

export class PhaseManager {
    /**
     * @param {object} opsi
     * @param {number} opsi.totalObjek jumlah objek interaktif di scene
     * @param {number} opsi.totalSlot jumlah slot puzzle di scene
     * @param {boolean} opsi.perangkatBerkemampuanController kemampuan perangkat, bukan
     *   status sambungan controller — Quest tetap Quest walau controller belum bangun
     * @param {(perubahan: {dari: string, ke: string, alasan: string}) => void} [opsi.onChange]
     */
    constructor({ totalObjek, totalSlot, perangkatBerkemampuanController, onChange }) {
        this.totalObjek = totalObjek;
        this.totalSlot = totalSlot;
        this.perangkatBerkemampuanController = perangkatBerkemampuanController;
        this.onChange = onChange ?? (() => {});

        this.fase = FASE.Orientasi;
        this.sudahTeleport = false;
        this.objekDiamati = new Set();
        this.jumlahTerpasang = 0;
    }

    get interaksiTersedia() {
        return this.perangkatBerkemampuanController && this.totalSlot > 0;
    }

    get semuaObjekDiamati() {
        if (this.totalObjek === 0) return true;
        return this.objekDiamati.size / this.totalObjek >= AMBANG_PENGAMATAN;
    }

    get puzzleSelesai() {
        return this.interaksiTersedia && this.jumlahTerpasang >= this.totalSlot;
    }

    catatTeleport() {
        this.sudahTeleport = true;
        this.evaluasi();
    }

    catatPanelDibuka(meshName) {
        if (meshName) this.objekDiamati.add(meshName);
        this.evaluasi();
    }

    catatPemasangan(jumlahTerpasang) {
        this.jumlahTerpasang = jumlahTerpasang;
        this.evaluasi();
    }

    /** Fase tertinggi yang syaratnya sudah terpenuhi. */
    faseTarget() {
        if (this.puzzleSelesai || (!this.interaksiTersedia && this.semuaObjekDiamati)) {
            return FASE.Refleksi;
        }
        if (this.semuaObjekDiamati) return FASE.Interaksi;
        if (this.sudahTeleport) return FASE.Eksplorasi;
        return FASE.Orientasi;
    }

    /**
     * Kenapa fase yang sedang berjalan ditinggalkan. Membedakan "responden
     * menyelesaikan interaksi" dari "responden tidak pernah diberi kesempatan" —
     * perbedaan yang menentukan validitas TKT 6.
     */
    alasanMeninggalkan(fase) {
        if (fase !== FASE.Interaksi) return ALASAN.Selesai;
        if (this.puzzleSelesai) return ALASAN.Selesai;
        if (!this.perangkatBerkemampuanController) return ALASAN.DilewatiPerangkat;
        return ALASAN.DilewatiTanpaSlot;
    }

    /**
     * Maju selangkah demi selangkah sampai fase target, tidak pernah mundur.
     * Melangkah satu per satu supaya fase yang dilompati tetap punya baris
     * FaseBerubah sendiri — kalau siswa memasang puzzle sebelum membuka semua panel,
     * data tetap menunjukkan ia melewati interaksi, bukan meloncat dari eksplorasi.
     */
    evaluasi() {
        const target = URUTAN.indexOf(this.faseTarget());
        let sekarang = URUTAN.indexOf(this.fase);

        while (sekarang < target) {
            const dari = URUTAN[sekarang];
            const ke = URUTAN[sekarang + 1];
            const alasan = this.alasanMeninggalkan(dari);
            this.fase = ke;
            sekarang++;
            this.onChange({ dari, ke, alasan });
        }
    }

    /** Teks panel fase. Dibaca tiap frame; panel menggambar ulang hanya bila berubah. */
    deskripsi() {
        switch (this.fase) {
            case FASE.Orientasi:
                return {
                    judul: 'Orientasi',
                    instruksi: 'Lihat sekeliling. Arahkan ke lantai lalu tekan untuk berpindah.',
                };
            case FASE.Eksplorasi:
                return {
                    judul: 'Eksplorasi',
                    instruksi: `Amati objek bercahaya. ${this.objekDiamati.size} dari ${this.totalObjek} objek sudah diamati.`,
                };
            case FASE.Interaksi:
                return {
                    judul: 'Interaksi',
                    instruksi: `Genggam objek dan pasang ke tempatnya. ${this.jumlahTerpasang} dari ${this.totalSlot} terpasang.`,
                };
            default:
                // Kalau fase interaksi terlewat karena perangkatnya, sebutkan — siswa yang
                // melihat objek berkedip tapi tidak bisa memasangnya berhak tahu alasannya,
                // bukan dibiarkan mengira dirinya gagal.
                if (!this.perangkatBerkemampuanController && this.totalSlot > 0) {
                    return {
                        judul: 'Selesai',
                        instruksi: 'Sesi selesai. Pemasangan objek hanya tersedia di headset VR. Lanjutkan refleksi di layar.',
                    };
                }

                return {
                    judul: 'Selesai',
                    instruksi: 'Sesi VR selesai. Lepas headset dan lanjutkan refleksi di layar.',
                };
        }
    }
}
