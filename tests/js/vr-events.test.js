import assert from "node:assert/strict";
import { describe, it } from "node:test";

import { EventLogger } from "../../public/assets/js/vr-events.js";

/** Logger dengan pengirim palsu; start() sengaja tidak dipanggil supaya tidak ada timer hidup. */
function buatLogger() {
    const terkirim = [];
    const logger = new EventLogger({
        url: "/vr/events",
        token: "csrf123",
        museumId: 7,
        kodeResponden: "R041",
        send: (url, payload) => terkirim.push({ url, payload: JSON.parse(payload) }),
    });
    return { logger, terkirim };
}

describe("EventLogger", () => {
    it("menyangga event dan tidak mengirim sebelum diminta", () => {
        const { logger, terkirim } = buatLogger();
        logger.log("teleport", null, { x: 1 });

        assert.equal(terkirim.length, 0);
        assert.equal(logger.buffer.length, 1);
    });

    it("mencatat jenis, mesh_name, detail, dan offset waktu", () => {
        const { logger } = buatLogger();
        logger.log("panel_dibuka", "punden_01", { a: 1 });

        const event = logger.buffer[0];
        assert.equal(event.jenis, "panel_dibuka");
        assert.equal(event.mesh_name, "punden_01");
        assert.deepEqual(event.detail, { a: 1 });
        assert.ok(Number.isInteger(event.offset_ms) && event.offset_ms >= 0);
    });

    it("mesh_name dan detail bernilai null kalau tidak diberikan", () => {
        const { logger } = buatLogger();
        logger.log("sesi_selesai");

        assert.equal(logger.buffer[0].mesh_name, null);
        assert.equal(logger.buffer[0].detail, null);
    });

    it("mengirim otomatis begitu penyangga penuh", () => {
        const { logger, terkirim } = buatLogger();
        for (let i = 0; i < EventLogger.FLUSH_SIZE; i++) logger.log("objek_dilihat", "m" + i);

        assert.equal(terkirim.length, 1);
        assert.equal(terkirim[0].payload.events.length, EventLogger.FLUSH_SIZE);
        assert.equal(logger.buffer.length, 0);
    });

    it("menyertakan identitas sesi di setiap kiriman", () => {
        const { logger, terkirim } = buatLogger();
        logger.log("sesi_mulai");
        logger.flush();

        const { url, payload } = terkirim[0];
        assert.equal(url, "/vr/events");
        // _token ikut di body karena sendBeacon tidak bisa menyetel header.
        assert.equal(payload._token, "csrf123");
        assert.equal(payload.museum_id, 7);
        // kode_responden adalah satu-satunya pembeda responden di mode kiosk.
        assert.equal(payload.kode_responden, "R041");
        assert.equal(payload.sesi_id, logger.sesiId);
    });

    it("tidak mengirim apa pun saat penyangga kosong", () => {
        const { logger, terkirim } = buatLogger();
        logger.flush();

        assert.equal(terkirim.length, 0);
    });

    it("kode responden kosong tersimpan sebagai null, bukan string kosong", () => {
        const logger = new EventLogger({ url: "/x", token: "t", museumId: 1, kodeResponden: "", send: () => {} });

        assert.equal(logger.kodeResponden, null);
    });
});
