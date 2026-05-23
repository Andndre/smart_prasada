# VR 360 Panorama - Environment Setup Plan

**Project:** Smart Prasada VR 360 Panorama Tour Editor  
**Document Version:** 1.0.0  
**Date:** 2026-05-18  
**Status:** Draft for Review

---

## 1. Executive Summary

Dokumen ini menyajikan perencanaan komprehensif untuk setup environment pengembangan fitur VR 360 Panorama. Perencanaan mencakup empat aspek utama:

1. **Analisis & Pemilihan Library** - Perbandingan A-Frame, Three.js, dan Pannellum dengan rekomendasi final
2. **Konfigurasi Development Environment** - Setup server, project structure, dan integrasi build tools
3. **Desain Database Schema** - Entity relationship untuk panorama data
4. **Rekomendasi Package & Dependency** - Versi yang kompatibel dan environment variables

**Existing Stack:**

- Laravel 11.31 + PHP 8.2
- Vite 8.x + TailwindCSS 3.4.19
- MySQL Database
- A-Frame 1.5.0 (planned via CDN)

---

## 2. Library & Framework Selection

### 2.1 Perbandingan Teknologi VR Panorama

| Criteria           | A-Frame 1.5.0                                  | Three.js (latest)     | Pannellum 2.5 |
| ------------------ | ---------------------------------------------- | --------------------- | ------------- |
| **Learning Curve** | Low (declarative HTML)                         | High (imperative JS)  | Low           |
| **Bundle Size**    | ~150KB (CDN)                                   | ~150KB+ (modular)     | ~45KB         |
| **VR Support**     | Built-in WebXR                                 | Requires extra setup  | No native VR  |
| **Hotspot System** | Excellent (aframe-environment, aframe-look-at) | Manual implementation | Basic         |
| **Ecosystem**      | Large component library                        | Core library only     | Limited       |
| **Maintenance**    | Active (Mozilla)                               | Very Active           | Active        |
| **Mobile Support** | Good                                           | Good                  | Excellent     |

### 2.2 Rekomendasi: A-Frame 1.5.0

**Alasan Pemilihan:**

1. **Declarative Syntax** - Integrasi dengan Blade template lebih intuitif

    ```html
    <a-scene>
        <a-sky src="panorama.jpg"></a-sky>
        <a-image class="hotspot" position="0 1.5 -4"></a-image>
    </a-scene>
    ```

2. **ECMAScript Modules** - Komponen dapat di-load sebagai ES modules

    ```javascript
    import "aframe-environment-component";
    ```

3. **VR Ready** - WebXR support out-of-the-box

    ```html
    <a-scene vr-mode-ui="enabled: true"></a-scene>
    ```

4. **Component Ecosystem** - Hotspot management lebih mudah dengan library yang tersedia
    - `aframe-environment-component@1.3.3` - Scene environment
    - `aframe-look-at-component@0.5.1` - Billboard hotspots
    - `aframe-extras` - Enhanced controls

5. **CDN Compatible** - Tidak perlu bundle, mengurangi complexity
    ```html
    <script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>
    ```

### 2.3 Kompatibilitas dengan Existing Stack

```
┌─────────────────────────────────────────────────────────┐
│                    Existing Stack                        │
├─────────────────────────────────────────────────────────┤
│  Laravel 11 (Blade) ←→ A-Frame (HTML-based)  ✓          │
│  Vite 8 ←→ A-Frame (CDN, not bundled)      ✓            │
│  TailwindCSS 3.4 ←→ A-Frame UI Overlays    ✓           │
│  MySQL ←→ Eloquent Models                   ✓           │
└─────────────────────────────────────────────────────────┘
```

---

## 3. Development Environment Configuration

### 3.1 Project Structure

