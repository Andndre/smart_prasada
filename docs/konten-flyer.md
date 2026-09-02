# Draft Konten Flyer — SmartPrasada

Bahan mentah untuk desain flyer/leaflet prototipe. Setiap blok di bawah adalah **copy
siap tempel** — pilih yang muat, jangan gabung semuanya. Angka di kurung adalah panjang
kira-kira, untuk memperkirakan ruang.

Acuan isi: [poin-proposal.md](poin-proposal.md) dan
[rencana-pengembangan.md](rencana-pengembangan.md). Kalau ada yang berubah di sana,
berkas ini ikut basi — **baca §12 sebelum menulis klaim baru.**

Asumsi yang saya ambil: audiensnya campuran (validator/reviewer, guru & kepala sekolah,
pengelola museum), format 2 sisi. Kalau audiensnya cuma satu, buang blok yang tidak
relevan — flyer yang mencoba bicara ke tiga orang sekaligus tidak bicara ke siapa pun.

---

## 1. Identitas & judul

**Nama produk**

    SmartPrasada

**Judul lengkap** (untuk sisi belakang / footer, 17 kata)

    Prototipe Immersive Virtual Reality untuk Digitalisasi Peninggalan Prasejarah
    sebagai Sumber Pembelajaran Berbasis Nilai Karakter

**Kategori produk** (badge kecil)

    Immersive Virtual Reality Learning Kit

---

## 2. Tagline — pilih satu

| # | Tagline | Kata | Nada |
|---|---|---|---|
| T1 | **Masuk ke situsnya. Bukan menontonnya.** | 5 | Paling kuat, langsung menegaskan pembeda dari tur virtual |
| T2 | **Belajar sejarah dengan berdiri di dalamnya.** | 6 | Hangat, cocok untuk guru/sekolah |
| T3 | **Dari peninggalan prasejarah ke nilai karakter, dalam satu sesi VR.** | 10 | Paling lengkap, butuh ruang |
| T4 | **Eksplorasi · Pengamatan · Refleksi** | 3 | Untuk dipakai bersama T1 sebagai baris kedua |

Rekomendasi: **T1 sebagai headline, T4 sebagai baris kecil di bawahnya.**

---

## 3. Pitch pembuka — pilih satu panjang

**Pendek (24 kata)** — untuk sisi depan di bawah headline

> SmartPrasada membawa siswa masuk ke situs peninggalan prasejarah lewat VR: menjelajah
> sendiri, mengamati artefak dari dekat, memasangnya kembali, lalu merefleksikan nilai
> karakter di baliknya.

**Sedang (48 kata)** — untuk sisi belakang, paragraf pembuka

> Situs peninggalan prasejarah sulit didatangi, rapuh, dan sering hanya jadi gambar di
> buku. SmartPrasada mengubahnya jadi ruang yang bisa dimasuki. Siswa menjelajah situs
> secara mandiri, membuka informasi artefak dari jarak dekat, memasang kembali bagian
> yang terlepas, lalu menuliskan refleksi nilai karakter yang ia temukan — satu alur
> utuh, bukan tayangan yang ditonton.

**Panjang (86 kata)** — kalau flyer punya kolom teks penuh

> Situs peninggalan prasejarah sulit didatangi, rapuh, dan di kelas sering menyusut jadi
> satu foto di buku teks. SmartPrasada mengubahnya menjadi ruang yang bisa dimasuki.
>
> Bukan tur virtual yang ditonton: siswa berpindah sendiri di dalam situs, mendekati
> artefak dan membuka penjelasannya, menggenggam bagian yang terlepas dan memasangnya
> kembali, lalu menuliskan refleksi atas nilai karakter yang ia temukan.
>
> Seluruh sesi berjalan di browser headset tanpa memasang aplikasi apa pun, dan setiap
> langkah siswa tercatat sebagai data yang bisa diunduh guru dan peneliti.

---

## 4. Cara kerja — blok utama flyer

Ini yang paling penting dan paling mudah didesain: **4 langkah, 4 ikon, satu baris teks
per langkah.** Alur ini bukan hiasan naratif — ia state machine yang benar-benar berjalan
di sistem dan berpindah sendiri berdasarkan pencapaian siswa, bukan timer.

### Versi ikon + satu baris (untuk pita horizontal / 4 kolom)

