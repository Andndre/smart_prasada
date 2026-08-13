/**
 * Titik masuk sesi VR: menyiapkan scene, memuat model, memilih jalur perangkat.
 *
 * Modul pendukungnya diimpor lewat BARE SPECIFIER (lihat importmap di
 * guest/vr/museum.blade.php), bukan path relatif: impor relatif tidak ikut
 * cache-busting ?v={filemtime} berkas induknya, jadi headset akan menyajikan versi
 * basi setelah salah satu modul disunting — dan hard-refresh di browser Quest bukan
 * hal sepele saat responden sudah antre.
 */
import * as THREE from "three";
import { GLTFLoader } from "three/jsm/loaders/GLTFLoader.js";
import { DRACOLoader } from "three/jsm/loaders/DRACOLoader.js";
import { VRButton } from "three/jsm/webxr/VRButton.js";
import { XRControllerModelFactory } from "three/jsm/webxr/XRControllerModelFactory.js";
import { PhaseManager } from "vr-phases";
import { EventLogger } from "vr-events";
import { TeleportControls } from "vr-controls";
import { ControllerHints, ExitButton, InfoPanel, PhasePanel } from "vr-panels";
import { showPostSessionPanel } from "vr-sesi";
import { startPhoneStereoSession } from "vr-hp";

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

async function startHeadsetSession(renderer, scene, camera, rig, teleport) {
    renderer.xr.enabled = true;
    // three r153 only copies the headset pose back into our camera when it is
    // registered as the "user camera" — without this the gaze raycast never moves.
    renderer.xr.setUserCamera(camera);
    document.getElementById("vr-button-container").appendChild(
        VRButton.createButton(renderer),
    );

    // Jalan keluar di dalam scene: VRButton dan tombol DOM "✕ Selesai" tidak dirender
    // di dalam sesi immersive, jadi tanpa ini fasilitator harus meraih controller siswa
    // tiap pergantian responden. Berakhirnya sesi tetap ditangani handler `sessionend`
    // di bawah, sama seperti kalau siswa keluar lewat tombol sistem Meta.
    teleport.exitButton = new ExitButton(camera, () => renderer.xr.getSession()?.end());

    const controllerModelFactory = new XRControllerModelFactory();
    const hints = [];
    // Aksi dilakukan dengan tangan mana pun menghapus labelnya di kedua tangan.
    const tandaiHint = (aksi) => hints.forEach((h) => h.tandai(aksi));

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
            controller.userData.handedness = event.data.handedness;
            ray.visible = tracked;
            if (tracked && !teleport.controller) teleport.controller = controller;
            if (tracked) hint.pasang(event.data.handedness);
        });
        controller.addEventListener("disconnected", () => {
            controller.userData.connected = false;
            controller.userData.gamepad = null;
            ray.visible = false;
            if (teleport.controller === controller) teleport.controller = null;
        });
        controller.addEventListener("select", () => {
            if (controller.userData.connected) teleport.controller = controller;
            tandaiHint("tekan");
            teleport.trigger();
        });
        controller.addEventListener("squeezestart", () => {
            if (controller.userData.connected) teleport.controller = controller;
            tandaiHint("genggam");
            teleport.update(); // refresh hoverNode for the hand that just squeezed, not the previous one
            teleport.grabStart(controller);
        });
        controller.addEventListener("squeezeend", () => teleport.grabEnd(controller));
        rig.add(controller);
        teleport.controllers.push(controller);

        // Renders the real hardware's controller model (Quest Touch, etc.) so the user sees their hands.
        const grip = renderer.xr.getControllerGrip(index);
        grip.add(controllerModelFactory.createControllerModel(grip));
        rig.add(grip);

        // Onboarding 20 detik (A3): label menempel di grip, dipasang saat handedness diketahui.
        const hint = new ControllerHints(grip, controller);
        hints.push(hint);
    }

    // Satu muara untuk semua cara keluar — tombol dalam scene, tombol sistem Meta, atau
    // headset dilepas — jadi siswa selalu disambut kembali dengan jalan ke refleksi.
    renderer.xr.addEventListener("sessionend", () => {
        teleport.logger?.log("sesi_selesai");
        teleport.logger?.flush();
        showPostSessionPanel(teleport.logger);
    });

    const clock = new THREE.Clock();
    renderer.setAnimationLoop(() => {
        teleport.gerakBebas(Math.min(clock.getDelta(), 0.1));
        for (const hint of hints) hint.update();
        teleport.update();
        renderer.render(scene, camera);
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

    // Ditanyakan sebelum model dipasang: registerPuzzle() perlu tahu apakah perangkat
    // ini bisa menggenggam sebelum memutuskan melepas potongan atau tidak.
    const headsetSupported = "xr" in navigator &&
        (await navigator.xr.isSessionSupported("immersive-vr").catch(() => false));

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
        model.traverse((node) => {
            const info = byMeshName.get(node.name);
            if (!info) return;
            node.userData.vrObject = info;
            teleport.markInteractive(node);
            if (Array.isArray(info.posisi_awal) && info.posisi_awal.length === 3) {
                teleport.registerPuzzle(node, info.posisi_awal, headsetSupported);
            }
        });
    }

    teleport.logger = new EventLogger({
        url: vrEventsUrl,
        token: csrfToken,
        museumId: museum.museum_id,
        kodeResponden: new URLSearchParams(location.search).get("kode"),
    }).start();
    teleport.logger.log("sesi_mulai", null, {
        perangkat: headsetSupported ? "headset" : "hp",
        objek_interaktif: teleport.interactiveMeshes.length,
        slot: teleport.totalPuzzle,
    });

    teleport.panel.bisaGenggam = headsetSupported;
    teleport.phasePanel = new PhasePanel(camera);
    teleport.phases = new PhaseManager({
        totalObjek: teleport.interactiveMeshes.length,
        totalSlot: teleport.totalPuzzle,
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
