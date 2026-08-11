# Brief Produksi Aset 3D — SmartPrasada

Untuk pemodel 3D dan pengisi suara. Disusun 2026-08-10.

Dokumen ini berisi spesifikasi teknis yang **mengikat**. Aset yang tidak memenuhi
bagian §3 dan §4 tidak akan berfungsi di aplikasi meskipun terlihat bagus di
Blender — bukan karena kualitas, tapi karena aplikasi menyambungkan data ke model
lewat nama node.

---

## 1. Konteks singkat

SmartPrasada adalah aplikasi pembelajaran Virtual Reality berbasis web (WebXR /
Three.js, bukan Unity). Siswa masuk ke lingkungan virtual berisi peninggalan
prasejarah, berpindah dengan teleport, menyentuh objek untuk membaca informasi dan
mendengar narasi, lalu memasang kembali objek yang terlepas ke posisi aslinya
sebagai aktivitas pengamatan.

Perangkat sasaran:

| Perangkat | Dipakai untuk |
|---|---|
| Meta Quest 2 (standalone) | Semua fase, termasuk manipulasi objek |
| HP + cardboard | Orientasi dan eksplorasi saja |

Karena Quest 2 adalah perangkat standalone dengan GPU mobile, budget di §6 tidak
bisa ditawar.

## 2. Lingkup pekerjaan

**Satu situs, dikerjakan tuntas.** Bukan banyak situs setengah jadi.

Situs: **Punden Berundak, Pura Mehu, Desa Selulung, Kintamani, Bangli.**

Sudah ada file rintisan `virtual-museum-assets/punden-berundak-pura-mehu/` berisi
model sketsa 7,8 KB, `daftar-objek.txt`, dan `credits.txt`. Pakai itu sebagai
acuan tata letak dan penamaan, **bukan sebagai basis geometri** — modelnya dibuat
procedural dari deskripsi teks tanpa tekstur, dan perlu dimodelkan ulang dengan
kualitas yang pantas disebut digitalisasi.

Referensi arkeologis: Balai Pelestarian Cagar Budaya Bali, "Punden Berundak
(Tinggalan dari Masa Pra Sejarah) di Pura Mehu Desa Selulung, Bangli". Foto
lapangan tambahan sangat diharapkan — struktur ini punya lumut, pelapukan, dan
variasi batu yang tidak tertangkap deskripsi teks.

Dimensi acuan dari sumber: alas segi empat memanjang ±5,7 × 5,5 m, tinggi total
±3,1 m, lima undakan mengerucut ke atas, bahan batu padas.

## 3. Spesifikasi teknis — MENGIKAT

### Format dan orientasi

| Item | Nilai |
|---|---|
| Format | `.glb` (glTF 2.0 binary), satu file |
| Kompresi | Draco aktif |
| Sumbu atas | Y-up (konvensi glTF; di Blender berarti Z-up lalu ekspor normal) |
| Skala | 1 unit = 1 meter, skala dunia nyata |
| Modifier | Apply Modifiers aktif saat ekspor |
| Ukuran file | Target ≤ 25 MB. Batas keras sistem 300 MB, tapi itu tidak realistis untuk diunduh headset lewat jaringan sekolah. |

### Posisi dan tata letak

Pengguna muncul (spawn) di titik **(0, 0, 0)** dengan mata di ketinggian 1,6 m.

- Letakkan struktur utama **±4 m di depan pengguna** pada sumbu −Z, sehingga saat
  masuk ia langsung melihat objek tanpa harus berputar.
- Dasar model harus berdiri di **Y = 0**. Aplikasi otomatis menurunkan model
  sampai titik terendahnya menyentuh lantai, jadi jangan biarkan ada geometri
  nyasar jauh di bawah struktur — satu vertex liar akan mengangkat seluruh model
  ke udara.
- Lantai dasar disediakan aplikasi berupa lingkaran radius 20 m yang otomatis
  melebar mengikuti ukuran model. Tidak perlu memodelkan tanah luas, tapi
  pelataran batu di sekitar punden tetap dimodelkan karena ia objek informatif.

### Permukaan yang bisa dipijak

Teleport bekerja dengan menembakkan sinar dan menerima permukaan yang menghadap
ke atas (normal Y > 0,6). Konsekuensinya:

- Permukaan yang dimaksudkan bisa dipijak — pelataran, tiap undakan — harus punya
  **face menghadap ke atas dengan normal yang benar**. Normal terbalik membuat
  area itu tidak bisa dituju.
