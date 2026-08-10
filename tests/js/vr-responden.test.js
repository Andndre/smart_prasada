import assert from "node:assert/strict";
import { describe, it } from "node:test";

import {
    kodeBerikutnya,
    kodeOtomatisBerikutnya,
    melewatiAkhir,
} from "../../public/assets/js/vr-responden.js";

describe("kodeBerikutnya", () => {
    it("menambah satu dan mempertahankan awalan", () => {
        assert.equal(kodeBerikutnya("R041"), "R042");
    });

    it("mempertahankan lebar angka nol di depan", () => {
        assert.equal(kodeBerikutnya("R009"), "R010");
        assert.equal(kodeBerikutnya("R099"), "R100");
    });

    it("melebar saat angka meluap", () => {
        assert.equal(kodeBerikutnya("R99"), "R100");
    });

    it("bekerja tanpa awalan", () => {
        assert.equal(kodeBerikutnya("7"), "8");
    });

    it("menerima awalan berisi angka di tengah", () => {
        assert.equal(kodeBerikutnya("SMP2-014"), "SMP2-015");
    });

    it("mengembalikan null untuk kode tanpa angka di akhir", () => {
        assert.equal(kodeBerikutnya("kelas-a"), null);
        assert.equal(kodeBerikutnya(""), null);
        assert.equal(kodeBerikutnya(null), null);
    });
});

describe("melewatiAkhir", () => {
    it("false selama masih di dalam rentang", () => {
        assert.equal(melewatiAkhir("R042", "R060"), false);
        assert.equal(melewatiAkhir("R060", "R060"), false);
    });

    it("true setelah melewati batas", () => {
        assert.equal(melewatiAkhir("R061", "R060"), true);
    });

    it("false kalau tidak ada batas akhir", () => {
        assert.equal(melewatiAkhir("R061", null), false);
    });

    it("menganggap awalan berbeda sebagai di luar deret", () => {
        assert.equal(melewatiAkhir("X061", "R060"), false);
    });
});

describe("kodeOtomatisBerikutnya", () => {
    it("memberi kode berikutnya selama rentang belum habis", () => {
        assert.equal(kodeOtomatisBerikutnya("R041", "R060"), "R042");
    });

    it("null saat rentang habis, supaya fasilitator mengetik", () => {
        assert.equal(kodeOtomatisBerikutnya("R060", "R060"), null);
    });

    it("null saat kode tidak berurutan", () => {
        assert.equal(kodeOtomatisBerikutnya("kelas-a", null), null);
    });

    it("terus menambah tanpa batas akhir", () => {
        assert.equal(kodeOtomatisBerikutnya("R041", null), "R042");
    });
});
