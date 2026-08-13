/**
 * Navigasi dan interaksi di dalam scene: teleport, hover, genggam, gerak bebas.
 */
import * as THREE from "three";

/** Short vibration on the controller that triggered the action; no-op without haptics. */
export function pulse(controller, intensity, milliseconds) {
    controller?.userData.gamepad?.hapticActuators?.[0]?.pulse(intensity, milliseconds);
}

// Point-and-teleport: reticle on the ground, from XR controller ray or gaze center.
// ponytail: raycasts ground only — reticle can show through the model; add occluder check in Fase 2 if it bothers users.
export class TeleportControls {
    static SCREEN_CENTER = new THREE.Vector2(0, 0);
    static UP = new THREE.Vector3(0, 1, 0);
    static tempMatrix = new THREE.Matrix4();
    static tempVector = new THREE.Vector3();
    /** Gaze must rest this long before it counts as looking at something, not sweeping past. */
    static GAZE_DWELL_MS = 500;
    /** Gerak bebas thumbstick: m/detik, dan besar satu langkah putar. */
    static KECEPATAN_JALAN = 2;
    static SUDUT_PUTAR = Math.PI / 6;
    /** Siluet tujuan potongan puzzle. Satu material dipakai bersama — hantunya tidak pernah berbeda. */
    static GHOST_MATERIAL = new THREE.MeshBasicMaterial({
        color: 0xfbbf24,
        transparent: true,
        opacity: 0.28,
        depthWrite: false,
        toneMapped: false,
        side: THREE.DoubleSide,
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
        // Jumlah potongan puzzle dihitung dari record DB lewat registerPuzzle(), bukan
        // dari mesh penanda yang terpasang di scene. Di HP potongan sengaja tidak
        // dilepas, dan kalau hitungannya ikut nol, alasan fase interaksi terlewat akan
        // berbalik dari "dilewati_perangkat" jadi "dilewati_tanpa_slot" — ekspor
        // penelitiannya jadi berbohong soal kenapa responden tidak pernah memasang.
        this.totalPuzzle = 0;
        this.solvedCount = 0;
        this.interactiveMeshes = [];
        this.controllers = [];
        this.putarSiap = true;
        this.logger = null;
        // Fase melacak dan memandu, tidak pernah mengunci — lihat vr-phases.js.
        // Jangan menambahkan pemeriksaan fase yang memblokir teleport/panel/genggam.
        this.phases = null;
        this.phasePanel = null;
        // Diisi jalur headset saja; di HP jalan keluarnya tombol DOM "✕ Selesai".
        this.exitButton = null;
        this.menunjukKeluar = false;
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
        this.cursor.visible = !this.controller?.userData.connected;

        if (this.controller?.userData.connected) {
            TeleportControls.tempMatrix.identity().extractRotation(this.controller.matrixWorld);
            this.raycaster.ray.origin.setFromMatrixPosition(this.controller.matrixWorld);
            this.raycaster.ray.direction.set(0, 0, -1).applyMatrix4(TeleportControls.tempMatrix);
        } else {
            this.raycaster.setFromCamera(TeleportControls.SCREEN_CENTER, this.camera);
        }

        // Tombol keluar dites lebih dulu dan mematikan hover museum selama ditunjuk —
        // supaya ia tidak pernah bocor jadi objek_dilihat / panel_dibuka di event log.
        this.menunjukKeluar = this.exitButton?.raycast(this.raycaster) ?? false;
        this.exitButton?.update(this.menunjukKeluar);

        const hits = this.menunjukKeluar
            ? []
            : this.raycaster.intersectObjects(this.targets, true).filter((h) => h.object.visible);

        const first = hits[0];
        this.hoverNode = first ? TeleportControls.findVrNode(first.object) : null;
        this.hoverInfo = this.hoverNode?.userData.vrObject ?? null;
        this.hoverPoint = this.hoverInfo ? first.point : null;

        // Permukaan yang bisa dipijak menang atas panel info. Lantai boleh saja punya
        // record objek, tapi di Cardboard trigger adalah satu-satunya input: kalau panel
        // yang menang, responden tidak akan pernah bisa teleport ke sana dan terkunci di
        // titik spawn. Potongan puzzle dikecualikan — sisi atasnya harus tetap digenggam.
        if (
            this.hoverInfo &&
            !this.hoverNode.userData.slotParent &&
            TeleportControls.menghadapAtas(first)
        ) {
            this.hoverInfo = null;
            this.hoverPoint = null;
        }

        this.pulseHovered();
        this.cursor.material.color.setHex(
            this.hoverInfo || this.menunjukKeluar ? 0xfbbf24 : 0xffffff,
        );
        this.trackGaze();
        if (this.phases) this.phasePanel?.update(this.phases.deskripsi());

        if (this.hoverInfo) {
            this.reticle.visible = false;
            return;
        }

        const hit = hits.find((h) => TeleportControls.menghadapAtas(h));

        this.reticle.visible = Boolean(hit);
        this.reticle.material.color.setHex(0x7c3aed);
        this.reticle.quaternion.identity();
        if (hit) {
            this.reticle.position.copy(hit.point);
            this.reticle.position.y += 0.01;
            this.reticle.scale.setScalar(Math.max(1, hit.distance / 6));
        }
    }

    /** Permukaan hasil raycast cukup mendatar untuk dipijak. */
    static menghadapAtas(hit) {
        if (!hit?.face) return false;
        return (
            TeleportControls.tempVector
                .copy(hit.face.normal)
                .transformDirection(hit.object.matrixWorld).y > 0.6
        );
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

    /**
     * Sorotan hanya pada objek yang sedang ditunjuk — berdenyut kuning lembut.
     *
     * Dulu semua objek interaktif menyala permanen; di headset seluruh museum jadi
     * bersinar dan sorotan berhenti berarti apa-apa. Shell outline (geometri
     * diduplikat, dibesarkan 4%, material kuning) juga dibuang: pada aset nyata ia
     * tampak seperti model kembar berwarna, bukan garis tepi.
     */
    pulseHovered() {
        const aktif = this.hoverInfo && !this.hoverNode.userData.solved ? this.hoverNode : null;
        const pulse = 0.35 + 0.25 * Math.sin(performance.now() / 300);
        for (const { node, materials } of this.interactiveMeshes) {
            const intensity = node === aktif ? pulse : 0;
            for (const material of materials) material.emissiveIntensity = intensity;
        }
    }

    /** Clone materials so the glow doesn't leak onto other instances sharing the same material. */
    markInteractive(node) {
        const materials = [];
        node.traverse((child) => {
            if (!child.isMesh) return;
            const wasArray = Array.isArray(child.material);
            const cloned = (wasArray ? child.material : [child.material]).map((m) => {
                if (!("emissive" in m)) return m;
                const clone = m.clone();
                clone.emissive = new THREE.Color(0xfbbf24);
                clone.emissiveIntensity = 0;
                materials.push(clone);
                return clone;
            });
            child.material = wasArray ? cloned : cloned[0];
        });
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

    /**
     * Squeeze/grip: pick up the hovered object; release puts it back in the scene graph.
     *
     * Hanya potongan puzzle (`slotParent`, dipasang oleh registerPuzzle) yang bisa
     * digenggam. Objek informatif tidak punya tempat kembali — `checkSlot` selalu
     * false untuknya, jadi ia tergeletak permanen di mana pun siswa melepasnya, dan
     * objek_digenggam/objek_dilepas-nya menggelembungkan metrik "jumlah percobaan"
     * fase interaksi. Tap/trigger untuk membuka panel info tetap terbuka untuk semua
     * objek — ini bukan penguncian fase.
     */
    grabStart(controller) {
        if (!this.hoverNode?.userData.slotParent) return;
        if (this.hoverNode.userData.solved || controller.userData.grabbedNode) return;
        controller.userData.grabbedNode = this.hoverNode;
        controller.userData.grabbedParent = this.hoverNode.parent;
        controller.attach(this.hoverNode);
        if (this.hoverNode.userData.ghost) this.hoverNode.userData.ghost.visible = true;
        pulse(controller, 0.4, 40);
        this.logger?.log("objek_digenggam", this.hoverNode.name);
    }

    grabEnd(controller) {
        const node = controller.userData.grabbedNode;
        if (!node) return;
        controller.userData.grabbedParent.attach(node);
        if (node.userData.ghost) node.userData.ghost.visible = false;
        controller.userData.grabbedNode = null;
        controller.userData.grabbedParent = null;
        // A release that misses the slot is one failed attempt — that's the "jumlah
        // percobaan" metric, so the outcome has to ride along with the event.
        const berhasil = this.checkSlot(node, controller);
        this.logger?.log("objek_dilepas", node.name, { berhasil });
    }

    /**
     * Daftarkan sebuah mesh sebagai potongan puzzle.
     *
     * Posisi terpasangnya adalah transform bawaannya di GLB — model selalu diekspor
     * dalam keadaan SUDAH TERPASANG, jadi tidak ada mesh penanda dan tidak ada
     * koordinat target yang perlu disimpan. Yang datang dari DB hanya `delta`:
     * selisih ke tempat potongan itu mulai terlepas.
     *
     * @param {THREE.Object3D} node
     * @param {number[]} delta [x, y, z] di ruang parent-local mesh
     * @param {boolean} bolehDilepas false di perangkat yang tidak bisa menggenggam
     */
    registerPuzzle(node, delta, bolehDilepas) {
        // Dikloning SEBELUM userData diisi: Object3D.copy() menyalin userData lewat
        // JSON, dan slotParent adalah referensi Object3D yang membuatnya melingkar.
        const ghost = bolehDilepas ? TeleportControls.buatHantu(node) : null;

        node.userData.slotPosition = node.position.clone();
        node.userData.slotQuaternion = node.quaternion.clone();
        node.userData.slotParent = node.parent;
        this.totalPuzzle++;

        // Perangkat tanpa genggam tidak akan pernah bisa memasangnya kembali. Biarkan
        // terpasang supaya punden tampil utuh, bukan potongan melayang yang tak
        // terjangkau selamanya. Sengaja TIDAK ditandai solved: kedipannya tetap perlu,
        // objeknya memang masih interaktif di HP (nama, deskripsi, audio).
        if (bolehDilepas) {
            // Hantu menempati transform bawaan; potongan aslinya yang bergeser.
            node.userData.ghost = ghost;
            node.parent.add(ghost);
            node.position.add(new THREE.Vector3().fromArray(delta));
        }
    }

    /**
     * Siluet tembus pandang potongan di posisi terpasangnya, muncul hanya selama
     * digenggam. Tanpanya radius snap 0,5 m tidak terlihat sama sekali dan siswa
     * memegang batu tanpa tahu ke mana — penyebab macet nomor satu (§8 A1).
     *
     * Memandu, tidak mengunci: murni visual, tidak ada aturan yang bergantung padanya.
     * `raycast` dimatikan di tiap mesh supaya ia tidak pernah jadi hoverNode dan
     * mencemari event log dengan objek_dilihat / panel_dibuka palsu.
     */
    static buatHantu(node) {
        const ghost = node.clone();
        ghost.traverse((child) => {
            if (!child.isMesh) return;
            child.material = TeleportControls.GHOST_MATERIAL;
            child.raycast = () => {};
        });
        ghost.visible = false;
        return ghost;
    }

    /** Puzzle: released piece within reach of its slot snaps in place and counts as solved. */
    checkSlot(node, controller) {
        const slotParent = node.userData.slotParent;
        if (!slotParent || node.userData.solved) return false;

        const nodePos = node.getWorldPosition(new THREE.Vector3());
        // Jarak diukur di ruang dunia supaya 0,5 m tetap berarti 0,5 m walau rantai
        // parent-nya berskala.
        const slotPos = slotParent.localToWorld(node.userData.slotPosition.clone());
        // ponytail: 0.5m snap radius, single knob; make per-object if pieces vary wildly in size.
        if (nodePos.distanceTo(slotPos) > 0.5) return false;

        slotParent.attach(node);
        node.position.copy(node.userData.slotPosition);
        node.quaternion.copy(node.userData.slotQuaternion);
        node.userData.solved = true;
        this.solvedCount++;
        pulse(controller, 1, 120);
        this.logger?.log("puzzle_benar", node.name, { urutan: this.solvedCount });
        this.phases?.catatPemasangan(this.solvedCount);

        const done = this.solvedCount >= this.totalPuzzle;
        this.panel?.show({
            nama: done ? "Puzzle Selesai! 🎉" : "Tepat!",
            deskripsi: done
                ? "Semua objek sudah kembali ke tempat yang benar. Kerja bagus!"
                : `${node.userData.vrObject.nama} sudah di tempat yang benar. (${this.solvedCount}/${this.totalPuzzle})`,
        }, slotPos);

        return true;
    }

    /** Single entry point for trigger/tap: keluar > close panel > open panel > teleport. */
    trigger() {
        if (this.menunjukKeluar) {
            this.exitButton.tekan();
            return;
        }
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

    /**
     * Gerak bebas dengan thumbstick, mendampingi teleport (bukan menggantikannya).
     *
     * Stik kiri berjalan searah pandangan, stik kanan memutar per langkah 30°.
     * Putar bertahap, bukan mulus: rotasi mulus adalah penyebab motion sickness
     * nomor satu di VR, dan respondennya siswa yang baru pertama pakai headset.
     *
     * ponytail: tanpa collision — lantai datar dan museumnya terbuka, jadi siswa
     * hanya bisa menembus objek pajangan. Tambahkan raycast dinding kalau scene
     * berikutnya punya ruangan.
     */
    gerakBebas(delta) {
        for (const controller of this.controllers) {
            const axes = controller.userData.connected && controller.userData.gamepad?.axes;
            if (!axes) continue;
            // axes[0..1] = touchpad, axes[2..3] = thumbstick pada profil Quest/Touch.
            const x = axes[2] ?? axes[0] ?? 0;
            const y = axes[3] ?? axes[1] ?? 0;

            if (controller.userData.handedness === "right") {
                if (Math.abs(x) < 0.7) {
                    this.putarSiap = true;
                    continue;
                }
                if (!this.putarSiap) continue;
                this.putarSiap = false;
                this.putarRig(-Math.sign(x) * TeleportControls.SUDUT_PUTAR);
                continue;
            }

            if (Math.hypot(x, y) < 0.15) continue;
            const arah = new THREE.Vector3(x, 0, y)
                .applyQuaternion(this.camera.getWorldQuaternion(new THREE.Quaternion()));
            arah.y = 0;
            if (arah.lengthSq() < 1e-6) continue;
            this.rig.position.addScaledVector(
                arah.normalize(),
                TeleportControls.KECEPATAN_JALAN * delta * Math.min(1, Math.hypot(x, y)),
            );
        }
    }

    /** Putar rig mengelilingi kepala, bukan titik asalnya — kalau tidak, siswa terlempar menyamping. */
    putarRig(sudut) {
        const kepala = this.camera.getWorldPosition(new THREE.Vector3());
        this.rig.position.sub(kepala).applyAxisAngle(TeleportControls.UP, sudut).add(kepala);
        this.rig.rotation.y += sudut;
    }
}
