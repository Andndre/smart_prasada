# Seeder untuk Konten Elearning - Plan

## Overview
Telah dibuat seeder untuk mengisi konten elearning yang lengkap agar bisa diuji alur flow elearning (pretest → ebook → posttest → virtual living museum).

## Files Created/Modified

### 1. `database/seeders/ElearningContentSeeder.php` (NEW)
Seeder utama yang mengisi konten untuk semua materi:

- **Pretest**: 5 pertanyaan per materi dengan pilihan A-E dan jawaban benar
- **Ebook**: 1 modul ebook per materi
- **Posttest**: 5 pertanyaan per materi dengan pilihan A-E dan jawaban benar
- **Situs Peninggalan**: 1-2 situs per materi (sesuai topik historis)
- **Virtual Museum**: 1 museum virtual per situs + 2 objek AR per museum

#### Konten Berdasarkan Era & Topik:
| Era | Topik | Konteks |
|-----|-------|---------|
| A (Prasejarah) | Punden Berundak, Sarkofagus, Arca Megalitik, Menhir, Dolmen | Masa 2000 SM - 800 M |
| B (Hindu-Buddha) | Arca Hindu-Buddha, Candi, Prasasti | Abad ke-8 - ke-14 |
| C (Majapahit) | Periode Majapahit | 1343 M - Abad ke-15 |
| D (Gelgel) | Wayang Kamasan | Abad ke-16 - ke-19 |
| E (Kolonial) | Masa Kolonial Belanda | 1846 - 1942 |
| F (Pasca Merdeka) | Masa Pasca-Kemerdekaan | 1945 - Sekarang |

### 2. `database/seeders/TestUserSeeder.php` (NEW)
Seeder untuk test user yang bisa login:
- `test@example.com` / `password`
- `siswa@example.com` / `password`

### 3. `database/seeders/DatabaseSeeder.php` (MODIFIED)
Menambahkan pemanggilan seeder baru:
```php
$this->call([
    AdminSeeder::class,
    EraSeeder::class,
    TestUserSeeder::class,        // NEW
    MateriHierarchySeeder::class,
    ElearningContentSeeder::class, // NEW
]);
```

## Usage
```bash
# Jalankan seeder (fresh database)
php artisan migrate:fresh --seed

# Atau jalankan seeder saja
php artisan db:seed

# Untuk materi tertentu saja
php artisan db:seed --class=ElearningContentSeeder
```

## Test Users
| Email | Password | Role |
|-------|----------|------|
| admin@gmail.com | password | admin |
| test@example.com | password | user |
| siswa@example.com | password | user |

## Catatan Teknis
- Semua data menggunakan `updateOrCreate` agar aman dijalankan berkali-kali
- Konten Indonesia/Sejarah Bali yang realistis berdasarkan fakta historis
- Foreign key ke materi_id digunakan untuk relasi yang benar
- Virtual museum object memerlukan situs_id yang valid