/**
 * Panel yang digambar DI DALAM scene.
 *
 * Semuanya di sini karena DOM tidak dirender di dalam sesi `immersive-vr`, dan di mode
 * stereo HP elemen DOM hanya jatuh di salah satu separuh layar — hanya satu mata yang
 * melihatnya. Apa pun yang harus terbaca SELAMA sesi berjalan wajib berupa mesh.
 * Lihat catatan lengkapnya di CLAUDE.md.
 */
import * as THREE from "three";

/** Bungkus teks ke beberapa baris di canvas; sisa yang tidak muat dipotong dengan elipsis. */
export function wrapText(ctx, text, x, y, maxWidth, lineHeight, maxLines) {
    const words = text.split(/\s+/);
    let line = "";
    let lines = 0;
    for (const word of words) {
        const attempt = line ? line + " " + word : word;
        if (ctx.measureText(attempt).width > maxWidth && line) {
            lines++;
            if (lines === maxLines) {
                ctx.fillText(line + "…", x, y);
                return;
            }
            ctx.fillText(line, x, y);
            y += lineHeight;
            line = word;
        } else {
            line = attempt;
        }
    }
    if (line) ctx.fillText(line, x, y);
}

/**
 * Membagi teks panjang menjadi halaman-halaman baris teks agar tidak terpotong elipsis di VR.
 */
export function layoutTextPages(ctx, text, maxWidth, linesPerPage = 6) {
    if (!text) return [[]];
    const paragraphs = String(text).split(/\r?\n/);
    const allLines = [];

    for (const paragraph of paragraphs) {
        const words = paragraph.split(/\s+/).filter(Boolean);
        if (words.length === 0) {
            continue;
        }
        let currentLine = "";
        for (const word of words) {
            const attempt = currentLine ? currentLine + " " + word : word;
            if (ctx.measureText(attempt).width > maxWidth && currentLine) {
                allLines.push(currentLine);
                currentLine = word;
            } else {
                currentLine = attempt;
            }
        }
        if (currentLine) {
            allLines.push(currentLine);
        }
    }

    if (allLines.length === 0) return [[]];

    const pages = [];
    for (let i = 0; i < allLines.length; i += linesPerPage) {
        pages.push(allLines.slice(i, i + linesPerPage));
    }
    return pages;
}

// Floating in-scene info card (HTML overlays are not rendered inside a WebXR session).
export class InfoPanel {
    constructor(scene, camera) {
        this.camera = camera;
        this.canvas = document.createElement("canvas");
        this.canvas.width = 1024;
        this.canvas.height = 640;
        this.texture = new THREE.CanvasTexture(this.canvas);
        this.texture.colorSpace = THREE.SRGBColorSpace;

        this.mesh = new THREE.Mesh(
            new THREE.PlaneGeometry(1.2, 0.75),
            new THREE.MeshBasicMaterial({
                map: this.texture,
                transparent: true,
                toneMapped: false,
                depthTest: false,
            }),
        );
        // Naikkan renderOrder dari 1000 ke 2000 agar PhasePanel (1002) dan ExitButton (1002)
        // tidak tembus/bocor di atas InfoPanel ketika info card sedang dibuka.
        this.mesh.renderOrder = 2000;
        this.mesh.visible = false;
        // Diisi main(): menentukan apakah objek puzzle perlu menjelaskan kenapa ia
        // tidak bisa dipasang di perangkat ini.
        this.bisaGenggam = false;

        this.info = null;
        this.pages = [];
        this.currentPage = 0;
        this.totalPages = 1;

        scene.add(this.mesh);
    }

    show(info, targetPoint) {
        this.info = info;
        this.currentPage = 0;
        this.paginate();
        this.draw();

        const cameraWorld = this.camera.getWorldPosition(new THREE.Vector3());
        const direction = new THREE.Vector3().subVectors(
            targetPoint,
            cameraWorld,
        );
        const distance = Math.min(direction.length() * 0.7, 2);
        direction.normalize();

        this.mesh.position
            .copy(cameraWorld)
            .addScaledVector(direction, distance);
        this.mesh.lookAt(cameraWorld);
        this.mesh.visible = true;

        this.stopAudio();
        if (info.path_audio) {
            this.audio = new Audio("/storage/" + info.path_audio);
            this.audio.play().catch(() => {});
        }
    }

