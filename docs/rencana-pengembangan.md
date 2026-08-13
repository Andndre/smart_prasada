# SmartPrasada — Rencana Pengembangan menuju TKT 6

Dokumen kerja. Diperbarui 2026-08-10.

Acuan: proposal Hilirisasi Riset Prioritas — Pengujian Model dan Prototipe 2026,
No. Dokumen PRO-251410100722. Ketua: I Wayan Pardi (Undiksha).
Anggaran Rp 149.790.000. Pelaksanaan Juli–Desember 2026.

---

## 1. Target TKT

**TKT 4 → 6.**

Formulir administratif BIMA (hal. 1) menulis "Target Akhir TKT: 5". Seluruh bagian
naratif (hal. 6, 9, 10, 11, 19) serta jadwal (hal. 14) dan luaran wajib #1
menyebut TKT 6. Anggaran juga memuat dua tingkat uji terpisah — uji produk
(50–56 orang) dan uji lapangan (100–106 orang, ~Rp 52 juta) — dan pos uji
lapangan tidak punya guna kalau target berhenti di TKT 5.

Keputusan tim: kerjakan sebagai **target 6**, angka 5 di formulir dianggap salah
isi. Konfirmasi ke ketua peneliti sedang berjalan untuk pencatatan, tidak
memblokir pekerjaan.

Konsekuensi yang mengikat:

- Mode kiosk wajib — alur harus jalan **tanpa intervensi teknis pengembang**
  (hal. 11).
- Data keterpakaian dari pengguna nyata jadi bukti utama.
- Demonstrasi di kelas/lab nyata bersama guru dan siswa.

---

## 2. Arsitektur wajib — 5 modul

Sumber daftar lima modul adalah **hal. 11 (Blueprint Usulan Prototipe)**, bukan
hal. 22. Hal. 22 hanya menyebut empat modul; refleksi di situ muncul sebagai
*fase* dalam state management. Ini penting karena hal. 11 adalah bagian
"Blueprint", dan blueprint adalah **luaran wajib #2** — modul refleksi bukan
sekadar fitur kurang, ia komponen dokumen yang harus diserahkan.

| Modul | Status | Catatan |
|---|---|---|
| VR Rendering Engine | ADA | Nama resmi yang dipakai konsisten di kode dan dokumen. Hal. 9 menyebutnya "VR Environment Engine" — abaikan, pakai satu nama. |
| Interaction Module | **SEPARUH** | Navigasi dan seleksi objek ada. Manipulasi artefak (wajib, hal. 19 & 22) belum pernah tereksekusi — tidak ada satu pun data untuk mengujinya, dan asetnya tidak dirancang untuk itu. |
| Learning Content Management Module | **KOSONG** | Bukan sekadar kurang field nilai karakter. Lihat §3. |
| User Interface Layer | ADA | `InfoPanel` = context-aware overlay non-intrusif di ruang virtual. Sesuai hal. 22. |
| Modul Refleksi | **NOL** | Belum ada sama sekali. |

### Alur operasional wajib

    orientasi → eksplorasi → interaksi → refleksi

dengan state management antar fase (hal. 22). Alur aplikasi sekarang
`pretest → e-book → museum → posttest` — berbeda total. Gap konseptual terbesar.

### Penyimpangan spesifikasi yang disengaja