```
smart_prasada/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       └── PanoramaController.php    # NEW
│   │   └── Middleware/
│   │       └── AdminAuth.php                 # Existing
│   └── Models/
│       ├── Tour.php                          # NEW
│       ├── Scene.php                        # NEW
│       ├── Hotspot.php                      # NEW
│       ├── HotspotTemplate.php               # NEW
│       ├── Kabupaten.php                    # Existing (may need relation)
│       └── Kategori.php                      # Existing (may need relation)
│
├── database/
│   ├── migrations/
│   │   ├── 2026_05_18_000001_create_tours_table.php
│   │   ├── 2026_05_18_000002_create_scenes_table.php
│   │   ├── 2026_05_18_000003_create_hotspots_table.php
│   │   ├── 2026_05_18_000004_create_hotspot_templates_table.php
│   │   └── 2026_05_18_000005_add_panorama_to_kabupaten_kategori.php
│   └── factories/
│       ├── TourFactory.php                   # NEW
│       ├── SceneFactory.php                  # NEW
│       └── HotspotFactory.php                # NEW
│
├── public/
│   ├── hotspots/                             # SVG hotspot templates
│   │   ├── nav.svg
│   │   ├── info.svg
│   │   ├── text.svg

│   └── js/
│       └── viewer/                           # ES module scripts
│           ├── viewer.js
│           ├── hotspots.js
│           ├── navigation.js
│           ├── toc-menu.js
│           └── gyro.js
│
├── resources/
│   ├── css/
│   │   └── viewer.css                        # NEW - viewer-specific styles
│   ├── js/
│   │   ├── admin/
│   │   │   ├── app.js
│   │   │   └── panorama-editor.js           # NEW
│   │   └── viewer/
│   │       └── app.js                        # NEW
│   └── views/
│       ├── admin/
│       │   └── panorama/                     # NEW
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       ├── edit.blade.php
│       │       └── templates.blade.php
│       └── panorama/
│           └── viewer/
│               └── index.blade.php           # NEW - public viewer
│
├── routes/
│   └── web.php                               # Add panorama routes
│
└── storage/
    └── app/
        └── public/
            ├── panoramas/                    # Uploaded panorama images
            └── hotspot-templates/             # Template files
```

### 3.2 Development Server Configuration

**Recommended: Laravel Valet (macOS) or Laravel Herd**

```bash
# Option 1: Valet
composer global require laravel/valet
valet install
cd ~/Sites/smart_prasada
valet link

# Option 2: Herd (recommended)
# Download from https://herd.laravel.com
```

**Alternative: PHP Built-in Server**

```bash
cd /path/to/smart_prasada
php artisan serve --host=127.0.0.1 --port=8000
```

### 3.3 Vite Configuration Updates

```javascript
// vite.config.js - Add panorama entry points

import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/admin/app.js",
                "resources/js/viewer/app.js", // NEW
            ],
            refresh: true,
        }),
    ],
    // A-Frame loaded via CDN, not bundled
    build: {
        rollupOptions: {
            external: ["aframe"], // Prevent bundling
        },
    },
});
```

### 3.4 Environment Variables (.env)

```env
# =============================================
# VR Panorama Configuration
# =============================================

# Storage
PANORAMA_DISK=public
PANORAMA_MAX_SIZE=51200  # 50MB in KB
PANORAMA_ALLOWED_MIMES=jpg,jpeg,png,webp

# Image Processing
PANORAMA_THUMBNAIL_WIDTH=400
PANORAMA_THUMBNAIL_HEIGHT=225
PANORAMA_QUALITY=85

# Viewer Settings
PANORAMA_DEFAULT_FOV=80
PANORAMA_DEFAULT_CAMERA_X=0
PANORAMA_DEFAULT_CAMERA_Y=0
PANORAMA_DEFAULT_CAMERA_Z=0
PANORAMA_HOTSPOT_FAR=500

# Feature Flags
PANORAMA_GYRO_ENABLED=true
PANORAMA_VR_MODE_ENABLED=false
```

---

## 4. Database Schema Design

### 4.1 Design Decision: Integrate with SitusPeninggalan

After reviewing the existing architecture, the decision was made to integrate 360 VR Panorama directly with the existing `situs_peninggalan` table instead of creating a separate `tours` table. This provides:

- **Unified Management**: Sites can have both Virtual Living Museum AND 360 Tour content
- **Simpler Data Model**: No need for separate Tour entity
- **Consistent with Existing Patterns**: Follows project's convention of relating content to sites
- **Scalability**: Scenes are linked directly to situs, allowing flexible content per site

### 4.2 Table Definitions

#### 4.2.1 tours

```php
// database/migrations/2026_05_18_000001_create_tours_table.php

Schema::create('tours', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('cover_image')->nullable();
    $table->boolean('is_active')->default(true);
    $table->text('description')->nullable();
    $table->string('location_name')->nullable();
    $table->text('location_google_map_link')->nullable();
    $table->boolean('is_featured')->default(false);

    // Relations (existing tables may need adjustment)
    $table->foreignId('kabupaten_id')->nullable()
        ->constrained('kabupatens')->nullOnDelete();
    $table->foreignId('kategori_id')->nullable()
        ->constrained('kategoris')->nullOnDelete();

    // SEO & Settings
    $table->string('meta_title')->nullable();
    $table->text('meta_description')->nullable();
    $table->json('settings')->nullable(); // viewer_config, permissions, etc.

    $table->timestamps();

    $table->index(['is_active', 'is_featured']);
    $table->index('slug');
});
```

