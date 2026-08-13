/**
 * Jalur HP: stereo terbagi dua + kontrol giroskop.
 *
 * Ini BUKAN sesi WebXR — hanya halaman dengan kanvas terbagi dua, jadi DOM tetap
 * dirender. Tapi di layout berdampingan setiap elemen DOM hanya jatuh di salah satu
 * separuh layar, jadi hanya satu mata yang melihatnya. DOM di sini terbatas pada hal
 * yang dilihat saat ponsel sudah dikeluarkan dari viewer.
 */
import * as THREE from "three";
import { StereoEffect } from "three/jsm/effects/StereoEffect.js";
import { showPostSessionPanel } from "vr-sesi";

// ponytail: three.js removed DeviceOrientationControls from examples/jsm around r125.
// Inlined minimal port (standard W3C deviceorientation -> camera quaternion algorithm).
export class DeviceOrientationControls {
    constructor(camera) {
        this.camera = camera;
        this.camera.rotation.reorder("YXZ");
        this.deviceOrientation = {};
        this.screenOrientation = window.orientation || 0;

        this.onDeviceOrientation = (event) => {
            this.deviceOrientation = event;
        };
        this.onScreenOrientation = () => {
            this.screenOrientation = window.orientation || 0;
        };

        window.addEventListener("deviceorientation", this.onDeviceOrientation);
        window.addEventListener("orientationchange", this.onScreenOrientation);
    }

    update() {
        const { alpha, beta, gamma } = this.deviceOrientation;
        if (alpha === undefined || alpha === null) return;

        const degToRad = Math.PI / 180;
        const euler = new THREE.Euler();
        const q0 = new THREE.Quaternion();
        const q1 = new THREE.Quaternion(-Math.sqrt(0.5), 0, 0, Math.sqrt(0.5));

        euler.set(
            (beta || 0) * degToRad,
            (alpha || 0) * degToRad,
            -(gamma || 0) * degToRad,
            "YXZ",
        );
        this.camera.quaternion.setFromEuler(euler);
        this.camera.quaternion.multiply(q1);
        this.camera.quaternion.multiply(
            q0.setFromAxisAngle(new THREE.Vector3(0, 0, 1), -this.screenOrientation * degToRad),
        );
    }
}

async function requestOrientationPermission() {
    if (
        typeof DeviceOrientationEvent !== "undefined" &&
        typeof DeviceOrientationEvent.requestPermission === "function"
    ) {
        try {
            const response = await DeviceOrientationEvent.requestPermission();
            return response === "granted";
        } catch (err) {
            console.warn("Orientation permission request failed:", err);
            return false;
        }
    }
    return true;
}

function createEnterButton(label) {
    const container = document.getElementById("vr-button-container");
    const button = document.createElement("button");
    button.textContent = label;
    container.appendChild(button);
    return button;
}

function showTeleportHint() {
    const hint = document.createElement("p");
    hint.textContent = "Ketuk layar untuk berpindah ke lingkaran ungu";
    hint.style.cssText =
        "position:fixed;bottom:24px;left:50%;transform:translateX(-50%);" +
        "color:#fff;background:rgba(0,0,0,.6);padding:8px 16px;border-radius:9999px;" +
        "z-index:10000001;font:500 13px Inter,sans-serif;pointer-events:none;white-space:nowrap";
    document.body.appendChild(hint);
    setTimeout(() => hint.remove(), 6000);
}

export async function startPhoneStereoSession(renderer, scene, camera, teleport) {
    const button = createEnterButton("Masuk VR");

    button.addEventListener("click", async () => {
        const granted = await requestOrientationPermission();
        if (!granted) {
            alert("Izin sensor orientasi diperlukan untuk mode VR di HP ini.");
            return;
        }

        button.remove();

        const controls = new DeviceOrientationControls(camera);
        const effect = new StereoEffect(renderer);
        effect.setSize(window.innerWidth, window.innerHeight);

        document.body.requestFullscreen?.().catch(() => {});

        renderer.domElement.addEventListener("pointerup", () => teleport.trigger());
        showTeleportHint();

        // Jalur keluar. Sebelumnya tidak ada sama sekali: tombol masuk dihapus, fullscreen
        // tidak pernah dilepas, dan render loop stereo berjalan selamanya — siswa harus
        // keluar fullscreen sendiri sambil kanvas terbelah tetap merender di belakang.
        //
        // Tombol DOM boleh di sini karena ia dipakai justru saat ponsel SUDAH dikeluarkan
        // dari viewer — siswa tidak bisa menyentuh layar selama ponsel di dalam viewer,
        // jadi ia melihat layar utuh secara normal dan stereo tidak relevan. Jangan pakai
        // posisi ini sebagai alasan menaruh UI lain di DOM: di layout berdampingan, elemen
        // DOM mana pun hanya masuk ke satu mata. Lihat catatan di CLAUDE.md.
        const keluar = document.createElement("button");
        keluar.textContent = "✕ Selesai";
        keluar.style.cssText =
            "position:fixed;top:12px;left:12px;z-index:10000002;background:rgba(0,0,0,.6);" +
            "color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:9999px;" +
            "padding:8px 16px;font:600 13px Inter,sans-serif;cursor:pointer";
        document.body.appendChild(keluar);

        let frameId = null;
        function render() {
            controls.update();
            teleport.update();
            effect.render(scene, camera);
            frameId = requestAnimationFrame(render);
        }
        render();

        const ubahUkuran = () => effect.setSize(window.innerWidth, window.innerHeight);
        window.addEventListener("resize", ubahUkuran);

        keluar.addEventListener("click", () => {
            cancelAnimationFrame(frameId);
            window.removeEventListener("resize", ubahUkuran);
            document.exitFullscreen?.().catch(() => {});
            keluar.remove();
            // Kembalikan render mono supaya kanvas tidak tertinggal terbelah dua.
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.render(scene, camera);
            teleport.logger?.log("sesi_selesai");
            teleport.logger?.flush();
            showPostSessionPanel(teleport.logger);
        });
    });
}
