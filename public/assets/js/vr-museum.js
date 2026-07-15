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
    static UP = new THREE.Vector3(0, 1, 0);
    static tempMatrix = new THREE.Matrix4();
    static tempVector = new THREE.Vector3();

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

        const hits = this.raycaster
            .intersectObjects(this.targets, true)
            .filter((h) => h.object.visible);

        const first = hits[0];
        this.hoverNode = first ? TeleportControls.findVrNode(first.object) : null;
        this.hoverInfo = this.hoverNode?.userData.vrObject ?? null;
        this.hoverPoint = this.hoverInfo ? first.point : null;

        if (this.hoverInfo) {
            this.reticle.visible = true;
            this.reticle.material.color.setHex(0xfbbf24);
            this.reticle.position.copy(first.point);
            this.reticle.scale.setScalar(Math.max(1, first.distance / 6));
            const normal = TeleportControls.tempVector
                .copy(first.face.normal)
                .transformDirection(first.object.matrixWorld);
            this.reticle.quaternion.setFromUnitVectors(TeleportControls.UP, normal);
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
    }

    grabEnd(controller) {
        const node = controller.userData.grabbedNode;
        if (!node) return;
        controller.userData.grabbedParent.attach(node);
        controller.userData.grabbedNode = null;
        controller.userData.grabbedParent = null;
        this.checkSlot(node);
    }

    /** Puzzle: released piece within reach of its slot snaps in place and counts as solved. */
    checkSlot(node) {
        const slot = this.slots.get(node.userData.vrObject?.slot_mesh_name);
        if (!slot || node.userData.solved) return;

        const nodePos = node.getWorldPosition(new THREE.Vector3());
        const slotPos = slot.getWorldPosition(new THREE.Vector3());
        // ponytail: 0.5m snap radius, single knob; make per-object if pieces vary wildly in size.
        if (nodePos.distanceTo(slotPos) > 0.5) return;

        slot.parent.attach(node);
        node.position.copy(slot.position);
        node.quaternion.copy(slot.quaternion);
        node.userData.solved = true;
        this.solvedCount++;

        const done = this.solvedCount >= this.slots.size;
        this.panel?.show({
            nama: done ? "Puzzle Selesai! 🎉" : "Tepat!",
            deskripsi: done
                ? "Semua objek sudah kembali ke tempat yang benar. Kerja bagus!"
                : `${node.userData.vrObject.nama} sudah di tempat yang benar. (${this.solvedCount}/${this.slots.size})`,
        }, slotPos);
    }

    /** Single entry point for trigger/tap: close panel > open panel > teleport. */
    trigger() {
        if (this.panel?.mesh.visible) {
            this.panel.hide();
            return;
        }
        if (this.hoverInfo && this.hoverPoint) {
            this.panel?.show(this.hoverInfo, this.hoverPoint);
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
        this.wrapText(ctx, info.deskripsi || "", 40, 180, width - 80, 50, 8);

        ctx.font = "28px Inter, sans-serif";
        ctx.fillStyle = "#9ca3af";
        ctx.fillText("Ketuk / tekan trigger untuk menutup", 40, height - 36);

        this.texture.needsUpdate = true;
    }

    wrapText(ctx, text, x, y, maxWidth, lineHeight, maxLines) {
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
            ray.visible = tracked;
            if (tracked && !teleport.controller) teleport.controller = controller;
        });
        controller.addEventListener("disconnected", () => {
            controller.userData.connected = false;
            ray.visible = false;
            if (teleport.controller === controller) teleport.controller = null;
        });
        controller.addEventListener("select", () => {
            if (controller.userData.connected) teleport.controller = controller;
            teleport.trigger();
        });
        controller.addEventListener("squeezestart", () => teleport.grabStart(controller));
        controller.addEventListener("squeezeend", () => teleport.grabEnd(controller));
        rig.add(controller);
    }

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

        renderer.domElement.addEventListener("pointerup", () => teleport.trigger());
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
            if (info) node.userData.vrObject = info;
            if (slotNames.has(node.name)) {
                node.visible = false;
                teleport.slots.set(node.name, node);
            }
        });
    }

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
