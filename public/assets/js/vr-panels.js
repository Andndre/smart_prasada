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
        this.mesh.renderOrder = 1000;
        this.mesh.visible = false;
        // Diisi main(): menentukan apakah objek puzzle perlu menjelaskan kenapa ia
        // tidak bisa dipasang di perangkat ini.
        this.bisaGenggam = false;
        scene.add(this.mesh);
    }

    show(info, targetPoint) {
        this.draw(info);

        const cameraWorld = this.camera.getWorldPosition(new THREE.Vector3());
        const direction = new THREE.Vector3().subVectors(targetPoint, cameraWorld);
        const distance = Math.min(direction.length() * 0.7, 2);
        direction.normalize();

        this.mesh.position.copy(cameraWorld).addScaledVector(direction, distance);
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

    draw(info) {
        const ctx = this.canvas.getContext("2d");
        const { width, height } = this.canvas;
        ctx.clearRect(0, 0, width, height);

        ctx.fillStyle = "rgba(17, 24, 39, 0.92)";
        ctx.beginPath();
        ctx.roundRect(0, 0, width, height, 32);
        ctx.fill();

        ctx.fillStyle = "#7c3aed";
        ctx.beginPath();
        ctx.roundRect(0, 0, width, 110, [32, 32, 0, 0]);
        ctx.fill();

        ctx.fillStyle = "#ffffff";
        ctx.font = "bold 52px Inter, sans-serif";
        ctx.fillText(info.nama, 40, 74);

        ctx.font = "36px Inter, sans-serif";
        ctx.fillStyle = "#e5e7eb";
        // 6 baris, bukan 8 — dua baris terakhir disisihkan untuk chip nilai karakter.
        wrapText(ctx, info.deskripsi || "", 40, 180, width - 80, 50, 6);

        this.drawChips(ctx, info.nilai_karakter, 40, 455, width - 80);

        // Objek puzzle di perangkat tanpa controller: sebutkan alasannya, jangan biarkan
        // buntu diam-diam. Kedipannya sengaja tidak dimatikan — objeknya memang tetap
        // interaktif di HP (nama, deskripsi, audio), yang tidak tersedia hanya pemasangan.
        if (info.posisi_awal && !this.bisaGenggam) {
            ctx.font = "italic 26px Inter, sans-serif";
            ctx.fillStyle = "#fbbf24";
            ctx.fillText("Objek ini bisa dilepas dan dipasang kembali di headset VR.", 40, 578);
        }

        ctx.font = "28px Inter, sans-serif";
        ctx.fillStyle = "#9ca3af";
        ctx.fillText("Ketuk / tekan trigger untuk menutup", 40, height - 36);

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
            performance.now() < this.sorotSampai ? 1 : PhasePanel.OPASITAS_REDUP;
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
        this.mesh.material.opacity = ditunjuk || this.bersenjata ? 1 : ExitButton.OPASITAS_REDUP;
    }

    draw() {
        const ctx = this.canvas.getContext("2d");
        const { width, height } = this.canvas;
        ctx.clearRect(0, 0, width, height);

        ctx.fillStyle = this.bersenjata ? "rgba(185, 28, 28, 0.92)" : "rgba(17, 24, 39, 0.85)";
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
        if (axes && Math.hypot(axes[2] ?? axes[0] ?? 0, axes[3] ?? axes[1] ?? 0) > 0.5) {
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
        this.sisa.forEach((p, i) => ctx.fillText(p.teks, width / 2, y0 + i * 40));
        ctx.textAlign = "left";

        this.texture.needsUpdate = true;
    }
}
