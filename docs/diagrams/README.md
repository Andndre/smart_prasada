# Katalog Diagram Arsitektur & Alur Bisnis SmartPrasada (Draw.io)

Koleksi diagram ini dibuat untuk menggantikan teks dan sketsa ASCII pada dokumen `Blueprint-SmartPrasada.docx`. Seluruh diagram dirancang dengan **orientasi Portrait vertikal** dan tipografi berukuran besar (`fontSize: 17-18px`) agar pas dan terbaca jelas pada tata letak halaman dokumen Microsoft Word (A4 Portrait).

---

## 📁 Struktur Berkas

* Direktori Utama: `D:\smart_prasada\docs\diagrams\`
  * Berkas `.drawio`: Format XML native Draw.io (dapat diedit via [app.diagrams.net](https://app.diagrams.net) atau VS Code Draw.io Integration).
  * Berkas `.svg`: Format vektor resolusi tinggi untuk disisipkan langsung ke Word (`Insert > Pictures`), web, atau laporan.
* Direktori Sumber: `D:\smart_prasada\docs\diagrams\src\`
  * Berkas `.yaml`: Spesifikasi deklaratif sumber berbasis *Draw.io Design System*.
* Skrip Kompilasi: `D:\smart_prasada\docs\diagrams\compile_all.js`

---

## 📊 Daftar Diagram & Pemetaan Bagian Blueprint

| No | Berkas Diagram | Bagian Blueprint | Dimensi (px) | Deskripsi Utama |
|---|---|---|---|---|
| **01** | `01_alur_inti_sesi_vr_4_fase` | **2.2** Alur Inti Sesi VR | 552 × 2384 | State machine 4 fase non-blocking: Orientasi → Eksplorasi (100% objek) → Interaksi (Puzzle grab & snap 0.5m) → Refleksi nilai karakter. Dilengkapi logika bypass perangkat. |
| **02** | `02_alur_elearning_berjenjang` | **2.3** Alur E-Learning | 848 × 1336 | Siklus 4 instrumen: Pre-Test → E-Book Flipbook → Virtual Living Museum → Post-Test. Mekanisme unlock level, mode demo, dan alur remedial. |
| **03** | `03_alur_fasilitator_kiosk` | **2.4** Alur Fasilitator | 648 × 1824 | Operasional mandiri guru: Peluncur QR (token HMAC 30m) → Scan headset → Mode Kiosk → Penguncian panel penutup → Tombol "Responden Berikutnya" (auto-reload R-xxx). |
| **04** | `04_alur_pengelolaan_konten_admin` | **2.5** Pengelolaan Konten | 544 × 1608 | 7 Tahap administrasi: Era & Materi → E-Book & Soal → Situs → Virtual Museum GLB → Editor Visual 3D (mesh linking & delta puzzle) → Bank Refleksi → Ekspor CSV. |
| **05** | `05_alur_data_penelitian_tkt` | **2.6 & 3.6** Data Penelitian | 560 × 2128 | Pipeline data lintas instrumen berbasis `kode_responden`: VR batch event beacon (`vr_event`), jawaban kualitatif (`jawaban_refleksi`), skor kognitif (`jawaban_user`), SUS (`kritik_saran`) → Sintesis TKT 6. |
| **06** | `06_arsitektur_lima_modul_sistem` | **3.1 & 3.2** Arsitektur Sistem | 976 × 1728 | Struktur lima modul blueprint: 1. VR Engine, 2. Interaction Controller, 3. Learning CMS, 4. UI Layer Spasial, 5. Modul Refleksi, berinteraksi dengan Laravel 11 & MySQL. |
| **07** | `07_arsitektur_runtime_vr_dan_auth_token` | **3.3 & 3.4** Runtime & Auth | 504 × 2376 | Pemisahan modularitas: Modul Three.js WebXR vs Modul Logika Murni (unit testable di Node), serta alur pertukaran token HMAC stateless via middleware `ar.token`. |
| **08** | `08_peta_navigasi_aplikasi` | **5.2** Peta Navigasi (Sitemap) | 560 × 2480 | Peta hierarki antarmuka sistem bertingkat: Portal Siswa/E-learning, Portal Fasilitator Sesi Kiosk, dan Dashboard Administrator. |

---

## 🛠️ Cara Mengompilasi Ulang Diagram

Jika melakukan perubahan pada berkas spesifikasi YAML di dalam folder `src/`, jalankan:

```powershell
node D:\smart_prasada\docs\diagrams\compile_all.js
```
