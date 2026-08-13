# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Virtual Living Museum** is an AR-enhanced heritage education platform built with Laravel 11 (PHP 8.2+). It provides gamified e-learning (Pre-test → E-book → Virtual Museum → Post-test), dual AR experiences (marker-based + WebXR), and heritage site mapping.

---

## Commands

```bash
# Setup
composer install && npm install
php artisan key:generate
php artisan migrate --seed           # Fresh db with seed data
php artisan migrate:fresh --seed      # Rebuild everything

# Development
composer run dev                     # Laravel server + Vite (concurrent)

# Production build
npm run build
php artisan storage:link

# Testing
php artisan test                      # Pest PHP
npm run test:js                       # node --test, logika JS murni (tests/js/)
                                      # WAJIB dari shell WSL — lihat catatan di bawah

# Linting
./vendor/bin/pint                     # Laravel Pint (code style)
# Qodana: qodana.yaml (PHP 8.2, threshold: 15 issues)

# Cache
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear
```

---

## Architecture

### Route Middleware Stacks (routes/web.php)

| Stack    | Middleware       | Purpose                                          |
| -------- | ---------------- | ------------------------------------------------ |
| Guest    | `guest`          | Auth flows (login, register, password reset)     |
| User     | `auth` + `user`  | E-learning, AR, maps, reports                    |
| Admin    | `auth` + `admin` | Content management dashboard                     |
| AR Token | `ar.token`       | Stateless HMAC auth for AR routes (cross-device) |

### Progressive Learning Flow

User progress tracked via `User::$level_sekarang` and `$progress_level_sekarang`:

1. `PRE_TEST (1)` → Complete pre-test → unlocks EBOOK
2. `EBOOK (2)` → Finish reading → unlocks VIRTUAL_LIVING_MUSEUM
3. `VIRTUAL_LIVING_MUSEUM (3)` → Visit 3D museum → unlocks POST_TEST
4. `POST_TEST (4)` → Complete → advance to next `Materi` level

Key methods: `User::incrementLevel()`, `User::incrementProgressLevel()`, `Materi::shouldIncrementProgress()`

### Dual AR Implementation

1. **Marker-Based (AR.js + A-Frame)**: Pattern files in `/storage/{path_patt}`, touch gestures via `public/js/gesture-detector.js` / `gesture-handler.js`
2. **WebXR (Three.js)**: Class-based architecture in `public/assets/js/ar-museum-3.js` — `SceneManager`, `RendererManager`, `ModelLoader`; DRACO compression, HDRI skybox, PCFSoftShadowMap

AR code in `/public/assets/js/` is served directly (not bundled via Vite). Heavy libs (Three.js, A-Frame, PDF.js) loaded via CDN.

### Token Authentication

`app/Helper/TokenHelper.php` generates HMAC-SHA256 tokens for stateless AR access:

```php
TokenHelper::generate($userId, $expiryMinutes);
TokenHelper::verify($token); // returns userId or false
```

---

## Database Conventions

**Custom primary keys** — always `{table_singular}_id` (NOT standard `id`):

```php
$table->id('materi_id');                        // Primary key
$table->foreignId('materi_id')->constrained('materi', 'materi_id')->onDelete('cascade');
```

**Always `onDelete('cascade')`** — no soft deletes in this project.

**Composite uniques** on pivot tables to prevent duplicates:

```php
$table->unique(['user_id', 'situs_id']);
```

**Indonesian naming** for all tables/columns: `situs_peninggalan`, `pertanyaan`, `jawaban_benar`, `jawaban_benar` enum `['A','B','C','D']`.

**Geo-coordinates**: `decimal('lat', 10, 8)`, `decimal('lng', 11, 8)`.

**Timestamp strategies** vary by table — some use `$table->timestamps()`, others use manual `->useCurrent()` only. Always check the model for `$timestamps`.

---

## Key Models

| Model                  | Key Fields / Methods                                                                        |
| ---------------------- | ------------------------------------------------------------------------------------------- |
| `User`                 | `level_sekarang`, `progress_level_sekarang`, `incrementLevel()`, `incrementProgressLevel()` |
| `Materi`               | `materi_id` PK, `shouldIncrementProgress()`, `getLinearLevel()`, `orderedMateriIds()`       |
| `Pretest` / `Posttest` | `materi_id` FK, `pertanyaan`, `pilihan_a/b/c/d`, `jawaban_benar`                            |
| `JawabanUser`          | Pivot: `user_id`, `materi_id`, `jenis` ('pretest'/'posttest'), `benar`, `poin`              |
| `SitusPeninggalan`     | `situs_id` PK, `lat`, `lng`, `path_patt`, `path_obj`                                        |
| `VirtualMuseum`        | `museum_id` PK, `situs_id` FK, `path_obj` for 3D scenes                                     |

---

## Demo Mode

Toggle via `.env`:

```env
APP_DEMO_MODE=true
```

When enabled:

