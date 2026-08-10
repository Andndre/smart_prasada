import * as THREE from "three";
import { GLTFLoader } from "three/jsm/loaders/GLTFLoader.js";
import { DRACOLoader } from "three/jsm/loaders/DRACOLoader.js";
import { VRButton } from "three/jsm/webxr/VRButton.js";
import { StereoEffect } from "three/jsm/effects/StereoEffect.js";
import { XRControllerModelFactory } from "three/jsm/webxr/XRControllerModelFactory.js";
// Bare specifier, bukan "./vr-phases.js": dipetakan lewat importmap di museum.blade.php
// supaya berkas ini ikut cache-busting. Impor relatif akan tersaji basi di headset.
import { PhaseManager } from "vr-phases";

// ponytail: three.js removed DeviceOrientationControls from examples/jsm around r125.
// Inlined minimal port (standard W3C deviceorientation -> camera quaternion algorithm).
class DeviceOrientationControls {
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

function hideElement(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.visibility = "hidden";
    el.style.pointerEvents = "none";
}

function showElement(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.visibility = "visible";
    el.style.pointerEvents = "auto";
}

/**
 * Buffers runtime events and ships them in batches.
 *
 * Timing comes from performance.now(), not the wall clock and not the server: events
 * are sent in batches, so a server timestamp would stamp a whole batch with the same
 * instant and destroy time-on-task. performance.now() is also monotonic, so an NTP
 * correction mid-session can't produce a negative duration.
 */
class EventLogger {
    static FLUSH_INTERVAL_MS = 15000;
    static FLUSH_SIZE = 20;

    constructor({ url, token, museumId, kodeResponden }) {
        this.url = url;
        this.token = token;
        this.museumId = museumId;
        this.kodeResponden = kodeResponden || null;
        this.sesiId = crypto.randomUUID();
        this.startedAt = performance.now();
        this.buffer = [];

        setInterval(() => this.flush(), EventLogger.FLUSH_INTERVAL_MS);

        // pagehide, not unload: a plain fetch() gets cancelled when the page goes away,
        // which would drop the session-closing events exactly when they matter most.
        window.addEventListener("pagehide", () => {
            this.log("sesi_selesai");
            this.flush();
        });
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
        const payload = JSON.stringify({
            _token: this.token,
            sesi_id: this.sesiId,
            museum_id: this.museumId,
            kode_responden: this.kodeResponden,
            events,
        });

        // sendBeacon survives page teardown, but cannot set headers — hence _token in
        // the body, which Laravel checks just like the header. No CSRF exemption needed.
        navigator.sendBeacon(
            this.url,
            new Blob([payload], { type: "application/json" }),
        );
    }
}

/** Short vibration on the controller that triggered the action; no-op without haptics. */
function pulse(controller, intensity, milliseconds) {
    controller?.userData.gamepad?.hapticActuators?.[0]?.pulse(intensity, milliseconds);
}

class ModelLoader {
    static loader = new GLTFLoader();
    static dracoLoader = new DRACOLoader();

