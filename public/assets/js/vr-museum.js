import * as THREE from "three";
import { GLTFLoader } from "three/jsm/loaders/GLTFLoader.js";
import { DRACOLoader } from "three/jsm/loaders/DRACOLoader.js";
import { VRButton } from "three/jsm/webxr/VRButton.js";
import { StereoEffect } from "three/jsm/effects/StereoEffect.js";

// ponytail: temporary on-screen error surface for remote phone testing (no devtools access). Remove once VR is stable.
(function installDebugSurface() {
    const banner = document.createElement("div");
    banner.textContent = "vr-museum.js loaded " + new Date().toLocaleTimeString();
    banner.style.cssText =
        "position:fixed;top:0;left:0;right:0;z-index:2147483647;background:#16a34a;" +
        "color:#fff;font:12px monospace;padding:3px;text-align:center;pointer-events:none";
    document.body.appendChild(banner);
    setTimeout(() => banner.remove(), 4000);

    const errorBox = document.createElement("pre");
    errorBox.style.cssText =
        "position:fixed;inset:0;z-index:2147483647;background:#000;color:#f87171;" +
        "font:12px monospace;padding:12px;white-space:pre-wrap;overflow:auto;display:none";
    document.body.appendChild(errorBox);

    function showError(label, err) {
        errorBox.style.display = "block";
        errorBox.textContent += `[${label}] ${err?.stack || err?.message || err}\n\n`;
    }

    window.addEventListener("error", (e) => showError("error", e.error || e.message));
    window.addEventListener("unhandledrejection", (e) => showError("promise", e.reason));
})();

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
    static tempMatrix = new THREE.Matrix4();
    static tempVector = new THREE.Vector3();

    constructor(scene, camera, rig, targets) {
        this.camera = camera;
        this.rig = rig;
        this.targets = targets;
        this.controller = null;
        this.raycaster = new THREE.Raycaster();

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
    }

    update() {
        if (this.controller?.userData.connected) {
            TeleportControls.tempMatrix.identity().extractRotation(this.controller.matrixWorld);
            this.raycaster.ray.origin.setFromMatrixPosition(this.controller.matrixWorld);
            this.raycaster.ray.direction.set(0, 0, -1).applyMatrix4(TeleportControls.tempMatrix);
        } else {
            this.raycaster.setFromCamera(TeleportControls.SCREEN_CENTER, this.camera);
        }

        const hits = this.raycaster.intersectObjects(this.targets, true);
        const hit = hits.find((h) => {
            if (!h.face) return false;
            const normal = TeleportControls.tempVector
                .copy(h.face.normal)
                .transformDirection(h.object.matrixWorld);
            return normal.y > 0.6;
        });

        this.reticle.visible = Boolean(hit);
        if (hit) {
            this.reticle.position.copy(hit.point);
            this.reticle.position.y += 0.01;
            this.reticle.scale.setScalar(Math.max(1, hit.distance / 6));
        }
    }

    teleport() {
        if (!this.reticle.visible) return;
        const cameraWorld = this.camera.getWorldPosition(TeleportControls.tempVector);
        this.rig.position.x += this.reticle.position.x - cameraWorld.x;
        this.rig.position.z += this.reticle.position.z - cameraWorld.z;
        this.rig.position.y = this.reticle.position.y - 0.01;
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

    const controller = renderer.xr.getController(0);
    controller.addEventListener("connected", (event) => {
        controller.userData.connected = event.data.targetRayMode === "tracked-pointer";
    });
    controller.addEventListener("disconnected", () => {
        controller.userData.connected = false;
    });
    controller.addEventListener("select", () => teleport.teleport());
    rig.add(controller);
    teleport.controller = controller;

    renderer.setAnimationLoop(() => {
        teleport.update();
        renderer.render(scene, camera);
    });
}

// ponytail: temporary on-screen debug readout for remote phone testing; remove once teleport is confirmed working.
function createDebugOverlay() {
    const el = document.createElement("div");
    el.id = "vr-debug";
    el.style.cssText =
        "position:fixed;top:8px;left:8px;z-index:10000002;color:#0f0;" +
        "background:rgba(0,0,0,.7);font:11px monospace;padding:4px 8px;" +
        "border-radius:4px;pointer-events:none;white-space:pre;line-height:1.4";
    document.body.appendChild(el);
    return el;
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
    const debugEl = createDebugOverlay();
    const isSecureCtx = window.isSecureContext;
    debugEl.textContent = `secure: ${isSecureCtx}\nscript: v2\nwaiting for tap...`;

    const button = createEnterButton("Masuk VR");

    button.addEventListener("click", async () => {
        debugEl.textContent = `secure: ${isSecureCtx}\nrequesting permission...`;

        const granted = await requestOrientationPermission();
        if (!granted) {
            debugEl.textContent = `secure: ${isSecureCtx}\npermission: DENIED`;
            alert("Izin sensor orientasi diperlukan untuk mode VR di HP ini.");
            return;
        }

        button.remove();

        const controls = new DeviceOrientationControls(camera);
        const effect = new StereoEffect(renderer);
        effect.setSize(window.innerWidth, window.innerHeight);

        document.body.requestFullscreen?.().catch(() => {});

        renderer.domElement.addEventListener("pointerup", () => teleport.teleport());
        showTeleportHint();

        function render() {
            controls.update();
            teleport.update();
            effect.render(scene, camera);

            const o = controls.deviceOrientation;
            debugEl.textContent =
                `secure: ${isSecureCtx}\n` +
                `alpha: ${o.alpha?.toFixed(1) ?? "none"}\n` +
                `beta: ${o.beta?.toFixed(1) ?? "none"}\n` +
                `gamma: ${o.gamma?.toFixed(1) ?? "none"}\n` +
                `reticle hit: ${teleport.reticle.visible}`;

            requestAnimationFrame(render);
        }
        render();

        window.addEventListener("resize", () => {
            effect.setSize(window.innerWidth, window.innerHeight);
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

    const headsetSupported = "xr" in navigator &&
        (await navigator.xr.isSessionSupported("immersive-vr").catch(() => false));

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
    window.dispatchEvent(new ErrorEvent("error", { error: err }));
});