- All materi are accessible (no "Terkunci" locks on materi cards or tabs)
- All tabs within a materi (pre-test, ebook, museum, post-test) are open
- **No progress is tracked** — `incrementProgressLevel()` calls are skipped across all controllers
- User can explore all content freely without affecting their `level_sekarang` or `progress_level_sekarang`

Use case: shareable demo accounts where anyone can browse all content without a linear progression gate.

### Files that respect `APP_DEMO_MODE`

| File                                                 | Behavior                                              |
| ---------------------------------------------------- | ----------------------------------------------------- |
| `HomeController::elearningMateri()`                  | All tab availability flags set to open                |
| `HomeController::elearningList()` / `elearningEra()` | All materi marked `is_available = true`               |
| `HomeController::submitPretest()`                    | Skips `incrementProgressLevel()`                      |
| `HomeController::submitPosttest()`                   | Skips `incrementProgressLevel()`                      |
| `HomeController::markEbookRead()`                    | Skips progress increment                              |
| `HomeController::arMuseum()`                         | Skips museum visit progress tracking                  |
| `Materi::shouldIncrementProgress()`                  | Returns `true` (safety net for any future call sites) |

Three separate bundles in `vite.config.js`:

- `resources/css/app.css` — Tailwind
- `resources/js/app.js` — Alpine.js + utilities
- `resources/js/ebook.js` — PDF.js + page-flip flipbook (standalone)

---

## Common Pitfalls

- ❌ Using `id()` instead of `id('materi_id')` in migrations — models expect custom PK names
- ❌ Forgetting `onDelete('cascade')` on foreign keys
- ❌ Missing composite unique constraints on pivot tables
- ❌ Bundling AR modules with Vite — keep in `/public/assets/js/`
- ❌ English naming in DB — use Indonesian throughout
- ❌ Assuming soft deletes exist — all deletions are permanent

---

## VR Puzzle Mechanic & Visual Editor

Built on top of the existing `mesh_name` scene-graph linking (`VirtualMuseumObject.mesh_name` ↔ node name in the GLB). Not yet committed — pending explicit request.

### `posisi_awal` (place-object puzzle)

**Model selalu diekspor dalam keadaan SUDAH TERPASANG.** Editor yang melepas.

`VirtualMuseumObject.posisi_awal` — kolom JSON nullable, cast `array`, berisi
`[x, y, z]`:

- Kosong → objek informatif biasa: tap/trigger membuka panel info.
- Terisi → objek jadi **potongan puzzle** yang bisa digenggam. Nilainya adalah
  **selisih** ke tempat potongan itu mulai terlepas.

Tempat terpasangnya adalah **transform bawaan mesh di GLB** — nol data, tidak ada
mesh penanda, tidak bisa meleset. Rotasi terpasang juga rotasi bawaan, karena itu
tidak ada kolom rotasi sama sekali.

**Selisih, bukan koordinat absolut — jangan diubah.** `vr-editor.js` dan
`vr-museum.js` menaruh model pada tinggi berbeda (`placeModel` menggeser
`position.y -= box.min.y`, editor meniru itu tapi keduanya bisa berubah sendiri).
Koordinat absolut yang disimpan dari editor akan mendarat meleset di headset tanpa
error apa pun. Selisih kebal terhadap transform root mana pun, dan tetap benar
setelah model diekspor ulang dengan origin sama.

Runtime di `TeleportControls`: `registerPuzzle()` menyimpan posisi/rotasi/parent
bawaan ke `userData` lalu menerapkan selisihnya; `grabStart`/`grabEnd`/`checkSlot`
sisanya. Snap 0,5 m diukur di ruang dunia.

`slot_mesh_name` **dihapus** — mekanisme lama yang butuh mesh penanda tak terlihat
di GLB. Nol dari 30 baris memakainya, jadi penggantiannya bersih. Jangan
menghidupkannya kembali berdampingan.

#### Hitung potongan dari record, bukan dari scene

`TeleportControls.totalPuzzle` naik di `registerPuzzle()`, yang dipanggil untuk
setiap record ber-`posisi_awal` — termasuk di HP, di mana potongannya sengaja
**tidak** digeser (punden tampil utuh, bukan motif melayang yang tak terjangkau
selamanya; panel info menjelaskannya).

Kalau hitungannya ikut nol di HP, alasan fase interaksi terlewat berbalik dari
`dilewati_perangkat` jadi `dilewati_tanpa_slot`, dan ekspor penelitian berbohong
soal kenapa responden tidak pernah memasang. Empat tempat memakai angka ini:
`totalSlot` fase, detail `sesi_mulai`, kondisi "Puzzle Selesai", dan teks
`(n/total)`.

Potongan di HP sengaja **tidak** ditandai `solved` — kedipannya masih perlu,
objeknya memang tetap interaktif di sana (nama, deskripsi, audio).

### Visual 3D Editor

