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
