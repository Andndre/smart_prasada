import * as THREE from "three";
import { GLTFLoader } from "three/jsm/loaders/GLTFLoader.js";
import { DRACOLoader } from "three/jsm/loaders/DRACOLoader.js";
import { OrbitControls } from "three/jsm/controls/OrbitControls.js";
import { TransformControls } from "three/jsm/controls/TransformControls.js";

const {
    museum,
    objects,
    saveUrl,
    deleteUrlTemplate,
    editUrlTemplate,
    csrf,
    modelMtime,
} = window.editorData;

// mesh_name -> object record from DB; kept in sync after every save/delete.
const objectsByMesh = new Map(objects.map((o) => [o.mesh_name, o]));

/**
 * Ambang peringatan jarak seret. Bukan pembatas — admin tetap boleh melewatinya,
 * tapi keduanya menandai potongan yang di lapangan sulit atau mustahil dipasang.
 *
 * `grabStart` di runtime memakai `controller.attach()`, yang mempertahankan transform
 * dunia: potongan yang mulai 30 m jauhnya tetap 30 m saat digenggam, jadi siswa harus
 * teleport ke sana dulu. Dan teleport butuh lantai — potongan yang melayang di atas
 * jangkauan tangan tidak akan pernah bisa dibawa masuk ke radius snap 0,5 m.
 */
const JARAK_PERINGATAN = 8; // meter, mendatar
const TINGGI_JANGKAUAN = 2.2; // meter di atas lantai model

const state = {
    selectedNode: null,
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

// Gizmo geser untuk posisi lepas potongan puzzle. Translate saja: rotasi terpasang
// selalu rotasi asli objek, dan rotasi saat melayang tidak berpengaruh apa pun secara
// pedagogis. Kalau suatu saat perlu, setMode("rotate") satu baris.
const gizmo = new TransformControls(camera, renderer.domElement);
gizmo.setMode("translate");
gizmo.setSize(0.8);
// Tanpa ini, menyeret panah ikut memutar kamera.
gizmo.addEventListener("dragging-changed", (e) => {
    controls.enabled = !e.value;
});
gizmo.addEventListener("objectChange", refreshPuzzleReadout);
scene.add(gizmo);

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
    // Lepas gizmo dari objek sebelumnya sebelum pilihan berpindah, supaya panahnya
    // tidak tertinggal menempel di objek yang tidak lagi dipilih.
    gizmo.detach();
    state.selectedNode = node;
    highlightNode(node);
    renderTree();
    showPanel(node);
}

// ---------- Property panel ----------
const nilaiKarakterInputs = () => [
    ...document.querySelectorAll('input[name="nilai_karakter"]'),
];

function readNilaiKarakter() {
    return nilaiKarakterInputs()
        .filter((input) => input.checked)
        .map((input) => input.value);
}

function writeNilaiKarakter(values = []) {
    for (const input of nilaiKarakterInputs()) {
        input.checked = values.includes(input.value);
    }
}

function showPanel(node) {
    const record = objectsByMesh.get(node.name);
    el("panel-empty").classList.add("hidden");
    el("panel-form").classList.remove("hidden");
    el("field-mesh-name").value = node.name;
    el("field-nama").value = record?.nama ?? node.name.replaceAll("_", " ");
    el("field-deskripsi").value = record?.deskripsi ?? "";
    writeNilaiKarakter(record?.nilai_karakter ?? []);
    setPuzzle(Boolean(record?.posisi_awal));
    el("btn-delete").classList.toggle("hidden", !record);
    const fullEdit = el("link-full-edit");
    fullEdit.classList.toggle("hidden", !record);
    if (record)
        fullEdit.href = editUrlTemplate.replace("__ID__", record.object_id);
}

// ---------- Puzzle: posisi lepas ----------

/**
 * Selisih posisi sekarang terhadap posisi bawaan mesh di GLB, di ruang parent-local.
 * Inilah satu-satunya angka yang disimpan — posisi terpasangnya tidak perlu disimpan
 * karena ia memang transform bawaan model.
 */
function deltaOf(node) {
    return node.position.clone().sub(node.userData.posisiBawaan);
}

function isPuzzleAktif() {
    return el("field-puzzle").checked;
}

function refreshPuzzleReadout() {
    const node = state.selectedNode;
    if (!node || !node.userData.posisiBawaan) return;

    const d = deltaOf(node);
    el("puzzle-readout").textContent =
        `Δ ${d.length().toFixed(2)} m  (x ${d.x.toFixed(2)}, y ${d.y.toFixed(2)}, z ${d.z.toFixed(2)})`;

    const peringatan = [];
    if (Math.hypot(d.x, d.z) > JARAK_PERINGATAN) {
        peringatan.push(
            `Lebih dari ${JARAK_PERINGATAN} m mendatar — siswa harus teleport ke sana dulu untuk menggenggamnya.`,
        );
    }
    // Lantai model ada di y = 0 dunia: modelRoot digeser naik sebesar box.min.y saat
    // dimuat, sama seperti yang dilakukan runtime.
    const dasar = new THREE.Box3().setFromObject(node).min.y;
    if (dasar > TINGGI_JANGKAUAN) {
        peringatan.push(
            `${dasar.toFixed(1)} m di atas lantai — di luar jangkauan tangan orang berdiri, tidak akan pernah bisa dipasang.`,
        );
    }
    el("puzzle-warning").textContent = peringatan.join(" ");
}