`GET /admin/virtual-museum/{museum_id}/editor` (`AdminController::editorVirtualMuseum`) — an alternative to the plain text-field admin forms for assigning `mesh_name`/`posisi_awal`/`nama`/`deskripsi`/`nilai_karakter` to GLB nodes.

- View: `resources/views/admin/virtual-museum/editor.blade.php` — standalone full-screen page (not `x-app-layout`), Three.js via CDN importmap (same pattern as the AR/VR runtime, not bundled through Vite).
- Logic: `public/assets/js/vr-editor.js` — loads the GLB, renders a mesh tree (left), 3D canvas with click-to-select + orbit controls (middle), property panel (right).
- Save endpoint: `POST /admin/virtual-museum/{museum_id}/editor/objects` (`AdminController::editorSaveObject`) — `updateOrCreate` keyed by `(museum_id, mesh_name)`, so re-saving the same mesh never duplicates rows.
- Entry point: purple "Editor VR" button on `resources/views/admin/virtual-museum/show.blade.php`.

#### Menetapkan posisi lepas

`TransformControls` mode translate saja, dari prefix `three/jsm/` yang sudah ada
di importmap. `dragging-changed` mematikan OrbitControls selama menyeret, dan
`gizmo.dragging` menahan raycast pilih supaya sentakan pendek di panah tidak
terbaca sebagai klik.

Rotasi sengaja tidak bisa diatur — lihat di atas. Kalau suatu saat perlu,
`setMode("rotate")` satu baris dan satu kunci JSON tambahan.

Editor meniru pergeseran `y -= box.min.y` milik runtime supaya lantai ada di
`y = 0`. Selisihnya memang kebal terhadap ini; yang butuh acuan itu adalah
peringatan tinggi jangkauan.

**Dua peringatan jarak** (`JARAK_PERINGATAN` 8 m mendatar, `TINGGI_JANGKAUAN`
2,2 m di atas lantai). Bukan pembatas. Alasannya: `grabStart` memakai
`controller.attach()` yang mempertahankan transform dunia, jadi potongan yang
mulai jauh tetap jauh saat digenggam — siswa harus teleport ke sana dulu, dan
teleport butuh lantai. Potongan di atas jangkauan tangan tidak akan pernah bisa
dibawa masuk ke radius snap. Dulu jarak ini dijaga mata manusia di Blender;
sekarang kanvas editor satu-satunya penjaga.

#### Dua peringatan data basi

Keduanya **hanya di editor** — runtime sengaja diam supaya siswa tidak pernah
melihat pesan admin, dan record yang mesh-nya hilang cukup diabaikan.

1. **Node hilang** — `mesh_name` di DB tidak ada di GLB. Menangkap rename, hapus,
   dan jebakan sufiks `.001` Blender.
2. **Model berubah setelah posisi disimpan** — `model_mtime` (filemtime GLB saat
   menyimpan) dibandingkan dengan filemtime sekarang. Nama node sama dengan
   geometri berbeda tidak bisa dideteksi umum; ini aproksimasinya. Positif palsu
   saat berkas sama diunggah ulang tidak apa-apa — itu justru saat yang tepat
   untuk memeriksa.

`model_mtime` hanya diisi kalau `posisi_awal` ada. Objek informatif tidak
menyimpan koordinat, jadi tidak ada yang bisa basi.

### Susunan berkas runtime VR (`public/assets/js/`)

Dulu satu `vr-museum.js` 1200 baris. Sekarang tujuh modul, semuanya tetap di
`public/assets/js/` dan **didaftarkan di importmap sebagai bare specifier** (blade
`guest/vr/museum.blade.php` melakukannya lewat satu array `$modulVr`). Jangan
memindahkannya ke Vite dan jangan mengimpornya lewat path relatif — impor relatif
tidak ikut cache-busting `?v={filemtime}`, jadi headset menyajikan versi basi setelah
salah satu modul disunting.

| Berkas           | Isi                                                         | Impor Three? |
| ---------------- | ----------------------------------------------------------- | ------------ |
| `vr-museum.js`   | scene, cahaya, model, jalur headset, `main()`                | ya           |
| `vr-controls.js` | `TeleportControls`, `pulse()`                                | ya           |
| `vr-panels.js`   | `InfoPanel`, `PhasePanel`, `ExitButton`, `ControllerHints`, `wrapText` | ya |
| `vr-hp.js`       | `DeviceOrientationControls`, jalur stereo HP                 | ya           |
| `vr-events.js`   | `EventLogger`                                                | **tidak**    |
| `vr-phases.js`   | `PhaseManager`, state 4 fase                                 | **tidak**    |
| `vr-responden.js`| deret `kode_responden` mode kiosk                            | **tidak**    |
| `vr-petunjuk.js` | pilih objek belum-diamati terdekat + sudut penanda           | **tidak**    |
| `vr-sesi.js`     | panel penutup sesi, pergantian responden (DOM murni)         | **tidak**    |
| `vr-pandangan.js`| titik berdiri + yaw rig pembuka, dari bounding box model      | **tidak**    |

