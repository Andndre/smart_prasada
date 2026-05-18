# VR 360 Panorama Tour Editor — Development Plan

**Project:** Smart Prasada VR 360 Panorama Tour Editor  
**Version:** 1.0.0  
**Last Updated:** 2026-05-18  
**Status:** Planning

---

## 1. Executive Summary

### 1.1 Project Overview

Smart Prasada is a Laravel 13 + A-Frame powered 360° panorama tour editor that enables administrators to create, manage, and publish immersive VR experiences. The system follows a hierarchical data model: **Tour → Scene → Hotspot**, where users navigate between panoramic scenes via clickable hotspots.

### 1.2 Current System Capabilities

| Component      | Implementation                                                     |
| -------------- | ------------------------------------------------------------------ |
| Backend        | Laravel 13 + PHP 8.3                                               |
| VR Rendering   | A-Frame 1.7.1 (CDN)                                                |
| Frontend Build | Vite 8 + Tailwind CSS 4                                            |
| Admin Editor   | 3-panel layout (scene list, A-Frame preview, tabbed forms)         |
| Public Viewer  | Scene transitions, hotspot navigation, gyroscope support, TOC menu |
| Media Storage  | Laravel public disk (`storage/app/public/panoramas/`)              |

### 1.3 Planning Scope

This document covers:

- Complete database schema with relationships
- Admin editor feature specifications
- Public viewer implementation details
- End-to-end workflow documentation
- File organization standards
- Development timeline and phases
- Third-party library recommendations
- Code examples and patterns
- Testing strategy
- Security considerations

---

## 2. Library and Dependency Setup

### 2.1 Core Dependencies (Current)

| Library      | Version       | Purpose            | CDN/Source             |
| ------------ | ------------- | ------------------ | ---------------------- |
| Laravel      | 13.x          | PHP Framework      | composer               |
| PHP          | 8.3+          | Runtime            | system                 |
| A-Frame      | 1.5.0         | VR Scene Rendering | aframe.io CDN          |
| Three.js     | (via A-Frame) | 3D WebGL           | aframe.io CDN          |
| Vite         | 8.x           | Build Tool         | npm                    |
| Tailwind CSS | 4.x           | Styling            | npm (@theme directive) |

### 2.2 Frontend Assets

| Asset             | Location                | Notes                                            |
| ----------------- | ----------------------- | ------------------------------------------------ |
| A-Frame Library   | CDN                     | `https://aframe.io/releases/1.5.0/aframe.min.js` |
| Viewer JS Modules | `public/js/viewer/`     | viewer.js, hotspots.js, toc-menu.js, gyro.js     |
| Hotspot SVGs      | `public/hotspots/`      | nav.svg, info.svg with `[[COLOR]]` placeholders  |
| Admin JS          | `resources/js/admin.js` | Transform controls, editor logic                 |
| CSS               | `resources/css/app.css` | Tailwind v4 with `@theme` directive              |

### 2.3 Recommended Additional Libraries

| Library                      | Version    | Purpose            | Justification                                 |
| ---------------------------- | ---------- | ------------------ | --------------------------------------------- |
| aframe-environment-component | 1.3.3      | Scene environments | Adds procedural sky, ground, lighting presets |
| aframe-look-at-component     | 0.5.1      | Billboard hotspots | Hotspots always face camera                   |
| aframe-extras                | latest     | Movement controls  | Enhanced navigation beyond basic              |
| aframe-inspector             | (built-in) | Dev tools          | Ctrl+Alt+I in editor for debugging            |

---

## 3. Database Schema & Migrations

### 3.1 Entity Relationship Diagram

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐       ┌───────────────────┐
│   kabupaten   │       │   tours      │       │   scenes     │       │    hotspots       │
├──────────────┤       ├──────────────┤       ├──────────────┤       ├───────────────────┤
│ id (PK)       │───┐   │ id (PK)      │───┐   │ id (PK)      │───┐   │ id (PK)           │
│ nama          │   │   │ name         │   │   │ tour_id (FK) │───┘   │ scene_id (FK)     │
│ google_map_link   │   │ slug (UNIQUE)│   │   │ name         │       │ label             │
│ cover_image   │   └──►│ cover_image  │       │ image        │       │ position_x/y/z    │
│ created_at    │       │ is_active    │       │ camera_x/y/z  │       │ rotation_x/y/z    │
│ updated_at    │       │ is_featured  │       │ order        │       │ target_scene_id   │───┐
└──────────────┘       │ description   │       │ created_at    │       │ color             │   │
       ▲               │ location_name │       │ updated_at    │       │ order             │   │
       │               │ location_google_map_link   │└──────────────┘       │ type              │   │
       │               │ kabupaten_id (FK)│       └─────────────────────────│ modal_title       │   │
       │               │ kategori_id (FK)│                              │ modal_content     │   │
       │               │ created_at      │                              │ modal_image        │   │
       │               │ updated_at      │                              │ template_id (FK)  │───┘
       │               └──────────────┘                                 └───────────────────┘
       │                                                                     ▲
       │               ┌──────────────┐       ┌────────────────────────────┘
       │               │  kategoris    │       │
       ├───────────────┤──────────────┤       │
       │               │ id (PK)       │       │
       │               │ nama_kategori │       │
       │               │ cover_image   │       │
       │               │ created_at    │       │
       │               │ updated_at    │       │
       └───────────────┴──────────────┴───────┘