    static async loadModel(url, onProgress) {
        this.dracoLoader.setDecoderConfig({ type: "js" });
        this.dracoLoader.setDecoderPath(
            "https://www.gstatic.com/draco/v1/decoders/",
        );
        this.loader.setDRACOLoader(this.dracoLoader);
        const gltf = await this.loader.loadAsync(url, onProgress);

        gltf.scene.traverse((object) => {
            if (object.isMesh) {
                object.castShadow = true;
                object.receiveShadow = true;
            }
        });

        return gltf.scene;
    }
}

// ponytail: reuse existing AR skybox texture as placeholder environment; swap for a prehistoric HDRI in Fase 2.
function createSkybox(scene) {
    const texture = new THREE.TextureLoader().load("/images/hdri/langit.jpg");
    texture.mapping = THREE.EquirectangularReflectionMapping;
    texture.colorSpace = THREE.SRGBColorSpace;

    const sky = new THREE.Mesh(
        new THREE.SphereGeometry(500, 64, 48),
        new THREE.MeshBasicMaterial({ map: texture, side: THREE.BackSide, toneMapped: false }),
    );
    scene.add(sky);
    scene.environment = texture;
}

function createGround(scene) {
    const ground = new THREE.Mesh(
        new THREE.CircleGeometry(20, 48),
        new THREE.MeshStandardMaterial({ color: 0x3a3226, roughness: 1 }),
    );
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    scene.add(ground);
    return ground;
}

function createScene() {
    const scene = new THREE.Scene();

    const hemisphereLight = new THREE.HemisphereLight(0xffffff, 0xbbbbff, 0.6);
    scene.add(hemisphereLight);

    const sunLight = new THREE.DirectionalLight(0xffffff, 1);
    sunLight.position.set(5, 10, 7.5);
    sunLight.castShadow = true;
    scene.add(sunLight);

    createSkybox(scene);
    const ground = createGround(scene);

    return { scene, ground };
}

// Point-and-teleport: reticle on the ground, from XR controller ray or gaze center.
// ponytail: raycasts ground only — reticle can show through the model; add occluder check in Fase 2 if it bothers users.
class TeleportControls {
    static SCREEN_CENTER = new THREE.Vector2(0, 0);
    static UP = new THREE.Vector3(0, 1, 0);
    static tempMatrix = new THREE.Matrix4();
    static tempVector = new THREE.Vector3();
    /** Gaze must rest this long before it counts as looking at something, not sweeping past. */
    static GAZE_DWELL_MS = 500;
    static outlineMaterial = new THREE.MeshBasicMaterial({
        color: 0xfbbf24,
        side: THREE.BackSide,
        toneMapped: false,
    });

    constructor(scene, camera, rig, targets) {
        this.camera = camera;
        this.rig = rig;
        this.targets = targets;
        this.controller = null;
        this.panel = null;
        this.hoverNode = null;
        this.hoverInfo = null;
        this.hoverPoint = null;
        this.raycaster = new THREE.Raycaster();
        this.slots = new Map();
        this.solvedCount = 0;
        this.interactiveMeshes = [];
        this.outlinedNode = null;
        this.logger = null;
        // Fase melacak dan memandu, tidak pernah mengunci — lihat vr-phases.js.
        // Jangan menambahkan pemeriksaan fase yang memblokir teleport/panel/genggam.
        this.phases = null;
        this.phasePanel = null;
        this.gazeNode = null;
        this.gazeSince = 0;
        this.gazeLogged = false;
        this.panelDibukaUntuk = null;

        this.reticle = new THREE.Mesh(
            new THREE.RingGeometry(0.15, 0.25, 32).rotateX(-Math.PI / 2),
            new THREE.MeshBasicMaterial({
                color: 0x7c3aed,
                toneMapped: false,
                depthTest: false,
                transparent: true,
                opacity: 0.9,
            }),
        );
        this.reticle.renderOrder = 999;
        this.reticle.visible = false;
        scene.add(this.reticle);

        // Aim cursor for gaze mode (phone/no controller): a ring fixed to the camera so it
        // renders correctly in both stereo eyes, unlike an HTML overlay.
        this.cursor = new THREE.Mesh(
            new THREE.RingGeometry(0.006, 0.011, 24),
            new THREE.MeshBasicMaterial({
                color: 0xffffff,
                toneMapped: false,
                depthTest: false,
                transparent: true,
                opacity: 0.9,
            }),
        );
        this.cursor.position.z = -1;
        this.cursor.renderOrder = 1001;
        camera.add(this.cursor);
    }

    update() {
        this.pulseInteractive();
        this.cursor.visible = !this.controller?.userData.connected;

        if (this.controller?.userData.connected) {
            TeleportControls.tempMatrix.identity().extractRotation(this.controller.matrixWorld);
            this.raycaster.ray.origin.setFromMatrixPosition(this.controller.matrixWorld);
            this.raycaster.ray.direction.set(0, 0, -1).applyMatrix4(TeleportControls.tempMatrix);
        } else {
            this.raycaster.setFromCamera(TeleportControls.SCREEN_CENTER, this.camera);
        }

        const hits = this.raycaster
            .intersectObjects(this.targets, true)
            .filter((h) => h.object.visible);

        const first = hits[0];
        this.hoverNode = first ? TeleportControls.findVrNode(first.object) : null;
        this.hoverInfo = this.hoverNode?.userData.vrObject ?? null;
        this.hoverPoint = this.hoverInfo ? first.point : null;

        this.setOutline(this.hoverInfo ? this.hoverNode : null);
        this.cursor.material.color.setHex(this.hoverInfo ? 0xfbbf24 : 0xffffff);
        this.trackGaze();
        if (this.phases) this.phasePanel?.update(this.phases.deskripsi());

        if (this.hoverInfo) {
            this.reticle.visible = false;
            return;
        }

        const hit = hits.find((h) => {
            if (!h.face) return false;
            const normal = TeleportControls.tempVector
                .copy(h.face.normal)
                .transformDirection(h.object.matrixWorld);
            return normal.y > 0.6;
        });

        this.reticle.visible = Boolean(hit);
        this.reticle.material.color.setHex(0x7c3aed);
        this.reticle.quaternion.identity();
        if (hit) {
            this.reticle.position.copy(hit.point);
            this.reticle.position.y += 0.01;
            this.reticle.scale.setScalar(Math.max(1, hit.distance / 6));
        }
    }