Berkas tanpa Three.js diuji `npm run test:js`. `EventLogger` punya `start()`
terpisah dari constructor justru untuk itu: constructor hanya menyiapkan state,
`start()` yang memasang `setInterval` dan pengait `pagehide`, jadi tes bisa membuat
logger tanpa meninggalkan timer hidup yang menahan proses node. `flush()` menerima
pengirim opsional (`send`) yang di produksi selalu `navigator.sendBeacon`.

### VR interaction affordances

Iterating on "how does the user know what's interactive" — in order of what was tried:

1. **Sorotan hanya saat ditunjuk** (`TeleportControls.pulseHovered`) — `markInteractive` mengkloning material per-instance (supaya sorotan tidak bocor ke mesh lain yang sematerial) dan menyetel `emissive` kuning dengan `emissiveIntensity = 0`; hanya node yang sedang di-hover yang berdenyut. **Dua pendekatan sebelumnya sudah dicoba dan ditolak di headset nyata, jangan dihidupkan lagi:** (a) glow permanen di semua objek interaktif — seluruh museum bersinar dan sorotan berhenti berarti apa-apa; (b) outline shell (geometri diduplikat, dibesarkan 4%, material `BackSide` kuning) — di aset nyata ia terbaca sebagai model kembar berwarna, bukan garis tepi.
2. **Gerak bebas thumbstick** (`gerakBebas`), mendampingi teleport bukan menggantikannya. Stik kiri berjalan searah pandangan (`KECEPATAN_JALAN` 2 m/s), stik kanan memutar **per langkah 30°** (`SUDUT_PUTAR`) — bertahap, bukan mulus, karena rotasi mulus adalah penyebab motion sickness nomor satu dan respondennya siswa yang baru pertama pakai headset. `putarRig` memutar mengelilingi posisi kepala, bukan titik asal rig, kalau tidak siswa terlempar menyamping. Handedness dibaca dari `event.data.handedness` di handler `connected`. Tidak ada collision — lantai datar dan museum terbuka.
3. **Penanda arah objek belum diamati** (`TeleportControls.updatePetunjuk`, logika murni di `vr-petunjuk.js`) — panah kecil di tepi kursor menunjuk objek belum-diamati **terdekat**, muncul hanya kalau simpangannya dari pusat pandangan > 18°. Perlu karena `AMBANG_PENGAMATAN` = 100%: satu objek terlewat di balik punden menahan siswa di eksplorasi selamanya tanpa error apa pun. Sumber "sudah diamati" adalah `PhaseManager.objekDiamati` yang sama dengan pemicu transisi — jangan membuat pelacakan kedua. Mati sendiri saat `semuaObjekDiamati`, dan karena fase interaksi/refleksi hanya tercapai setelah ambang itu, satu pemeriksaan itu juga yang menahannya muncul di dua fase terakhir. Ditempel ke kamera (bukan DOM: stereo HP hanya memperlihatkannya ke satu mata) dan bukan anggota `targets`, jadi tidak pernah kena raycast atau mencemari event log.
4. **Gaze cursor for phone/no-controller mode**: a small ring mesh parented to the camera (`this.cursor`, not an HTML overlay — an HTML overlay would sit on the seam between the two stereo eyes on phone VR). Turns yellow when hovering something interactive. Hidden automatically when a tracked XR controller is connected (controller ray + sorotan hover take over).
5. **Multi-controller active-switching bug fix**: `TeleportControls.controller` (the one driving hover raycasts) used to lock to whichever controller connected first and never change. Fixed so `select` *and* `squeezestart` from either hand promote it to active, with a forced `teleport.update()` before `grabStart()` so the just-activated hand's own hover state (not the previous hand's) is what gets grabbed.

6. **Controller models**: `XRControllerModelFactory` on `renderer.xr.getControllerGrip(index)` renders the real hardware's controller (Quest Touch, etc.) instead of only a ray line. The factory fetches profiles from `@webxr-input-profiles/assets` on jsDelivr at runtime — allowed by the CSP's `connect-src https:`, so don't be surprised by the network request.
7. **Haptics**: `controller.userData.gamepad` is captured in the `connected` handler; the `pulse()` helper fires a short vibration on grab (0.4 / 40ms) and a stronger one on a correct puzzle snap (1.0 / 120ms). No-op on devices without haptic actuators.

The on-screen debug surfaces (load banner, fullscreen error box, gyro readout) were removed once a real headset was available — a load failure now logs to the console and shows a message in `#loading-container`.

### Nilai karakter (`nilai_karakter`)

`VirtualMuseumObject.nilai_karakter` — kolom JSON nullable, cast `'array'`, berisi daftar
value dari `App\Enums\NilaiKarakter`. Satu objek boleh punya beberapa nilai.