┌─────────────────────────┐
│  hotspot_templates      │
├─────────────────────────┤
│ id (PK)                 │
│ name                    │
│ type (enum)             │
│ file_path               │
│ thumbnail_path          │
│ is_animated             │
│ default_color           │
│ created_at              │
│ updated_at              │
└─────────────────────────┘
```

### 3.2 Current Schema Definition

#### 3.2.1 tours

```php
// Migration: 2026_05_05_000001_create_tours_table.php + 2026_05_13_000003_add_tour_metadata_and_relations.php
Schema::create('tours', function (Blueprint $table) {
    $table->id();
    $table->string('name');                          // Tour title
    $table->string('slug')->unique();                // URL-friendly identifier
    $table->string('cover_image')->nullable();       // Tour thumbnail URL
    $table->boolean('is_active')->default(true);    // Visibility toggle
    $table->text('description')->nullable();        // Tour description
    $table->string('location_name')->nullable();     // Human-readable location
    $table->text('location_google_map_link')->nullable(); // Google Maps URL
    $table->boolean('is_featured')->default(false); // Featured tour flag
    $table->foreignId('kabupaten_id')->nullable()->constrained('kabupatens')->nullOnDelete();
    $table->foreignId('kategori_id')->nullable()->constrained('kategoris')->nullOnDelete();
    $table->timestamps();

    // Indexes
    $table->index(['is_active', 'is_featured']);
    $table->index('kabupaten_id');
    $table->index('kategori_id');
});
```

#### 3.2.2 scenes

```php
// Migration: 2026_05_05_000002_create_scenes_table.php
Schema::create('scenes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
    $table->string('name');                          // Scene name
    $table->string('image');                        // Panorama image URL
    $table->double('camera_x')->default(0);         // Camera position X
    $table->double('camera_y')->default(0);         // Camera position Y
    $table->double('camera_z')->default(0);         // Camera position Z
    $table->integer('order')->default(0);           // Scene order in tour
    $table->timestamps();

    // Indexes
    $table->index(['tour_id', 'order']);
});
```

#### 3.2.3 hotspots

```php
// Migration: 2026_05_05_000003_create_hotspots_table.php + 2026_05_11_000001_add_hotspot_type_and_modal_content.php
Schema::create('hotspots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
    $table->string('label');                         // Hotspot display label
    $table->double('position_x')->default(0);       // 3D position X
    $table->double('position_y')->default(-0.5);    // 3D position Y
    $table->double('position_z')->default(-4);      // 3D position Z
    $table->double('rotation_x')->default(0);       // 3D rotation X
    $table->double('rotation_y')->default(0);       // 3D rotation Y
    $table->double('rotation_z')->default(0);       // 3D rotation Z
    $table->foreignId('target_scene_id')->nullable()->constrained('scenes')->nullOnDelete();
    $table->string('color')->default('#00bcd4');    // Hotspot color (hex)
    $table->integer('order')->default(0);           // Display order
    $table->enum('type', ['navigation', 'info', 'text', 'compass'])->default('navigation');
    $table->string('modal_title')->nullable();       // Info modal title
    $table->text('modal_content')->nullable();      // Info modal content (HTML)
    $table->string('modal_image')->nullable();      // Info modal image URL
    $table->foreignId('template_id')->nullable()->constrained('hotspot_templates')->nullOnDelete();
    $table->timestamps();

    // Indexes
    $table->index(['scene_id', 'order']);
    $table->index('target_scene_id');
});
```

#### 3.2.4 hotspot_templates

```php
// Migration: 2026_05_14_102855_create_hotspot_templates_table.php
Schema::create('hotspot_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');                          // Template name
    $table->enum('type', ['navigation', 'info', 'text', 'compass']);
    $table->string('file_path');                     // Template file URL
    $table->string('thumbnail_path')->nullable();    // Preview thumbnail URL
    $table->boolean('is_animated')->default(false); // Animation flag
    $table->string('default_color')->nullable();     // Default hotspot color
    $table->timestamps();

    // Indexes
    $table->index('type');
});
```

#### 3.2.5 kabupaten & kategoris

```php
// Migration: 2026_05_13_000001_create_kabupatens_table.php
Schema::create('kabupatens', function (Blueprint $table) {
    $table->id();
    $table->string('nama');                          // Kabupaten name
    $table->text('google_map_link')->nullable();     // Google Maps URL
    $table->string('cover_image')->nullable();       // Cover image
    $table->timestamps();
});

// Migration: 2026_05_13_000002_create_kategoris_table.php
Schema::create('kategoris', function (Blueprint $table) {
    $table->id();
    $table->string('nama_kategori');                // Category name
    $table->string('cover_image')->nullable();      // Cover image
    $table->timestamps();
});
```

### 3.3 Recommended Schema Modifications

| Table             | Modification                         | Justification                          |
| ----------------- | ------------------------------------ | -------------------------------------- |
| tours             | Add `meta_title`, `meta_description` | SEO optimization for public pages      |
| tours             | Add `settings` JSON column           | Future flexibility for viewer settings |
| scenes            | Add `hotspot_defaults` JSON column   | Template for default hotspot configs   |
| hotspots          | Add `animation_config` JSON column   | Control animation duration, easing     |
| hotspot_templates | Add `preview_url` column             | Better admin preview                   |

---

## 4. Admin Editor Features Specification

### 4.1 Layout Structure

The admin editor uses a 3-panel layout:

```
┌─────────────────────────────────────────────────────────────────────┐
│  Header: Tour Name                                    [←] [+ Scene]  │
├───────────────┬─────────────────────────────┬───────────────────────┤
│               │                             │                       │
│  Scene List   │     A-Frame Preview         │   Tabbed Forms        │
│  (Left Panel) │     (Center Panel)          │   (Right Panel)       │
│               │                             │                       │
│  - Draggable  │  - Live panorama preview    │  [Info] [Scene] [HS] │
│  - Thumbnails │  - Transform controls       │                       │
│  - Hotspot    │  - Click to select hotspot  │  Form fields for     │
│    counts     │  - Inspector (Ctrl+Alt+I)   │  selected item        │
│               │                             │                       │
├───────────────┴─────────────────────────────┴───────────────────────┤
│  Position: X: 0.0  Y: -0.5  Z: -4.0                    [Inspector] │
└─────────────────────────────────────────────────────────────────────┘
```

### 4.2 Panel Specifications

#### 4.2.1 Left Panel — Scene List

| Feature     | Description                                        |
| ----------- | -------------------------------------------------- |
| Scene Items | Draggable list with thumbnail, name, hotspot count |
| Selection   | Click to select; first scene auto-selected on load |
| Add Scene   | Opens modal with name input + file upload          |
| Empty State | Shows camera icon + "Belum ada scene" message      |

#### 4.2.2 Center Panel — A-Frame Preview

| Feature            | Description                                          |
| ------------------ | ---------------------------------------------------- |
| Sky                | Displays panorama image for selected scene           |
| Camera             | look-controls with magicWindowTrackingEnabled: false |
| Cursor             | Raycaster targets `.editor-hs` class, far: 500       |
| Hotspots           | Rendered as a-image entities with `.editor-hs` class |
| Transform Controls | Gizmo-based position manipulation                    |
| Position Display   | Shows selected hotspot XYZ coordinates               |
| Inspector          | Ctrl+Alt+I opens A-Frame Inspector                   |

#### 4.2.3 Right Panel — Tabbed Forms

| Tab     | Content                                                                                |
| ------- | -------------------------------------------------------------------------------------- |
| Info    | Tour metadata (name, description, location, cover, county, category, featured, active) |
| Scene   | Selected scene edit form (name, image, camera position, actions)                       |
| Hotspot | Hotspot list + add button + manipulation instructions                                  |

### 4.3 Form Input Specifications

#### 4.3.1 Tour Form Fields

| Field                    | Type     | Validation                               | Notes                   |
| ------------------------ | -------- | ---------------------------------------- | ----------------------- |
| name                     | text     | required, max:255                        | Tour title              |
| description              | textarea | nullable                                 | Brief description       |
| location_name            | text     | nullable, max:255                        | Human-readable location |
| location_google_map_link | url      | nullable                                 | Google Maps URL         |
| cover_image              | file     | nullable, image, mimes:jpg,jpeg,png,webp | Upload + preview        |
| kabupaten_id             | select   | nullable, exists:kabupatens,id           | Dropdown                |
| kategori_id              | select   | nullable, exists:kategoris,id            | Dropdown                |
| is_featured              | checkbox | boolean                                  | Featured flag           |
| is_active                | checkbox | boolean                                  | Visibility toggle       |

#### 4.3.2 Scene Form Fields

| Field    | Type   | Validation        | Notes                       |
| -------- | ------ | ----------------- | --------------------------- |
| name     | text   | required, max:255 | Scene title                 |
| image    | text   | required          | Panorama URL or file upload |
| camera_x | number | nullable, numeric | Camera position X           |
| camera_y | number | nullable, numeric | Camera position Y           |
| camera_z | number | nullable, numeric | Camera position Z           |

#### 4.3.3 Hotspot Form Fields

| Field           | Type     | Validation                            | Notes                             |
| --------------- | -------- | ------------------------------------- | --------------------------------- |
| label           | text     | required, max:255                     | Hotspot label                     |
| type            | select   | in:navigation,info,text,compass       | Hotspot type                      |
| position_x      | number   | nullable, numeric                     | X coordinate                      |
| position_y      | number   | nullable, numeric                     | Y coordinate                      |
| position_z      | number   | nullable, numeric                     | Z coordinate                      |
| rotation_x      | number   | nullable, numeric                     | Rotation X                        |
| rotation_y      | number   | nullable, numeric                     | Rotation Y                        |
| rotation_z      | number   | nullable, numeric                     | Rotation Z                        |
| target_scene_id | select   | nullable, exists:scenes,id            | Navigation target                 |
| color           | color    | nullable                              | Hex color picker                  |
| template_id     | select   | nullable, exists:hotspot_templates,id | Icon template                     |
| modal_title     | text     | nullable, max:255                     | Info modal title                  |
| modal_content   | textarea | nullable                              | Info modal content (HTML allowed) |
| modal_image     | text     | nullable                              | Info modal image URL              |

### 4.4 Media Upload Handling

| Setting          | Value                           |
| ---------------- | ------------------------------- |
| Max File Size    | 50MB (51200 KB)                 |
| Allowed Mimes    | jpg, jpeg, png, webp            |
| Storage Location | `storage/app/public/panoramas/` |
| Public URL       | `/storage/panoramas/{filename}` |
| Upload Endpoint  | `POST /admin/upload`            |

**Upload Flow:**

1. Client sends `FormData` with `file` field
2. Server validates: `required|image|mimes:jpg,jpeg,png,webp|max:51200`
3. File stored to `panoramas/` disk with UUID filename
4. Returns `{ url: "/storage/panoramas/uuid.jpg", path: "panoramas/uuid.jpg" }`

### 4.5 Live A-Frame Preview

| Feature           | Implementation                                                                  |
| ----------------- | ------------------------------------------------------------------------------- |
| Hotspot Selection | Click hotspot → gizmo appears; second click within 300ms → edit modal           |
| Transform Gizmo   | A-Frame transform-controls component                                            |
| Position Sync     | On transform end, update form fields + save to server                           |
| Camera            | look-controls with pointerLockEnabled: false, magicWindowTrackingEnabled: false |
| Raycaster         | `objects: .editor-hs; far: 500` for hotspot detection                           |

---

## 5. Public Viewer Implementation

### 5.1 Component Structure

```html
<!-- A-Frame Scene -->
<a-scene
    id="panorama-scene"
    embedded
    vr-mode-ui="enabled: false"
    data-tour="@json($tour->toViewerJson())"
