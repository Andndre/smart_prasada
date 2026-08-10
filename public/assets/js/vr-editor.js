import * as THREE from "three";
import { GLTFLoader } from "three/jsm/loaders/GLTFLoader.js";
import { DRACOLoader } from "three/jsm/loaders/DRACOLoader.js";
import { OrbitControls } from "three/jsm/controls/OrbitControls.js";

const { museum, objects, saveUrl, deleteUrlTemplate, editUrlTemplate, csrf } =
    window.editorData;

// mesh_name -> object record from DB; kept in sync after every save/delete.
const objectsByMesh = new Map(objects.map((o) => [o.mesh_name, o]));
const slotMeshNames = () =>
    new Set(
        [...objectsByMesh.values()]
            .map((o) => o.slot_mesh_name)
            .filter(Boolean),
    );

const state = {
    selectedNode: null,
    pickingSlot: false,
    highlighted: [],
};

const el = (id) => document.getElementById(id);

const STATUS_TONE_CLASS = {
    neutral: "text-gray-400",
    success: "text-green-600",
    error: "text-red-600",
};

function setStatus(text, tone = "neutral") {
    const node = el("editor-status");
    node.textContent = text;
    node.className = "text-xs font-semibold " + STATUS_TONE_CLASS[tone];
}

const BUTTON_TONE_CLASS = {
    success: ["bg-green-600", "hover:bg-green-700"],
    error: ["bg-red-600", "hover:bg-red-700"],
};

// Briefly swap a button's label/color to confirm an action, then restore it.
function flashButton(btn, text, tone) {
    const defaultClasses = ["bg-blue-600", "hover:bg-blue-700"];
    const original = btn.dataset.originalText ?? btn.textContent;
    btn.dataset.originalText = original;
    btn.textContent = text;
    btn.classList.remove(...defaultClasses, ...BUTTON_TONE_CLASS.success, ...BUTTON_TONE_CLASS.error);
    btn.classList.add(...BUTTON_TONE_CLASS[tone]);
    clearTimeout(btn._flashTimeout);
    btn._flashTimeout = setTimeout(() => {
        btn.textContent = original;
        btn.classList.remove(...BUTTON_TONE_CLASS.success, ...BUTTON_TONE_CLASS.error);
        btn.classList.add(...defaultClasses);
    }, 1400);
}

// ---------- Scene ----------
const container = el("canvas-container");
const scene = new THREE.Scene();
scene.background = new THREE.Color(0x1f2937);
scene.add(new THREE.HemisphereLight(0xffffff, 0x666688, 1.1));
const sun = new THREE.DirectionalLight(0xffffff, 1);
sun.position.set(5, 10, 7.5);
scene.add(sun);
scene.add(new THREE.GridHelper(40, 40, 0x4b5563, 0x374151));

const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 1000);
camera.position.set(6, 5, 8);

const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.outputColorSpace = THREE.SRGBColorSpace;
container.appendChild(renderer.domElement);
renderer.domElement.classList.add("absolute", "inset-0");

const controls = new OrbitControls(camera, renderer.domElement);
controls.enableDamping = true;

function resize() {
    const { clientWidth: w, clientHeight: h } = container;
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    renderer.setSize(w, h);
}
new ResizeObserver(resize).observe(container);
resize();

renderer.setAnimationLoop(() => {
    controls.update();
    renderer.render(scene, camera);
});

// ---------- Selection / highlight ----------
function clearHighlight() {
    for (const { material, emissive } of state.highlighted) {
        material.emissive?.setHex(emissive);
    }
    state.highlighted = [];
}

function highlightNode(node) {
    clearHighlight();
    node.traverse((child) => {
        if (child.isMesh && child.material?.emissive) {
            state.highlighted.push({
                material: child.material,
                emissive: child.material.emissive.getHex(),
            });
            child.material.emissive.setHex(0xff8800);
        }
    });
}

function focusCamera(node) {
    const box = new THREE.Box3().setFromObject(node);
    if (box.isEmpty()) return;
    const center = box.getCenter(new THREE.Vector3());
    const size = box.getSize(new THREE.Vector3()).length() || 1;
    const direction = camera.position.clone().sub(controls.target).normalize();
    controls.target.copy(center);
    camera.position
        .copy(center)
        .addScaledVector(direction, Math.max(size * 1.5, 1));
}

function selectNode(node) {
    if (state.pickingSlot) {
        finishSlotPick(node.name);
        return;
    }
    state.selectedNode = node;
    highlightNode(node);
    renderTree();
    showPanel(node);
}

// ---------- Property panel ----------
function showPanel(node) {
    const record = objectsByMesh.get(node.name);
    el("panel-empty").classList.add("hidden");
    el("panel-form").classList.remove("hidden");
    el("field-mesh-name").value = node.name;
    el("field-nama").value = record?.nama ?? node.name.replaceAll("_", " ");
    el("field-deskripsi").value = record?.deskripsi ?? "";
    el("field-slot").value = record?.slot_mesh_name ?? "";
    el("btn-delete").classList.toggle("hidden", !record);
    const fullEdit = el("link-full-edit");
    fullEdit.classList.toggle("hidden", !record);
    if (record)
        fullEdit.href = editUrlTemplate.replace("__ID__", record.object_id);
}

function startSlotPick() {
    state.pickingSlot = true;
    el("pick-slot-banner").classList.remove("hidden");
}

function finishSlotPick(meshName) {
    state.pickingSlot = false;
    el("pick-slot-banner").classList.add("hidden");
    if (meshName && meshName !== state.selectedNode?.name) {
        el("field-slot").value = meshName;
    }
}