- Hindari permukaan pijak yang lebih miring dari ±50°.

### Material

- Gunakan **Principled BSDF**. Node group rumit, shader prosedural Blender, dan
  material non-PBR tidak ikut terekspor ke glTF.
- Setiap objek interaktif akan diberi efek sorot oleh aplikasi dengan memanipulasi
  properti *emissive* materialnya. Material objek interaktif karena itu **tidak
  boleh** sudah memakai emissive untuk keperluan lain.
- Objek interaktif sebaiknya tidak berbagi material dengan objek non-interaktif.
  Aplikasi menyalin material per objek untuk mencegah efek sorot bocor, tapi
  memisahkannya sejak awal lebih bersih.
- **Jangan memanggang pencahayaan, bayangan, atau ambient occlusion ke dalam base
  color.** Ini kebiasaan wajar untuk aset game, tapi aplikasi memakai directional
  light real-time dengan bayangan lunak — hasilnya akan gelap dobel. Base color
  harus albedo murni. AO boleh diserahkan sebagai *AO map* terpisah, bukan dilebur
  ke tekstur warna.

## 4. Penamaan node — MENGIKAT

Ini bagian yang paling sering salah dan paling mahal akibatnya.

Aplikasi menyambungkan baris database ke objek 3D **lewat nama node di dalam
GLB**. Admin mengetik nama mesh di panel editor; kalau tidak ada node dengan nama
persis sama, objek itu tidak akan pernah muncul sebagai objek interaktif — tanpa
pesan error, hanya diam.

Aturan:

1. Nama node harus **unik di seluruh file**.
2. Gunakan `PascalCase_Dengan_Garis_Bawah`. Tanpa spasi, tanpa tanda baca, tanpa
   karakter beraksen.
3. **Blender menambahkan sufiks `.001`, `.002` pada nama duplikat.** Sufiks itu
   ikut terekspor dan akan memutus sambungan. Periksa Outliner sebelum ekspor.
4. Objek interaktif boleh berupa satu mesh atau satu Empty/collection yang
   membungkus beberapa mesh. Beri nama pada node terluar — aplikasi menelusuri ke
   atas dari mesh yang tersentuh sampai menemukan node bernama.
5. Sertakan **daftar nama node final dalam bentuk teks** bersama file GLB
   (perbarui `daftar-objek.txt`). Admin akan mengetik ulang dari daftar itu.

### Aktivitas pemasangan objek — kirim model UTUH

Aturannya satu kalimat: **model selalu diserahkan dalam keadaan sudah terpasang.**

Jangan melepas apa pun. Jangan membuat mesh penanda slot. Punden dipahat lengkap
dengan motif masih menempel di sudut alasnya, seperti aslinya di lapangan.

Editor visual yang melepas potongan saat runtime, dan posisi lepasnya ditetapkan
admin dengan menyeret panah di kanvas editor. Tempat kembalinya adalah posisi asli
objek itu di model — tidak perlu disimpan, tidak perlu ditandai, tidak bisa
meleset. Rotasinya juga mengikuti rotasi asli.

Konsekuensinya untuk pemodel: satu aset melayani tampilan utuh **sekaligus**
aktivitas puzzle, dan koreksi posisi tidak lagi berarti ekspor ulang.

Yang **tetap wajib** dan tidak bisa dipindahkan ke editor:

- **Pemisahan node.** Tiap objek yang akan dilepas harus jadi objek tersendiri di
  Blender. Editor tidak bisa memecah mesh yang sudah menyatu. Kalau empat motif
  ceplok bunga tergabung dalam satu mesh alas, aktivitasnya mustahil.
- Semua aturan penamaan di atas — sambungan tetap lewat nama node.

Angka yang masih berlaku:

- Toleransi pemasangan **0,5 meter**. Jangan menempatkan dua objek yang bisa
  dilepas lebih dekat dari 1 m satu sama lain — siswa akan kesulitan membedakan.
- Objek yang bisa dilepas harus punya lantai yang bisa dipijak di sekitarnya
  dalam radius berjalan, karena siswa harus teleport mendekat untuk
  menggenggamnya.

**Usulan aktivitas untuk situs ini** (perlu persetujuan tim materi): empat motif
ceplok bunga di sudut alas punden dilepas dan diletakkan tersebar di pelataran.
Siswa mengamati pola sudut, lalu mengembalikan tiap motif ke sudut yang benar.
Aktivitas ini menuntut pengamatan terarah, bukan sekadar melihat — sesuai
kebaruan yang diklaim proposal.