    hide() {
        this.mesh.visible = false;
        this.stopAudio();
    }

    stopAudio() {
        this.audio?.pause();
        this.audio = null;
    }

    paginate() {
        const ctx = this.canvas.getContext("2d");
        ctx.font = "34px Inter, sans-serif";
        const maxWidth = this.canvas.width - 90;
        const linesPerPage = 6;
        this.pages = layoutTextPages(
            ctx,
            this.info?.deskripsi || "",
            maxWidth,
            linesPerPage,
        );
        this.totalPages = Math.max(1, this.pages.length);
        if (this.currentPage >= this.totalPages) {
            this.currentPage = this.totalPages - 1;
        }
    }

    nextPage() {
        if (this.currentPage < this.totalPages - 1) {
            this.currentPage++;
            this.draw();
            return true;
        }
        return false;
    }

    prevPage() {
        if (this.currentPage > 0) {
            this.currentPage--;
            this.draw();
            return true;
        }
        return false;
    }

    handleTrigger(raycaster) {
        if (!this.mesh.visible) return false;

        const hits = raycaster
            ? raycaster.intersectObject(this.mesh, false)
            : [];
        if (hits.length === 0) {
            // Menekan trigger sambil mengarahkan pointer ke luar panel -> tutup panel
            this.hide();
            return false;
        }

        const hit = hits[0];
        if (!hit.uv) {
            return this.advanceOrClose();
        }

        const px = hit.uv.x * this.canvas.width;
        const py = (1 - hit.uv.y) * this.canvas.height;

        // 1. Tombol silang di header (pojok kanan atas)
        if (px >= 890 && py <= 110) {
            this.hide();
            return false;
        }

        // 2. Tombol navigasi di footer
        if (py >= 535) {
            // Tombol kiri "← Kembali"
            if (px <= 250 && this.currentPage > 0) {
                this.prevPage();
                return true;
            }
            // Tombol kanan "Lanjut →" atau "Tutup ✕"
            if (px >= 750) {
                if (this.currentPage < this.totalPages - 1) {
                    this.nextPage();
                    return true;
                } else {
                    this.hide();
                    return false;
                }
            }
        }

        // 3. Klik di area badan panel: maju ke halaman berikutnya atau tutup jika halaman terakhir
        return this.advanceOrClose();
    }

    advanceOrClose() {
        if (this.currentPage < this.totalPages - 1) {
            this.nextPage();
            return true;
        }
        this.hide();
        return false;
    }