Kosakata enum saat ini **placeholder** (6 dimensi Profil Pelajar Pancasila). Daftar final
harus diambil dari Pardi, Sendratari, Margi (2017) — referensi #1 proposal, meneliti situs
yang sama dengan museum uji. Ganti isi enum saja, tidak ada kode lain yang perlu disentuh.

Jalur lengkapnya: form admin create/edit (checkbox) → editor visual 3D (checkbox di panel
properti) → `HomeController::vrMuseum` mengirimnya lewat `window.vrObjects` → `InfoPanel.draw()`
merendernya jadi chip ungu. Label untuk JS datang dari `window.nilaiKarakterLabels` yang
di-inject di `guest/vr/museum.blade.php`, supaya enum tetap satu-satunya sumber kebenaran.

Karena chip butuh ruang, `wrapText` deskripsi turun dari 8 ke 6 baris. Kalau daftar nilai
final jauh lebih panjang, batasi tampilan ke 3 nilai teratas per objek.

### Alur 4 fase (`vr-phases.js`)

`orientasi → eksplorasi → interaksi → refleksi`, wajib per hal. 22. State hanya hidup
di klien — tidak ada tabel, tidak ada kolom. Fase adalah properti sesi VR yang sedang
berjalan, bukan progres user berjenjang, jadi headset yang mati di tengah sesi membuat
responden berikutnya mulai dari orientasi. Di mode kiosk itu perilaku yang benar.

**Fase melacak dan memandu, tidak pernah mengunci.** Tidak ada kode yang boleh
memblokir teleport, panel, atau genggam berdasarkan fase. Kalau fase mengunci, tiap
pemicu transisi jadi titik macet permanen — punden di museum uji berjarak 4 m dari
titik spawn dan keempat panelnya bisa dibuka sambil berdiri diam, jadi siswa yang tidak
pernah teleport akan tersangkut di orientasi tanpa error apa pun, lalu memanggil
fasilitator. Itu intervensi yang dilarang kriteria TKT 6.

Transisi berbasis pencapaian, bukan timer: teleport pertama → eksplorasi; semua objek
pernah dibuka panelnya (`AMBANG_PENGAMATAN`, kini 100%) → interaksi; semua puzzle
terpasang → refleksi. Fase interaksi dilewati kalau perangkat tanpa controller atau
scene tanpa slot, dan alasannya (`selesai` / `dilewati_perangkat` /
`dilewati_tanpa_slot`) ikut ke event `FaseBerubah` — supaya tim peneliti bisa memisahkan
"responden menyelesaikan interaksi" dari "responden tidak pernah diberi kesempatan".

`PhasePanel` di `vr-museum.js` menempel pada kamera di jarak 1,5 m, tetap tampil redup
lalu menyala 4 detik saat fase berganti. Menempel kamera karena DOM tidak dirender di
dalam sesi WebXR; 1,5 m karena elemen yang terlalu dekat memaksa mata menyilang.

**Modul JS murni didaftarkan di importmap, bukan diimpor lewat path relatif.**
`vr-museum.js` memakai `import { PhaseManager } from "vr-phases"` dan blade memetakan
`"vr-phases"` ke `/assets/js/vr-phases.js?v={filemtime}`. Impor relatif tidak ikut
cache-busting berkas induknya, jadi headset akan menyajikan versi basi setelah aturannya
disunting — dan hard-refresh di browser Quest bukan hal sepele saat responden sudah
antre. Modul murni berikutnya ikuti pola yang sama: satu entri importmap, satu bare
specifier.

### Modul refleksi (`pertanyaan_refleksi` / `jawaban_refleksi`)

Modul kelima blueprint (hal. 11), dijalankan di layar biasa setelah sesi VR.

Skema pretest/posttest **sengaja tidak dipakai ulang** — ia punya `jawaban_benar`
yang selamanya kosong di sini dan akan mengundang salah tafsir bahwa refleksi adalah
kuis bernilai. Yang dipakai ulang hanya konvensinya (PK kustom, cascade, penamaan).

`nilai_karakter` di sini **string tunggal**, sementara di `virtual_museum_object` ia
array JSON. Disengaja: satu objek membawa beberapa nilai, satu pertanyaan menggali
tepat satu nilai supaya jawabannya bisa dianalisis per nilai. Alasannya juga ditulis
di migration-nya.

Penyambungan ke data VR lewat `kode_responden` (utama) dan `sesi_id` (nullable,
tepat). `sesi_id` hanya tersedia saat refleksi dibuka dari perangkat yang sama dengan
sesi VR — jalur HP, lewat tombol "Lanjut ke Refleksi" di panel penutup sesi. Perlu
karena tiap responden mengikuti 2 kegiatan dan `kode_responden` saja akan melebur
keduanya.

Museum tanpa pertanyaan menampilkan penjelasan, bukan halaman kosong — kelas kegagalan
yang sama dengan "scene tanpa slot" di Fase 3. Batas jawaban 2000 karakter,
divalidasi di server, bukan hanya `maxlength` di form.