Untuk pemodel, permintaannya sekarang hanya: **pastikan keempat motif itu objek
terpisah**, dan biarkan di tempatnya.

## 5. Lingkungan situs

Yang dinilai bukan hanya strukturnya, tapi **lingkungan prasejarahnya**. Langit
yang dipakai aplikasi sekarang adalah tekstur generik warisan modul lama dan harus
diganti — ia tidak menggambarkan dataran tinggi Kintamani sama sekali.

### Panorama langit

| Item | Nilai |
|---|---|
| Format | **JPEG** equirectangular, rasio 2:1 |
| Resolusi | 4096 × 2048. Boleh sampai 6144 × 3072 bila detailnya menuntut. |
| Ukuran file | **≤ 4 MB** |
| Isi | Langit dan cakrawala dataran tinggi Kintamani — perbukitan, vegetasi tropis pegunungan, awan |
| Waktu | Pagi atau sore hari dengan matahari rendah; hindari tengah hari yang datar |

Dua catatan yang mengikat:

- **Harus JPEG, bukan `.hdr` atau `.exr`.** Aplikasi memuatnya dengan pemuat
  tekstur biasa yang tidak mengerti format HDR. File HDR akan gagal dimuat tanpa
  pesan error, hanya langit hitam.
- Batas 4 MB itu serius. Berkas langit yang dipakai sekarang berukuran **18 MB**
  dan harus diunduh sebelum scene tampil — di jaringan sekolah itu penantian yang
  merusak sesi uji. Kompres agresif; panorama langit memaafkan kompresi jauh lebih
  baik daripada tekstur objek.

Panorama ini juga dipakai aplikasi sebagai sumber pantulan lingkungan untuk seluruh
material, jadi warna dan kecerahannya ikut menentukan tampilan batu punden.

### Sekitar situs

Aplikasi menyediakan lantai datar berupa lingkaran radius 20 m berwarna tanah polos
yang otomatis melebar mengikuti ukuran model. Itu cukup untuk berdiri, tapi tanpa
apa-apa di sekelilingnya, situs akan terasa seperti cakram melayang di depan foto
langit.

Yang diminta:

- **Cincin lingkungan** di sekeliling pelataran sampai radius ±25 m: kontur tanah,
  rumput, batu lepas, pohon atau semak khas pegunungan Bali. Cukup untuk menutup
  tepi lantai dan memberi kedalaman.
- **Latar jauh** boleh sangat sederhana — siluet perbukitan berpoligon rendah
  antara cincin lingkungan dan garis cakrawala panorama, supaya peralihan dari
  geometri ke panorama tidak terlihat memotong.
- Vegetasi cukup *billboard* atau bidang bersilang bertekstur alpha. Jangan
  memodelkan daun satu per satu — budget segitiga di §6 mencakup lingkungan juga.
- Lingkungan **tidak** boleh diberi nama node yang menyerupai objek interaktif.

Kalau anggaran atau waktu memaksa memilih, prioritaskan panorama langit dan cincin
lingkungan terdekat; latar jauh boleh menyusul.

## 6. Budget performa

Quest 2 harus merender scene ini **dua kali per frame** (satu per mata) pada 72 Hz.
Angka di bawah adalah untuk seluruh scene, bukan per objek.

| Item | Batas |
|---|---|
| Total segitiga | ≤ 100.000 |
| Ukuran tekstur | ≤ 2048 × 2048, gunakan 1024 bila cukup |
| Jumlah set material | ≤ 8 |
| Jumlah draw call (objek terpisah) | ≤ 60 |

Yang **tidak** akan berfungsi dan tidak perlu dikerjakan:

- Physics, rigid body, collision — aplikasi tidak memakai physics engine sama
  sekali. Ini keputusan sadar tim, bukan kelalaian.
- Animasi rig / armature.
- Partikel, volumetrik, hair.
- Subsurface scattering, transmission, dan fitur shader lanjutan yang tidak
  terekspor ke glTF.

Detail permukaan (pelapukan, lumut, retakan) harus datang dari **tekstur dan
normal map**, bukan dari geometri. Itu cara paling murah menaikkan kesan
"digitalisasi" tanpa melanggar budget segitiga.

## 7. Audio narasi

Satu berkas per objek interaktif.