    draw() {
        if (!this.info) return;
        const ctx = this.canvas.getContext("2d");
        const { width, height } = this.canvas;
        ctx.clearRect(0, 0, width, height);

        // Background kartu gelap
        ctx.fillStyle = "rgba(17, 24, 39, 0.95)";
        ctx.beginPath();
        ctx.roundRect(0, 0, width, height, 32);
        ctx.fill();

        // Border halus
        ctx.strokeStyle = "rgba(255, 255, 255, 0.12)";
        ctx.lineWidth = 2;
        ctx.stroke();

        // Banner header ungu
        ctx.fillStyle = "#7c3aed";
        ctx.beginPath();
        ctx.roundRect(0, 0, width, 110, [32, 32, 0, 0]);
        ctx.fill();

        // Judul header
        ctx.fillStyle = "#ffffff";
        ctx.textAlign = "left";
        const maxTitleWidth = this.totalPages > 1 ? 710 : 830;
        let titleFontSize = 48;
        ctx.font = `bold ${titleFontSize}px Inter, sans-serif`;
        while (
            ctx.measureText(this.info.nama).width > maxTitleWidth &&
            titleFontSize > 26
        ) {
            titleFontSize -= 2;
            ctx.font = `bold ${titleFontSize}px Inter, sans-serif`;
        }
        ctx.fillText(this.info.nama, 44, 72);

        // Indikator halaman di header (jika > 1 halaman)
        if (this.totalPages > 1) {
            const badgeW = 100;
            const badgeH = 42;
            const badgeX = width - 44 - 48 - 14 - badgeW;
            const badgeY = 34;

            ctx.fillStyle = "rgba(0, 0, 0, 0.32)";
            ctx.beginPath();
            ctx.roundRect(badgeX, badgeY, badgeW, badgeH, badgeH / 2);
            ctx.fill();

            ctx.font = "600 22px Inter, sans-serif";
            ctx.fillStyle = "#ffffff";
            ctx.textAlign = "center";
            ctx.fillText(
                `${this.currentPage + 1} / ${this.totalPages}`,
                badgeX + badgeW / 2,
                badgeY + 28,
            );
        }

        // Tombol Close (✕) di header
        const closeBtnX = width - 44 - 44;
        const closeBtnY = 33;
        ctx.fillStyle = "rgba(0, 0, 0, 0.28)";
        ctx.beginPath();
        ctx.roundRect(closeBtnX, closeBtnY, 44, 44, 22);
        ctx.fill();
        ctx.font = "bold 24px Inter, sans-serif";
        ctx.fillStyle = "#ffffff";
        ctx.textAlign = "center";
        ctx.fillText("✕", closeBtnX + 22, closeBtnY + 30);
        ctx.textAlign = "left";

        // Teks deskripsi halaman aktif
        ctx.font = "34px Inter, sans-serif";
        ctx.fillStyle = "#f3f4f6";
        const currentLines = this.pages[this.currentPage] || [];
        let textY = 175;
        for (const line of currentLines) {
            ctx.fillText(line, 44, textY);
            textY += 48;
        }

        // Chip nilai karakter dan catatan puzzle hanya pada halaman terakhir
        const isLastPage = this.currentPage === this.totalPages - 1;
        if (isLastPage) {
            const chipsY = Math.max(450, textY + 14);
            this.drawChips(
                ctx,
                this.info.nilai_karakter,
                44,
                chipsY,
                width - 88,
            );

            if (this.info.posisi_awal && !this.bisaGenggam) {
                ctx.font = "italic 24px Inter, sans-serif";
                ctx.fillStyle = "#fbbf24";
                ctx.fillText(
                    "Objek ini bisa dilepas dan dipasang kembali di headset VR.",
                    44,
                    528,
                );
            }
        }

        // Garis pemisah footer
        ctx.strokeStyle = "rgba(255, 255, 255, 0.08)";
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(40, 545);
        ctx.lineTo(width - 40, 545);
        ctx.stroke();

        // Tombol dan petunjuk footer
        if (this.totalPages > 1) {
            // Tombol "← Kembali" di kiri jika bukan halaman pertama
            if (this.currentPage > 0) {
                ctx.fillStyle = "rgba(255, 255, 255, 0.12)";
                ctx.beginPath();
                ctx.roundRect(44, 560, 165, 52, 26);
                ctx.fill();

                ctx.font = "600 22px Inter, sans-serif";
                ctx.fillStyle = "#e5e7eb";
                ctx.textAlign = "center";
                ctx.fillText("← Kembali", 44 + 82, 593);
                ctx.textAlign = "left";
            }

            // Teks petunjuk tengah
            ctx.font = "24px Inter, sans-serif";
            ctx.fillStyle = "#9ca3af";
            ctx.textAlign = "center";
            const hintText = isLastPage
                ? "Ketuk / tekan trigger untuk menutup"
                : "Ketuk / tekan trigger untuk lanjut →";
            ctx.fillText(hintText, width / 2, 593);
            ctx.textAlign = "left";

            // Tombol aksi kanan: "Lanjut →" atau "Tutup ✕"
            const rightBtnX = width - 44 - 165;
            ctx.fillStyle = isLastPage
                ? "rgba(255, 255, 255, 0.15)"
                : "#7c3aed";
            ctx.beginPath();
            ctx.roundRect(rightBtnX, 560, 165, 52, 26);
            ctx.fill();

            ctx.font = "600 22px Inter, sans-serif";
            ctx.fillStyle = "#ffffff";
            ctx.textAlign = "center";
            ctx.fillText(
                isLastPage ? "Tutup ✕" : "Lanjut →",
                rightBtnX + 82,
                593,
            );
            ctx.textAlign = "left";
        } else {
            // Halaman tunggal
            ctx.font = "28px Inter, sans-serif";
            ctx.fillStyle = "#9ca3af";
            ctx.fillText(
                "Ketuk / tekan trigger untuk menutup",
                44,
                height - 36,
            );

            const rightBtnX = width - 44 - 150;
            ctx.fillStyle = "rgba(255, 255, 255, 0.12)";
            ctx.beginPath();
            ctx.roundRect(rightBtnX, 560, 150, 52, 26);
            ctx.fill();

            ctx.font = "600 22px Inter, sans-serif";
            ctx.fillStyle = "#e5e7eb";
            ctx.textAlign = "center";
            ctx.fillText("Tutup ✕", rightBtnX + 75, 593);
            ctx.textAlign = "left";
        }

        this.texture.needsUpdate = true;
    }