>
    <!-- Sky (panorama sphere) -->
    <a-sky id="panorama-sky" rotation="0 -90 0" radius="500"></a-sky>

    <!-- Lighting -->
    <a-light type="ambient" color="#fff" intensity="1"></a-light>

    <!-- Camera Rig -->
    <a-entity id="camera-rig" position="0 0 0">
        <a-camera
            id="camera"
            look-controls="pointerLockEnabled: false; magicWindowTrackingEnabled: false"
            wasd-controls="enabled: false"
            fov="80"
        >
            <a-entity
                id="cursor"
                cursor="fuse: false; rayOrigin: mouse"
                raycaster="objects: .clickable; far: 500"
                geometry="primitive: ring; radiusInner: 0.006; radiusOuter: 0.009"
                material="color: #00bcd4; shader: flat; opacity: 0.8"
                visible="false"
            ></a-entity>
        </a-camera>
    </a-entity>

    <!-- Hotspot Container -->
    <a-entity id="hotspots-container"></a-entity>
</a-scene>

<!-- UI Overlays -->
<div id="menu-toggle">...</div>
<div id="gyro-toggle">...</div>
<div id="toc-menu">...</div>
<div id="info-modal">...</div>
```

### 5.2 Hotspot Rendering

| Aspect            | Implementation                                              |
| ----------------- | ----------------------------------------------------------- |
| Container         | `<a-entity id="hotspots-container">` populated by JS        |
| Hotspot Entity    | `<a-entity position="X Y Z" rotation="X Y Z">`              |
| Clickable Element | `<a-image class="clickable" ...>` with raycaster target     |
| Label             | `<a-text value="label" position="0 0.65 0" align="center">` |
| SVG Templates     | Loaded from `/hotspots/nav.svg` and `/hotspots/info.svg`    |
| Color Replacement | `[[COLOR]]` placeholder replaced with hotspot color         |
| Animation         | Pulse animation on hover; `property: scale; ...`            |

### 5.3 Performance Optimizations

| Technique            | Implementation                                                        |
| -------------------- | --------------------------------------------------------------------- |
| Lazy Loading         | Scene transitions load next panorama only when needed                 |
| Reduced Motion       | `@media (prefers-reduced-motion: reduce)` disables hotspot animations |
| Texture Optimization | A-Frame's built-in texture compression                                |
| Loader Timeout       | Fallback hide after 8s even if texture not loaded                     |
| Overlay Transitions  | 300ms fade to black during scene changes                              |
| Dynamic Import       | JS modules loaded as ES modules                                       |

### 5.4 Responsiveness

| Breakpoint       | Behavior                                         |
| ---------------- | ------------------------------------------------ |
| Mobile (<768px)  | Show gyro toggle button; touch-friendly controls |
| Desktop (≥768px) | Hide gyro toggle; mouse-based navigation         |
| Full-screen      | VR mode available via A-Frame's vr-mode-ui       |

### 5.5 User Interactions

| Interaction              | Behavior                                          |
| ------------------------ | ------------------------------------------------- |
| Click Navigation Hotspot | Navigate to target_scene in tour                  |
| Click Info Hotspot       | Open info modal with title, content, image        |
| Gyro Toggle (mobile)     | Enable/disable device orientation tracking        |
| TOC Menu                 | Slide-in panel with scene list; click to navigate |
| Escape Key               | Close info modal                                  |
| Scene Transition         | 300ms black overlay fade                          |

---

## 6. End-to-End Workflow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              ADMIN WORKFLOW                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. Login to Admin                                                          │
│     └─► GET /admin/login → POST /admin/login (rate limited: 3/min)          │
│                                                                             │
│  2. Tour Management                                                         │
│     ├─► GET /admin/tours — List all tours                                  │
│     ├─► POST /admin/tours — Create new tour (slug auto-generated)          │
│     └─► GET /admin/tours/{slug} — Open editor                               │
│                                                                             │
│  3. Scene Management                                                        │
│     ├─► Upload panorama: POST /admin/upload → returns url                  │
│     ├─► Create scene: POST /admin/scenes (auto-set order = max+1)           │
│     ├─► Edit scene: PUT /admin/scenes/{id}                                  │
│     └─► Delete scene: DELETE /admin/scenes/{id} (cascades hotspots)          │
│                                                                             │
│  4. Hotspot Management                                                      │
│     ├─► Create hotspot: POST /admin/hotspots                                │
│     ├─► Position hotspot: click in A-Frame → transform gizmo → drag        │
│     ├─► Edit hotspot: PUT /admin/hotspots/{id}                              │
│     └─► Delete hotspot: DELETE /admin/hotspots/{id}                        │
│                                                                             │
│  5. Preview & Publish                                                        │
│     ├─► Set is_active = true (visible on landing)                          │
│     └─► Optional: set is_featured = true (featured section)                │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              PUBLIC WORKFLOW                                │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. Landing Page                                                            │
│     └─► GET / → Show featured tours + search/filter                         │
│                                                                             │
│  2. Browse Tours                                                            │
│     ├─► GET /kabupaten/{id} — Filter by county                             │
│     └─► GET /kategori/{id} — Filter by category                            │
│                                                                             │
│  3. View Tour (Public Viewer)                                               │
│     ├─► GET /tour/{slug} — Load viewer with tour data                      │
│     ├─► Click hotspots → Navigate between scenes                           │
│     ├─► Click info hotspots → Open modal                                    │
│     └─► Use TOC menu → Jump to specific scene                               │
│                                                                             │
│  4. API Access (optional)                                                   │
│     └─► GET /tour/{slug}/api — JSON tour data for external integrations    │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 7. File and Folder Structure

```
smart_prasada/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php          # Admin auth
│   │   │   ├── PanoramaController.php      # CRUD + viewer
│   │   │   └── HotspotTemplateController.php
│   │   └── Middleware/
│   │       └── AdminAuth.php               # Admin gate
│   └── Models/
│       ├── Tour.php                         # toViewerJson()
│       ├── Scene.php                        # toViewerJson()
│       ├── Hotspot.php                      # toViewerJson(), type constants
│       ├── HotspotTemplate.php              # toViewerJson()
│       ├── Kabupaten.php
│       └── Kategori.php
│
├── database/
│   ├── migrations/
│   │   ├── 2026_05_05_000001_create_tours_table.php
│   │   ├── 2026_05_05_000002_create_scenes_table.php
│   │   ├── 2026_05_05_000003_create_hotspots_table.php
│   │   ├── 2026_05_11_000001_add_hotspot_type_and_modal_content.php
│   │   ├── 2026_05_13_000001_create_kabupatens_table.php
│   │   ├── 2026_05_13_000002_create_kategoris_table.php
│   │   ├── 2026_05_13_000003_add_tour_metadata_and_relations.php
│   │   ├── 2026_05_14_102855_create_hotspot_templates_table.php
│   │   └── 2026_05_14_102925_add_template_id_to_hotspots_table.php
│   └── seeders/
│       ├── AdminUserSeeder.php
│       ├── KabupatenSeeder.php
│       └── KategoriSeeder.php
│
├── public/
│   ├── hotspots/
│   │   ├── nav.svg                          # Navigation hotspot SVG (has [[COLOR]])
│   │   └── info.svg                        # Info hotspot SVG (has [[COLOR]])
│   └── js/
│       └── viewer/
│           ├── viewer.js                    # Entry point
│           ├── hotspots.js                  # Hotspot rendering & interaction
│           ├── navigation.js                # Scene transition logic
│           ├── toc-menu.js                   # Table of contents
│           └── gyro.js                       # Gyroscope support
│
├── resources/
│   ├── css/
│   │   └── app.css                          # Tailwind v4 with @theme
│   ├── js/
│   │   ├── app.js                           # A-Frame import only
│   │   ├── admin.js                         # Admin editor logic
│   │   └── components/
│   │       └── transform-controls.js        # A-Frame gizmo integration
│   └── views/
│       ├── admin/
│       │   ├── layout.blade.php
│       │   ├── login.blade.php
│       │   ├── tours.blade.php
│       │   ├── kabupatens.blade.php
│       │   ├── kategoris.blade.php
│       │   └── panorama/
│       │       ├── layout.blade.php         # 3-panel editor layout
│       │       ├── editor-tour.blade.php    # Tour editor with A-Frame
│       │       └── hotspot-templates.blade.php
│       └── panorama/
│           ├── viewer/
│           │   └── index.blade.php          # Public viewer
│           ├── kabupaten.blade.php
│           ├── kategori.blade.php
│           └── welcome.blade.php            # Landing page
│
├── routes/
│   └── web.php                              # All route definitions
│
└── storage/
    └── app/
        └── public/
            ├── panoramas/                   # Uploaded panorama images
            └── hotspot-templates/           # Hotspot template files