| Item | Nilai |
|---|---|
| Format | MP3 |
| Ukuran | ≤ 10 MB per berkas (batas sistem) |
| Durasi | 30–60 detik per objek |
| Bahasa | Indonesia |
| Gaya | Naratif untuk siswa SD/SMP, bukan bahasa laporan arkeologi |

Audio diputar saat siswa membuka panel informasi objek. Naskah disusun tim materi;
pengisi suara hanya merekam. Rekam dengan noise floor rendah — siswa akan
mendengarnya lewat speaker headset di ruang kelas yang ramai.

## 8. Daftar objek interaktif

Diambil dari `daftar-objek.txt` yang ada, ditambah kolom yang sebelumnya kosong.
Kolom nilai karakter dan pemilihan objek yang dilepas **perlu dikonfirmasi tim
materi** sebelum produksi dimulai.

Kolom "Jenis" tidak mengubah apa pun yang dikerjakan pemodel — semuanya dipahat
di tempatnya. Ia hanya memberi tahu objek mana yang nanti dijadikan potongan
lepas lewat editor, dan karena itu **harus jadi node terpisah**.

| Nama node | Nama tampilan | Jenis |
|---|---|---|
| `Punden_Berundak_Utama` | Punden Berundak Utama | Informatif |
| `Padma_Kurung` | Padma Kurung | Informatif |
| `Area_Persembahyangan` | Area Persembahyangan | Informatif |
| `Motif_Ceplok_Bunga_A` … `_D` | Motif Ceplok Bunga | Dilepas lewat editor — wajib node terpisah |

Deskripsi tiap objek sudah tersedia di `daftar-objek.txt` dan dipakai apa adanya.

## 9. Daftar periksa sebelum menyerahkan

Cek satu per satu. Butir yang gagal berarti aset tidak berfungsi, bukan sekadar
kurang rapi.

- [ ] File `.glb` tunggal, Draco aktif, ≤ 25 MB
- [ ] Skala meter, Y-up, Apply Modifiers aktif
- [ ] Titik terendah geometri berada di Y = 0, tidak ada vertex nyasar di bawahnya
- [ ] Struktur utama ±4 m di arah −Z dari titik (0,0,0)
- [ ] Semua nama node unik, tanpa sufiks `.001`
- [ ] Nama node cocok persis dengan daftar teks yang diserahkan
- [ ] Model diserahkan **utuh** — tidak ada objek yang sengaja dilepas
- [ ] Objek yang akan dilepas lewat editor adalah node terpisah, bukan bagian dari mesh gabungan
- [ ] Jarak antar objek yang bisa dilepas ≥ 1 m
- [ ] Permukaan pijak punya normal menghadap ke atas
- [ ] Semua material Principled BSDF, tidak ada emissive terpakai
- [ ] Tidak ada pencahayaan/bayangan/AO yang dipanggang ke base color
- [ ] Panorama langit JPEG equirectangular 2:1, ≤ 4 MB — bukan `.hdr`/`.exr`
- [ ] Cincin lingkungan menutup tepi lantai sampai radius ±25 m
- [ ] Total segitiga ≤ 100.000 termasuk lingkungan
- [ ] `daftar-objek.txt` diperbarui
- [ ] `credits.txt` diperbarui: sumber referensi, aset pihak ketiga, lisensinya
- [ ] Berkas audio MP3 per objek interaktif

## 10. Cara memverifikasi sendiri sebelum menyerahkan

Buka model di https://gltf-viewer.donmccurdy.com/ (Three.js, mesin yang sama
dengan aplikasi). Kalau tampil benar di sana dan daftar periksa §9 lolos, aset
hampir pasti berfungsi.

Setelah diserahkan, admin mengunggah GLB lewat menu Virtual Living Museum lalu
membuka **Editor VR** untuk mencocokkan nama node dengan data objek. Editor
menampilkan pohon mesh dari file yang diunggah — kalau ada nama yang tidak muncul
di sana, berarti ekspornya bermasalah.

---

## Perlu keputusan tim sebelum produksi mulai

1. **Daftar nilai karakter per objek** — dari Pardi, Sendratari, Margi (2017),
   yang meneliti situs ini.
2. **Naskah narasi audio** per objek.
3. **Persetujuan aktivitas pemasangan** yang diusulkan di §4 (empat motif ceplok
   bunga), atau gantinya.
4. **Foto lapangan** Punden Berundak Pura Mehu untuk acuan tekstur.
