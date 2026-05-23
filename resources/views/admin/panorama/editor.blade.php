<x-app-layout>
    <!-- A-Frame Scripts MUST be loaded before a-scene -->
    <script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>
    <script src="https://unpkg.com/aframe-look-at-component@0.8.0/dist/aframe-look-at-component.min.js"></script>
    
    <div class="h-[calc(100vh-4rem)] flex flex-col bg-gray-100 overflow-hidden" x-data="panoramaEditor({{ $situs->situs_id }})" x-init="init()">
        
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-4 py-3 flex justify-between items-center shadow-sm z-10 shrink-0">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.situs.show', $situs->situs_id) }}" class="text-gray-500 hover:text-gray-700 p-2 rounded-full hover:bg-gray-100 transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 leading-tight">Editor 360° Panorama</h1>
                    <p class="text-sm text-gray-500 leading-tight">{{ $situs->nama }}</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <button @click="loadScenes" class="btn btn-secondary text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md font-medium border border-gray-300 transition">
                    <i class="fas fa-sync-alt mr-2" :class="{'fa-spin': isLoading}"></i>
                    Refresh
                </button>
                <a href="{{ route('guest.situs.panorama', $situs->situs_id) }}" target="_blank" class="btn btn-primary text-sm bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-md font-medium shadow-sm transition">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Lihat Publik
                </a>
            </div>
        </div>

        <!-- Main 3-Panel Layout -->
        <div class="flex flex-1 overflow-hidden relative">
            
            <!-- Left Panel: Scene List -->
            <div class="w-72 bg-white border-r border-gray-200 flex flex-col shrink-0 relative z-10 shadow-lg">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Daftar Adegan</h2>
                    <button @click="openSceneModal(null)" class="text-cyan-600 hover:text-cyan-800 bg-cyan-50 hover:bg-cyan-100 p-1.5 rounded-md transition" title="Tambah Adegan Baru">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-3 space-y-3">
                    <template x-if="scenes.length === 0 && !isLoading">
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-camera text-4xl mb-3 opacity-30"></i>
                            <p class="text-sm">Belum ada adegan.</p>
                        </div>
                    </template>
                    
                    <template x-for="(scene, index) in scenes" :key="scene.id">
                        <div 
                            class="border rounded-lg p-2 cursor-pointer transition-all duration-200 group relative"
                            :class="state.activeSceneId === scene.id ? 'border-cyan-500 bg-cyan-50 shadow-sm' : 'border-gray-200 hover:border-cyan-300 hover:bg-gray-50'"
                            @click="selectScene(scene.id)"
                        >
                            <div class="aspect-video w-full bg-gray-200 rounded mb-2 overflow-hidden bg-cover bg-center" :style="`background-image: url('${scene.image}')`"></div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-medium text-sm text-gray-900 truncate w-40" x-text="scene.name"></h3>
                                    <p class="text-xs text-gray-500"><i class="fas fa-dot-circle mr-1 text-cyan-600"></i><span x-text="scene.hotspots ? scene.hotspots.length : 0"></span> Hotspot</p>
                                </div>
                                <button @click.stop="deleteScene(scene.id)" class="text-gray-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition p-1">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Center Panel: A-Frame Preview -->
            <div class="flex-1 bg-black relative" id="aframe-container">
                <!-- A-Frame Loading Overlay -->
                <div x-show="isLoading" class="absolute inset-0 bg-black/80 flex items-center justify-center z-50 text-white">
                    <div class="text-center">
                        <i class="fas fa-circle-notch fa-spin text-4xl text-cyan-500 mb-3"></i>
                        <p>Memuat Data...</p>
                    </div>
                </div>

                <!-- Empty State -->
                <div x-show="!state.activeSceneId && !isLoading" class="absolute inset-0 bg-gray-900 flex items-center justify-center z-40 text-gray-400">
                    <div class="text-center">
                        <i class="fas fa-hand-pointer text-5xl mb-4 opacity-50"></i>
                        <p class="text-lg">Pilih adegan dari panel kiri untuk mulai mengedit</p>
                    </div>
                </div>

                <!-- A-Frame Scene -->
                <a-scene embedded vr-mode-ui="enabled: false" class="w-full h-full" id="editor-scene" cursor="rayOrigin: mouse" raycaster="objects: .clickable">
                    <a-assets>
                        <img id="hotspot-nav" crossorigin="anonymous" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512' viewBox='0 0 512 512'><path fill='%23ffffff' d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z'/></svg>">
                        <img id="hotspot-info" crossorigin="anonymous" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512' viewBox='0 0 512 512'><path fill='%23ffffff' d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z'/></svg>">
                    </a-assets>
                    
                    <a-sky id="editor-sky" class="clickable" rotation="0 -90 0" color="#fff" radius="500"></a-sky>
                    
                    <!-- Lighting -->
                    <a-light type="ambient" color="#fff" intensity="1"></a-light>
                    
                    <a-entity id="camera-rig">
                        <a-camera id="editor-camera" look-controls="pointerLockEnabled: false; reverseMouseDrag: true" fov="80"></a-camera>
                    </a-entity>

                    <a-entity id="editor-hotspots"></a-entity>
                </a-scene>

                <!-- Hotspot Controls Overlay -->
                <div x-show="state.activeSceneId" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/60 backdrop-blur-md rounded-full px-6 py-2 text-white text-sm flex items-center space-x-6 z-30 shadow-lg border border-white/20">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-mouse-pointer text-cyan-400"></i>
                        <span>Klik scene untuk menambah Hotspot</span>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Contextual Forms -->
            <div class="w-80 bg-white border-l border-gray-200 flex flex-col shrink-0 shadow-[-4px_0_15px_rgba(0,0,0,0.05)] z-20">
                
                <div x-show="!state.activeSceneId" class="flex-1 flex items-center justify-center p-6 text-center text-gray-400">
                    <div>
                        <i class="fas fa-edit text-4xl mb-3 opacity-30"></i>
                        <p class="text-sm">Pilih adegan atau hotspot untuk melihat properti.</p>
                    </div>
                </div>

                <!-- Scene Properties Form -->
                <div x-show="state.activeSceneId && !state.activeHotspotId" class="flex flex-col h-full">
                    <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h2 class="font-semibold text-gray-800"><i class="fas fa-image mr-2 text-gray-500"></i>Properti Adegan</h2>
                        <button @click="openSceneModal(activeScene)" class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                    </div>
                    <div class="p-4 overflow-y-auto">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Nama</label>
                                <div class="text-sm font-medium text-gray-900" x-text="activeScene?.name"></div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-2">Daftar Hotspot</label>
                                <div class="space-y-2">
                                    <template x-for="hs in (activeScene?.hotspots || [])" :key="hs.id">
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-200 hover:border-cyan-300 cursor-pointer transition" @click="selectHotspot(hs.id)">
                                            <div class="flex items-center space-x-2 truncate">
                                                <div class="w-3 h-3 rounded-full" :style="`background-color: ${hs.color || '#0ea5e9'}`"></div>
                                                <span class="text-sm font-medium text-gray-700 truncate" x-text="hs.label"></span>
                                            </div>
                                            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                                        </div>
                                    </template>
                                    <template x-if="!(activeScene?.hotspots?.length > 0)">
                                        <p class="text-xs text-gray-400 italic">Belum ada hotspot. Klik di area panorama untuk menambahkan.</p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hotspot Properties Form -->
                <div x-show="state.activeHotspotId" class="flex flex-col h-full bg-cyan-50/30">
                    <div class="p-4 border-b border-cyan-100 bg-cyan-50 flex justify-between items-center">
                        <h2 class="font-semibold text-gray-800"><i class="fas fa-bullseye mr-2 text-cyan-600"></i>Properti Hotspot</h2>
                        <button @click="state.activeHotspotId = null" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-4">
                        <form @submit.prevent="saveHotspot" class="space-y-4">
                            <!-- Type -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Tipe Hotspot</label>
                                <select x-model="hotspotForm.type" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                    <option value="navigation">Navigasi (Pindah Adegan)</option>
                                    <option value="info">Informasi (Popup Model)</option>
                                </select>
                            </div>

                            <!-- Label -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Label / Judul</label>
                                <input type="text" x-model="hotspotForm.label" required class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                            </div>

                            <!-- Nav Target (if navigation) -->
                            <div x-show="hotspotForm.type === 'navigation'">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Tujuan Adegan</label>
                                <select x-model="hotspotForm.target_scene_id" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                    <option value="">-- Pilih Adegan --</option>
                                    <template x-for="s in scenes" :key="s.id">
                                        <option :value="s.id" x-text="s.name" x-show="s.id !== state.activeSceneId"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Info Content (if info) -->
                            <div x-show="hotspotForm.type === 'info'" class="space-y-3 border-l-2 border-cyan-200 pl-3 py-1">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Judul Info</label>
                                    <input type="text" x-model="hotspotForm.modal_title" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Konten (Mendukung HTML)</label>
                                    <textarea x-model="hotspotForm.modal_content" rows="4" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">URL Gambar (Opsional)</label>
                                    <input type="url" x-model="hotspotForm.modal_image" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                </div>
                            </div>

                            <!-- Positioning & Color -->
                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200">
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Posisi XYZ</label>
                                    <div class="flex space-x-1">
                                        <input type="number" step="any" x-model="hotspotForm.position_x" @input="updateHotspotVisual" class="w-full text-xs px-2 py-1 border-gray-300 rounded shadow-sm">
                                        <input type="number" step="any" x-model="hotspotForm.position_y" @input="updateHotspotVisual" class="w-full text-xs px-2 py-1 border-gray-300 rounded shadow-sm">
                                        <input type="number" step="any" x-model="hotspotForm.position_z" @input="updateHotspotVisual" class="w-full text-xs px-2 py-1 border-gray-300 rounded shadow-sm">
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1">Anda juga dapat menggeser posisi langsung pada viewer dengan klik tahan pada hotspot (Simulasi A-Frame)</p>
                                </div>
                                
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Warna Hotspot</label>
                                    <input type="color" x-model="hotspotForm.color" @input="updateHotspotVisual" class="w-full h-8 border border-gray-300 rounded shadow-sm cursor-pointer">
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="pt-4 flex justify-between items-center border-t border-gray-200 mt-6">
                                <button type="button" @click="deleteHotspot" class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center">
                                    <i class="fas fa-trash mr-1"></i> Hapus
                                </button>
                                <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow-sm transition flex items-center" :disabled="isSaving">
                                    <i class="fas fa-save mr-2" :class="{'fa-spin': isSaving}"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Scene Modal -->
        <div x-show="showSceneModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity" @click="showSceneModal = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75 backdrop-blur-sm"></div>
                </div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form @submit.prevent="saveScene">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" x-text="sceneForm.id ? 'Edit Adegan' : 'Tambah Adegan Baru'"></h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Adegan</label>
                                    <input type="text" x-model="sceneForm.name" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Panorama (URL atau Upload)</label>
                                    <div class="flex space-x-2">
                                        <input type="text" x-model="sceneForm.image" required placeholder="https://..." class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                        <button type="button" @click="$refs.fileInput.click()" class="bg-gray-100 border border-gray-300 text-gray-700 px-3 py-2 rounded-md hover:bg-gray-200 transition">
                                            <i class="fas fa-upload"></i>
                                        </button>
                                        <input type="file" x-ref="fileInput" @change="uploadImage" accept="image/jpeg,image/png,image/webp" class="hidden">
                                    </div>
                                    <div x-show="uploadingImage" class="text-xs text-cyan-600 mt-1"><i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah gambar...</div>
                                    <div x-show="sceneForm.image" class="mt-2 relative aspect-video bg-gray-100 rounded overflow-hidden border border-gray-200">
                                        <img :src="sceneForm.image" class="w-full h-full object-cover">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-row-reverse space-x-2 space-x-reverse">
                            <button type="submit" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-cyan-600 text-base font-medium text-white hover:bg-cyan-700 focus:outline-none sm:text-sm" :disabled="isSaving || uploadingImage">
                                Simpan
                            </button>
                            <button type="button" @click="showSceneModal = false" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- AlpineJS Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('panoramaEditor', (situsId) => ({
                situsId: situsId,
                scenes: [],
                isLoading: false,
                isSaving: false,
                uploadingImage: false,
                showSceneModal: false,
                
                state: {
                    activeSceneId: null,
                    activeHotspotId: null
                },
                
                sceneForm: { id: null, name: '', image: '', situs_id: situsId },
                hotspotForm: { id: null, label: '', type: 'navigation', position_x: 0, position_y: 0, position_z: 0, color: '#0ea5e9', target_scene_id: '', modal_title: '', modal_content: '', modal_image: '' },

                get activeScene() {
                    return this.scenes.find(s => s.id === this.state.activeSceneId);
                },

                init() {
                    this.loadScenes();
                    
                    // Setup A-Frame click listener for placing hotspots
                    const sky = document.getElementById('editor-sky');
                    sky.addEventListener('click', (e) => {
                        if(!this.state.activeSceneId) return;
                        
                        // Calculate position roughly 4 units away in direction of click
                        const intersection = e.detail.intersection;
                        if(intersection) {
                            const p = intersection.point;
                            // Normalize and multiply by 4
                            const len = Math.sqrt(p.x*p.x + p.y*p.y + p.z*p.z);
                            const dist = 4.0;
                            const x = parseFloat(((p.x / len) * dist).toFixed(2));
                            const y = parseFloat(((p.y / len) * dist).toFixed(2));
                            const z = parseFloat(((p.z / len) * dist).toFixed(2));
                            
                            this.createNewHotspotAt(x, y, z);
                        }
                    });
                },

                async loadScenes() {
                    this.isLoading = true;
                    try {
                        const res = await fetch(`/admin/panorama/scenes/${this.situsId}`);
                        const data = await res.json();
                        this.scenes = data.scenes || [];
                        
                        // Auto select first scene if available and none selected
                        if(this.scenes.length > 0 && !this.state.activeSceneId) {
                            this.selectScene(this.scenes[0].id);
                        } else if(this.state.activeSceneId) {
                            // Re-select to trigger render
                            this.selectScene(this.state.activeSceneId);
                        }
                    } catch (err) {
                        alert('Gagal memuat data scene');
                    } finally {
                        this.isLoading = false;
                    }
                },

                selectScene(id) {
                    this.state.activeSceneId = id;
                    this.state.activeHotspotId = null;
                    const scene = this.activeScene;
                    
                    // Set Sky
                    document.getElementById('editor-sky').setAttribute('src', scene.image);
                    
                    this.renderAframeHotspots();
                },

                renderAframeHotspots() {
                    const container = document.getElementById('editor-hotspots');
                    // Clear existing
                    while (container.firstChild) {
                        container.removeChild(container.firstChild);
                    }
                    
                    const scene = this.activeScene;
                    if(!scene || !scene.hotspots) return;
                    
                    scene.hotspots.forEach(hs => {
                        const el = document.createElement('a-entity');
                        el.setAttribute('position', hs.position || `${hs.position_x} ${hs.position_y} ${hs.position_z}`);
                        el.setAttribute('look-at', '#editor-camera');
                        
                        const img = document.createElement('a-image');
                        // Use basic icon based on type
                        const src = hs.type === 'navigation' ? '#hotspot-nav' : '#hotspot-info';
                        
                        img.setAttribute('src', src);
                        img.setAttribute('color', hs.color || '#0ea5e9');
                        img.setAttribute('class', 'clickable');
                        
                        // Add selection glow if active
                        if(this.state.activeHotspotId === hs.id) {
                            img.setAttribute('scale', '1.3 1.3 1.3');
                            // Replace emissive logic with color highlight since flat shader doesn't support emissive
                            img.setAttribute('color', '#ffeb3b');
                        }

                        const text = document.createElement('a-text');
                        text.setAttribute('value', hs.label);
                        text.setAttribute('align', 'center');
                        text.setAttribute('position', '0 -0.8 0');
                        text.setAttribute('color', 'white');
                        text.setAttribute('scale', '1.5 1.5 1.5');
                        
                        el.appendChild(img);
                        el.appendChild(text);
                        
                        // Add click event for selection
                        img.addEventListener('click', (e) => {
                            e.stopPropagation(); // Prevent sky click
                            this.selectHotspot(hs.id);
                        });
                        
                        // Set ID for updating visual later
                        el.id = `hs-entity-${hs.id}`;
                        container.appendChild(el);
                    });
                },

                selectHotspot(hsId) {
                    this.state.activeHotspotId = hsId;
                    const hs = this.activeScene.hotspots.find(h => h.id === hsId);
                    
                    // Fill form
                    this.hotspotForm = {
                        id: hs.id,
                        label: hs.label,
                        type: hs.type || 'navigation',
                        position_x: hs.position_x,
                        position_y: hs.position_y,
                        position_z: hs.position_z,
                        color: hs.color,
                        target_scene_id: hs.target_scene_id || '',
                        modal_title: hs.modal_title || '',
                        modal_content: hs.modal_content || '',
                        modal_image: hs.modal_image || '',
                    };
                    
                    this.renderAframeHotspots(); // Re-render to show active state
                },

                updateHotspotVisual() {
                    // Update visual immediately when form changes (no save yet)
                    if(!this.hotspotForm.id) return;
                    const el = document.getElementById(`hs-entity-${this.hotspotForm.id}`);
                    if(el) {
                        el.setAttribute('position', `${this.hotspotForm.position_x} ${this.hotspotForm.position_y} ${this.hotspotForm.position_z}`);
                        const img = el.querySelector('a-image');
                        if(img) img.setAttribute('color', this.hotspotForm.color);
                    }
                },

                createNewHotspotAt(x, y, z) {
                    this.hotspotForm = {
                        id: null,
                        label: 'Hotspot Baru',
                        type: 'navigation',
                        position_x: x,
                        position_y: y,
                        position_z: z,
                        color: '#0ea5e9',
                        target_scene_id: '',
                        modal_title: '',
                        modal_content: '',
                        modal_image: '',
                    };
                    this.state.activeHotspotId = 'new';
                    this.renderAframeHotspots(); // Re-render to clear active state from others
                },

                // --- API Calls ---

                async saveScene() {
                    this.isSaving = true;
                    try {
                        const isEdit = !!this.sceneForm.id;
                        const url = isEdit ? `/admin/panorama/scenes/${this.sceneForm.id}` : `/admin/panorama/scenes`;
                        const method = isEdit ? 'PUT' : 'POST';
                        
                        const payload = { ...this.sceneForm, scene_type: 'panorama' };
                        
                        const res = await fetch(url, {
                            method: method,
                            headers: { 
                                'Content-Type': 'application/json', 
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        if(res.ok) {
                            this.showSceneModal = false;
                            await this.loadScenes();
                            // Select new scene if added
                            if(!isEdit) {
                                const newScene = this.scenes[this.scenes.length-1];
                                this.selectScene(newScene.id);
                            }
                        } else {
                            const err = await res.text();
                            alert('Gagal menyimpan adegan: ' + (res.status === 406 ? 'Expected JSON' : err));
                        }
                    } catch(e) {
                        alert('Error koneksi');
                    } finally {
                        this.isSaving = false;
                    }
                },

                async deleteScene(id) {
                    if(!confirm('Yakin ingin menghapus adegan ini? Semua hotspot didalamnya akan ikut terhapus.')) return;
                    
                    try {
                        const res = await fetch(`/admin/panorama/scenes/${id}`, {
                            method: 'DELETE',
                            headers: { 
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                            }
                        });
                        
                        if(res.ok) {
                            if(this.state.activeSceneId === id) {
                                this.state.activeSceneId = null;
                                document.getElementById('editor-scene').style.display = 'none';
                            }
                            await this.loadScenes();
                        }
                    } catch(e) {}
                },

                async saveHotspot() {
                    this.isSaving = true;
                    try {
                        const isEdit = !!this.hotspotForm.id;
                        const url = isEdit ? `/admin/panorama/hotspots/${this.hotspotForm.id}` : `/admin/panorama/hotspots`;
                        const method = isEdit ? 'PUT' : 'POST';
                        
                        const payload = { 
                            ...this.hotspotForm, 
                            scene_id: this.state.activeSceneId,
                            adegan_id: this.state.activeSceneId // for backend naming matching
                        };
                        
                        // Clean empty strings
                        if(payload.target_scene_id === '') payload.target_scene_id = null;
                        
                        const res = await fetch(url, {
                            method: method,
                            headers: { 
                                'Content-Type': 'application/json', 
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        if(res.ok) {
                            await this.loadScenes();
                            const newHs = await res.json();
                            this.selectHotspot(newHs.id);
                            alert('Hotspot disimpan!');
                        } else {
                            const err = await res.text();
                            alert('Gagal menyimpan hotspot: ' + err);
                        }
                    } catch(e) {
                        console.error(e);
                        alert('Error koneksi');
                    } finally {
                        this.isSaving = false;
                    }
                },

                async deleteHotspot() {
                    if(!this.hotspotForm.id) {
                        this.state.activeHotspotId = null;
                        return;
                    }
                    if(!confirm('Hapus hotspot ini?')) return;
                    
                    try {
                        const res = await fetch(`/admin/panorama/hotspots/${this.hotspotForm.id}`, {
                            method: 'DELETE',
                            headers: { 
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                            }
                        });
                        
                        if(res.ok) {
                            this.state.activeHotspotId = null;
                            await this.loadScenes();
                        }
                    } catch(e) {}
                },

                async uploadImage(e) {
                    const file = e.target.files[0];
                    if(!file) return;
                    
                    // Client-side size check (50MB)
                    if(file.size > 50 * 1024 * 1024) {
                        alert('Ukuran file melebihi batas 50MB.');
                        e.target.value = null;
                        return;
                    }
                    
                    this.uploadingImage = true;
                    const formData = new FormData();
                    formData.append('file', file);
                    
                    try {
                        const res = await fetch('/admin/panorama/upload', {
                            method: 'POST',
                            headers: { 
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                            },
                            body: formData
                        });
                        
                        if(res.ok) {
                            const data = await res.json();
                            this.sceneForm.image = data.url;
                        } else {
                            if(res.status === 413) {
                                alert('Gagal unggah: File terlalu besar untuk server. (Error 413 Payload Too Large)');
                            } else if(res.status === 422) {
                                const errData = await res.json();
                                // if validation fails, it might be due to php.ini dropping the file
                                if(errData.errors && errData.errors.file) {
                                    alert('Gagal unggah: ' + errData.errors.file[0] + '\n\n(Catatan: Jika ukuran file < 50MB, mungkin dibatasi oleh upload_max_filesize/post_max_size di php.ini server Anda yang defaultnya 2MB)');
                                } else {
                                    alert('Gagal unggah gambar. Pastikan format sesuai.');
                                }
                            } else {
                                alert('Gagal unggah gambar. Kemungkinan ukuran melebihi batas konfigurasi server (php.ini).');
                            }
                        }
                    } catch(e) {
                        alert('Error koneksi saat mengunggah');
                    } finally {
                        this.uploadingImage = false;
                        e.target.value = null; // reset input
                    }
                },

                openSceneModal(scene = null) {
                    if(scene) {
                        this.sceneForm = { id: scene.id, name: scene.name, image: scene.image, situs_id: this.situsId };
                    } else {
                        this.sceneForm = { id: null, name: '', image: '', situs_id: this.situsId };
                    }
                    this.showSceneModal = true;
                }
            }));
        });
    </script>
</x-app-layout>