```

---

## 8. Development Timeline Estimate

| Phase                                   | Duration | Description                                                             |
| --------------------------------------- | -------- | ----------------------------------------------------------------------- |
| **Phase 1: Core Setup & Migrations**    | 1–2 days | Review existing schema; add recommended columns; verify relationships   |
| **Phase 2: Admin Editor Enhancements**  | 3–5 days | Implement transform controls refinement, multi-select, batch operations |
| **Phase 3: Public Viewer Improvements** | 2–4 days | Performance optimization, accessibility, mobile refinements             |
| **Phase 4: Testing & Optimization**     | 2–3 days | Browser testing, E2E tests, performance profiling                       |
| **Phase 5: Documentation & Deployment** | 1–2 days | Documentation finalization, deployment procedures                       |

**Total Estimate: 9–16 days**

### Phase 1 Details: Core Setup and Migrations

- [ ] Review current migrations for completeness
- [ ] Create new migration for recommended schema additions (meta_title, meta_description, etc.)
- [ ] Add database indexes for query optimization
- [ ] Verify foreign key constraints and cascade behavior
- [ ] Test migration rollback scenarios

### Phase 2 Details: Admin Editor Enhancements

- [ ] Implement multi-hotspot selection
- [ ] Add batch delete for hotspots
- [ ] Refine transform controls UX (snapping, keyboard shortcuts)
- [ ] Add scene reordering via drag-and-drop
- [ ] Implement hotspot template preview in admin

### Phase 3 Details: Public Viewer Improvements

- [ ] Implement lazy loading for off-screen scenes
- [ ] Add WebP/AVIF auto-conversion for panoramas
- [ ] Implement prefers-reduced-motion correctly
- [ ] Add keyboard navigation (arrow keys for scene switching)
- [ ] Accessibility audit (ARIA labels, focus management)

### Phase 4 Details: Testing and Optimization

- [ ] Cross-browser testing (Chrome, Firefox, Safari, Edge)
- [ ] Mobile device testing (iOS Safari, Android Chrome)
- [ ] Performance profiling with Lighthouse
- [ ] Write Playwright/Cypress E2E tests
- [ ] Load testing with multiple concurrent viewers

### Phase 5 Details: Documentation and Deployment

- [ ] Update AGENTS.md with new patterns
- [ ] Create deployment runbook
- [ ] Document rollback procedures
- [ ] Update README with new features

---

## 9. Third-Party Libraries

### 9.1 Core Libraries (In Use)

| Library  | Version   | CDN/Source    | Purpose                               |
| -------- | --------- | ------------- | ------------------------------------- |
| A-Frame  | 1.5.0     | aframe.io CDN | VR scene rendering, WebGL abstraction |
| Three.js | (bundled) | via A-Frame   | Low-level 3D engine                   |

### 9.2 Recommended Additions

| Library                      | Version | Purpose                 | Justification                                                  |
| ---------------------------- | ------- | ----------------------- | -------------------------------------------------------------- |
| aframe-environment-component | 1.3.3   | Procedural environments | Adds sky gradients, fog, lighting presets without manual setup |
| aframe-look-at-component     | 0.5.1   | Billboard hotspots      | Keeps hotspot labels facing camera                             |
| aframe-haptics-component     | 0.1.0   | VR haptic feedback      | Future VR controller support                                   |
| GSAP                         | 3.12+   | Animation sequencing    | Better control over hotspot transitions                        |
| Pannellum                    | (alt)   | Alternative panorama    | Consider for fallback if A-Frame issues arise                  |

### 9.3 Installation Commands

```bash
# A-Frame environment component
# Add to package.json or via CDN in blade
<script src="https://unpkg.com/aframe-environment-component@1.3.3/dist/aframe-environment-component.min.js"></script>