    /** Nilai karakter sebagai pil ungu; membungkus ke baris kedua, sisanya dipotong. */
    drawChips(ctx, values, x, y, maxWidth) {
        if (!Array.isArray(values) || !values.length) return;

        ctx.font = "600 26px Inter, sans-serif";
        const paddingX = 20;
        const gap = 12;
        const chipHeight = 44;
        let cursorX = x;
        let rows = 1;

        for (const value of values) {
            const label = window.nilaiKarakterLabels?.[value] ?? value;
            const chipWidth = ctx.measureText(label).width + paddingX * 2;

            if (cursorX + chipWidth > x + maxWidth) {
                if (rows === 2) return;
                rows++;
                cursorX = x;
                y += chipHeight + gap;
            }

            ctx.fillStyle = "rgba(124, 58, 237, 0.35)";
            ctx.beginPath();
            ctx.roundRect(cursorX, y, chipWidth, chipHeight, chipHeight / 2);
            ctx.fill();

            ctx.fillStyle = "#ddd6fe";
            ctx.fillText(label, cursorX + paddingX, y + 31);

            cursorX += chipWidth + gap;
        }
    }
}

/**
 * Panel fase kecil yang menempel pada kamera, di kiri-bawah pandangan.
 *
 * Menempel kamera, bukan mengambang di dunia, karena ia harus selalu terbaca ke mana
 * pun kepala menoleh — dan karena DOM tidak dirender di dalam sesi WebXR, jadi overlay
 * HTML tidak akan terlihat sama sekali. Jarak 1,5 m disengaja: elemen yang terlalu
 * dekat memaksa mata menyilang dan cepat bikin pusing.
 *
 * Tetap tampil (redup) sepanjang sesi, bukan sekadar muncul sesaat: kriteria TKT 6
 * menuntut alur berjalan tanpa intervensi pengembang, dan siswa yang lupa instruksi
 * tanpa cara mengingatnya kembali akan memanggil fasilitator.
 */
export class PhasePanel {
    static SOROT_MS = 4000;
    static OPASITAS_REDUP = 0.35;

    constructor(camera) {
        this.canvas = document.createElement("canvas");
        this.canvas.width = 512;
        this.canvas.height = 160;
        this.texture = new THREE.CanvasTexture(this.canvas);
        this.texture.colorSpace = THREE.SRGBColorSpace;
        this.teksTerakhir = null;
        this.sorotSampai = 0;

        this.mesh = new THREE.Mesh(
            new THREE.PlaneGeometry(0.5, 0.156),
            new THREE.MeshBasicMaterial({
                map: this.texture,
                transparent: true,
                toneMapped: false,
                depthTest: false,
                opacity: PhasePanel.OPASITAS_REDUP,
            }),
        );
        this.mesh.position.set(-0.45, -0.3, -1.5);
        this.mesh.renderOrder = 1002;
        camera.add(this.mesh);
    }

    sorot() {
        this.sorotSampai = performance.now() + PhasePanel.SOROT_MS;
    }

    update(deskripsi) {
        const teks = deskripsi.judul + "\n" + deskripsi.instruksi;
        if (teks !== this.teksTerakhir) {
            this.teksTerakhir = teks;
            this.draw(deskripsi);
        }
        this.mesh.material.opacity =
            performance.now() < this.sorotSampai
                ? 1
                : PhasePanel.OPASITAS_REDUP;
    }

    draw({ judul, instruksi }) {
        const ctx = this.canvas.getContext("2d");
        const { width, height } = this.canvas;
        ctx.clearRect(0, 0, width, height);

        ctx.fillStyle = "rgba(17, 24, 39, 0.85)";
        ctx.beginPath();
        ctx.roundRect(0, 0, width, height, 20);
        ctx.fill();

        ctx.fillStyle = "#c4b5fd";
        ctx.font = "600 30px Inter, sans-serif";
        ctx.fillText(judul.toUpperCase(), 24, 48);

        ctx.fillStyle = "#e5e7eb";
        ctx.font = "24px Inter, sans-serif";
        wrapText(ctx, instruksi, 24, 90, width - 48, 32, 2);

        this.texture.needsUpdate = true;
    }
}

