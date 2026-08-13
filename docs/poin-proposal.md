# Poin-poin Proposal — SmartPrasada (PRO-251410100722)

Ringkasan **isi mengikat** proposal Hilirisasi Riset Prioritas: Pengujian Model dan
Prototipe 2026. Dokumen ini hanya mencatat apa yang dijanjikan ke pemberi dana beserta
nomor halamannya, supaya klaim bisa dicek tanpa membuka PDF. Rencana kerja, status
fase, dan keputusan teknis ada di [rencana-pengembangan.md](rencana-pengembangan.md) —
jangan menggandakannya ke sini.

Nama file PDF sumber: *SmartPrasada Prototipe Immersive Virtual Reality untuk
Digitalisasi Peninggalan Prasejarah sebagai Sumber Pembelajaran Berbasis Nilai
Karakter*. 27 halaman, disetujui LPPM 12/01/2026.

---

## 1. Identitas

| | |
|---|---|
| Judul | SmartPrasada: Prototipe Immersive VR untuk Digitalisasi Peninggalan Prasejarah sebagai Sumber Pembelajaran Berbasis Nilai Karakter |
| Ketua | I Wayan Pardi (Undiksha, Pendidikan Sejarah) |
| Anggota | I Wayan Lasmawan (konten nilai karakter), I Made Pageh (user flow & usability) |
| Mahasiswa | Lukas Sumanto Sababalat, Angela Arga Mayasari Dabukke |
| Bidang | Soshum — Digitalisasi: AI & Semikonduktor |
| Jenis produk | *(Immersive) Virtual Reality Learning Kit* |
| Pelaksanaan | 2026, 1 tahun (jadwal Juli–Desember) |
| TKT | 4 → 6 (formulir hal. 1 menulis 5; lihat rencana-pengembangan §1) |
| RAB | Rp 149.790.000 |

## 2. Luaran wajib (hal. 4)

1. **Bukti uji validasi di lingkungan yang relevan** — bukti peningkatan TKT 5 dan 6.
   Status target: *Ada dan Tercapai*.
2. **Dokumen blueprint** prototipe.
3. **Video** proses pengembangan dan hasil prototipe → YouTube @FIPUndiksha (*Published*).
4. **Poster** prototipe → medsos FIP Undiksha.

Luaran #1 adalah alasan seluruh infrastruktur pencatatan event dan kuesioner ada.

## 3. Klaim kebaruan yang harus dijaga (hal. 6, 9, 10)

- VR **bukan** media visualisasi, **bukan** tur virtual pasif. Ia komponen inti sistem
  pembelajaran terstruktur.
- Yang membedakan: alur **eksplorasi → pengamatan terarah → refleksi nilai karakter**
  terintegrasi dalam satu sistem dan diarahkan ke capaian pembelajaran.
- Pendekatan: *experiential learning*.
- Sasaran pengguna: siswa SD/SMP, guru sebagai fasilitator, institusi pendidikan dan
  lembaga budaya. Pasar: >439 ribu sekolah, ~300–442 museum. Model pendapatan: lisensi
  institusional.

**Konsekuensi desain:** setiap fitur yang membuat pengalaman terasa seperti tur pasif
melemahkan novelty utama. Itu satu-satunya alasan panorama 360 dipertanyakan.

## 4. Spesifikasi teknis yang dijanjikan

### Arsitektur — 5 modul (hal. 11, blueprint; hal. 9 dan 22 menyebut 4)

1. Modul lingkungan virtual peninggalan prasejarah berbasis 3D (*VR Rendering Engine* /
   hal. 9 menyebutnya *VR Environment Engine*)
2. Modul eksplorasi dan interaksi pengguna berbasis VR (*Interaction Controller Module*)
3. Konten pembelajaran sosial–humaniora serta nilai karakter (*Learning Content
   Management Module*)
4. Antarmuka pengguna dan sistem navigasi (*User Interface Layer*, hal. 22: berbasis
   **context-aware overlay non-intrusif di ruang virtual**)