    /** Log an object as "looked at" once the gaze has rested on it, not every frame. */
    trackGaze() {
        if (this.hoverNode !== this.gazeNode) {
            this.gazeNode = this.hoverNode;
            this.gazeSince = performance.now();
            this.gazeLogged = false;
        }
        if (
            !this.gazeLogged &&
            this.hoverInfo &&
            performance.now() - this.gazeSince >= TeleportControls.GAZE_DWELL_MS
        ) {
            this.gazeLogged = true;
            this.logger?.log("objek_dilihat", this.hoverNode.name);
        }
    }

    /** Faint permanent glow on interactive objects so they're spottable without pointing at them. */
    pulseInteractive() {
        if (!this.interactiveMeshes.length) return;
        const pulse = 0.12 + 0.1 * Math.sin(performance.now() / 500);
        for (const { node, materials } of this.interactiveMeshes) {
            const intensity = node.userData.solved ? 0 : pulse;
            for (const material of materials) material.emissiveIntensity = intensity;
        }
    }

    /** Show/hide the back-side outline shell of the node being looked at. */
    setOutline(node) {
        if (node === this.outlinedNode) return;
        for (const mesh of this.outlinedNode?.userData.outlineMeshes ?? []) mesh.visible = false;
        for (const mesh of node?.userData.outlineMeshes ?? []) mesh.visible = true;
        this.outlinedNode = node;
    }

    /** Clone materials so the glow doesn't leak onto other instances sharing the same material. */
    markInteractive(node) {
        const materials = [];
        const outlineMeshes = [];
        const meshes = [];
        node.traverse((child) => {
            if (child.isMesh) meshes.push(child);
        });
        for (const child of meshes) {
            const wasArray = Array.isArray(child.material);
            const cloned = (wasArray ? child.material : [child.material]).map((m) => {
                if (!("emissive" in m)) return m;
                const clone = m.clone();
                clone.emissive = new THREE.Color(0xfbbf24);
                materials.push(clone);
                return clone;
            });
            child.material = wasArray ? cloned : cloned[0];

            // ponytail: outline = back-side shell, 4% bigger; misses concave detail — swap for OutlinePass only if it looks bad on real assets.
            const outline = new THREE.Mesh(child.geometry, TeleportControls.outlineMaterial);
            outline.scale.setScalar(1.04);
            // Scale about the geometry's own center, not its origin — otherwise the shell drifts
            // when the geometry is authored far from its local origin.
            child.geometry.computeBoundingBox();
            outline.position.copy(child.geometry.boundingBox.getCenter(new THREE.Vector3())).multiplyScalar(-0.04);
            outline.visible = false;
            outline.raycast = () => {};
            child.add(outline);
            outlineMeshes.push(outline);
        }
        node.userData.outlineMeshes = outlineMeshes;
        if (materials.length) this.interactiveMeshes.push({ node, materials });
    }

    static findVrNode(object) {
        let node = object;
        while (node) {
            if (node.userData.vrObject) return node;
            node = node.parent;
        }
        return null;
    }

    /** Squeeze/grip: pick up the hovered object; release puts it back in the scene graph. */
    grabStart(controller) {
        if (!this.hoverNode || this.hoverNode.userData.solved || controller.userData.grabbedNode) return;
        controller.userData.grabbedNode = this.hoverNode;
        controller.userData.grabbedParent = this.hoverNode.parent;
        controller.attach(this.hoverNode);
        pulse(controller, 0.4, 40);
        this.logger?.log("objek_digenggam", this.hoverNode.name);
    }

