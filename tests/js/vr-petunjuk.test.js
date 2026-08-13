import assert from "node:assert/strict";
import { describe, it } from "node:test";

import { petunjukLayar, targetTerdekat } from "../../public/assets/js/vr-petunjuk.js";

const KANDIDAT = [
    { nama: "Dekat", x: 1, y: 0, z: 0 },
    { nama: "Jauh", x: 10, y: 0, z: 0 },
];
const ASAL = { x: 0, y: 0, z: 0 };

describe("targetTerdekat", () => {
    it("memilih objek belum diamati yang paling dekat", () => {
        assert.equal(targetTerdekat(KANDIDAT, ASAL, new Set()).nama, "Dekat");
    });

    it("melewati objek yang panelnya sudah pernah dibuka", () => {
        assert.equal(targetTerdekat(KANDIDAT, ASAL, new Set(["Dekat"])).nama, "Jauh");
    });

    it("mengembalikan null kalau semua sudah diamati — penanda mati sendiri", () => {
        assert.equal(targetTerdekat(KANDIDAT, ASAL, new Set(["Dekat", "Jauh"])), null);
    });

    it("mengukur jarak dari kepala, bukan dari titik asal dunia", () => {
        assert.equal(targetTerdekat(KANDIDAT, { x: 9, y: 0, z: 0 }, new Set()).nama, "Jauh");
    });
});

describe("petunjukLayar", () => {
    it("diam kalau target sudah dekat pusat pandangan", () => {
        assert.equal(petunjukLayar(0, 0, -5).tampil, false);
        assert.equal(petunjukLayar(0.1, 0, -5).tampil, false);
    });

    it("muncul kalau target menyimpang jauh dari pusat pandangan", () => {
        assert.equal(petunjukLayar(5, 0, -5).tampil, true);
    });

    it("muncul kalau target ada di belakang siswa", () => {
        assert.equal(petunjukLayar(0.1, 0, 5).tampil, true);
    });

    it("menunjuk ke arah target di ruang layar", () => {
        assert.equal(Math.round((petunjukLayar(5, 0, -5).sudut * 180) / Math.PI), 0);
        assert.equal(Math.round((petunjukLayar(0, 5, -5).sudut * 180) / Math.PI), 90);
        assert.equal(Math.round((petunjukLayar(-5, 0, -5).sudut * 180) / Math.PI), 180);
    });
});
