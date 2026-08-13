import assert from "node:assert/strict";
import { describe, it } from "node:test";

import { JARAK_MINIMUM, titikPandangAwal } from "../../public/assets/js/vr-pandangan.js";

const FOV = 70;
const ASPECT = 16 / 9;

/** Kotak model setinggi `tinggi` dan selebar `lebar`, berpusat di (cx, cz). */
function kotak(cx, cz, tinggi, lebar = 2) {
    return {
        minX: cx - lebar / 2, maxX: cx + lebar / 2,
        minY: 0, maxY: tinggi,
        minZ: cz - lebar / 2, maxZ: cz + lebar / 2,
    };
}

/** Arah pandang rig setelah diputar `yaw`; kamera menghadap −Z lokal. */
function arahPandang(yaw) {
    return { x: -Math.sin(yaw), z: -Math.cos(yaw) };
}

describe("titikPandangAwal", () => {
    it("berdiri sejauh `jarak` dari pusat model", () => {
        const awal = titikPandangAwal(kotak(8, -3, 4), FOV, ASPECT);
        assert.ok(Math.abs(Math.hypot(8 - awal.x, -3 - awal.z) - awal.jarak) < 1e-9);
    });

    it("menghadap pusat model", () => {
        const awal = titikPandangAwal(kotak(8, -3, 4), FOV, ASPECT);
        const arah = arahPandang(awal.yaw);
        assert.ok(Math.abs(arah.x - (8 - awal.x) / awal.jarak) < 1e-9);
        assert.ok(Math.abs(arah.z - (-3 - awal.z) / awal.jarak) < 1e-9);
    });

    it("mendekat dari sisi titik asal dunia — sisi yang dipilih seniman", () => {
        const awal = titikPandangAwal(kotak(10, 0, 4), FOV, ASPECT);
        assert.ok(awal.x < 10, "berdiri di antara titik asal dan model, bukan di belakangnya");
        assert.ok(Math.abs(awal.z) < 1e-9);
    });

    it("model besar didekati dari lebih jauh supaya utuh dalam bingkai", () => {
        const kecil = titikPandangAwal(kotak(0, -10, 3), FOV, ASPECT);
        const besar = titikPandangAwal(kotak(0, -10, 12), FOV, ASPECT);
        assert.ok(besar.jarak > kecil.jarak);
    });

    it("model kecil tidak menempel di wajah", () => {
        assert.equal(titikPandangAwal(kotak(0, -5, 0.2, 0.2), FOV, ASPECT).jarak, JARAK_MINIMUM);
    });

    it("model yang pusatnya di titik asal: mundur ke +Z, menghadap −Z", () => {
        const awal = titikPandangAwal(kotak(0, 0, 4), FOV, ASPECT);
        assert.ok(Math.abs(awal.x) < 1e-9);
        assert.ok(awal.z > 0);
        assert.ok(Math.abs(arahPandang(awal.yaw).z + 1) < 1e-9);
    });

    it("model lebar di layar sempit didekati dari lebih jauh daripada di layar lebar", () => {
        const lebar = titikPandangAwal(kotak(0, -10, 2, 12), FOV, 16 / 9);
        const sempit = titikPandangAwal(kotak(0, -10, 2, 12), FOV, 9 / 16);
        assert.ok(sempit.jarak > lebar.jarak);
    });
});