| # | Fase | Judul | Baris teks (≤14 kata) | Saran ikon |
|---|---|---|---|---|
| 1 | Orientasi | **Tiba di situs** | Siswa muncul di depan bangunan utama, langsung mengenali skala aslinya. | pin lokasi / gunung |
| 2 | Eksplorasi | **Menjelajah sendiri** | Berpindah bebas ke titik mana pun, mendekati artefak yang menarik perhatian. | jejak kaki / kompas |
| 3 | Interaksi | **Menyentuh & memasang** | Menggenggam bagian yang terlepas, mengembalikannya ke tempat semula. | tangan menggenggam |
| 4 | Refleksi | **Memaknai** | Menuliskan nilai karakter yang ia temukan, di layar biasa setelah headset dilepas. | pena / gelembung pikir |

### Versi paragraf pendek (kalau ruang lebih lega)

**1 · Orientasi.** Sesi dibuka dengan siswa berdiri di titik yang sudah ditentukan,
menghadap bangunan utama pada jarak yang membuat ukurannya terasa. Petunjuk singkat
menempel di model controller di tangannya, lalu hilang sendiri setelah dipakai.

**2 · Eksplorasi.** Siswa berpindah ke titik mana pun yang ia pilih, berjalan bebas, dan
memutar pandangan. Objek yang bisa diperiksa menyala saat ditunjuk. Panah kecil menuntun
ke objek yang belum sempat dilihat — supaya tidak ada yang terlewat, dan tidak ada yang
tersangkut.

**3 · Interaksi.** Bagian yang terlepas dari bangunan bisa digenggam dan dibawa. Siluet
transparan menunjukkan ke mana ia harus kembali; begitu cukup dekat, ia terpasang sendiri
dengan bunyi, kilau, dan getaran di controller.

**4 · Refleksi.** Headset dilepas. Di layar biasa, siswa menjawab pertanyaan terbuka
tentang nilai karakter yang ia temui di situs tadi — satu pertanyaan menggali tepat satu
nilai, supaya jawabannya bisa dibaca per nilai.

### Catatan desain untuk blok ini

Fase **memandu, tidak mengunci.** Kalau flyer menggambarkannya sebagai "level yang harus
dilewati", itu salah tafsir yang berbahaya: tidak ada satu pun pintu yang tertutup di
sistem ini. Pakai kata **"tahap"** atau **"alur"**, jangan **"level"** atau **"terkunci"**.

---

## 5. Fitur utama — kartu/bullet

Sembilan di bawah ini semuanya sudah berjalan. Ambil 5–6 yang paling relevan dengan
audiens flyer; sembilan kartu di satu sisi akan jadi bubur.

| Fitur | Kalimat flyer (≤18 kata) | Prioritas |
|---|---|---|
| **Tanpa instal aplikasi** | Berjalan langsung di browser headset. Pindai QR dari laptop guru, sesi langsung mulai. | ★ wajib |
| **Panel info di dalam ruang** | Penjelasan artefak muncul melayang di sebelah objeknya, bukan menutupi pandangan. | ★ wajib |
| **Nilai karakter melekat pada objek** | Tiap artefak membawa nilai karakter yang ditampilkan bersama penjelasannya. | ★ wajib |
| **Manipulasi artefak** | Genggam, bawa, dan pasang kembali bagian bangunan yang terlepas. | ★ wajib |
| **Rekaman aktivitas otomatis** | Setiap perpindahan, pengamatan, dan pemasangan tercatat; guru mengunduhnya sebagai CSV. | ★ wajib |
| **Mode kiosk** | Ganti responden cukup satu ketukan — tidak perlu login ulang, tidak perlu ketik di udara. | ○ kalau audiensnya guru/peneliti |
| **Jalan di HP juga** | Tanpa headset mahal: ponsel + viewer cardboard sudah cukup untuk menjelajah dan mengamati. | ○ kalau audiensnya sekolah |
| **Editor 3D untuk pengelola** | Tandai objek, isi deskripsi dan nilai karakter langsung di atas model, tanpa menulis kode. | ○ kalau audiensnya museum/institusi |
| **Nyaman untuk pemula** | Perpindahan bertahap dan rotasi per langkah — dirancang menekan rasa pusing pengguna baru. | ○ pengisi |

---

## 6. Spesifikasi teknis — tabel