Setelah simpan: halaman terima kasih netral, bukan daftar materi. Di mode kiosk semua
responden berbagi satu akun, jadi daftar materi akan membocorkan progres akun bersama
itu ke setiap siswa. Jangan merantainya ke post-test — langkah berikutnya milik
fasilitator.

Ekspor di `RefleksiController::export()`, bukan menumpang `VrEventController`.

### Mode kiosk & pengantaran `kode_responden`

Satu-satunya tempat yang mengirimkan `?kode=` adalah **peluncur fasilitator**,
`GET /vr/peluncur/{museum_id}`. Ia menghasilkan QR/tautan berisi
`?arToken=…&kode=…&kode_akhir=…&kiosk=1`. Token dibuat **hanya untuk akun yang sedang
login** — jangan pernah menambahkan parameter `user_id`, itu akan mengubahnya jadi
mesin pembuat sesi untuk akun mana pun. TTL 30 menit karena fasilitator perlu waktu
berjalan dari laptop ke headset.

Responden kedua dan seterusnya tidak memindai ulang: sesi login sudah terbentuk, jadi
mereka hanya butuh `?kode=` baru. Tombol **"Responden berikutnya"** di panel penutup
sesi memuat ulang halaman dengan kode berikutnya — satu ketukan kalau peluncur mengirim
`kode_akhir` (deret berurutan), atau satu kolom ketik kalau kodenya tidak berurutan.
Logika deretnya di `vr-responden.js`, diuji `npm run test:js`.

**Panel penutup tidak bisa ditutup di mode kiosk.** Itu gerbang integritas data:
sesi berikutnya tidak boleh dimulai sebelum kodenya ditangani, karena dua responden
yang tergabung di satu kode adalah kesalahan diam-diam — lebih buruk daripada kode
kosong yang setidaknya jujur. Ini **tidak** melanggar "fase tidak pernah mengunci"
dari Fase 3: yang itu soal alur belajar siswa di dalam sesi, ini langkah fasilitator
di antara sesi.

`?kiosk=1` juga menyembunyikan navigasi aplikasi di halaman VR, dan **harus ikut
terbawa** setiap kali halaman dimuat ulang untuk responden berikutnya.

#### `?kiosk=1` bukan pengamanan

Ia **pengurang kesasar, bukan kunci**. Browser Quest punya bilah alamatnya sendiri
yang tidak bisa disembunyikan halaman web, jadi siswa yang berniat tetap bisa mengetik
URL lain dan masuk ke aplikasi memakai akun yang sedang login.

**Syarat operasional untuk Fase 7:** jalankan uji lapangan dengan **akun kiosk khusus**
yang tidak punya progres, rapor, atau data pribadi untuk dilihat — bukan akun
fasilitator sungguhan. Itu pengamanan yang sebenarnya; `?kiosk=1` hanya merapikan.

### Jalur keluar sesi VR

Sebelum Fase 4, jalur HP **tidak punya jalan keluar sama sekali**: tombol masuk
dihapus, fullscreen tidak pernah dilepas, dan render loop stereo berjalan selamanya.
Sekarang ada tombol "✕ Selesai" di kiri atas (bukan tengah, supaya tidak jatuh di
jahitan dua mata) yang membatalkan RAF, keluar fullscreen, mengembalikan render mono,
lalu memunculkan `showPostSessionPanel()`.

Di headset, jalan keluarnya **`ExitButton`, mesh di dalam scene** (`vr-panels.js`),
memanggil `renderer.xr.getSession().end()`. Perlu digambar di scene karena `VRButton`
dan tombol "✕ Selesai" sama-sama DOM, dan DOM tidak dirender di dalam sesi
`immersive-vr` — tanpa ini satu-satunya jalan keluar adalah tombol sistem Meta, jadi
fasilitator harus meraih controller siswa tiap pergantian responden, puluhan kali per
sesi kiosk. Berakhirnya sesi tetap lewat handler `sessionend` milik `renderer.xr`,
satu muara untuk semua cara keluar.

Dua hal yang menjaga tombol itu tidak merusak apa pun:

- **Tidak boleh tersenggol.** Diletakkan di kanan-bawah, cermin `PhasePanel` di
  kiri-bawah, jadi harus ditunjuk dengan sengaja — plus konfirmasi tekan-dua-kali
  dalam 4 detik. Satu siswa yang keluar tak sengaja berarti satu responden hilang
  datanya, dan itu tidak bisa diulang setelah ia pulang.
- **Tidak boleh bocor ke event log.** `TeleportControls.update()` menguji tombol lebih
  dulu; selama ia ditunjuk, raycast target museum dilewati sepenuhnya sehingga
  `hoverNode` null dan tidak ada `objek_dilihat` / `panel_dibuka` yang tercatat.