**Physics simulation** (hal. 22: "scene rendering, *physics simulation*, dan
event-driven interaction") **tidak akan diimplementasikan.**

Alasan: snap berbasis jarak sudah memenuhi nilai pedagogis manipulasi artefak.
Physics penuh menambah risiko performa di perangkat mobile tanpa kontribusi ke
capaian pembelajaran. Dicatat di sini supaya menjadi keputusan sadar, bukan
kelalaian — kalau validator mempersoalkan, alasannya tersedia.

---

## 3. Kondisi konten — jalur kritis proyek

Ini temuan terpenting dari audit, dan ia menggeser prioritas seluruh rencana.

### Angka

    virtual_museum_object  : 30 baris
    punya mesh_name        : 4
    punya slot_mesh_name   : 0   (kolom ini kemudian dihapus, lihat Fase 2c)
    punya path_audio       : 0
    file GLB di repo       : 1

Objek tanpa `mesh_name` disaring keluar di `HomeController::vrMuseum` sebelum
dikirim ke scene. Jadi **26 dari 30 objek tidak eksis di VR sama sekali** —
hanya baris database. 13 museum selain museum uji punya persis 2 objek tanpa
`mesh_name`: keluaran seeder, bukan konten.

Yang bisa ditunjukkan ke validator hari ini: satu museum berisi 4 objek hidup,
tanpa audio, tanpa puzzle, tanpa nilai karakter.

### Aset 3D adalah sketsa, bukan digitalisasi

`punden_berundak_pura_mehu.glb` berukuran **7,8 KB**. Menurut `credits.txt`,
model dibuat procedural di Blender dari deskripsi artikel BPCB Bali — bukan
fotogrametri, tanpa tekstur, hanya 3 material polos (`Mat_Batu_Padas`,
`Mat_Batu_Berlumut`, `Mat_Pelataran_Batu`). Ornamennya sendiri didokumentasikan
sebagai "representasi disederhanakan sebagai 4 medali".

Proposal berjudul **"Digitalisasi Peninggalan Prasejarah"** dengan pos anggaran
**Asset Digital Prasejarah (3D & Audio) Rp 5.000.000** dan **Bahan Produksi
Konten VR Rp 1.000.000**. Yang ada sekarang adalah sketsa penempatan, bukan
digitalisasi.

### Manipulasi artefak tidak punya jalur konten

`daftar-objek.txt` menyatakan eksplisit: *"tidak ada relasi puzzle/slot pada
scene ini (semua objek statis/informatif, bukan piece yang perlu dipasangkan)"*.

Jadi 0/30 `slot_mesh_name` **bukan data yang belum diisi** — asetnya memang tidak
dirancang untuk puzzle. Mekanik grab/snap yang sudah ditulis di
`vr-museum.js` tidak punya konten untuk dijalankan.

*(Fase 2c kemudian membalik kesimpulan operasionalnya: aset tidak lagi perlu
memodelkan marker slot sama sekali. Yang tetap benar adalah temuan intinya —
tidak ada konten puzzle, dan objeknya harus jadi node terpisah agar bisa ada.)*

Karena "manipulasi artefak" wajib per hal. 19 ("modul interaksi pengguna berbasis
*motion controller*") dan hal. 22, ini bukan fitur opsional yang bisa
dikesampingkan.

### Katalog situs tidak cocok dengan ruang lingkup proposal

Dari 17 `situs_peninggalan`, yang benar-benar prasejarah hanya sekitar 7–9.
Sisanya di luar lingkup "peninggalan prasejarah":

- Museum Le Mayeur — pelukis Belgia, 1932–1958
- Monumen Perjuangan Rakyat Bali
- Gedung Art Deco Singaraja
- Candi Gunung Kawi
- Pura Kehen
- Sentra Wayang Kamasan

Menurut `log_aktivitas`, konten yang **paling banyak dipakai justru Le Mayeur
(94 sesi)** — di luar lingkup proposal.

Ada juga kesalahan data: **"Situs Prasasti Sukuh" dialamatkan di Karangasem,
Bali.** Candi Sukuh berada di Karanganyar, Jawa Tengah.

---

## 4. Keputusan desain yang sudah diambil

### Tier perangkat

| Fase | Perangkat |
|---|---|
| Orientasi | HP/cardboard cukup |
| Eksplorasi | HP/cardboard cukup |
| Interaksi (manipulasi artefak) | **Quest saja** — hal. 19 mengikatnya ke motion controller |
| Refleksi | layar biasa, tidak perlu VR |

Cardboard **dibekukan** di orientasi + eksplorasi + panel info. Jangan tambah
fitur ke jalur itu.

Tier ini sesuai proposal, bukan kompromi: hal. 10 menyebut "perangkat VR
sederhana" dan "perangkat mobile" sebagai sarana pendukung.

### Headset tanpa login (mode kiosk)

Headset langsung ke scene. Identitas responden dipegang perangkat fasilitator.
Jalur `ar.token` + QR yang sudah ada jadi pondasinya.

Alasan: kriteria "tanpa intervensi teknis pengembang", dan mengetik di keyboard
virtual akan merusak uji keterpakaian.

### Fitur mati dihapus, dua diubah fungsi

- **Kritik & Saran** → kuesioner keterpakaian pasca-sesi VR (wajib TKT 6)
- **Pretest/Posttest** → alat ukur outcome, dilepas dari gerbang progres berjenjang

### Nilai karakter: banyak nilai per objek

Satu kolom JSON, kosakata dikunci PHP enum. Bukan tabel/relasi baru.

Alasan utama: arah yang murah dibatalkan adalah multi. Turun dari multi ke single
= ambil elemen pertama, tanpa migrasi. Naik dari single ke multi = migrasi kolom
plus menyentuh ulang setiap pembaca (form admin, editor 3D, panel VR, modul
refleksi).

Kosakata nilai harus berasal dari **Pardi, Sendratari, Margi (2017),
"Rekonstruksi nilai-nilai pendidikan karakter pada peninggalan purbakala di Desa
Pakraman Selulung, Kintamani, Bangli"** — referensi #1 proposal, meneliti situs
yang persis sama dengan museum uji. Sampai daftar itu tersedia, enum diisi
placeholder 6 dimensi Profil Pelajar Pancasila.

---

## 5. Kendala lapangan

- Anggaran hanya **1 headset standalone** + 2 controller.
- Responden: **56 orang uji produk** (transport hanya dianggarkan 50 — inkonsistensi
  internal proposal) dan **100–106 orang uji lapangan**, masing-masing 2 kegiatan.
- Satu headset bergiliran ratusan orang. Protokol uji harus memastikan setiap
  responden menyentuh Quest minimal untuk **fase interaksi** — kalau mayoritas
  diuji di cardboard, fitur utama tidak pernah tervalidasi dan itu langsung
  menabrak kriteria lulus TKT 6.
- Jadwal Juli–Desember 2026. Per 2026-08-10 sudah **bulan ke-2 dari 6**.

---

## 6. Urutan kerja

### Jalur paralel — Produksi Aset 3D

**Dimulai sekarang, berjalan bersamaan dengan seluruh jalur kode.**

Bukan fase dalam antrean kode. Ini pekerjaan Blender + audio, sumber dayanya
pemodel 3D dan pengisi suara, bukan pemrogram — dan pos anggarannya sudah ada
(Rp 6 juta). Menaruhnya dalam antrean berarti membiarkan sumber daya yang tidak
bentrok saling menunggu.

Ia jalur kritis karena punya lead time terpanjang dan **semua yang di bawahnya
bergantung padanya**: Fase 2b tidak punya yang diisi, Fase 3 tidak punya fase
interaksi yang bermakna, Fase 4 tidak punya bahan refleksi, Fase 5 mencatat event
dari scene yang hampir kosong, Fase 7 menguji ke 106 responden dengan 4 objek.

Deliverable pertama: **brief produksi aset** — sudah tersedia di
`docs/brief-produksi-aset.md`. Empat butir di bagian penutupnya perlu keputusan
tim sebelum produksi bisa dipesan.

### Jalur kode — berurutan

| Urutan | Fase | Alasan posisi |
|---|---|---|
| 1 | **Fase 2** — Pemetaan nilai karakter | Murah, satu commit, membuka Fase 2b dan Fase 4 |
| 2 | ~~**Fase 5** — Runtime event logging~~ *selesai* | Luaran wajib #1 butuh data uji; harus terpasang sebelum uji apa pun dijalankan |
| 3 | ~~**Fase 3** — State machine 4 fase~~ *selesai* | Alur wajib hal. 22 |
| 4 | ~~**Fase 4** — Modul refleksi~~ *selesai* | Modul ke-5 blueprint |
| 5 | ~~**Fase 6** — Capability gating & mode kiosk~~ *selesai* | Wajib untuk "tanpa intervensi pengembang" |
| 6 | ~~**Fase 2c** — Puzzle pindah ke data editor~~ *selesai* | Menyisipkan diri sebelum Fase 2b: melepas Fase 2b dari siklus ekspor ulang Blender |
| 7 | **Fase 2b** — Pengisian konten museum uji | Menunggu aset dari jalur paralel |
| 8 | **Fase 1** — Pembersihan fitur mati | Tidak menghasilkan bukti TKT; kerapian saja |
| 9 | **Fase 7** — Kesiapan uji TKT 5/6 | Penutup |

---

## 7. Rincian fase

### Fase 2 — Pemetaan nilai karakter *(disetujui, dieksekusi)*

Melengkapi Learning Content Management Module.

1. Enum `App\Enums\NilaiKarakter` — string-backed, `label()` + `options()`.
   Placeholder Profil Pelajar Pancasila, diganti setelah daftar Pardi 2017 ada.
2. Migration `add_nilai_karakter_to_virtual_museum_object` — kolom `json` nullable.
3. Model: `$fillable` + cast `'array'` (ikut properti `$casts` yang sudah ada).
4. Form admin: checkbox group di create/edit, chip di show.
5. Validasi `storeVirtualMuseumObject` + `updateVirtualMuseumObject` — inline
   `$request->validate()` mengikuti konvensi repo, bukan Form Request.
6. Editor visual 3D: checkbox di panel properti + validasi `editorSaveObject`.
7. `HomeController::vrMuseum` — tambah kolom ke `get([...])`.
8. `InfoPanel.draw()` — chip nilai karakter; baris deskripsi turun 8 → 6.
9. Tes di `VirtualMuseumObjectTest.php`.

### Jalur paralel — Brief produksi aset 3D *(selesai, menunggu keputusan tim)*

Dokumen spesifikasi untuk pemodel 3D: `docs/brief-produksi-aset.md`. Memuat:

- **Konvensi penamaan mesh.** `mesh_name` harus persis sama dengan nama node di
  GLB — ini yang menyambungkan baris database ke objek 3D.
- **Model diserahkan utuh.** Tidak ada marker slot, tidak ada objek yang dilepas
  di Blender — editor visual yang melepas saat runtime (lihat Fase 2c). Yang
  tersisa sebagai syarat mengikat: objek yang akan dilepas harus jadi **node
  terpisah**, karena editor tidak bisa memecah mesh yang sudah menyatu.
- Skala dunia nyata, Y-up, origin di titik spawn, model 4 m di depan pengguna.
- Draco compression, budget poligon dan tekstur untuk standalone headset.
- Daftar objek interaktif per situs beserta nilai karakter yang melekat.
- Narasi audio per objek.

**Rekomendasi lingkup: satu situs unggulan, dikerjakan tuntas.** Bukan cakupan
tipis untuk 14 museum. Kandidat: **Punden Berundak Pura Mehu** — situsnya
prasejarah tulen (megalitikum), nilai karakternya sudah diteliti ketua peneliti
sendiri (Pardi 2017), dan sudah ada sketsa GLB plus daftar objek sebagai titik
mulai.

### Fase 5 — Runtime event logging *(selesai)*

Wajib hal. 22, sumber bukti luaran wajib #1. Rincian teknis di CLAUDE.md.

Tabel `vr_event` tunggal, `App\Enums\JenisEventVr`, waktu diukur klien sebagai
`offset_ms`, dikirim batch lewat `sendBeacon`, ekspor CSV baris mentah di
`GET /admin/vr-events/export`.

Sepuluh jenis event terpasang; `FaseBerubah` menunggu Fase 3.

`kode_responden` diisi klien dari query string `?kode=`. Fase 6 harus memastikan
tautan/QR yang dipakai fasilitator membawa parameter itu, kalau tidak seluruh data
uji lapangan jadi anonim dan tidak bisa disilangkan dengan angket maupun pretest.

Jalur pengantarnya sempat putus: `ArTokenAuth` me-redirect ke `url()->current()`,
yang di Laravel hanya mengembalikan path sehingga seluruh query ikut terbuang saat
token ditukar sesi. Sudah diperbaiki — arToken dibuang, parameter lain dipertahankan
— dan dijaga `tests/Feature/Vr/ArTokenHandoffTest.php`. Tidak terdeteksi lebih awal
karena di lokal kita sudah login, jadi tidak ada redirect sama sekali: jalan di dev,
gagal di lapangan.

**Catatan untuk kelak, belum dikerjakan:** `kode_responden` masuk CSV yang dibuka tim
peneliti di Excel. Nilai yang diawali `=` `+` `-` `@` ditafsirkan Excel sebagai rumus.
Risiko sekarang rendah karena kode berasal dari QR yang kita buat sendiri, tapi kalau
fasilitator mulai mengetik kode manual, saring karakter pembuka itu di `VrEventController::export()`.

### Fase 3 — State machine 4 fase *(selesai)*

State hanya di klien, tanpa tabel. Transisi berbasis pencapaian, bukan timer.
Rincian di CLAUDE.md; logika di `public/assets/js/vr-phases.js`, diuji
`npm run test:js`.

**Fase melacak dan memandu, tidak pernah mengunci** — keputusan paling penting di
fase ini. Fase yang mengunci mengubah tiap pemicu transisi jadi titik macet
permanen, dan siswa macet memanggil fasilitator, yang dilarang kriteria TKT 6.

Fase interaksi baru punya isi setelah ada objek ber-`posisi_awal`. Sejak Fase 2c
itu tidak lagi menuntut sunting Blender: cukup pastikan keempat motif ceplok bunga
adalah node terpisah, lalu tetapkan posisi lepasnya di editor visual. Sampai itu
ada, setiap sesi Quest akan mencatat `dilewati_tanpa_slot` — jaring pengamannya
berfungsi, tapi itu bukan kondisi yang diinginkan.

### Fase 4 — Modul refleksi *(selesai)*

Modul kelima blueprint. Dua tabel baru (`pertanyaan_refleksi`, `jawaban_refleksi`),
CRUD admin penuh sehingga tim materi bisa mengarang pertanyaan tanpa lewat
pengembang, halaman siswa di layar biasa, dan ekspor CSV. Rincian di CLAUDE.md.

Skema pretest tidak dipakai ulang — kolom `jawaban_benar` yang selamanya kosong akan
mengundang salah tafsir bahwa refleksi adalah kuis bernilai.

Sekalian diperbaiki: jalur stereo HP sebelumnya **tidak punya jalan keluar sama
sekali**. Sekarang ada tombol selesai yang menutup sesi dengan benar dan mengantarkan
ke modul refleksi.

Pertanyaan untuk museum uji belum disusun — menunggu daftar nilai karakter dari
Pardi dkk. (2017). Sampai itu ada, halaman refleksi menjelaskan keadaannya, bukan
menampilkan halaman kosong.

### Fase 6 — Capability gating & mode kiosk *(selesai)*

Rincian di CLAUDE.md. Tiga hal yang mendarat:

**Pengantaran `kode_responden`** — peluncur fasilitator di `/vr/peluncur/{museum_id}`
menghasilkan QR berisi kode, opsional rentang berurutan, dan bendera kiosk. Ini
satu-satunya pengirim `?kode=`; tanpanya seluruh Fase 5 menghasilkan data anonim.
Panel penutup sesi tidak bisa ditutup di mode kiosk, supaya kode basi jadi mustahil,
bukan sekadar terlihat.

**Afordansi yang berbohong** — kedipan objek puzzle di HP sengaja **tidak** dimatikan:
objeknya memang tetap interaktif di sana (nama, deskripsi, audio), dan mematikannya
justru menyembunyikan konten yang sah. Yang ditambahkan adalah satu baris di panel
info — "Objek ini bisa dilepas dan dipasang kembali di headset VR" — plus deskripsi
fase refleksi yang menyebut alasan pelewatan.

**Mode kiosk** — `?kiosk=1` menyembunyikan navigasi aplikasi. Perlu diingat ia
pengurang kesasar, bukan kunci; pengamanan sebenarnya adalah akun kiosk khusus, yang
jadi syarat operasional Fase 7.

### Fase 2c — Definisi puzzle pindah dari GLB ke data editor *(selesai)*

Rincian di CLAUDE.md. Alasannya kondisi lapangan: pemodel 3D-nya adalah pengembang
sendiri, jadi setiap koreksi posisi sekecil apa pun berarti buka Blender, geser
penanda, ekspor GLB, unggah ulang, sambungkan ulang. Ketergantungan teknis yang
sama jenisnya dengan yang dihindari di tempat lain, hanya di sisi penyusun konten.

`slot_mesh_name` diganti `posisi_awal` — dihapus, tidak dibiarkan berdampingan,
karena nol dari 30 baris memakainya. Inversi yang membuatnya murah: kalau model
diekspor **utuh**, posisi terpasang sebuah potongan adalah transform bawaannya di
GLB, jadi yang perlu disimpan hanya posisi lepasnya. Satu kolom, bukan dua, dan
tidak ada kolom rotasi sama sekali.

Konsekuensi kerja untuk pemodel: **selalu pahat dalam keadaan sudah terpasang.**
`docs/brief-produksi-aset.md` §4 ditulis ulang mengikuti ini; yang tersisa sebagai
syarat mengikat hanya pemisahan node.

Dua peringatan data basi ditambahkan di editor (node hilang, model berubah setelah
posisi disimpan) dan dua peringatan jarak seret, karena mata manusia di Blender
tidak lagi jadi penjaga jarak.

### Fase 2b — Pengisian konten museum uji

`mesh_name`, audio, nilai karakter, dan posisi lepas puzzle untuk museum yang
dipakai uji. Editor visual 3D sudah ada untuk mempercepatnya.

Catatan: audio **bukan** celah fitur — form admin sudah menerima `audio_file`
(mp3/wav/ogg/aac, maks 10MB) dan `InfoPanel.show()` sudah memutarnya. Murni
soal konten.

### Fase 1 — Pembersihan fitur mati

Hapus: Tugas · Laporan Peninggalan (+`laporan_gambar`, `laporan_komentar`,
`laporan_suka`) · Video Peninggalan · Riwayat Pengembang · Templat Hotspot ·
`akses_situs_user`.

Cakupan tiap fitur: route, controller, model, view, entri nav/menu, kunci
terjemahan, factory/seeder, test, dan migration penghapus tabel. Ikuti pola
`2026_07_15_092204_drop_ar_and_katalog_tables.php` — tambah migration drop baru,
jangan mengedit migration lama.

Jangan sentuh `kritik_saran` dan pretest/posttest.

Sekalian: perbaiki alamat "Situs Prasasti Sukuh".

### Fase 7 — Kesiapan uji TKT 5/6

- **Akun kiosk khusus** untuk uji lapangan — tanpa progres, rapor, atau data pribadi
  untuk dilihat. `?kiosk=1` hanya menyembunyikan navigasi aplikasi; browser Quest tetap
  punya bilah alamat sendiri, jadi ini pengamanan yang sebenarnya.
- Kritik & Saran → kuesioner keterpakaian pasca-sesi.
- Pretest/posttest dilepas dari gerbang progres.
- Pengecekan frame rate dan latency di Quest 2 dan HP.
- Uji alur penuh end-to-end tanpa bantuan teknis, meniru kondisi kelas.

---

## 8. Roadmap pengalaman VR (kesenangan & keterbacaan)

Rencana di §6–§7 memastikan sistemnya **ada**. Bagian ini soal apakah siswa
**menikmati** dan **mengerti** — yang di TKT 6 bukan kemewahan, karena skor
keterpakaian dan jumlah panggilan ke fasilitator adalah datanya.

### Anggaran waktu — pembatas terkeras, baca dulu

Satu headset, 106 responden uji lapangan × 2 kegiatan, plus 56 responden uji produk.
Pada 6 menit per orang itu sudah **>20 jam** giliran. Jadi anggaran desain adalah
**sesi 5 menit, sekali jalan, tanpa penjelasan lisan**.

Konsekuensi yang membunuh banyak ide bagus: tidak ada tutorial panjang, tidak ada
konten bercabang, tidak ada yang perlu diulang. Setiap detik yang dipakai siswa untuk
kebingungan adalah detik yang dicuri dari data. Fitur yang menambah durasi sesi harus
membayar dirinya dengan pengurangan kebingungan.

Prioritas seluruh daftar di bawah diurut dengan itu, bukan dengan kekerenan.

### Tahap A — sebelum uji apa pun. Ini penutup celah, bukan hiasan

| # | Item | Ukuran | Kenapa wajib |
|---|---|---|---|
| A1 | **Hantu tujuan saat menggenggam** — siluet transparan potongan muncul di posisi terpasangnya selama digenggam, hilang saat terpasang | kecil | Tanpa ini puzzle **tidak terbaca**. Siswa memegang batu dan tidak tahu ke mana. Radius snap 0,5 m tidak terlihat sama sekali. Ini penyebab macet nomor satu yang bisa diramalkan, dan macet = panggil fasilitator = menabrak kriteria hal. 11 |
| A2 | ~~**Petunjuk arah objek belum dilihat**~~ *(selesai — `vr-petunjuk.js`)* — panah/titik kecil di tepi kursor menunjuk objek interaktif terdekat yang panelnya belum pernah dibuka | kecil | `AMBANG_PENGAMATAN` = 100%. Satu objek yang terlewat di belakang punden menahan siswa di fase eksplorasi selamanya. Matikan sendiri begitu semua terlihat |
| A3 | **Onboarding 20 detik** — label melayang menempel di model controller ("Tekan = pilih", "Dorong = jalan"), hilang setelah aksi pertama tiap jenis | kecil | Mayoritas responden belum pernah memakai headset. Label yang menempel di controller dibaca sambil melihat tangan sendiri; panel teks di depan wajah tidak dibaca siapa pun |
| A4 | **Ambience audio** — satu loop lingkungan (angin, serangga, gamelan sangat pelan) yang menyala saat sesi mulai | sepele (kode), konten | Peningkatan kehadiran per byte tertinggi yang ada. Keheningan total membuat scene terasa seperti model 3D, bukan tempat. Sudah masuk pos anggaran "Aset Digital (3D & **Audio**)" |
| A5 | **Umpan balik snap yang memuaskan** — bunyi "tuk" + kilau singkat + haptic (haptic sudah ada) | sepele | Puzzle terasa selesai, bukan sekadar berhenti bergerak. Ini yang membuat siswa mau memasang potongan kedua |

A1 dan A2 sebenarnya perbaikan keterpakaian yang menyamar jadi fitur. Kalau waktu
habis, keduanya tetap dikerjakan; A3–A5 boleh gugur.

### Tahap B — kalau A selesai dan aset sudah datang

| # | Item | Ukuran | Catatan |
|---|---|---|---|
| B1 | **Momen penutup** — saat potongan terakhir terpasang: audio menguat, cahaya berubah sebentar, narasi satu kalimat, lalu `PhasePanel` mengantar ke refleksi | kecil | Sesi butuh akhir yang terasa, bukan panel yang tiba-tiba muncul. Ini juga jembatan naratif ke modul refleksi — persis "pemaknaan" hal. 9 |
| B2 | **Narasi audio per objek** | konten saja | `path_audio` + `InfoPanel.show()` sudah jalan. Suara mengalahkan teks di VR: siswa bisa tetap melihat objek sambil mendengar. Pertimbangkan **memangkas teks panel** begitu ada audio |
| B3 | ~~**Skala & titik pandang pembuka**~~ *(selesai — `vr-pandangan.js`)* | sepele | Arahkan pandangan awal ke punden pada jarak yang membuatnya terasa besar. Kesan pertama menentukan seluruh laporan keterpakaian, dan ini gratis |

### Tahap C — sengaja tidak dikerjakan

Ditulis supaya tidak diusulkan ulang tiap bulan.

| Item | Alasan menolak |
|---|---|
| **Kuis pilihan ganda di dalam VR** | Alat ukur kognitif sudah tiga: pretest, posttest, refleksi. Kuis keempat menduplikasi sambil menambah risiko titik macet di dalam headset. Nilai pedagogisnya sudah ditutup refleksi, yang jawabannya lebih kaya untuk analisis per nilai karakter |
| **Skor, bintang, papan peringkat** | Mengubah fokus dari makna ke angka — berlawanan dengan "internalisasi nilai karakter". Juga mendorong siswa buru-buru, merusak data time-on-task |
| **Physics rigidbody** | Lihat §2. Keputusan yang sudah diambil |
| **Multiplayer / sesi bersama** | Satu headset. Tidak ada yang bisa diajak |
| **Hand tracking** | Anggaran menyebut controller, uji terikat motion controller (hal. 19). Menambah jalur input kedua berarti dua jalur yang harus diuji dengan waktu giliran yang sama |
| **Gerak lokomosi mulus penuh** | Penyebab motion sickness nomor satu. Rotasi per langkah 30° tetap |
| **Museum kedua** | Satu situs tuntas mengalahkan empat belas situs tipis. Lihat brief aset |
| **Panorama 360 diangkat jadi modul orientasi** | Sempat diusulkan, lalu ditarik — lihat di bawah |

### Panorama 360: biarkan di tempatnya

Ia **di luar jalur sesi VR**: rute sendiri (`/panorama/{situsId}` dari halaman detail
situs), stack sendiri (A-Frame), tidak disinggung `guest/vr/museum.blade.php`. Jadi ia
tidak memakan satu detik pun dari anggaran 5 menit.

**Jangan dihapus.** Biayanya nyata — 2 rute, controller, `Scene` + `Hotspot`, 5 view
admin, viewer, 2 migration, berkas storage — dan yang dibeli nol perbaikan terukur.
Risiko "terlihat seperti tur pasif" adalah masalah **presentasi**, bukan kode: cukup
jangan taruh panorama di skrip demo TKT.

**Jangan pula diintegrasikan.** Menjadikannya modul orientasi menambah durasi sesi *dan*
memasukkan stack 3D kedua ke jalur VR — dua hal yang dilarang anggaran waktu di atas.

Pemakaian yang benar: tampilkan di **layar laptop fasilitator** sebagai konteks sebelum
headset dipakai. Nol biaya VR, nol baris kode, dan justru berharga selama GLB-nya masih
sketsa.

Satu verifikasi tertunda: DB dev berisi **0 adegan**; angka 27 berasal dari audit DB
produksi. Kalau prod ternyata juga kosong, panorama masuk Fase 1 sebagai fitur mati —
bukan karena novelty, karena memang tidak ada isinya.

### Urutan eksekusi

    A1 → A2 → A3 → A4/A5 → [aset datang] → B2 → B1 → B3

A1–A3 tidak menunggu aset apa pun; bisa dikerjakan sekarang di GLB yang ada.

---

## 9. Perlu keputusan ketua peneliti / tim

1. **Daftar nilai karakter** dari Pardi dkk. (2017) untuk mengganti placeholder enum.
2. **Lingkup katalog situs.** 17 situs, hanya 7–9 prasejarah, dan konten paling
   dipakai (Le Mayeur, 94 sesi) di luar lingkup. Dipangkas untuk uji TKT, atau
   dibiarkan? Ini mengubah alur yang dilihat pengguna.
3. ~~**Panorama 360.**~~ *Diputuskan: biarkan di tempatnya, di luar jalur VR — §8.*
   Yang tersisa hanya verifikasi: apakah DB produksi benar berisi 27 `adegan`?
4. **Klaim AI** (hal. 3). Kata "AI"/"kecerdasan buatan" hanya muncul sekali di
   seluruh 27 halaman, di kolom Bidang Strategis, dan rumusan masalah yang
   dipilih di kolom itu ("6.9 Rendahnya Pengakuan Internasional") bukan tentang
   AI. Tidak disebut di spesifikasi, blueprint, software engineering, luaran,
   jadwal, maupun anggaran. Bukti kuat ia normatif — tetap konfirmasi.
5. **Komisioning aset 3D** — Rp 6 juta tersedia, lead time terpanjang, jalur kritis.
6. **Inkonsistensi 50 vs 56 orang** dan **mata anggaran lisensi game engine**
   (Rp 4 juta, sementara implementasi WebXR/Three.js tidak butuh lisensi) —
   bukan ranah teknis.