/**
 * Tombol keluar sesi, digambar di dalam scene.
 *
 * Di headset, satu-satunya jalan keluar sebelumnya adalah tombol sistem Meta: VRButton
 * dan tombol "✕ Selesai" keduanya DOM, dan DOM tidak dirender di dalam sesi
 * `immersive-vr`. Fasilitator jadi harus meraih controller siswa tiap pergantian
 * responden — puluhan kali dalam satu sesi kiosk.
 *
 * Ditempatkan di kanan-bawah, cermin dari PhasePanel di kiri-bawah: siswa harus
 * menoleh ke bawah-kanan dengan sengaja. Ditambah konfirmasi tekan-dua-kali, karena
 * satu siswa yang keluar tak sengaja di tengah sesi berarti satu responden hilang
 * datanya dan tidak bisa diulang setelah ia pulang.
 *
 * Hanya dipasang di jalur headset. Di HP tombol DOM "✕ Selesai" sudah benar — mode
 * stereo HP bukan sesi WebXR, dan tombolnya baru ditekan setelah ponsel dikeluarkan
 * dari viewer.
 */
export class ExitButton {
    static KONFIRMASI_MS = 4000;
    static OPASITAS_REDUP = 0.35;

    /** @param {() => void} onExit dijalankan setelah tekanan kedua */
    constructor(camera, onExit) {
        this.onExit = onExit;
        this.bersenjataSampai = 0;
        this.terakhirBersenjata = null;

        this.canvas = document.createElement("canvas");
        this.canvas.width = 512;
        this.canvas.height = 128;
        this.texture = new THREE.CanvasTexture(this.canvas);
        this.texture.colorSpace = THREE.SRGBColorSpace;

        this.mesh = new THREE.Mesh(
            new THREE.PlaneGeometry(0.34, 0.085),
            new THREE.MeshBasicMaterial({
                map: this.texture,
                transparent: true,
                toneMapped: false,
                depthTest: false,
                opacity: ExitButton.OPASITAS_REDUP,
            }),
        );
        this.mesh.position.set(0.5, -0.32, -1.5);
        this.mesh.renderOrder = 1002;
        camera.add(this.mesh);
        this.draw();
    }

    get bersenjata() {
        return performance.now() < this.bersenjataSampai;
    }

    /**
     * Apakah sinar sedang mengenai tombol.
     *
     * ponytail: diuji terpisah dari target museum dan selalu menang kalau kena — tombol
     * menempel kamera di 1,5 m dan di pojok, jadi tidak mungkin tak sengaja tertunjuk
     * bersamaan dengan objek yang diamati. Kalau suatu saat ia perlu bersaing, bandingkan
     * `distance` hasil kedua raycast.
     */
    raycast(raycaster) {
        return raycaster.intersectObject(this.mesh, false).length > 0;
    }

    /** Tekanan pertama menyiapkan, tekanan kedua dalam 4 detik benar-benar keluar. */
    tekan() {
        if (this.bersenjata) {
            this.bersenjataSampai = 0;
            this.onExit();
            return;
        }
        this.bersenjataSampai = performance.now() + ExitButton.KONFIRMASI_MS;
    }

    update(ditunjuk) {
        if (this.bersenjata !== this.terakhirBersenjata) {
            this.terakhirBersenjata = this.bersenjata;
            this.draw();
        }
        this.mesh.material.opacity =
            ditunjuk || this.bersenjata ? 1 : ExitButton.OPASITAS_REDUP;
    }

    draw() {
        const ctx = this.canvas.getContext("2d");
        const { width, height } = this.canvas;
        ctx.clearRect(0, 0, width, height);

        ctx.fillStyle = this.bersenjata
            ? "rgba(185, 28, 28, 0.92)"
            : "rgba(17, 24, 39, 0.85)";
        ctx.beginPath();
        ctx.roundRect(0, 0, width, height, height / 2);
        ctx.fill();

        ctx.fillStyle = "#ffffff";
        ctx.font = "600 34px Inter, sans-serif";
        ctx.textAlign = "center";
        ctx.fillText(
            this.bersenjata ? "Tekan lagi untuk keluar" : "✕  Keluar VR",
            width / 2,
            height / 2 + 12,
        );
        ctx.textAlign = "left";

        this.texture.needsUpdate = true;
    }
}

