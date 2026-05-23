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
