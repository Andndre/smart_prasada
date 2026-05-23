<!-- Add/Edit Scene Modal -->
<div x-show="showSceneModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
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

<!-- Modal Manajemen Aset -->
<div x-show="showAssetModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity" @click="showAssetModal = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900"><i class="fas fa-folder-open text-cyan-600 mr-2"></i>Pustaka Aset Hotspot</h3>
                    <button @click="showAssetModal = false" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times text-xl"></i></button>
                </div>
                
                <!-- Upload Form -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Unggah Aset Baru</h4>
                    <form @submit.prevent="uploadAsset" class="flex items-end space-x-3">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nama Aset</label>
                            <input type="text" x-model="assetForm.nama" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-sm" placeholder="Misal: Pintu Kayu Besar">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-700 mb-1">File (PNG/WebM/MP4, max 10MB)</label>
                            <input type="file" id="asset-file" accept="image/png,image/jpeg,image/svg+xml,video/webm,video/mp4" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100">
                        </div>
                        <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow-sm" :disabled="uploadingAsset">
                            <span x-show="!uploadingAsset">Unggah</span>
                            <span x-show="uploadingAsset"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </form>
                </div>

                <!-- Asset Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 max-h-96 overflow-y-auto p-1">
                    <template x-for="asset in assets" :key="asset.aset_id">
                        <div class="relative group border rounded-lg overflow-hidden bg-gray-50 aspect-square flex flex-col items-center justify-center">
                            <!-- Preview -->
                            <template x-if="asset.tipe === 'video'">
                                <video :src="asset.url" class="w-full h-24 object-cover" autoplay loop muted playsinline></video>
                            </template>
                            <template x-if="asset.tipe === 'image'">
                                <img :src="asset.url" class="w-full h-24 object-cover">
                            </template>
                            
                            <div class="p-2 text-center w-full bg-white border-t">
                                <p class="text-xs font-medium text-gray-800 truncate" x-text="asset.nama"></p>
                                <p class="text-[10px] text-gray-500 uppercase" x-text="asset.tipe"></p>
                            </div>
                            
                            <!-- Delete Button -->
                            <button @click="deleteAsset(asset.aset_id)" class="absolute top-1 right-1 bg-red-600 text-white w-6 h-6 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </template>
                    <template x-if="assets.length === 0">
                        <div class="col-span-full py-8 text-center text-gray-400 text-sm">
                            Belum ada aset yang diunggah.
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