el("btn-pick-slot").addEventListener("click", startSlotPick);
el("btn-clear-slot").addEventListener(
    "click",
    () => (el("field-slot").value = ""),
);
window.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && state.pickingSlot) finishSlotPick(null);
});

el("panel-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!state.selectedNode) return;
    const saveBtn = el("btn-save");
    setStatus("Menyimpan…");
    const response = await fetch(saveUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
        body: JSON.stringify({
            mesh_name: state.selectedNode.name,
            nama: el("field-nama").value,
            deskripsi: el("field-deskripsi").value || null,
            slot_mesh_name: el("field-slot").value || null,
        }),
    });
    if (!response.ok) {
        setStatus("Gagal menyimpan", "error");
        flashButton(saveBtn, "Gagal ✕", "error");
        return;
    }
    const saved = await response.json();
    objectsByMesh.set(saved.mesh_name, {
        object_id: saved.object_id,
        mesh_name: saved.mesh_name,
        nama: el("field-nama").value,
        deskripsi: el("field-deskripsi").value || null,
        slot_mesh_name: el("field-slot").value || null,
    });
    setStatus("Tersimpan ✓", "success");
    flashButton(saveBtn, "Tersimpan ✓", "success");
    renderTree();
    showPanel(state.selectedNode);
});

el("btn-delete").addEventListener("click", async () => {
    const record = objectsByMesh.get(state.selectedNode?.name);
    if (
        !record ||
        !confirm(`Hapus objek "${record.nama}" beserta file terkait?`)
    )
        return;
    setStatus("Menghapus…");
    const response = await fetch(
        deleteUrlTemplate.replace("__ID__", record.object_id),
        {
            method: "DELETE",
            headers: { "X-CSRF-TOKEN": csrf },
        },
    );
    if (!response.ok) {
        setStatus("Gagal menghapus", "error");
        return;
    }
    objectsByMesh.delete(record.mesh_name);
    setStatus("Terhapus", "success");
    renderTree();
    showPanel(state.selectedNode);
});

// ---------- Tree view ----------
let modelRoot = null;

function renderTree() {
    if (!modelRoot) return;
    const slots = slotMeshNames();
    const treeContainer = el("mesh-tree");
    treeContainer.innerHTML = "";
    treeContainer.appendChild(buildTreeList(modelRoot.children, slots));
}

function buildTreeList(children, slots) {
    const ul = document.createElement("ul");
    ul.className = "space-y-0.5";
    for (const child of children) {
        if (!child.name) continue;
        const li = document.createElement("li");
        const row = document.createElement("button");
        row.type = "button";
        const selected = state.selectedNode === child;
        row.className =
            "flex w-full items-center gap-1.5 rounded px-2 py-1 text-left text-xs hover:bg-gray-100 " +
            (selected ? "bg-blue-50 font-semibold text-blue-700" : "");

        const record = objectsByMesh.get(child.name);
        let badge = "";
        if (record) badge = record.slot_mesh_name ? "🧩" : "📄";
        else if (slots.has(child.name)) badge = "📍";

        row.innerHTML =
            `<span class="text-gray-400">${child.isMesh ? "▪" : "▸"}</span>` +
            `<span class="truncate">${child.name}</span>` +
            (badge ? `<span class="ml-auto">${badge}</span>` : "");
        row.addEventListener("click", () => {
            selectNode(child);
            focusCamera(child);
        });
        li.appendChild(row);

        if (child.children.length) {
            const sub = buildTreeList(child.children, slots);
            if (sub.childElementCount) {
                sub.className += " ml-3 border-l border-gray-100 pl-1";
                li.appendChild(sub);
            }
        }
        ul.appendChild(li);
    }
    return ul;
}

// ---------- Canvas picking ----------
const raycaster = new THREE.Raycaster();
const pointer = new THREE.Vector2();
let downAt = null;

renderer.domElement.addEventListener(
    "pointerdown",
    (e) => (downAt = [e.clientX, e.clientY]),
);
renderer.domElement.addEventListener("pointerup", (e) => {
    // Ignore drags (orbit), only treat short clicks as picks.
    if (!downAt || Math.hypot(e.clientX - downAt[0], e.clientY - downAt[1]) > 5)
        return;
    const rect = renderer.domElement.getBoundingClientRect();
    pointer.set(
        ((e.clientX - rect.left) / rect.width) * 2 - 1,
        -((e.clientY - rect.top) / rect.height) * 2 + 1,
    );
    raycaster.setFromCamera(pointer, camera);
    const hit = raycaster.intersectObject(modelRoot, true)[0];
    if (!hit) return;
    let node = hit.object;
    while (node && !node.name && node.parent) node = node.parent;
    if (node?.name) selectNode(node);
});

// ---------- Load model ----------
async function main() {
    const dracoLoader = new DRACOLoader();
    dracoLoader.setDecoderConfig({ type: "js" });
    dracoLoader.setDecoderPath("https://www.gstatic.com/draco/v1/decoders/");
    const loader = new GLTFLoader();
    loader.setDRACOLoader(dracoLoader);

    const gltf = await loader.loadAsync(
        "/storage/" + museum.path_obj,
        (event) => {
            const total = event.total || museum.file_size || 1;
            el("loading-bar").style.width =
                `${Math.min((event.loaded / total) * 100, 100)}%`;
        },
    );
    el("loading-container").remove();

    modelRoot = gltf.scene;
    scene.add(modelRoot);
    focusCamera(modelRoot);
    renderTree();
    setStatus(`${objectsByMesh.size} objek ter-link`);
}

main().catch((err) => {
    console.error(err);
    setStatus("Gagal memuat model");
    el("mesh-tree").innerHTML =
        '<p class="p-2 text-xs text-red-500">Gagal memuat model 3D.</p>';
});