# aframe-look-at-component
<script src="https://unpkg.com/aframe-look-at-component@0.5.1/dist/aframe-look-at-component.min.js"></script>
```

---

## 10. Code Examples

### 10.1 Model with toViewerJson()

```php
<?php
// app/Models/Hotspot.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hotspot extends Model
{
    use HasFactory;

    // Type constants
    const TYPE_NAVIGATION = 'navigation';
    const TYPE_INFO = 'info';
    const TYPE_TEXT = 'text';
    const TYPE_COMPASS = 'compass';

    protected $fillable = [
        'scene_id', 'label', 'position_x', 'position_y', 'position_z',
        'rotation_x', 'rotation_y', 'rotation_z', 'target_scene_id', 'color', 'order',
        'type', 'modal_title', 'modal_content', 'modal_image', 'template_id',
    ];

    protected $casts = [
        'position_x' => 'float', 'position_y' => 'float', 'position_z' => 'float',
        'rotation_x' => 'float', 'rotation_y' => 'float', 'rotation_z' => 'float',
        'order' => 'integer',
    ];

    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    public function targetScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'target_scene_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(HotspotTemplate::class);
    }

    public function isNavigation(): bool
    {
        return $this->type === self::TYPE_NAVIGATION;
    }

    public function isInfo(): bool
    {
        return $this->type === self::TYPE_INFO;
    }

    public function toViewerJson(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type ?? self::TYPE_NAVIGATION,
            'position' => "{$this->position_x} {$this->position_y} {$this->position_z}",
            'rotation' => "{$this->rotation_x} {$this->rotation_y} {$this->rotation_z}",
            'targetScene' => $this->target_scene_id,
            'color' => $this->color,
            'modalTitle' => $this->modal_title,
            'modalContent' => $this->modal_content,
            'modalImage' => $this->modal_image,
            'template' => $this->relationLoaded('template')
                ? $this->template?->toViewerJson()
                : null,
        ];
    }
}
```

### 10.2 Controller CRUD Operations

```php
<?php
// app/Http/Controllers/PanoramaController.php (excerpt)

class PanoramaController extends Controller
{
    // ── Scene CRUD ───────────────────────────────────────────────────

    public function storeScene(Request $request): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $data = $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'name' => 'required|string|max:255',
            'image' => 'required|string',
            'camera_x' => 'nullable|numeric',
            'camera_y' => 'nullable|numeric',
            'camera_z' => 'nullable|numeric',
        ]);

        // Auto-calculate order as max(order) + 1
        $data['order'] = Scene::where('tour_id', $data['tour_id'])->max('order') + 1;

        $scene = Scene::create($data);
        $scene->load('hotspots');

        return response()->json($scene, 201);
    }

    public function updateScene(Request $request, int $id): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $scene = Scene::findOrFail($id);
        $scene->update($request->only([
            'name', 'image', 'camera_x', 'camera_y', 'camera_z', 'order'
        ]));

        return response()->json($scene->load('hotspots'));
    }

    public function destroyScene(Request $request, int $id): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        Scene::destroy($id);

        return response()->json(['ok' => true]);
    }

    // ── Hotspot CRUD ─────────────────────────────────────────────────

    public function storeHotspot(Request $request): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $data = $request->validate([
            'scene_id' => 'required|exists:scenes,id',
            'label' => 'required|string|max:255',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'position_z' => 'nullable|numeric',
            'rotation_x' => 'nullable|numeric',
            'rotation_y' => 'nullable|numeric',
            'rotation_z' => 'nullable|numeric',
            'target_scene_id' => 'nullable|exists:scenes,id',
            'color' => 'nullable|string',
            'type' => 'nullable|in:navigation,info,text,compass',
            'modal_title' => 'nullable|string|max:255',
            'modal_content' => 'nullable|string',
            'modal_image' => 'nullable|string',
            'template_id' => 'nullable|exists:hotspot_templates,id',
        ]);

        // Set defaults
        $data['color'] = $data['color'] ?? '#00bcd4';
        $data['type'] = $data['type'] ?? 'navigation';
        $data['order'] = Hotspot::where('scene_id', $data['scene_id'])->max('order') + 1;

        $hotspot = Hotspot::create($data);
        $hotspot->load('targetScene', 'template');

        return response()->json($hotspot, 201);
    }

    // ── Image Upload ─────────────────────────────────────────────────

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,webp|max:51200',
        ]);

        $path = $request->file('file')->store('panoramas', 'public');
        $url = "/storage/{$path}";

        return response()->json(['url' => $url, 'path' => $path]);
    }
}
```

### 10.3 Blade View with A-Frame Scene

```blade
{{-- resources/views/panorama/viewer/index.blade.php --}}

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tour->name }} - 360° Tour</title>
    @vite('resources/css/app.css')
    <script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>
