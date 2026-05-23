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
            <img id="icon-door" crossorigin="anonymous" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512' viewBox='0 0 512 512'><path fill='%23ffffff' d='M320 48v416c0 26.5-21.5 48-48 48H128c-26.5 0-48-21.5-48-48V48C80 21.5 101.5 0 128 0h144c26.5 0 48 21.5 48 48zm-16 0c0-8.8-7.2-16-16-16H128c-8.8 0-16 7.2-16 16v416c0 8.8 7.2 16 16 16h144c8.8 0 16-7.2 16-16V48zm128 0v416c0 26.5-21.5 48-48 48h-16v-32h16c8.8 0 16-7.2 16-16V48c0-8.8-7.2-16-16-16h-16V0h16c26.5 0 48 21.5 48 48zm-96 240c0 13.3-10.7 24-24 24s-24-10.7-24-24 10.7-24 24-24 24 10.7 24 24z'/></svg>">
            <img id="icon-arrow-up" crossorigin="anonymous" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512' viewBox='0 0 512 512'><path fill='%23ffffff' d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM256 127c9.4 0 18.8 3.8 26.3 11.3l112 112c14.6 14.6 14.6 38.2 0 52.7s-38.2 14.6-52.7 0L256 225 178 303c-14.6 14.6-38.2 14.6-52.7 0s-14.6-38.2 0-52.7l112-112c7.5-7.5 16.9-11.3 26.3-11.3z'/></svg>">
            <img id="icon-arrow-down" crossorigin="anonymous" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512' viewBox='0 0 512 512'><path fill='%23ffffff' d='M256 0a256 256 0 1 0 0 512A256 256 0 1 0 256 0zM256 385c-9.4 0-18.8-3.8-26.3-11.3l-112-112c-14.6-14.6-14.6-38.2 0-52.7s38.2-14.6 52.7 0L256 287 334 209c14.6-14.6 38.2-14.6 52.7 0s14.6 38.2 0 52.7l-112 112c-7.5 7.5-16.9 11.3-26.3 11.3z'/></svg>">
            <img id="icon-arrow-right" crossorigin="anonymous" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512' viewBox='0 0 512 512'><path fill='%23ffffff' d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM385 256c0 9.4-3.8 18.8-11.3 26.3l-112 112c-14.6 14.6-38.2 14.6-52.7 0s-14.6-38.2 0-52.7L287 256 209 178c-14.6-14.6-14.6-38.2 0-52.7s38.2-14.6 52.7 0l112 112c7.5 7.5 11.3 16.9 11.3 26.3z'/></svg>">
            <img id="icon-arrow-left" crossorigin="anonymous" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512' viewBox='0 0 512 512'><path fill='%23ffffff' d='M256 0a256 256 0 1 0 0 512A256 256 0 1 0 256 0zM127 256c0-9.4 3.8-18.8 11.3-26.3l112-112c14.6-14.6 38.2-14.6 52.7 0s14.6 38.2 0 52.7L225 256l78 78c14.6 14.6 14.6 38.2 0 52.7s-38.2 14.6-52.7 0l-112-112c-7.5-7.5-11.3-16.9-11.3-26.3z'/></svg>">
            <img id="icon-info" crossorigin="anonymous" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512' viewBox='0 0 512 512'><path fill='%23ffffff' d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z'/></svg>">
        </a-assets>
        
        <a-sky id="editor-sky" class="clickable" rotation="0 -90 0" color="#fff" radius="500"></a-sky>
        
        <!-- Floor Grid -->
        <a-plane id="editor-floor" class="clickable" position="0 -1.6 0" rotation="-90 0 0" width="100" height="100" material="color: #2dd4bf; wireframe: true; transparent: true; opacity: 0.3"></a-plane>

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