    grabEnd(controller) {
        const node = controller.userData.grabbedNode;
        if (!node) return;
        controller.userData.grabbedParent.attach(node);
        controller.userData.grabbedNode = null;
        controller.userData.grabbedParent = null;
        // A release that misses the slot is one failed attempt — that's the "jumlah
        // percobaan" metric, so the outcome has to ride along with the event.
        const berhasil = this.checkSlot(node, controller);
        this.logger?.log("objek_dilepas", node.name, { berhasil });
    }

    /** Puzzle: released piece within reach of its slot snaps in place and counts as solved. */
    checkSlot(node, controller) {
        const slot = this.slots.get(node.userData.vrObject?.slot_mesh_name);
        if (!slot || node.userData.solved) return false;

        const nodePos = node.getWorldPosition(new THREE.Vector3());
        const slotPos = slot.getWorldPosition(new THREE.Vector3());
        // ponytail: 0.5m snap radius, single knob; make per-object if pieces vary wildly in size.
        if (nodePos.distanceTo(slotPos) > 0.5) return false;

        slot.parent.attach(node);
        node.position.copy(slot.position);
        node.quaternion.copy(slot.quaternion);
        node.userData.solved = true;
        this.solvedCount++;
        pulse(controller, 1, 120);
        this.logger?.log("puzzle_benar", node.name, { urutan: this.solvedCount });
        this.phases?.catatPemasangan(this.solvedCount);

        const done = this.solvedCount >= this.slots.size;
        this.panel?.show({
            nama: done ? "Puzzle Selesai! 🎉" : "Tepat!",
            deskripsi: done
                ? "Semua objek sudah kembali ke tempat yang benar. Kerja bagus!"
                : `${node.userData.vrObject.nama} sudah di tempat yang benar. (${this.solvedCount}/${this.slots.size})`,
        }, slotPos);

        return true;
    }

    /** Single entry point for trigger/tap: close panel > open panel > teleport. */
    trigger() {
        if (this.panel?.mesh.visible) {
            this.panel.hide();
            // Only pair a close with an open the student actually made — the puzzle
            // success panel opens by itself and would otherwise skew reading time.
            if (this.panelDibukaUntuk) {
                this.logger?.log("panel_ditutup", this.panelDibukaUntuk);
                this.panelDibukaUntuk = null;
            }
            return;
        }
        if (this.hoverInfo && this.hoverPoint) {
            this.panel?.show(this.hoverInfo, this.hoverPoint);
            this.panelDibukaUntuk = this.hoverNode.name;
            this.logger?.log("panel_dibuka", this.panelDibukaUntuk);
            this.phases?.catatPanelDibuka(this.panelDibukaUntuk);
            return;
        }
        this.teleport();
    }

    teleport() {
        if (!this.reticle.visible || this.hoverInfo) return;
        const cameraWorld = this.camera.getWorldPosition(TeleportControls.tempVector);
        this.rig.position.x += this.reticle.position.x - cameraWorld.x;
        this.rig.position.z += this.reticle.position.z - cameraWorld.z;
        this.rig.position.y = this.reticle.position.y - 0.01;
        // Destination is logged too: hal. 19 wants evidence on navigation stability,
        // and where students actually go is that evidence.
        this.logger?.log("teleport", null, {
            x: Math.round(this.reticle.position.x * 100) / 100,
            z: Math.round(this.reticle.position.z * 100) / 100,
        });
        this.phases?.catatTeleport();
    }
}

// Floating in-scene info card (HTML overlays are not rendered inside a WebXR session).
class InfoPanel {
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