</head>

<body class="overflow-hidden bg-black">
    <!-- Loading Screen -->
    <div id="viewer-loader" class="z-65 fixed inset-0 flex items-center justify-center bg-black">
        <p class="animate-pulse text-white/60">Memuat panorama...</p>
    </div>

    <!-- A-Frame Scene with tour data -->
    <a-scene id="panorama-scene"
             embedded
             vr-mode-ui="enabled: false"
             loading-screen="dots-color: #00bcd4; background-color: #000"
             data-tour='@json($tour->toViewerJson())'>
        <a-sky id="panorama-sky" rotation="0 -90 0" radius="500"></a-sky>
        <a-light type="ambient" color="#fff" intensity="1"></a-light>

        <a-entity id="camera-rig" position="0 0 0">
            <a-camera id="camera" fov="80">
                <a-entity id="cursor" cursor="fuse: false; rayOrigin: mouse"
                          raycaster="objects: .clickable; far: 500"></a-entity>
            </a-camera>
        </a-entity>

        <a-entity id="hotspots-container"></a-entity>
    </a-scene>

    <!-- UI Overlay -->
    <button id="menu-toggle" class="fixed top-4 left-4 z-30">Menu</button>

    <!-- Scripts -->
    <script type="module" src="{{ asset('js/viewer/viewer.js') }}"></script>
</body>
</html>
```

### 10.4 JavaScript Hotspot Module

```javascript
// public/js/viewer/hotspots.js

/**
 * Initialize hotspots module
 * @param {HTMLElement} scene - A-Frame scene element
 * @param {Array} hotspotsData - Array of hotspot data from tour
 * @param {Function} goToScene - Navigation callback
 * @param {Function} openModal - Info modal callback
 * @returns {Object} Public methods
 */
export function initHotspots(scene, hotspotsData, goToScene, openModal) {
    const container = document.getElementById("hotspots-container");

    // SVG templates loaded once
    let navSvg = null;
    let infoSvg = null;

    async function loadTemplates() {
        const [navRes, infoRes] = await Promise.all([
            fetch("/hotspots/nav.svg"),
            fetch("/hotspots/info.svg"),
        ]);
        navSvg = await navRes.text();
        infoSvg = await infoRes.text();
    }

    function render(hotspots) {
        container.innerHTML = "";

        if (!hotspots || hotspots.length === 0) return;

        hotspots.forEach((hs) => {
            const isInfo = hs.type === "info";
            const template = isInfo ? infoSvg : navSvg;
            const svgWithColor = template.replace(
                "[[COLOR]]",
                hs.color || "#00bcd4",
            );

            const entity = document.createElement("a-entity");
            entity.setAttribute("position", hs.position);
            if (hs.rotation) entity.setAttribute("rotation", hs.rotation);

            const image = document.createElement("a-image");
            image.setAttribute("class", "clickable");
            image.setAttribute(
                "src",
                `data:image/svg+xml;base64,${btoa(svgWithColor)}`,
            );
            image.setAttribute("width", "0.55");
            image.setAttribute("height", "0.55");
            image.setAttribute(
                "material",
                "shader: flat; side: double; transparent: true",
            );

            // Click handler
            image.addEventListener("click", () => {
                if (isInfo) {
                    openModal(hs);
                } else if (hs.targetScene) {
                    const sceneIndex = tourData.scenes.findIndex(
                        (s) => s.id === hs.targetScene,
                    );
                    if (sceneIndex !== -1) goToScene(sceneIndex);
                }
            });

            // Hover animation
            image.addEventListener("mouseenter", () => {
                image.setAttribute(
                    "animation",
                    "property: scale; to: 1.15 1.15 1.15; dur: 200",
                );
            });
            image.addEventListener("mouseleave", () => {
                image.setAttribute(
                    "animation",
                    "property: scale; to: 1 1 1; dur: 200",
                );
            });

            // Label
            const label = document.createElement("a-text");
            label.setAttribute("value", (isInfo ? "ℹ " : "→ ") + hs.label);
            label.setAttribute("align", "center");
            label.setAttribute("width", "4");
            label.setAttribute("position", "0 0.65 0");
            label.setAttribute("color", hs.color || "#00bcd4");

            entity.appendChild(image);
            entity.appendChild(label);
            container.appendChild(entity);
        });
    }

    // Initialize templates on load
    loadTemplates();

    return { render };
}
```

### 10.5 JavaScript Viewer Initialization

```javascript
// public/js/viewer/viewer.js

import { initHotspots } from "./hotspots.js";
import { initTocMenu } from "./toc-menu.js";
import { initGyro } from "./gyro.js";

document.addEventListener("DOMContentLoaded", () => {
    const scene = document.getElementById("panorama-scene");
    const sky = document.getElementById("panorama-sky");
    const loader = document.getElementById("viewer-loader");
    const overlay = document.getElementById("scene-overlay");

    // Load tour data from data attribute
    const tourData = JSON.parse(scene.dataset.tour || "{}");

    if (!tourData.scenes || tourData.scenes.length === 0) {
        console.error("[viewer] No scenes found");
        return;
    }

    let currentIndex = 0;

    // Initialize modules
    const hotspots = initHotspots(
        scene,
        tourData.scenes[0].hotspots,
        goToScene,
        openModal,
    );
    const toc = initTocMenu(tourData, goToScene);
    initGyro(scene, document.getElementById("gyro-toggle"));

    // Hide loader when ready
    scene.addEventListener("loaded", () => {
        loadScene(0);
        scene.addEventListener(
            "materialtextureloaded",
            () => {
                loader.classList.add("hidden");
            },
            { once: true },
        );
        setTimeout(() => loader.classList.add("hidden"), 8000);
    });

    async function loadScene(index) {
        const sceneData = tourData.scenes[index];
        if (!sceneData) return;

        overlay.style.opacity = "1";
        overlay.style.pointerEvents = "auto";

        setTimeout(() => {
            sky.setAttribute("src", resolveUrl(sceneData.image));
            hotspots.render(sceneData.hotspots);
            toc.updateActive(index);
            currentIndex = index;

            overlay.style.opacity = "0";
            overlay.style.pointerEvents = "none";
        }, 300);
    }

    function goToScene(index) {
        if (index < 0 || index >= tourData.scenes.length) return;
        if (index === currentIndex) return;
        loadScene(index);
    }

    function resolveUrl(path) {
        if (!path) return "";
        if (path.startsWith("http")) return path;
        return (
            window.location.origin + (path.startsWith("/") ? path : "/" + path)
        );
    }

    function openModal(hotspot) {
        // Modal implementation
    }
});
```

---

## 11. Testing Strategy

### 11.1 Unit Tests

| Model/Class                       | Test Cases                                                      |
| --------------------------------- | --------------------------------------------------------------- |
| Tour.toViewerJson()               | Returns correct structure, includes scenes, hotspots, relations |
| Scene.toViewerJson()              | Camera position formatting, hotspot nesting                     |
| Hotspot.toViewerJson()            | Position/rotation string formatting, null handling              |
| Hotspot.isNavigation() / isInfo() | Type checking works correctly                                   |
| Tour Slug Generation              | Auto-generate, handle collisions (-1, -2, etc.)                 |

**Example Test:**

```php
<?php
// tests/Unit/Models/TourTest.php

