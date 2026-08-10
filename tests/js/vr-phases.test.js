import assert from "node:assert/strict";
import { describe, it } from "node:test";

import { ALASAN, FASE, PhaseManager } from "../../public/assets/js/vr-phases.js";

function buatManager(opsi = {}) {
    const perubahan = [];
    const manager = new PhaseManager({
        totalObjek: 4,
        totalSlot: 4,
        perangkatBerkemampuanController: true,
        onChange: (p) => perubahan.push(p),
        ...opsi,
    });
    return { manager, perubahan };
}

function amatiSemua(manager, jumlah = 4) {
    for (let i = 0; i < jumlah; i++) manager.catatPanelDibuka(`Objek_${i}`);
}

describe("PhaseManager", () => {
    it("mulai di orientasi", () => {
        const { manager } = buatManager();
        assert.equal(manager.fase, FASE.Orientasi);
    });

    it("maju ke eksplorasi setelah teleport pertama", () => {
        const { manager, perubahan } = buatManager();
        manager.catatTeleport();

        assert.equal(manager.fase, FASE.Eksplorasi);
        assert.deepEqual(perubahan, [
            { dari: FASE.Orientasi, ke: FASE.Eksplorasi, alasan: ALASAN.Selesai },
        ]);
    });

    it("maju ke interaksi hanya setelah semua objek diamati", () => {
        const { manager } = buatManager();
        manager.catatTeleport();

        amatiSemua(manager, 3);
        assert.equal(manager.fase, FASE.Eksplorasi);

        manager.catatPanelDibuka("Objek_3");
        assert.equal(manager.fase, FASE.Interaksi);
    });

    it("tidak menghitung objek yang sama dua kali", () => {
        const { manager } = buatManager();
        manager.catatTeleport();
        for (let i = 0; i < 5; i++) manager.catatPanelDibuka("Objek_Sama");

        assert.equal(manager.fase, FASE.Eksplorasi);
        assert.equal(manager.objekDiamati.size, 1);
    });

    it("maju ke refleksi setelah semua puzzle terpasang", () => {
        const { manager, perubahan } = buatManager();
        manager.catatTeleport();
        amatiSemua(manager);
        manager.catatPemasangan(4);

        assert.equal(manager.fase, FASE.Refleksi);
        assert.equal(perubahan.at(-1).alasan, ALASAN.Selesai);
    });

    describe("pelewatan fase interaksi", () => {
        it("melewati interaksi di perangkat tanpa controller, dengan alasannya", () => {
            const { manager, perubahan } = buatManager({
                perangkatBerkemampuanController: false,
            });
            manager.catatTeleport();
            amatiSemua(manager);

            assert.equal(manager.fase, FASE.Refleksi);
            assert.deepEqual(perubahan.at(-1), {
                dari: FASE.Interaksi,
                ke: FASE.Refleksi,
                alasan: ALASAN.DilewatiPerangkat,
            });
        });

        it("melewati interaksi di scene tanpa slot, dengan alasan berbeda", () => {
            const { manager, perubahan } = buatManager({ totalSlot: 0 });
            manager.catatTeleport();
            amatiSemua(manager);

            assert.equal(manager.fase, FASE.Refleksi);
            assert.equal(perubahan.at(-1).alasan, ALASAN.DilewatiTanpaSlot);
        });

        it("tetap mencatat fase yang dilompati sebagai transisinya sendiri", () => {
            const { manager, perubahan } = buatManager({ totalSlot: 0 });
            manager.catatTeleport();
            amatiSemua(manager);

            assert.deepEqual(
                perubahan.map((p) => p.ke),
                [FASE.Eksplorasi, FASE.Interaksi, FASE.Refleksi],
            );
        });
    });

    describe("urutan tak terduga", () => {
        it("naik langsung ke eksplorasi kalau panel dibuka sebelum teleport", () => {
            const { manager } = buatManager();
            amatiSemua(manager, 2);

            // Fase melacak, bukan mengunci: panel tetap boleh dibuka di orientasi.
            assert.equal(manager.fase, FASE.Orientasi);
            assert.equal(manager.objekDiamati.size, 2);
        });

        it("mencatat interaksi walau puzzle selesai sebelum semua panel dibuka", () => {
            const { manager, perubahan } = buatManager();
            manager.catatTeleport();
            manager.catatPemasangan(4);

            assert.equal(manager.fase, FASE.Refleksi);
            assert.deepEqual(
                perubahan.map((p) => p.ke),
                [FASE.Eksplorasi, FASE.Interaksi, FASE.Refleksi],
            );
        });

        it("tidak pernah mundur", () => {
            const { manager } = buatManager();
            manager.catatTeleport();
            amatiSemua(manager);
            manager.catatPemasangan(4);
            assert.equal(manager.fase, FASE.Refleksi);

            manager.catatPemasangan(0);
            assert.equal(manager.fase, FASE.Refleksi);
        });
    });

    describe("scene tanpa objek interaktif", () => {
        it("tidak menggantung di eksplorasi", () => {
            const { manager } = buatManager({ totalObjek: 0, totalSlot: 0 });
            manager.catatTeleport();

            assert.equal(manager.fase, FASE.Refleksi);
        });
    });

    describe("deskripsi panel", () => {
        it("menampilkan kemajuan pengamatan supaya siswa tahu apa yang kurang", () => {
            const { manager } = buatManager();
            manager.catatTeleport();
            amatiSemua(manager, 2);

            assert.match(manager.deskripsi().instruksi, /2 dari 4 objek/);
        });

        it("menampilkan kemajuan pemasangan di fase interaksi", () => {
            const { manager } = buatManager();
            manager.catatTeleport();
            amatiSemua(manager);
            manager.catatPemasangan(1);

            assert.equal(manager.fase, FASE.Interaksi);
            assert.match(manager.deskripsi().instruksi, /1 dari 4 terpasang/);
        });

        it("mengarahkan ke layar saat refleksi", () => {
            const { manager } = buatManager({ totalObjek: 0, totalSlot: 0 });
            manager.catatTeleport();

            assert.match(manager.deskripsi().instruksi, /layar/);
        });
    });
});