Ini **tidak** melanggar "fase tidak pernah mengunci" dan bukan pula pengganti tombol
DOM di HP: di HP tombol "✕ Selesai" sudah benar, karena mode stereo HP bukan sesi
WebXR.

Panel penutup itu HTML biasa dan itu benar, tapi alasannya bukan yang paling jelas — baca ini
sebelum menaruh UI lain di DOM. Panel dibangun di JS, bukan blade, karena `sesi_id`
lahir dari `crypto.randomUUID()` di klien.

#### DOM di mode stereo HP: terlihat, tapi selalu monokular

**Mode stereo HP bukan sesi WebXR** — hanya halaman dengan kanvas terbagi dua, jadi DOM
memang tetap dirender. Aturan "DOM tidak dirender" hanya berlaku untuk `immersive-vr`
di headset.

**Tapi jangan menyimpulkan DOM aman dipakai selama sesi stereo.** Di layout
berdampingan, separuh kiri layar adalah mata kiri dan separuh kanan adalah mata kanan.
Elemen DOM di posisi mana pun hanya jatuh di salah satu separuh, jadi **hanya satu mata
yang melihatnya** — hasilnya rivalitas binokular. Tidak ada "zona aman" di kiri atas
atau di mana pun; tengah hanya mengganti masalahnya jadi terbelah dua.

Aturannya:

- DOM hanya untuk hal yang dilihat saat **ponsel sudah dikeluarkan dari viewer** —
  tombol "✕ Selesai", panel penutup sesi, tombol masuk. Siswa tidak bisa menyentuh layar
  selama ponsel di dalam viewer, jadi ia memang melihat layar utuh secara normal.
- Apa pun yang harus terbaca **selama sesi berlangsung** wajib digambar di dalam scene,
  seperti `InfoPanel`, `PhasePanel`, dan kursor pandangan. Kalau butuh DOM di tengah
  sesi, gandakan elemennya di kedua separuh — atau lebih baik, jangan.

### Runtime event logging (`vr_event`)

Wajib per proposal hal. 22, sumber bukti luaran wajib #1. Satu tabel `vr_event`, jenis
event dikunci `App\Enums\JenisEventVr`. Sengaja tidak ada tabel sesi terpisah — semua
metrik sesi bisa diturunkan dari baris event, dan sesi yang mati mendadak terbaca dari
tidak adanya event `SesiSelesai`.

Dua hal yang **jangan diubah tanpa membaca alasannya**:

- **`offset_ms`, bukan timestamp server.** Event dikirim batch, jadi `created_at` server
  akan memberi waktu identik untuk semua event dalam satu batch dan menghancurkan
  time-on-task. Klien mengukurnya dengan `performance.now()` yang monoton — koreksi jam
  NTP di tengah sesi tidak bisa menghasilkan durasi negatif.
- **`kode_responden`.** Mode kiosk (Fase 6) membuat ratusan responden masuk lewat satu
  akun, jadi `user_id` berhenti membedakan siapa pun. Tanpa kolom ini data sesi tidak
  bisa disilangkan dengan angket dan pretest, dan itu tidak bisa direkonstruksi setelah
  responden pulang. Diisi dari query string `?kode=` saat sesi dimulai.

Pengiriman lewat `navigator.sendBeacon()` — `fetch()` biasa dibatalkan browser saat
halaman ditutup, justru saat event penutup sesi paling dibutuhkan. `sendBeacon` tidak
bisa menyetel header, jadi token CSRF ikut di body sebagai `_token`; Laravel memeriksa
keduanya, tidak ada rute yang dikecualikan dari CSRF.

`EventLogger` di `vr-museum.js` menyangga dan mengirim tiap 15 detik / 20 event / saat
`pagehide`. `JenisEventVr::FaseBerubah` sudah didefinisikan tapi belum dipanggil —
Fase 3 (state machine) tinggal memakainya tanpa migrasi baru.

Ekspor: `GET /admin/vr-events/export`, CSV stream baris mentah. Kolom `perangkat`
diresolusi sekali per sesi dari detail `SesiMulai` lalu diisi ke setiap baris.

### Penyimpangan spesifikasi yang disengaja: physics simulation

Proposal hal. 22 menyebut "real-time 3D engine yang mendukung scene rendering, *physics
simulation*, dan event-driven interaction". Physics **tidak diimplementasikan** — tidak ada
rigidbody, gravitasi, atau collision; objek yang dilepas di VR diam di udara, dan snap puzzle
murni pengecekan jarak 0.5 m.

Keputusan sadar tim: snap berbasis jarak sudah memenuhi nilai pedagogis manipulasi artefak,
sementara physics penuh menambah risiko performa di perangkat mobile tanpa kontribusi ke
capaian pembelajaran. Jangan "perbaiki" ini tanpa membaca `docs/rencana-pengembangan.md`.

### Rencana pengembangan

Urutan fase, status modul, dan keputusan desain menuju TKT 6 ada di
`docs/rencana-pengembangan.md`. Baca itu sebelum mengusulkan pekerjaan baru.