5. Modul refleksi pembelajaran berbasis pengalaman eksplorasi

### Alur operasional (hal. 9 & 22)

    orientasi → eksplorasi → interaksi → refleksi

dengan **state management** untuk transisi antar fase. Hal. 9 merincinya: orientasi dan
inisialisasi konteks prasejarah → eksplorasi mandiri atau terbimbing → interaksi langsung
dengan objek virtual → refleksi yang mengarahkan pada pemaknaan dan internalisasi nilai
karakter.

### Software engineering (hal. 22)

- *real-time 3D engine*: **scene rendering, physics simulation, event-driven interaction**
- Interaction Controller Module memproses input **head-mounted display dan motion
  controller** untuk **navigasi, seleksi objek, dan manipulasi artefak**
- Pipeline: *asset loading* → *real-time interaction processing* → *state management*
- Berjalan pada perangkat VR **standalone maupun PC-tethered**
- Optimasi: *frame rate stability*, *latency control*
- **Pencatatan runtime events** untuk uji fungsional, usability evaluation, dan validasi

### Teknologi & bahan baku (hal. 10)

Perangkat lunak pengembangan VR (game engine dan software 3D); perangkat VR sederhana;
komputer/laptop dan perangkat mobile; headset VR. Bahan baku: **aset digital 3D**, data
visual peninggalan prasejarah, konten pembelajaran digital.

## 5. Kriteria lulus TKT (hal. 10–11, 19)

TKT 5 — integrasi dan pengujian komponen: functional testing, usability testing terbatas,
verifikasi alur operasional, **uji kompatibilitas perangkat VR**, optimasi frame rate dan
latency.

TKT 6 — demonstrasi di lingkungan pembelajaran relevan dan terkontrol (kelas/lab) bersama
guru dan peserta didik: uji keterpakaian (*user acceptance*), observasi interaksi, evaluasi
kinerja selama sesi, pengumpulan umpan balik.

> **Keberhasilan TKT 6 ditunjukkan apabila prototipe dapat dijalankan secara stabil
> sebagai satu sistem terintegrasi, seluruh fitur utama berfungsi sesuai desain, dan alur
> pembelajaran dapat dilaksanakan secara utuh _tanpa intervensi teknis pengembang_.**
> — hal. 11

Kalimat terakhir itu yang melahirkan mode kiosk.

## 6. Anggaran yang mengikat pekerjaan teknis (hal. 5)

| Item | Nilai |
|---|---|
| Aset Digital Prasejarah (3D & Audio) | Rp 5.000.000 |
| Bahan Produksi Konten VR | Rp 1.000.000 |
| Lisensi Software Pengembangan VR | Rp 4.000.000 |
| Standalone VR headset untuk pengujian | Rp 9.000.000 (1 unit) |
| Controller VR / Hand Controller | Rp 5.000.000 (1 set) |
| Headphone Audio Immersive | Rp 3.000.000 (2 unit) |
| VR stand, cable link, casing · headset safety pad | Rp 2,5 jt · Rp 2 jt |

Responden: **56 orang uji produk** (transport dianggarkan 50) dan **100–106 orang uji
lapangan**, masing-masing × 2 kegiatan. Validator ahli media/materi/bahasa 6 orang × 2 keg.

## 7. Referensi kunci untuk konten

- **[1] Pardi, Sendratari, Margi (2017)** — rekonstruksi nilai pendidikan karakter pada
  peninggalan purbakala Desa Pakraman Selulung, Kintamani, Bangli. Situs yang sama dengan
  museum uji; **sumber wajib kosakata `NilaiKarakter`**.
- [2] Setiono dkk. (2023) — sarkofagus Desa Pedawa.
- **[14] Pardi & Tri Esaputra (2025)** — publikasi cikal bakal prototipe ini.

---

## 8. Analisis kesesuaian implementasi (per 2026-08-13)

### Sudah sesuai