        this.drawChips(ctx, info.nilai_karakter, 40, 470, width - 80);

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

/** Bungkus teks ke beberapa baris di canvas; sisa yang tidak muat dipotong dengan elipsis. */
function wrapText(ctx, text, x, y, maxWidth, lineHeight, maxLines) {
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
class PhasePanel {
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

// Drop the model onto the ground; X/Z position is authored in Blender and respected as-is
// so the artist controls how far the spawn point is from the model.
function placeModel(model, camera, ground) {
    const box = new THREE.Box3().setFromObject(model);
    model.position.y -= box.min.y;

    camera.position.set(0, 1.6, 0);

    const farthestCorner = Math.max(
        Math.abs(box.min.x), Math.abs(box.max.x),
        Math.abs(box.min.z), Math.abs(box.max.z),
    );
    ground.scale.setScalar(Math.max(1, (farthestCorner + 5) / 20));
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

function generateHandoffQrCode() {
    const container = document.getElementById("qr-code");
    if (!container) return;
    const url = window.location.href.includes("arToken")
        ? window.location.href
        : window.location.href + (window.location.search ? "&" : "?") + "arToken=" + arToken;

    new QRCode(container, {
        text: url,
        width: 160,
        height: 160,
        colorDark: "#000000",
        colorLight: "#ffffff",
    });
}

function createEnterButton(label) {
    const container = document.getElementById("vr-button-container");
    const button = document.createElement("button");
    button.textContent = label;
    container.appendChild(button);
    return button;
}

async function startHeadsetSession(renderer, scene, camera, rig, teleport) {
    renderer.xr.enabled = true;
    // three r153 only copies the headset pose back into our camera when it is
    // registered as the "user camera" — without this the gaze raycast never moves.
    renderer.xr.setUserCamera(camera);
    document.getElementById("vr-button-container").appendChild(
        VRButton.createButton(renderer),
    );

    const controllerModelFactory = new XRControllerModelFactory();

    for (const index of [0, 1]) {
        const controller = renderer.xr.getController(index);

        const ray = new THREE.Line(
            new THREE.BufferGeometry().setFromPoints([
                new THREE.Vector3(0, 0, 0),
                new THREE.Vector3(0, 0, -5),
            ]),
            new THREE.LineBasicMaterial({ color: 0x7c3aed, transparent: true, opacity: 0.7 }),
        );
        ray.visible = false;
        controller.add(ray);

        controller.addEventListener("connected", (event) => {
            const tracked = event.data.targetRayMode === "tracked-pointer";
            controller.userData.connected = tracked;
            controller.userData.gamepad = event.data.gamepad;
            ray.visible = tracked;
            if (tracked && !teleport.controller) teleport.controller = controller;
        });
        controller.addEventListener("disconnected", () => {
            controller.userData.connected = false;
            controller.userData.gamepad = null;
            ray.visible = false;
            if (teleport.controller === controller) teleport.controller = null;
        });
        controller.addEventListener("select", () => {
            if (controller.userData.connected) teleport.controller = controller;
            teleport.trigger();
        });
        controller.addEventListener("squeezestart", () => {
            if (controller.userData.connected) teleport.controller = controller;
            teleport.update(); // refresh hoverNode for the hand that just squeezed, not the previous one
            teleport.grabStart(controller);
        });
        controller.addEventListener("squeezeend", () => teleport.grabEnd(controller));
        rig.add(controller);

        // Renders the real hardware's controller model (Quest Touch, etc.) so the user sees their hands.
        const grip = renderer.xr.getControllerGrip(index);
        grip.add(controllerModelFactory.createControllerModel(grip));
        rig.add(grip);
    }

    // Di headset, keluar dari sesi ditangani sistem (tombol Meta / VRButton), jadi yang
    // perlu kita lakukan hanya menyambut siswa kembali ke layar dengan jalan ke refleksi.
    renderer.xr.addEventListener("sessionend", () => {
        teleport.logger?.log("sesi_selesai");
        teleport.logger?.flush();
        showPostSessionPanel(teleport.logger);
    });

    renderer.setAnimationLoop(() => {
        teleport.update();
        renderer.render(scene, camera);
    });
}

/**
 * Panel penutup sesi: jalan keluar dari VR menuju modul refleksi.
 *
 * HTML biasa, dan itu benar di sini — mode stereo HP bukan sesi WebXR, hanya halaman
 * dengan kanvas terbagi dua, jadi DOM tetap terlihat. (Di headset panel ini baru muncul
 * setelah sesi immersive berakhir, jadi DOM juga sudah kembali terlihat.)
 *
 * Dibangun di JS, bukan dirender blade, karena sesi_id lahir dari crypto.randomUUID()
 * di klien saat scene siap — blade tidak mungkin tahu nilainya.
 */
function showPostSessionPanel(logger) {
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
    panel.innerHTML =
        '<p style="font-size:20px;font-weight:600;margin:0">Sesi VR selesai</p>' +
        '<p style="margin:0;opacity:.8;max-width:32ch">Lanjutkan dengan menuliskan refleksimu.</p>' +
        `<a href="${tujuan}" style="background:#7c3aed;color:#fff;padding:14px 28px;` +
        'border-radius:9999px;font-weight:600;text-decoration:none">Lanjut ke Refleksi</a>' +
        '<button type="button" id="btn-lanjut-jelajah" style="background:none;border:none;' +
        'color:#c4b5fd;text-decoration:underline;font:inherit;cursor:pointer">Kembali menjelajah</button>';
    document.body.appendChild(panel);

    panel.querySelector("#btn-lanjut-jelajah").addEventListener("click", () => panel.remove());
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

async function startPhoneStereoSession(renderer, scene, camera, teleport) {
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

async function main() {
    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setPixelRatio(window.devicePixelRatio);
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    document.body.prepend(renderer.domElement);

    const camera = new THREE.PerspectiveCamera(
        70,
        window.innerWidth / window.innerHeight,
        0.01,
        1000,
    );

    const { scene, ground } = createScene();

    const rig = new THREE.Group();
    rig.add(camera);
    scene.add(rig);

    const teleport = new TeleportControls(scene, camera, rig, [ground]);
    teleport.panel = new InfoPanel(scene, camera);

    window.addEventListener("resize", () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    showElement("loading-container");
    const model = await ModelLoader.loadModel(
        "/storage/" + museum.path_obj,
        (event) => {
            const total = event.total || museum.file_size || 1;
            const progress = Math.min((event.loaded / total) * 100, 100);
            document.getElementById("loading-bar").style.width = `${progress}%`;
        },
    );
    hideElement("loading-container");

    placeModel(model, camera, ground);
    scene.add(model);
    teleport.targets.push(model);

    if (Array.isArray(window.vrObjects) && window.vrObjects.length) {
        const byMeshName = new Map(window.vrObjects.map((o) => [o.mesh_name, o]));
        const slotNames = new Set(
            window.vrObjects.map((o) => o.slot_mesh_name).filter(Boolean),
        );
        model.traverse((node) => {
            const info = byMeshName.get(node.name);
            if (info) {
                node.userData.vrObject = info;
                teleport.markInteractive(node);
            }
            if (slotNames.has(node.name)) {
                node.visible = false;
                teleport.slots.set(node.name, node);
            }
        });
    }

    const headsetSupported = "xr" in navigator &&
        (await navigator.xr.isSessionSupported("immersive-vr").catch(() => false));

    teleport.logger = new EventLogger({
        url: vrEventsUrl,
        token: csrfToken,
        museumId: museum.museum_id,
        kodeResponden: new URLSearchParams(location.search).get("kode"),
    });
    teleport.logger.log("sesi_mulai", null, {
        perangkat: headsetSupported ? "headset" : "hp",
        objek_interaktif: teleport.interactiveMeshes.length,
        slot: teleport.slots.size,
    });

    teleport.phasePanel = new PhasePanel(camera);
    teleport.phases = new PhaseManager({
        totalObjek: teleport.interactiveMeshes.length,
        totalSlot: teleport.slots.size,
        // Kemampuan perangkat, bukan status sambungan controller: controller Quest baru
        // terhubung beberapa saat setelah sesi XR dimulai, dan fase tidak boleh terlanjur
        // menyimpulkan perangkat ini tidak bisa menggenggam.
        perangkatBerkemampuanController: headsetSupported,
        onChange: ({ dari, ke, alasan }) => {
            teleport.logger.log("fase_berubah", null, { dari, ke, alasan });
            teleport.phasePanel.sorot();
        },
    });

    if (headsetSupported) {
        await startHeadsetSession(renderer, scene, camera, rig, teleport);
        return;
    }

    if (typeof DeviceOrientationEvent !== "undefined" && "ondeviceorientation" in window) {
        renderer.render(scene, camera);
        await startPhoneStereoSession(renderer, scene, camera, teleport);
        return;
    }

    showElement("vr-not-supported");
    generateHandoffQrCode();
}

main().catch((err) => {
    console.error(err);
    showElement("loading-container");
    document.querySelector("#loading-container p").textContent =
        "Gagal memuat pengalaman VR. Muat ulang halaman untuk mencoba lagi.";
});