namespace Tests\Unit\Models;

use App\Models\Tour;
use App\Models\Scene;
use App\Models\Hotspot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_viewer_json_includes_scenes_with_hotspots(): void
    {
        $tour = Tour::factory()->create();
        $scene = Scene::factory()->create(['tour_id' => $tour->id]);
        $hotspot = Hotspot::factory()->create(['scene_id' => $scene->id]);

        $tour->load('scenes.hotspots');

        $json = $tour->toViewerJson();

        $this->assertArrayHasKey('scenes', $json);
        $this->assertCount(1, $json['scenes']);
        $this->assertArrayHasKey('hotspots', $json['scenes'][0]);
        $this->assertCount(1, $json['scenes'][0]['hotspots']);
    }

    public function test_slug_auto_generated_from_name(): void
    {
        $tour = Tour::factory()->create(['name' => 'Monumen Jakarta']);

        $this->assertEquals('monumen-jakarta', $tour->slug);
    }

    public function test_slug_collision_handling(): void
    {
        Tour::factory()->create(['slug' => 'test-tour']);
        $tour = Tour::factory()->create(['name' => 'Test Tour']);

        $this->assertNotEquals('test-tour', $tour->slug);
        $this->assertStringStartsWith('test-tour-', $tour->slug);
    }
}
```

### 11.2 Feature/Integration Tests

| Feature      | Test Cases                                                            |
| ------------ | --------------------------------------------------------------------- |
| Admin Auth   | Login success, login failure (rate limited), logout clears session    |
| Tour CRUD    | Create tour, update tour, delete tour (cascades scenes/hotspots)      |
| Scene CRUD   | Create scene with auto-order, update scene, delete with cascade       |
| Hotspot CRUD | Create hotspot (nav/info types), update position, delete              |
| Image Upload | Valid image upload (jpg/png/webp), invalid mime rejection, size limit |
| Viewer Page  | Loads with tour data, hotspot rendering, scene navigation             |

**Example Test:**

```php
<?php
// tests/Feature/PanoramaAdminTest.php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Scene;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanoramaAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_scene_creation_auto_sets_order(): void
    {
        $tour = Tour::factory()->create();

        $scene1 = $this->postJson('/admin/scenes', [
            'tour_id' => $tour->id,
            'name' => 'Scene 1',
            'image' => '/panoramas/test.jpg'
        ]);

        $scene2 = $this->postJson('/admin/scenes', [
            'tour_id' => $tour->id,
            'name' => 'Scene 2',
            'image' => '/panoramas/test2.jpg'
        ]);

        $this->assertEquals(0, $scene1->json('order'));
        $this->assertEquals(1, $scene2->json('order'));
    }

    public function test_scene_requires_json_header(): void
    {
        $tour = Tour::factory()->create();

        $response = $this->post('/admin/scenes', [
            'tour_id' => $tour->id,
            'name' => 'Test',
            'image' => 'test.jpg'
        ]);

        $response->assertStatus(406);
    }

    public function test_hotspot_delete_cascades_properly(): void
    {
        $scene = Scene::factory()->create();
        $hotspot = Hotspot::factory()->create(['scene_id' => $scene->id]);

        $this->deleteJson("/admin/hotspots/{$hotspot->id}");

        $this->assertDatabaseMissing('hotspots', ['id' => $hotspot->id]);
    }
}
```

### 11.3 Browser/E2E Testing Considerations

| Browser | OS            | Priority |
| ------- | ------------- | -------- |
| Chrome  | Windows/macOS | High     |
| Firefox | Windows/macOS | High     |
| Safari  | macOS/iOS     | Medium   |
| Edge    | Windows       | Medium   |

**Playwright Test Example:**

```javascript
// tests/e2e/viewer.spec.js

import { test, expect } from "@playwright/test";

test("viewer loads and displays hotspots", async ({ page }) => {
    await page.goto("/tour/test-tour");

    // Wait for A-Frame scene to load
    await page.waitForSelector("a-scene[data-tour]", { timeout: 10000 });

    // Verify hotspots container exists
    const hotspots = page.locator("#hotspots-container");
    await expect(hotspots).toBeVisible();

    // Click first hotspot if exists
    const clickableHotspot = page.locator(".clickable").first();
    if (await clickableHotspot.isVisible()) {
        await clickableHotspot.click();
    }
});

test("scene navigation works", async ({ page }) => {
    await page.goto("/tour/test-tour");
    await page.waitForSelector("a-scene.loaded");

    // Open TOC menu
    await page.click("#menu-toggle");

    // Click second scene in menu
    await page.click(".toc-item:nth-child(2)");

    // Verify scene changed (overlay fade)
    await expect(page.locator("#scene-overlay")).toHaveCSS("opacity", "0");
});
```

---

## 12. Security Considerations and Best Practices

### 12.1 Input Validation and Sanitization

| Area                      | Implementation                                                                    |
| ------------------------- | --------------------------------------------------------------------------------- |
| All CRUD requests         | Require `Accept: application/json` header → 406 if missing                        |
| Form requests             | Laravel validation rules on all inputs                                            |
| Tour/Scene/Hotspot fields | Whitelist allowed fields via `$fillable`                                          |
| Modal content             | HTML allowed but sanitized on display (Blade `{!! !!}` should use `e()` or strip) |

```php
// Controller validation example
$data = $request->validate([
    'name' => 'required|string|max:255',
    'type' => 'nullable|in:navigation,info,text,compass',
    'position_x' => 'nullable|numeric',
    // ... other fields
]);
```

### 12.2 File Upload Security

| Check     | Implementation                                          |
| --------- | ------------------------------------------------------- | ------------------------ |
| File type | Server-side mime validation: `image                     | mimes:jpg,jpeg,png,webp` |
| File size | Max 50MB: `max:51200` (kilobytes)                       |
| Filename  | UUID-based to prevent path traversal                    |
| Storage   | Non-public path with public symlink via Laravel storage |

```php
// Upload validation
$request->validate([
    'file' => 'required|image|mimes:jpg,jpeg,png,webp|max:51200',
]);