Untuk sisi belakang. Versi ringkas dulu; versi lengkap kalau flyer memang teknis.

### Ringkas (untuk guru/sekolah)

| | |
|---|---|
| Platform | Aplikasi web — cukup browser, tanpa pemasangan |
| Perangkat VR | Headset standalone (Meta Quest 2), atau headset via PC |
| Alternatif hemat | Ponsel Android/iOS + viewer cardboard |
| Refleksi & laporan | Laptop, tablet, atau ponsel biasa |
| Jaringan | Wi-Fi lokal atau internet |
| Durasi sesi | ±5 menit per siswa |
| Bahasa | Indonesia |

### Lengkap (untuk validator/reviewer teknis)

| Aspek | Spesifikasi |
|---|---|
| Arsitektur | Aplikasi web Laravel 11 (PHP 8.2+), basis data MySQL |
| Mesin 3D | Three.js + WebXR, berjalan di browser tanpa plugin |
| Format aset | glTF/GLB terkompresi DRACO, pencahayaan berbasis HDRI |
| Antarmuka | Tailwind CSS + Alpine.js; panel VR digambar di dalam scene |
| Perangkat VR | Standalone (Meta Quest 2) **dan** PC-tethered — satu kode, tanpa build terpisah |
| Perangkat pendukung | Ponsel dengan mode stereo/cardboard; layar biasa untuk modul refleksi |
| Input | Head-mounted display + motion controller; model controller asli dan umpan getar |
| Navigasi | Teleport titik-tujuan dan gerak bebas; rotasi bertahap 30° per langkah |
| Manipulasi | Genggam–lepas dengan pemasangan otomatis pada radius 0,5 m |
| Pencatatan | 10 jenis runtime event, penanda waktu monoton per sesi, ekspor CSV |
| Otentikasi sesi | Token HMAC-SHA256 berbatas waktu lewat QR, untuk serah-terima antarperangkat |
| Modul | 5 modul: rendering, interaksi, manajemen konten, antarmuka, refleksi |

---

## 7. Alur pakai di kelas — untuk guru/fasilitator

Blok pendek, cocok jadi kotak kecil "3 langkah untuk guru".

1. **Buka peluncur di laptop.** Pilih museum, layar menampilkan QR beserta rentang kode
   responden.
2. **Pindai sekali dari headset.** Sesi terbuka; siswa pertama langsung mulai tanpa login.
3. **Ganti siswa dengan satu ketukan.** Kode responden berikutnya berjalan otomatis,
   headset tidak perlu dipindai ulang.

Setelah sesi: siswa mengisi refleksi di layar biasa, dan guru mengunduh rekaman aktivitas
serta jawaban refleksi dari dasbor.

---

## 8. Untuk siapa — blok sasaran

| Sasaran | Kalimat |
|---|---|
| **Siswa SD & SMP** | Belajar peninggalan prasejarah dengan mendatanginya, bukan menghafal fotonya. |
| **Guru** | Berperan sebagai fasilitator; alat ukur sebelum, sesudah, dan refleksi sudah tersedia. |
| **Sekolah** | Satu headset cukup untuk satu kelas bergiliran; sisanya bisa memakai ponsel. |
| **Museum & lembaga budaya** | Koleksi yang rapuh atau jauh tetap bisa didekati publik, lengkap dengan narasinya. |

---

## 9. Pembeda — kalau flyer perlu blok "kenapa ini berbeda"

Tiga poin, jangan lebih. Ini klaim kebaruan yang dijaga proposal.

- **Bukan tur virtual.** Siswa tidak menyusuri jalur yang sudah ditentukan; ia memilih
  ke mana pergi dan apa yang diperiksa.
- **Berujung pada nilai, bukan pada skor.** Sengaja tidak ada poin, bintang, atau papan
  peringkat — sesi ditutup dengan refleksi, karena yang dituju pemaknaan.
- **Menghasilkan bukti, bukan kesan.** Setiap sesi meninggalkan data terstruktur yang
  bisa dianalisis, bukan sekadar laporan "siswa terlihat antusias".

---

## 10. Identitas riset — footer sisi belakang

    Hilirisasi Riset Prioritas: Pengujian Model dan Prototipe 2026
    No. Dokumen PRO-251410100722 · Universitas Pendidikan Ganesha

    Ketua Peneliti   I Wayan Pardi
    Anggota          I Wayan Lasmawan · I Made Pageh
    Mahasiswa        Lukas Sumanto Sababalat · Angela Arga Mayasari Dabukke

    Tingkat Kesiapterapan Teknologi: TKT 6 (pengujian di lingkungan pembelajaran)