#### 4.2.2 scenes

```php
// database/migrations/2026_05_18_000002_create_scenes_table.php

Schema::create('scenes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('image'); // panorama image URL/path
    $table->double('camera_x')->default(0);
    $table->double('camera_y')->default(0);
    $table->double('camera_z')->default(0);
    $table->integer('order')->default(0);

    // Template defaults for new hotspots in this scene
    $table->json('hotspot_defaults')->nullable();

    $table->timestamps();

    $table->index(['tour_id', 'order']);
});
```

#### 4.2.3 hotspots

```php
// database/migrations/2026_05_18_000003_create_hotspots_table.php

Schema::create('hotspots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
    $table->string('label');

    // 3D Position (A-Frame coordinates)
    $table->double('position_x')->default(0);
    $table->double('position_y')->default(-0.5);
    $table->double('position_z')->default(-4);

    // 3D Rotation
    $table->double('rotation_x')->default(0);
    $table->double('rotation_y')->default(0);
    $table->double('rotation_z')->default(0);

    // Navigation target (nullable for info/text type)
    $table->foreignId('target_scene_id')->nullable()
        ->constrained('scenes')->nullOnDelete();

    $table->string('color')->default('#00bcd4');
    $table->integer('order')->default(0);

    // Type enum: navigation, info, text
    $table->enum('type', ['navigation', 'info', 'text'])
        ->default('navigation');

    // Info modal content (HTML allowed - admin input only)
    $table->string('modal_title')->nullable();
    $table->text('modal_content')->nullable();
    $table->string('modal_image')->nullable();

    // Template reference
    $table->foreignId('template_id')->nullable()
        ->constrained('hotspot_templates')->nullOnDelete();

    // Animation configuration
    $table->json('animation_config')->nullable();

    $table->timestamps();

    $table->index(['scene_id', 'order']);
    $table->index('target_scene_id');
});
```

#### 4.2.4 hotspot_templates

```php
// database/migrations/2026_05_18_000004_create_hotspot_templates_table.php

Schema::create('hotspot_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->enum('type', ['navigation', 'info', 'text']);
    $table->string('file_path'); // SVG file path
    $table->string('thumbnail_path')->nullable();
    $table->boolean('is_animated')->default(false);
    $table->string('default_color')->nullable();
    $table->timestamps();

    $table->index('type');
});
```

### 4.3 Migration Command

```bash
# Run new migrations
php artisan migrate

# For fresh database with seed data
php artisan migrate:fresh --seed
```

---

## 5. Package & Dependency Recommendations

### 5.1 NPM Packages (package.json additions)

```json
{
    "dependencies": {
        // A-Frame and components (CDN preferred, but available via npm)
        // "aframe": "^1.5.0",  // CDN recommended

        // Image processing
        "image-size": "^1.0.0",

        // UUID generation for filenames
        "uuid": "^9.0.0"
    },
    "devDependencies": {
        // Browser testing
        "@playwright/test": "^1.40.0"
    }
}
```

### 5.2 Composer Packages

```json
{
    "require": {
        // Image manipulation (if not already installed)
        // " Intervention/image": "^3.0"  // Check if already in project
    }
}
```

### 5.3 CDN Resources (Recommended - Not Bundled)

Per AGENTS.md: "Heavy CDN Libraries: Three.js, A-Frame, PDF.js loaded via CDN, not bundled"

```html
<!-- A-Frame Core -->
<script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>

<!-- A-Frame Components -->
<script src="https://unpkg.com/aframe-environment-component@1.3.3/dist/aframe-environment-component.min.js"></script>
<script src="https://unpkg.com/aframe-look-at-component@0.5.1/dist/aframe-look-at-component.min.js"></script>

<!-- Google Fonts for 3D Text (optional) -->
<link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&display=swap"
    rel="stylesheet"
/>
```

### 5.4 Recommended A-Frame Components

| Component                    | Version | Purpose                                  |
| ---------------------------- | ------- | ---------------------------------------- |
| aframe-environment-component | 1.3.3   | Procedural sky, ground, lighting presets |
| aframe-look-at-component     | 0.5.1   | Billboard hotspots (always face camera)  |
| aframe-extras                | latest  | Enhanced movement/controls               |

### 5.5 Development Environment Variables

