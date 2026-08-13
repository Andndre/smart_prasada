/**
 * Penutup sesi VR dan pergantian responden di mode kiosk.
 *
 * Semuanya DOM biasa, dan itu benar: elemen di sini baru dilihat SETELAH sesi berakhir
 * — headset dilepas, atau ponsel dikeluarkan dari viewer. Jangan pakai berkas ini
 * sebagai alasan menaruh UI lain di DOM; apa pun yang harus terbaca selama sesi
 * berjalan wajib digambar di dalam scene (lihat vr-panels.js).
 *
 * Dibangun di JS, bukan dirender blade, karena sesi_id lahir dari crypto.randomUUID()
 * di klien saat scene siap — blade tidak mungkin tahu nilainya.
 */
import { kodeOtomatisBerikutnya } from "vr-responden";

// Konteks sesi dari query string. Dibaca dari URL, bukan dari blade, supaya nilainya
// tetap sama setelah halaman dimuat ulang untuk responden berikutnya.
const parameterSesi = new URLSearchParams(location.search);
export const kioskAktif = parameterSesi.get("kiosk") === "1";
export const kodeAkhirResponden = parameterSesi.get("kode_akhir");

/** Panel penutup sesi: jalan keluar dari VR menuju modul refleksi. */
export function showPostSessionPanel(logger) {
    if (document.getElementById("panel-selesai")) return;

    const tujuan = new URL(refleksiUrl, location.origin);
    tujuan.searchParams.set("sesi", logger.sesiId);
    if (logger.kodeResponden) tujuan.searchParams.set("kode", logger.kodeResponden);

    const panel = document.createElement("div");
    panel.id = "panel-selesai";
    panel.style.cssText =
        "position:fixed;inset:0;z-index:10000002;display:flex;flex-direction:column;" +
        "align-items:center;justify-content:center;gap:16px;background:rgba(17,24,39,.95);" +
        "color:#fff;font:16px Inter,sans-serif;text-align:center;padding:24px";

    const gayaTombol =
        "background:#7c3aed;color:#fff;padding:14px 28px;border:none;" +
        "border-radius:9999px;font:600 16px Inter,sans-serif;text-decoration:none;cursor:pointer";
    const gayaTaut =
        "background:none;border:none;color:#c4b5fd;text-decoration:underline;font:inherit;cursor:pointer";

    let isi =
        '<p style="font-size:20px;font-weight:600;margin:0">Sesi VR selesai</p>' +
        (logger.kodeResponden
            ? `<p style="margin:0;opacity:.7;font:600 14px monospace">Responden: ${logger.kodeResponden}</p>`
            : "") +
        '<p style="margin:0;opacity:.8;max-width:32ch">Lanjutkan dengan menuliskan refleksimu.</p>' +
        `<a href="${tujuan}" style="${gayaTombol}">Lanjut ke Refleksi</a>`;

    if (kioskAktif) {
        // Gerbang integritas data, bukan gerbang belajar. Di mode kiosk panel ini TIDAK
        // bisa ditutup: kalau scene menerima responden berikutnya tanpa mengganti kode,
        // dua orang tergabung jadi satu kode — dan itu salah diam-diam, lebih buruk
        // daripada kode kosong yang setidaknya jujur. Mengandalkan fasilitator untuk
        // ingat, sambil ia sedang memasangkan headset ke siswa berikutnya, akan gagal.
        //
        // Ini tidak melanggar "fase tidak pernah mengunci" dari Fase 3: yang itu soal
        // alur belajar siswa di dalam sesi. Ini langkah fasilitator di antara sesi.
        isi += `<button type="button" id="btn-responden-berikutnya" style="${gayaTombol};background:#059669">Responden berikutnya</button>`;
    } else {
        isi += `<button type="button" id="btn-lanjut-jelajah" style="${gayaTaut}">Kembali menjelajah</button>`;
    }

    panel.innerHTML = isi;
    document.body.appendChild(panel);

    panel
        .querySelector("#btn-lanjut-jelajah")
        ?.addEventListener("click", () => panel.remove());
    panel
        .querySelector("#btn-responden-berikutnya")
        ?.addEventListener("click", () => mintaRespondenBerikutnya(panel, logger));
}

/** Muat ulang scene untuk responden berikutnya, mempertahankan seluruh konteks sesi. */
function mulaiRespondenBerikutnya(kode) {
    const url = new URL(location.href);
    url.searchParams.set("kode", kode);
    // kiosk dan kode_akhir harus ikut terbawa; kalau hilang, navigasi aplikasi muncul
    // kembali di tengah uji dan deret kode berhenti otomatis.
    if (kioskAktif) url.searchParams.set("kiosk", "1");
    if (kodeAkhirResponden) url.searchParams.set("kode_akhir", kodeAkhirResponden);
    url.searchParams.delete("arToken");
    location.href = url.toString();
}

function mintaRespondenBerikutnya(panel, logger) {
    const otomatis = kodeOtomatisBerikutnya(logger.kodeResponden, kodeAkhirResponden);
    if (otomatis) {
        mulaiRespondenBerikutnya(otomatis);
        return;
    }

    // Kode tidak berurutan atau rentangnya habis — fasilitator mengetik.
    const form = document.createElement("form");
    form.style.cssText = "display:flex;gap:8px;align-items:center";
    form.innerHTML =
        '<input type="text" id="input-kode" autocomplete="off" placeholder="Kode responden" ' +
        'style="padding:12px 16px;border-radius:9999px;border:none;font:16px monospace;width:180px">' +
        '<button type="submit" style="background:#059669;color:#fff;padding:12px 20px;border:none;' +
        'border-radius:9999px;font:600 15px Inter,sans-serif;cursor:pointer">Mulai</button>';
    panel.appendChild(form);

    const input = form.querySelector("#input-kode");
    input.focus();
    form.addEventListener("submit", (e) => {
        e.preventDefault();
        const kode = input.value.trim();
        if (kode) mulaiRespondenBerikutnya(kode);
    });
}
