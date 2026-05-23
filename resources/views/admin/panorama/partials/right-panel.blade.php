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
                    <label class="block text-xs font-medium text-gray-700 mb-1">Label / Judul (Opsional)</label>
                    <input type="text" x-model="hotspotForm.label" @input="updateHotspotVisual" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500" placeholder="Biarkan kosong jika hanya ikon">
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

                <!-- Icon & Animation Config -->
                <div class="grid grid-cols-2 gap-3 pt-4 pb-2 border-t border-gray-200">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Ikon Preset</label>
                        <select x-model="hotspotForm.animation_config.icon" @change="updateHotspotVisual" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                            <option value="icon-arrow-up">Panah Maju / Atas</option>
                            <option value="icon-arrow-down">Panah Mundur / Bawah</option>
                            <option value="icon-arrow-left">Panah Kiri</option>
                            <option value="icon-arrow-right">Panah Kanan</option>
                            <option value="icon-info">Info Circle</option>
                            <option value="custom">Kustom (URL WebM/PNG)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Efek Animasi</label>
                        <select x-model="hotspotForm.animation_config.animation" @change="updateHotspotVisual" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                            <option value="none">Tidak Ada (Statis)</option>
                            <option value="pulse">Berdenyut (Pulse)</option>
                            <option value="bob">Melayang Naik-Turun (Bobbing)</option>
                            <option value="spin">Berputar (Spin)</option>
                        </select>
                    </div>
                    <div x-show="hotspotForm.animation_config.icon === 'custom'" class="col-span-2">
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs font-medium text-gray-700">Pilih dari Pustaka / URL Media</label>
                            <button type="button" @click.prevent="showAssetModal = true" class="text-xs text-cyan-600 hover:text-cyan-800"><i class="fas fa-folder-open mr-1"></i>Kelola Pustaka</button>
                        </div>
                        <div class="flex space-x-2">
                            <select x-model="hotspotForm.animation_config.custom_url" @change="updateHotspotVisual" class="w-1/3 text-sm border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                <option value="">-- URL Manual --</option>
                                <template x-for="asset in assets" :key="asset.aset_id">
                                    <option :value="asset.url" x-text="asset.nama"></option>
                                </template>
                            </select>
                            <input type="text" x-model="hotspotForm.animation_config.custom_url" @change="updateHotspotVisual" class="flex-1 text-sm border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500" placeholder="https://...">
                        </div>
                        <div class="mt-2 flex items-center bg-gray-50 p-2 rounded border border-gray-200">
                            <label class="block text-[10px] font-medium text-gray-700 mr-2 whitespace-nowrap">Atau Upload Langsung:</label>
                            <input type="file" @change="quickUploadCustomMedia" accept="image/png,image/jpeg,image/svg+xml,video/webm,video/mp4" class="w-full text-[10px] text-gray-500 file:cursor-pointer file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-cyan-100 file:text-cyan-700 hover:file:bg-cyan-200">
                            <span x-show="uploadingAsset" class="ml-2 text-cyan-600 text-xs"><i class="fas fa-spinner fa-spin"></i></span>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">Disarankan format .webm dengan background transparan untuk video animasi.</p>
                    </div>
                </div>

                <!-- Positioning -->
                <div class="pt-2 border-t border-gray-200">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Posisi XYZ</label>
                    <div class="flex space-x-1">
                        <input type="number" step="any" x-model="hotspotForm.position_x" @input="updateHotspotVisual" class="w-full text-xs px-2 py-1 border-gray-300 rounded shadow-sm">
                        <input type="number" step="any" x-model="hotspotForm.position_y" @input="updateHotspotVisual" class="w-full text-xs px-2 py-1 border-gray-300 rounded shadow-sm">
                        <input type="number" step="any" x-model="hotspotForm.position_z" @input="updateHotspotVisual" class="w-full text-xs px-2 py-1 border-gray-300 rounded shadow-sm">
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Anda juga dapat menggeser posisi langsung pada viewer dengan klik tahan pada hotspot (Simulasi A-Frame)</p>
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