```env
# =============================================
# VR Panorama - Development (.env.local)
# =============================================

# APP_DEBUG=true (already in .env)

# Storage paths (Laravel defaults)
PANORAMA_STORAGE_PATH=storage/app/public/panoramas
PANORAMA_TEMPLATE_PATH=storage/app/public/hotspot-templates

# Upload settings
PANORAMA_MAX_UPLOAD_SIZE=50
PANORAMA_ALLOWED_EXTENSIONS=jpg,jpeg,png,webp

# Viewer defaults
PANORAMA_DEFAULT_ROTATION_Y=0
PANORAMA_SPHERE_RADIUS=500
PANORAMA_LOADER_TIMEOUT=8000

# Admin settings
PANORAMA_EDITOR_GIZMO_ENABLED=true
PANORAMA_INSPECTOR_ENABLED=true
```

### 5.6 Production Environment Variables

```env
# =============================================
# VR Panorama - Production (.env.production)
# =============================================

# APP_DEBUG=false
# APP_ENV=production

# Use S3 or other cloud storage for production
# PANORAMA_DISK=s3
# PANORAMA_S3_BUCKET=your-bucket
# PANORAMA_S3_REGION=ap-southeast-1

# Performance
PANORAMA_CACHE_ENABLED=true
PANORAMA_PRELOAD_NEARBY=true
```

---

## 6. Implementation Roadmap

### Phase 1: Environment Setup (Day 1)

| Step | Task                                      | Estimated Time |
| ---- | ----------------------------------------- | -------------- |
| 1.1  | Create database migrations                | 30 min         |
| 1.2  | Create Eloquent models with relationships | 30 min         |
| 1.3  | Setup public assets directory structure   | 15 min         |
| 1.4  | Configure environment variables           | 15 min         |
| 1.5  | Create basic route structure              | 15 min         |
| 1.6  | Run migrations and verify                 | 15 min         |

### Phase 2: Admin Panel Foundation (Day 2-3)

| Step | Task                          | Estimated Time |
| ---- | ----------------------------- | -------------- |
| 2.1  | Create admin layout and views | 2 hours        |
| 2.2  | Implement Tour CRUD           | 2 hours        |
| 2.3  | Implement Scene CRUD          | 2 hours        |
| 2.4  | Implement Hotspot CRUD        | 2 hours        |
| 2.5  | Create image upload handler   | 1 hour         |

### Phase 3: A-Frame Viewer Integration (Day 4-5)

| Step | Task                        | Estimated Time |
| ---- | --------------------------- | -------------- |
| 3.1  | Create public viewer page   | 1 hour         |
| 3.2  | Implement hotspot rendering | 2 hours        |
| 3.3  | Implement scene navigation  | 1 hour         |
| 3.4  | Add gyro support (mobile)   | 1 hour         |
| 3.5  | Add TOC menu                | 1 hour         |

---

## 7. Key Files to Create

### New Files Checklist

```
[ ] app/Models/Tour.php
[ ] app/Models/Scene.php
[ ] app/Models/Hotspot.php
[ ] app/Models/HotspotTemplate.php
[ ] app/Http/Controllers/Admin/PanoramaController.php
[ ] database/migrations/2026_05_18_*.php (4 files)
[ ] database/factories/TourFactory.php
[ ] database/factories/SceneFactory.php
[ ] database/factories/HotspotFactory.php
[ ] public/hotspots/nav.svg
[ ] public/hotspots/info.svg
[ ] public/js/viewer/viewer.js
[ ] public/js/viewer/hotspots.js
[ ] public/js/viewer/navigation.js
[ ] public/js/viewer/toc-menu.js
[ ] public/js/viewer/gyro.js
[ ] resources/css/viewer.css
[ ] resources/views/admin/panorama/*.blade.php
[ ] resources/views/panorama/viewer/index.blade.php
[ ] routes/web.php (add panorama routes)
```

---

## 8. Verification Checklist

- [ ] Migrations run successfully
- [ ] Models have correct relationships
- [ ] Routes accessible in browser
- [ ] Image upload works
- [ ] Admin panel loads
- [ ] Public viewer loads
- [ ] Hotspots render correctly
- [ ] Scene navigation works
- [ ] Mobile gyro support (if device available)

---

## 9. Next Steps

Setelah approval, development dapat dimulai dengan:

1. **Code Mode** - Implementasi Phase 1 (Environment Setup)
2. **Code Mode** - Implementasi Phase 2 (Admin Panel)
3. **Code Mode** - Implementasi Phase 3 (Viewer)

Dokumen ini akan diupdate sesuai dengan progress development.

---

_Document prepared: 2026-05-18_  
_Project: Smart Prasada VR 360 Panorama Tour Editor_