### Infra: tunnel/proxy + CSP

`bootstrap/app.php` calls `$middleware->trustProxies(at: '*')`. Needed because `SecurityHeaders`'s CSP `connect-src` is `'self' https: blob:...` — `'self'` needs exact scheme match and `https:` doesn't cover `http://`. Without trusting the tunnel's `X-Forwarded-Proto`, Laravel generated `http://` URLs even though the page loaded over `https://`, so `fetch()` calls from `vr-editor.js` were blocked by CSP. Don't relax the CSP itself to work around this class of bug — fix proxy trust instead.

### Testing

- `tests/Feature/Admin/VirtualMuseumObjectTest.php` — `describe('VR Editor', ...)` covers the editor page (admin-only) and the save endpoint (create, update-by-mesh_name without duplicating, validation).
- **Dua perkakas tes, pembagiannya disengaja.** `php artisan test` (Pest) untuk PHP.
  `npm run test:js` (`node --test`, nol dependensi baru) untuk logika JS murni di
  `tests/js/`. Pembatasnya: apa pun yang butuh Three.js, canvas, atau WebXR tidak bisa
  ditest — verifikasi manual di headset. Logika yang bisa dinyatakan sebagai fungsi
  murni **dipisahkan ke berkas tanpa impor Three.js** supaya bisa ditest; `vr-phases.js`,
  `vr-responden.js`, dan `vr-events.js` adalah contoh dan polanya. Kalau menambah logika
  VR yang tidak menggambar apa pun, ikuti pola itu, jangan menaruhnya di `vr-museum.js`.
- **`npm run test:js` wajib dijalankan dari shell WSL dengan node WSL**, sama seperti
  `php`/`composer`/`artisan`. Sebagian terminal di mesin ini mewarisi npm Windows dari
  `/mnt/c/Program Files/nodejs/`, yang berjalan lewat interop dengan cwd berupa path UNC
  `//wsl.localhost/...` dan tidak bisa menyelesaikan path itu.
  Periksa dengan `command -v node` — harus di bawah `~/.nvm` atau `/usr`, bukan `/mnt/c`.
  Skripnya sengaja menyebut berkas tes satu per satu, bukan glob: glob yang tidak cocok
  membuat `node --test` keluar dengan **kode 0 dan "tests 0"** — lampu hijau palsu yang
  lebih berbahaya daripada tidak ada tes sama sekali. Dengan path eksplisit, node yang
  salah gagal keras dengan exit 1. Menambah berkas tes baru berarti menambahkannya ke
  skrip di `package.json`; itu disengaja.
- Modul VR yang mengimpor Three.js (rendering, raycast, kontrol XR, panel canvas) tidak ditest otomatis — verifikasi manual di headset.
- `tests/Feature/User/VrEntryTest.php` memeriksa importmap halaman VR benar-benar JSON valid dan memuat seluruh modul. Importmap rusak = seluruh sesi VR gagal muat, dan itu tidak terlihat dari tes lain mana pun.
- A real **Meta Quest 2** headset is now available for testing (previously only the Chrome "Immersive Web Emulator" extension, which has known quirks: controller rays don't move until you drag the Pos/Rot sliders in its panel, and select/squeeze must be triggered from its panel buttons, not real hardware). Prefer testing puzzle/outline/controller-switching behavior on the real headset over the emulator going forward.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.21
- laravel/framework (LARAVEL) - v11
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- alpinejs (ALPINEJS) - v3
- prettier (PRETTIER) - v3
- tailwindcss (TAILWINDCSS) - v3

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs

- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function \_\_construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments

- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks

- Add useful array shape type definitions for arrays when appropriate.

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v11 rules ===

## Laravel 11

- Use the `search-docs` tool to get version specific documentation.
- Laravel 11 brought a new streamlined file structure which this project now uses.

### Laravel 11 Structure

- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

### New Artisan Commands

- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class`
    - `php artisan make:interface`

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest

### Testing

- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests

- All tests must be written using Pest. Use `php artisan make:test --pest <name>`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
  <code-snippet name="Basic Pest Test Example" lang="php">
  it('is true', function () {
  expect(true)->toBeTrue();
  });
  </code-snippet>

### Running Tests

- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions

- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
  <code-snippet name="Pest Example Asserting postJson Response" lang="php">
  it('returns all', function () {
  $response = $this->postJson('/api/docs', []);

        $response->assertSuccessful();

    });
    </code-snippet>

### Mocking

- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets

- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>

=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing

- When listing items, use gap utilities for spacing, don't use margins.

      <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
          <div class="flex gap-8">
              <div>Superior</div>
              <div>Michigan</div>
              <div>Erie</div>
          </div>
      </code-snippet>

### Dark Mode

- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v3 rules ===

## Tailwind 3

- Always use Tailwind CSS v3 - verify you're using only classes supported by this version.

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
  </laravel-boost-guidelines>