// UUID filename generation
$filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
$path = $file->storeAs('panoramas', $filename, 'public');
```

### 12.3 Authentication for Admin Routes

| Measure       | Implementation                                         |
| ------------- | ------------------------------------------------------ |
| Middleware    | `admin` middleware on all `/admin/*` routes            |
| Session       | Laravel session-based auth                             |
| Rate limiting | Login endpoint: `throttle:3,1` (3 attempts per minute) |
| CSRF          | Laravel CSRF middleware on all POST/PUT/DELETE         |

```php
// routes/web.php
Route::middleware('admin')->prefix('admin')->group(function () {
    // All admin routes protected
});

Route::post('/admin/login', [AdminController::class, 'login'])
    ->middleware('throttle:3,1');
```

### 12.4 Rate Limiting

| Endpoint             | Limit  | Purpose               |
| -------------------- | ------ | --------------------- |
| POST /admin/login    | 3/min  | Prevent brute force   |
| POST /admin/scenes   | 60/min | Prevent spam          |
| POST /admin/hotspots | 60/min | Prevent spam          |
| POST /admin/upload   | 30/min | Prevent storage abuse |

### 12.5 XSS Prevention

| Content        | Mitigation                                                |
| -------------- | --------------------------------------------------------- |
| Hotspot labels | Escaped in A-Frame via text component                     |
| Modal content  | Rendered with `innerHTML` but content is admin-only input |
| URL fields     | Validated as URL format                                   |
| Color values   | HTML5 color input (hex only)                              |

**Important:** Modal content accepts HTML (for rich text). If this content is ever user-generated (not admin-only), implement HTML sanitization:

```php
// If modal content ever comes from non-admin users
use Illuminate\Support\HtmlString;

$safeContent = new HtmlString(
    \Illuminate\Support\Str::markdown($hotspot->modal_content)
);
```

### 12.6 SQL Injection Prevention

| Practice        | Implementation                         |
| --------------- | -------------------------------------- |
| ORM usage       | Eloquent for all database operations   |
| Query builder   | When needed, use parameterized queries |
| Mass assignment | `$fillable` whitelist on all models    |

```php
// ✅ Safe - Eloquent ORM
Tour::where('slug', $slug)->firstOrFail();

// ✅ Safe - parameterized query
DB::select('SELECT * FROM tours WHERE slug = ?', [$slug]);

// ❌ Unsafe - never do this
// DB::select("SELECT * FROM tours WHERE slug = '$slug'");
```

### 12.7 CSRF Protection

| Method     | Implementation                            |
| ---------- | ----------------------------------------- |
| Forms      | Laravel CSRF token via `@csrf` directive  |
| AJAX       | `X-CSRF-TOKEN` header with `csrf_token()` |
| API routes | Same middleware applies                   |

```javascript
// AJAX header
fetch("/admin/scenes", {
    method: "POST",
    headers: {
        "X-CSRF-TOKEN": "{{ csrf_token() }}",
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    body: JSON.stringify(data),
});
```

### 12.8 Security Checklist

- [ ] All admin routes behind `admin` middleware
- [ ] Login rate limited (3 attempts/minute)
- [ ] File uploads validated (type + size)
- [ ] UUID filenames prevent path traversal
- [ ] No raw SQL queries (Eloquent only)
- [ ] CSRF tokens on all state-changing requests
- [ ] Session timeout configured
- [ ] HTTPS enforced in production
- [ ] `APP_DEBUG=false` in production
- [ ] Sensitive env vars not committed to git

---

## Appendix A: Route Summary

| Method | Route                | Auth | Returns                   |
| ------ | -------------------- | ---- | ------------------------- |
| GET    | /admin/login         | No   | Login form                |
| POST   | /admin/login         | No   | Auth (rate limited)       |
| POST   | /admin/logout        | Yes  | Clear session             |
| GET    | /admin/tours         | Yes  | Tour list                 |
| GET    | /admin/tours/{slug}  | Yes  | Editor view               |
| POST   | /admin/tours         | Yes  | Create (JSON or redirect) |
| PUT    | /admin/tours/{slug}  | Yes  | Update JSON               |
| DELETE | /admin/tours/{slug}  | Yes  | Delete (JSON or redirect) |
| POST   | /admin/scenes        | Yes  | Create JSON               |
| PUT    | /admin/scenes/{id}   | Yes  | Update JSON               |
| DELETE | /admin/scenes/{id}   | Yes  | Delete JSON               |
| POST   | /admin/hotspots      | Yes  | Create JSON               |
| PUT    | /admin/hotspots/{id} | Yes  | Update JSON               |
| DELETE | /admin/hotspots/{id} | Yes  | Delete JSON               |
| POST   | /admin/upload        | Yes  | Upload JSON               |
| GET    | /tour/{slug}         | No   | Viewer page               |
| GET    | /tour/{slug}/api     | No   | Tour JSON                 |
| GET    | /                    | No   | Landing page              |
| GET    | /kabupaten/{id}      | No   | County listing            |
| GET    | /kategori/{id}       | No   | Category listing          |

---

## Appendix B: Hotspot Type Reference

| Type         | Icon            | Behavior                 | Fields                                  |
| ------------ | --------------- | ------------------------ | --------------------------------------- |
| `navigation` | Arrow (nav.svg) | Navigate to target_scene | target_scene_id                         |
| `info`       | Info (info.svg) | Open modal               | modal_title, modal_content, modal_image |
| `text`       | Label only      | Display text label       | —                                       |
| `compass`    | Compass         | Points to direction      | rotation_x/y/z                          |

---

## Appendix C: A-Frame Component Registry

| Component          | Purpose                 | Used In                                       |
| ------------------ | ----------------------- | --------------------------------------------- |
| look-controls      | Camera rotation         | Editor + Viewer                               |
| wasd-controls      | Keyboard movement       | (disabled in viewer)                          |
| raycaster          | Hotspot click detection | Editor (`.editor-hs`) + Viewer (`.clickable`) |
| cursor             | Visual feedback         | Both                                          |
| transform-controls | Gizmo manipulation      | Editor only                                   |
| animation          | Hotspot pulse           | Editor + Viewer                               |
| embedded           | Responsive layout       | Both                                          |
| vr-mode-ui         | VR button               | Viewer only                                   |

---

_Document generated: 2026-05-18_
_Project: Smart Prasada VR 360 Panorama Tour Editor_