function setPuzzle(aktif) {
    const node = state.selectedNode;
    el("field-puzzle").checked = aktif;
    el("puzzle-controls").classList.toggle("hidden", !aktif);
    el("drag-banner").classList.toggle("hidden", !aktif);

    if (!node) return;
    if (aktif) {
        gizmo.attach(node);
        refreshPuzzleReadout();
    } else {
        gizmo.detach();
        node.position.copy(node.userData.posisiBawaan);
    }
}

el("field-puzzle").addEventListener("change", (e) =>
    setPuzzle(e.target.checked),
);
el("btn-reset-puzzle").addEventListener("click", () => {
    state.selectedNode?.position.copy(state.selectedNode.userData.posisiBawaan);
    refreshPuzzleReadout();
});

el("panel-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!state.selectedNode) return;
    const saveBtn = el("btn-save");
    const posisiAwal = isPuzzleAktif()
        ? deltaOf(state.selectedNode).toArray()
        : null;
    setStatus("Menyimpan…");
    const response = await fetch(saveUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
        body: JSON.stringify({
            mesh_name: state.selectedNode.name,
            nama: el("field-nama").value,
            deskripsi: el("field-deskripsi").value || null,
            posisi_awal: posisiAwal,
            nilai_karakter: readNilaiKarakter(),
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
        posisi_awal: posisiAwal,
        model_mtime: posisiAwal ? modelMtime : null,
        nilai_karakter: readNilaiKarakter(),
    });
    setStatus("Tersimpan ✓", "success");
    flashButton(saveBtn, "Tersimpan ✓", "success");
    renderTree();
    renderWarnings();
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
    const treeContainer = el("mesh-tree");
    treeContainer.innerHTML = "";
    treeContainer.appendChild(buildTreeList(modelRoot.children));
}

/**
 * Dua kelas data basi yang hanya bisa dilihat di sini — runtime sengaja diam supaya
 * siswa tidak pernah melihat pesan admin.
 *
 * 1. Node hilang: nama mesh berubah atau objeknya dihapus di Blender (jebakan paling
 *    sering: sufiks ".001" yang ditambahkan Blender saat menduplikasi).
 * 2. Model berubah setelah posisi disimpan: nama node yang sama dengan geometri
 *    berbeda tidak bisa dideteksi secara umum, jadi ini aproksimasinya. Positif palsu
 *    saat berkas yang sama diunggah ulang tidak apa-apa — itu justru saat yang tepat
 *    untuk memeriksa.
 */
function renderWarnings() {
    if (!modelRoot) return;
    const namaNode = new Set();
    modelRoot.traverse((n) => n.name && namaNode.add(n.name));

    const hilang = [...objectsByMesh.keys()].filter((n) => !namaNode.has(n));
    const basi = [...objectsByMesh.values()].filter(
        (o) => o.posisi_awal && o.model_mtime && o.model_mtime !== modelMtime,
    );

    const box = el("editor-warnings");
    box.innerHTML = "";
    if (hilang.length) {
        box.appendChild(
            warningRow(
                "border-red-200 bg-red-50 text-red-700",
                `${hilang.length} objek menunjuk mesh yang tidak ada di model ini: ${hilang.join(", ")}`,
            ),
        );
    }
    if (basi.length) {
        box.appendChild(
            warningRow(
                "border-amber-200 bg-amber-50 text-amber-800",
                `${basi.length} posisi puzzle disimpan sebelum model diganti — periksa: ${basi.map((o) => o.mesh_name).join(", ")}`,
            ),
        );
    }
}

function warningRow(tone, text) {
    const p = document.createElement("p");
    p.className = `rounded-md border px-2 py-1.5 text-xs ${tone}`;
    p.textContent = text;
    return p;
}

function buildTreeList(children) {
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
        const badge = record ? (record.posisi_awal ? "🧩" : "📄") : "";

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
            const sub = buildTreeList(child.children);
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
    // Ignore drags (orbit), only treat short clicks as picks. A short nudge on the
    // gizmo arrow is a move, not a pick.
    if (
        gizmo.dragging ||
        !downAt ||
        Math.hypot(e.clientX - downAt[0], e.clientY - downAt[1]) > 5
    )
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

    // Pergeseran yang sama dengan placeModel() di runtime, supaya lantai ada di y = 0
    // dan tinggi yang dilihat di sini jujur. Delta memang kebal terhadap ini — ini soal
    // kenyamanan menempatkan dan supaya peringatan tinggi jangkauan punya acuan.
    const box = new THREE.Box3().setFromObject(modelRoot);
    modelRoot.position.y -= box.min.y;

    // Posisi bawaan ditangkap SEBELUM delta tersimpan diterapkan — inilah tempat
    // terpasang setiap potongan puzzle.
    modelRoot.traverse((node) => {
        node.userData.posisiBawaan = node.position.clone();
    });
    modelRoot.updateMatrixWorld(true);

    for (const record of objectsByMesh.values()) {
        if (!Array.isArray(record.posisi_awal)) continue;
        const node = modelRoot.getObjectByName(record.mesh_name);
        node?.position.add(new THREE.Vector3().fromArray(record.posisi_awal));
    }

    scene.add(modelRoot);
    focusCamera(modelRoot);
    renderTree();
    renderWarnings();
    setStatus(`${objectsByMesh.size} objek ter-link`);
}

main().catch((err) => {
    console.error(err);
    setStatus("Gagal memuat model");
    el("mesh-tree").innerHTML =
        '<p class="p-2 text-xs text-red-500">Gagal memuat model 3D.</p>';
});