| Janji proposal | Implementasi |
|---|---|
| 5 modul blueprint | Kelimanya ada: scene Three.js, `TeleportControls`, `nilai_karakter` + konten admin, `InfoPanel`, modul refleksi |
| *Context-aware overlay non-intrusif di ruang virtual* (hal. 22) | `InfoPanel`/`PhasePanel` digambar **di dalam scene**, bukan DOM overlay — sesuai kata per kata |
| Alur 4 fase + state management | `vr-phases.js` |
| Navigasi, seleksi objek, manipulasi artefak | teleport + gerak bebas, raycast pilih, grab/snap `posisi_awal` |
| Input HMD + motion controller | jalur Quest penuh, model controller asli, haptics |
| *Pencatatan runtime events* | tabel `vr_event`, 11 jenis event, ekspor CSV |
| Standalone **dan** PC-tethered | WebXR jalan di dua-duanya tanpa build terpisah |
| Tanpa intervensi teknis pengembang | mode kiosk, peluncur QR, pergantian responden satu ketukan, tombol keluar di scene |
| Perangkat mobile sebagai sarana pendukung (hal. 10) | jalur stereo HP/cardboard |
| Refleksi mengarah ke internalisasi nilai | `pertanyaan_refleksi` satu nilai per pertanyaan |

### Menyimpang tipis — sadar, ada alasannya

| Penyimpangan | Ukuran | Alasan / mitigasi |
|---|---|---|
| **Physics simulation tidak ada** (hal. 22) | Sedang — satu kata dalam daftar tiga | Snap jarak 0,5 m memenuhi nilai pedagogis manipulasi artefak; physics penuh menambah risiko performa mobile tanpa kontribusi ke capaian belajar. Sudah dicatat sebagai keputusan sadar |
| **"Game engine" → WebXR/Three.js** | Kecil | Hal. 10 hanya menulis "perangkat lunak pengembangan VR (game engine dan software 3D)" — generik. Yang jadi masalah bukan teknisnya melainkan **mata anggaran lisensi Rp 4 juta** yang tidak terpakai; itu ranah keuangan, bukan kode |
| **Fase interaksi hanya di Quest** | Kecil | Justru mengikuti hal. 19 yang mengikat manipulasi ke motion controller. Alasan pelewatan tercatat di event log (`dilewati_perangkat`), jadi datanya jujur |
| **Klaim AI** (hal. 3) | Kecil, tapi terlihat | Kata "AI" muncul **sekali** di seluruh 27 halaman, di kolom Bidang Strategis, dan tidak ada di spesifikasi/blueprint/luaran/anggaran. Kemungkinan besar normatif. Kalau perlu ditambal, yang paling murah dan jujur adalah menyebut pemetaan nilai karakter sebagai *content mapping*, bukan menempelkan model AI yang tidak dianggarkan |
| **Panorama 360 (27 adegan)** | Kecil, arah berlawanan | Fitur ini persis "tur virtual pasif" yang ditolak di hal. 10. Opsi: jadikan modul **orientasi** (fase 1) supaya punya peran, atau lepas |
| **Katalog situs melebihi lingkup** | Sedang, non-kode | 17 situs, hanya 7–9 prasejarah; konten paling dipakai (Le Mayeur, 94 sesi) di luar lingkup judul |
| **"Digitalisasi" vs aset sketsa** | **Terbesar, bukan soal kode** | GLB 7,8 KB procedural, tanpa tekstur. Judul proposal menjanjikan digitalisasi. Rp 6 juta tersedia, lead time terpanjang. Lihat rencana-pengembangan §3 |

### Ruang berkreasi

Proposal **tidak** mengunci daftar mekanik interaksi. Yang disebut hanya "navigasi,
seleksi objek, dan manipulasi artefak" (hal. 22) plus "aktivitas pengamatan terarah"
(hal. 6). Fitur baru aman selama:

1. mendukung salah satu dari 4 fase dan menghasilkan bukti keterpakaian;
2. tidak menambah *tur pasif*;
3. tidak mengunci alur — kriteria "tanpa intervensi teknis pengembang" melarang titik
   macet apa pun;
4. tidak menuntut perangkat di luar 1 headset + 2 controller yang dianggarkan.