Sisakan ruang untuk: logo Undiksha, logo FIP, QR ke video YouTube @FIPUndiksha, dan
kontak. **Isi kontak belum ada di repo** — minta ke ketua peneliti sebelum cetak.

---

## 11. Sisipan pendek — untuk mengisi ruang sempit

Kalau desainer butuh potongan kecil:

- `Masuk ke situsnya, bukan menontonnya.` (5 kata)
- `Cukup browser. Tanpa instal aplikasi.` (5 kata)
- `±5 menit per siswa.` (4 kata)
- `Eksplorasi → Pengamatan → Refleksi` (untuk pita/garis pemisah)
- `Setiap langkah siswa tercatat, dan bisa diunduh.` (7 kata)
- `Satu headset, satu kelas, bergiliran.` (5 kata)

---

## 12. Klaim yang TIDAK boleh masuk flyer

Baca ini sebelum menambah kalimat apa pun. Semua di bawah pernah benar, terdengar
menarik, atau ada di dokumen lain — dan semuanya akan jadi masalah kalau tercetak.

| Jangan tulis | Alasan |
|---|---|
| **"Augmented Reality" / "AR"** | Fitur AR berbasis marker sudah dihapus dari sistem. Rutenya tidak ada lagi. Menyebutnya berarti menjanjikan sesuatu yang tidak bisa didemokan. |
| **"Simulasi fisika" / "physics"** | Sengaja tidak diimplementasikan — pemasangan artefak memakai pengecekan jarak, bukan rigidbody. Keputusan sadar tim, tercatat di rencana pengembangan. |
| **"Berbasis AI" / "kecerdasan buatan"** | Tidak ada model AI di sistem ini. Kata "AI" muncul sekali di proposal, di kolom bidang strategis, dan tidak ada di spesifikasi mana pun. |
| **"Hasil pemindaian 3D / fotogrametri"** | Model yang ada sekarang dibangun dari deskripsi tertulis, bukan pindaian lapangan. Kalau aset baru sudah datang, klaim ini boleh ditinjau ulang. |
| **"14 museum" / jumlah situs besar** | Konten yang benar-benar hidup baru satu museum uji. Menyebut angka katalog akan gugur begitu validator mengkliknya. |
| **"Tur 360 derajat"** | Fitur panorama ada, tapi di luar jalur sesi VR — dan menonjolkannya justru melemahkan klaim "bukan tur pasif". Boleh dipakai fasilitator, jangan dijual di flyer. |
| **"Multiplayer" / "belajar bersama di dalam VR"** | Satu headset, satu pengguna. Tidak pernah direncanakan. |
| **Klaim hasil belajar berangka** (mis. "meningkatkan pemahaman 40%") | Belum ada data uji. Angka apa pun sekarang adalah karangan. |
| **"Terkunci sampai tahap selesai"** | Kebalikan dari desain sistem. Fase memandu, tidak pernah mengunci. |

Kalau ragu soal satu kalimat: kalimat yang tidak bisa didemokan di depan validator dalam
5 menit, jangan dicetak.

---

## 13. Saran susunan dua sisi

Sekadar rangka, bukan desain.

**Sisi depan** — satu pesan saja, terbaca dari jarak satu meter.

    logo · nama produk
    HEADLINE (T1)
    baris kecil (T4)
    [ satu visual besar: tangkapan layar situs dari dalam VR ]
    pitch pendek (§3)
    badge: "Berjalan di browser · Tanpa instal aplikasi"

**Sisi belakang** — isi.

    Cara kerja: 4 ikon + satu baris (§4)
    Fitur utama: 5–6 kartu (§5)
    Spesifikasi: satu tabel (§6)
    Untuk siapa: 3–4 baris (§8)
    footer identitas riset + QR + kontak (§10)

Visual yang paling menjual adalah **tangkapan layar dari dalam headset dengan panel info
terbuka di sebelah artefak** — itu memperlihatkan sekaligus bahwa ini 3D, informatif, dan
bukan tur pasif. Ambil dari sesi nyata, jangan render promosi.
