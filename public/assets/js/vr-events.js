/**
 * Perekaman event runtime sesi VR (`vr_event`).
 *
 * Timing memakai performance.now(), bukan jam dinding dan bukan jam server: event
 * dikirim batch, jadi timestamp server akan menstempel satu batch dengan waktu yang
 * sama dan menghancurkan time-on-task. performance.now() juga monoton, jadi koreksi
 * NTP di tengah sesi tidak bisa menghasilkan durasi negatif.
 *
 * Berkas ini sengaja tidak mengimpor Three.js supaya bisa diuji `npm run test:js`.
 */

/** Pengirim bawaan: sendBeacon bertahan saat halaman dibongkar, fetch() dibatalkan. */
function kirimBeacon(url, payload) {
    // sendBeacon tidak bisa menyetel header — karena itu _token ikut di body, yang
    // diperiksa Laravel persis seperti header. Tidak ada rute yang dikecualikan CSRF.
    navigator.sendBeacon(url, new Blob([payload], { type: "application/json" }));
}

export class EventLogger {
    static FLUSH_INTERVAL_MS = 15000;
    static FLUSH_SIZE = 20;

    /**
     * @param {object} opsi
     * @param {(url: string, payload: string) => void} [opsi.send] disuntik hanya oleh tes
     */
    constructor({ url, token, museumId, kodeResponden, send }) {
        this.url = url;
        this.token = token;
        this.museumId = museumId;
        this.kodeResponden = kodeResponden || null;
        this.send = send ?? kirimBeacon;
        this.sesiId = crypto.randomUUID();
        this.startedAt = performance.now();
        this.buffer = [];
    }

    /**
     * Pasang timer dan pengait pagehide. Terpisah dari constructor supaya tes bisa
     * membuat logger tanpa meninggalkan interval hidup yang menahan proses node.
     */
    start() {
        setInterval(() => this.flush(), EventLogger.FLUSH_INTERVAL_MS);

        // pagehide, bukan unload: fetch() biasa dibatalkan saat halaman ditutup, justru
        // saat event penutup sesi paling dibutuhkan.
        window.addEventListener("pagehide", () => {
            this.log("sesi_selesai");
            this.flush();
        });
        return this;
    }

    log(jenis, meshName = null, detail = null) {
        this.buffer.push({
            jenis,
            mesh_name: meshName,
            detail,
            offset_ms: Math.round(performance.now() - this.startedAt),
        });
        if (this.buffer.length >= EventLogger.FLUSH_SIZE) this.flush();
    }

    flush() {
        if (!this.buffer.length) return;
        const events = this.buffer.splice(0, EventLogger.FLUSH_SIZE * 10);

        this.send(
            this.url,
            JSON.stringify({
                _token: this.token,
                sesi_id: this.sesiId,
                museum_id: this.museumId,
                kode_responden: this.kodeResponden,
                events,
            }),
        );
    }
}