/**
 * Onboarding 20 detik: label yang MENEMPEL DI MODEL CONTROLLER.
 *
 * Mayoritas responden belum pernah memakai headset, dan anggarannya 5 menit per
 * orang tanpa penjelasan lisan. Label ditempel ke grip karena melihat tangan
 * sendiri adalah gerakan pertama semua orang saat masuk VR; panel teks di depan
 * wajah tidak dibaca siapa pun — PhasePanel sudah di sana.
 *
 * Memandu, tidak mengunci: tidak ada aturan lain yang boleh bergantung padanya.
 * Sprite, bukan plane: selalu menghadap kamera, jadi label tetap terbaca berapa
 * pun tangan diputar. Bukan anggota `targets`, jadi tidak pernah kena raycast
 * maupun mencemari event log.
 */
export class ControllerHints {
    static UMUR_MS = 20000;
    static PETUNJUK = {
        left: [{ aksi: "dorong", teks: "Dorong = jalan" }],
        right: [
            { aksi: "tekan", teks: "Tekan = pilih" },
            { aksi: "genggam", teks: "Genggam = angkat" },
        ],
    };

    /** @param {THREE.Object3D} grip @param {THREE.Object3D} controller sumber gamepad */
    constructor(grip, controller) {
        this.grip = grip;
        this.controller = controller;
        this.sisa = [];
        this.sprite = null;
        this.kedaluwarsa = 0;
    }

    /** Dipanggil dari handler "connected" — handedness baru diketahui di sana. */
    pasang(handedness) {
        this.sisa = (ControllerHints.PETUNJUK[handedness] ?? []).slice();
        if (!this.sisa.length || this.sprite) return;

        this.canvas = document.createElement("canvas");
        this.canvas.width = 256;
        this.canvas.height = 128;
        this.texture = new THREE.CanvasTexture(this.canvas);
        this.texture.colorSpace = THREE.SRGBColorSpace;

        this.sprite = new THREE.Sprite(
            new THREE.SpriteMaterial({
                map: this.texture,
                transparent: true,
                toneMapped: false,
                depthTest: false,
            }),
        );
        this.sprite.scale.set(0.16, 0.08, 1);
        this.sprite.position.set(0, 0.11, 0);
        this.sprite.renderOrder = 1003;
        this.grip.add(this.sprite);
        this.kedaluwarsa = performance.now() + ControllerHints.UMUR_MS;
        this.draw();
    }

    /** Aksi sudah dilakukan sekali — labelnya hilang. */
    tandai(aksi) {
        if (!this.sprite) return;
        const sisa = this.sisa.filter((p) => p.aksi !== aksi);
        if (sisa.length === this.sisa.length) return;
        this.sisa = sisa;
        if (this.sisa.length) this.draw();
        else this.buang();
    }

    update() {
        if (!this.sprite) return;
        const axes = this.controller.userData.gamepad?.axes;
        if (
            axes &&
            Math.hypot(axes[2] ?? axes[0] ?? 0, axes[3] ?? axes[1] ?? 0) > 0.5
        ) {
            this.tandai("dorong");
        }
        // Yang tidak pernah dipakai hilang sendiri; label permanen jadi sampah visual.
        if (this.sprite && performance.now() > this.kedaluwarsa) this.buang();
    }

    buang() {
        this.grip.remove(this.sprite);
        this.sprite.material.map.dispose();
        this.sprite.material.dispose();
        this.sprite = null;
    }

    draw() {
        const ctx = this.canvas.getContext("2d");
        const { width, height } = this.canvas;
        ctx.clearRect(0, 0, width, height);

        ctx.fillStyle = "rgba(17, 24, 39, 0.85)";
        ctx.beginPath();
        ctx.roundRect(0, 0, width, height, 18);
        ctx.fill();

        ctx.fillStyle = "#ffffff";
        ctx.font = "600 30px Inter, sans-serif";
        ctx.textAlign = "center";
        const y0 = height / 2 - (this.sisa.length - 1) * 20 + 10;
        this.sisa.forEach((p, i) =>
            ctx.fillText(p.teks, width / 2, y0 + i * 40),
        );
        ctx.textAlign = "left";

        this.texture.needsUpdate = true;
    }
}
