<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>360 Virtual Tour - {{ $situs->nama }} | Smart Prasada</title>
    <!-- A-Frame -->
    <script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>
    <script src="https://unpkg.com/aframe-look-at-component@0.8.0/dist/aframe-look-at-component.min.js"></script>
    @vite(['resources/css/app.css'])
    
    <style>
        /* Base styles */
        body, html { width: 100%; height: 100%; margin: 0; padding: 0; overflow: hidden; background-color: #000; font-family: 'Inter', sans-serif; }
        
        /* UI Overlays */
        #ui-layer { position: absolute; inset: 0; pointer-events: none; z-index: 10; display: flex; flex-direction: column; justify-content: space-between; padding: 1rem; }
        .interactive { pointer-events: auto; }
        
        /* Header bar */
        #header-bar { display: flex; justify-content: space-between; align-items: flex-start; }
        
        .btn-circle { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); color: white; border: 1px solid rgba(255,255,255,0.2); cursor: pointer; transition: all 0.2s ease; }
        .btn-circle:hover { background: rgba(255,255,255,0.2); transform: scale(1.05); }
        
        .tour-title-box { background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); padding: 0.75rem 1.5rem; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.2); color: white; text-align: center; }
        
        /* Loading Overlay */
        #loading-overlay { position: absolute; inset: 0; background: #000; z-index: 50; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; transition: opacity 0.5s ease; }
        .spinner { width: 40px; height: 40px; border: 4px solid rgba(255,255,255,0.1); border-top-color: #0ea5e9; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 1rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        /* Modal Info */
        #info-modal { position: absolute; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 40; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; padding: 1rem; }
        #info-modal.active { opacity: 1; pointer-events: auto; }
        .modal-card { background: white; border-radius: 1rem; width: 100%; max-width: 28rem; overflow: hidden; transform: translateY(20px); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; flex-direction: column; max-height: 90vh; }
        #info-modal.active .modal-card { transform: translateY(0); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; }
        .modal-body { padding: 1.5rem; overflow-y: auto; }
        .modal-img { width: 100%; height: auto; max-height: 200px; object-fit: cover; border-radius: 0.5rem; margin-bottom: 1rem; display: none; }
        
        /* Scene Transition Overlay */
        #transition-overlay { position: absolute; inset: 0; background: black; z-index: 5; opacity: 0; pointer-events: none; transition: opacity 0.4s ease; }
    </style>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Loading Screen -->
    <div id="loading-overlay">
        <div class="spinner"></div>
        <h2 class="text-xl font-bold">Memuat Tur Virtual...</h2>
        <p class="text-sm text-gray-400 mt-2" id="loading-text">Menyiapkan panorama</p>
    </div>

    <!-- UI Overlay -->
    <div id="ui-layer">
        <div id="header-bar">
            <a href="{{ route('guest.situs.detail', $situs->situs_id) }}" class="btn-circle interactive" title="Kembali ke Detail Situs">
                <i class="fas fa-arrow-left"></i>
            </a>
            
            <div class="tour-title-box">
                <h1 class="text-sm font-bold m-0">{{ $situs->nama }}</h1>
                <p class="text-xs opacity-75 m-0" id="current-scene-name">Memuat...</p>
            </div>
            
            <button class="btn-circle interactive" id="btn-fullscreen" title="Layar Penuh">
                <i class="fas fa-expand"></i>
            </button>
        </div>
        
        <!-- Bottom Controls -->
        <div class="flex justify-center interactive">
            <div class="bg-black/50 backdrop-blur-md rounded-full px-6 py-2 border border-white/20 flex gap-4 text-white">
                <button id="btn-gyro" class="p-2 hover:text-cyan-400 transition-colors" title="Sensor Gyroscope (Mobile)">
                    <i class="fas fa-compass"></i>
                </button>
                <div class="w-px bg-white/20 my-2"></div>
                <button class="p-2 hover:text-cyan-400 transition-colors cursor-help" title="Geser untuk melihat sekeliling. Klik ikon untuk berinteraksi.">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Info Modal -->
    <div id="info-modal" class="interactive">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="text-lg font-bold text-gray-900 m-0" id="modal-title">Info</h3>
                <button id="btn-close-modal" class="text-gray-400 hover:text-gray-700 p-1">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="modal-body text-gray-700">
                <img id="modal-image" src="" alt="" class="modal-img">
                <div id="modal-content" class="prose prose-sm max-w-none"></div>
            </div>
        </div>
    </div>
    
    <!-- Transition Overlay -->
    <div id="transition-overlay"></div>

    <!-- A-Frame Scene -->
    <a-scene 
        id="panorama-scene" 
        vr-mode-ui="enabled: true; enterVRButton: #btn-vr-custom"
        loading-screen="enabled: false"
        renderer="antialias: true; colorManagement: true; sortObjects: true"
    >
        <a-assets id="scene-assets">
            <!-- Assets will be dynamically loaded here -->
            <img id="hotspot-nav" crossorigin="anonymous" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512' viewBox='0 0 512 512'><path fill='%23ffffff' d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z'/></svg>">
            <img id="hotspot-info" crossorigin="anonymous" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512' viewBox='0 0 512 512'><path fill='%23ffffff' d='M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z'/></svg>">
        </a-assets>

        <!-- Sky -->
        <a-sky id="panorama-sky" radius="500" rotation="0 -90 0" color="#fff"></a-sky>

        <!-- Lighting -->
        <a-light type="ambient" color="#fff" intensity="1"></a-light>

        <!-- Camera Rig -->
        <a-entity id="camera-rig" position="0 0 0">
            <a-camera 
                id="camera" 
                look-controls="pointerLockEnabled: false; magicWindowTrackingEnabled: false; reverseMouseDrag: true" 
                wasd-controls="enabled: false"
                fov="80"
            >
                <a-entity 
                    id="cursor" 
                    cursor="fuse: false; rayOrigin: mouse"
                    raycaster="objects: .clickable; far: 500"
                    geometry="primitive: ring; radiusInner: 0.006; radiusOuter: 0.009"
                    material="color: #0ea5e9; shader: flat; opacity: 0.8"
                    position="0 0 -1"
                    visible="false"
                ></a-entity>
            </a-camera>
        </a-entity>

        <!-- Hotspots Container -->
        <a-entity id="hotspots-container"></a-entity>
    </a-scene>

    <script>
        // Tour Data Payload
        const tourData = @json($situs->toViewerJson());
        
        const State = {
            currentSceneId: null,
            isGyroEnabled: false
        };

        const DOM = {
            scene: document.getElementById('panorama-scene'),
            sky: document.getElementById('panorama-sky'),
            hotspotsContainer: document.getElementById('hotspots-container'),
            assets: document.getElementById('scene-assets'),
            loadingOverlay: document.getElementById('loading-overlay'),
            loadingText: document.getElementById('loading-text'),
            transitionOverlay: document.getElementById('transition-overlay'),
            currentSceneName: document.getElementById('current-scene-name'),
            camera: document.getElementById('camera'),
            modal: document.getElementById('info-modal'),
            modalTitle: document.getElementById('modal-title'),
            modalContent: document.getElementById('modal-content'),
            modalImage: document.getElementById('modal-image'),
            btnGyro: document.getElementById('btn-gyro'),
        };

        // --- Core Functions ---
        
        function init() {
            if (!tourData.scenes || tourData.scenes.length === 0) {
                DOM.loadingText.textContent = "Tidak ada adegan (scene) yang tersedia.";
                return;
            }
            
            // Wait for A-Frame scene to load
            if (DOM.scene.hasLoaded) {
                loadInitialScene();
            } else {
                DOM.scene.addEventListener('loaded', loadInitialScene);
            }
            
            setupEventListeners();
        }

        function loadInitialScene() {
            // Find first scene, or a specific one if needed
            const firstScene = tourData.scenes[0];
            loadScene(firstScene.id);
            
            // Hide loading overlay after a short delay
            setTimeout(() => {
                DOM.loadingOverlay.style.opacity = '0';
                setTimeout(() => DOM.loadingOverlay.style.display = 'none', 500);
            }, 1000);
        }

        function getSceneById(id) {
            return tourData.scenes.find(s => s.id === id);
        }

        function loadScene(sceneId) {
            const scene = getSceneById(sceneId);
            if (!scene) return;
            
            State.currentSceneId = sceneId;
            DOM.currentSceneName.textContent = scene.name;
            
            // Transition effect
            DOM.transitionOverlay.style.opacity = '1';
            
            setTimeout(() => {
                // Update Sky Image
                DOM.sky.setAttribute('src', scene.image);
                
                // Optional: Update Camera Rotation if defined in scene
                // if(scene.cameraPosition) {
                //    DOM.camera.setAttribute('rotation', scene.cameraPosition);
                // }
                
                // Render Hotspots
                renderHotspots(scene.hotspots);
                
                // Wait for image to load before fading back in
                DOM.sky.addEventListener('materialtextureloaded', function onTextureLoaded() {
                    DOM.transitionOverlay.style.opacity = '0';
                    DOM.sky.removeEventListener('materialtextureloaded', onTextureLoaded);
                });
                
                // Fallback fade in if texture event fails
                setTimeout(() => DOM.transitionOverlay.style.opacity = '0', 1000);
            }, 400); // Wait for fade to black
        }

        function renderHotspots(hotspots) {
            // Clear existing
            while (DOM.hotspotsContainer.firstChild) {
                DOM.hotspotsContainer.removeChild(DOM.hotspotsContainer.firstChild);
            }
            
            if (!hotspots) return;
            
            hotspots.forEach(hs => {
                const entity = document.createElement('a-entity');
                entity.setAttribute('position', hs.position);
                entity.setAttribute('rotation', hs.rotation || "0 0 0");
                entity.setAttribute('look-at', '#camera');
                
                // Icon base
                const isNav = hs.type === 'navigation';
                const imgSrc = isNav ? '#hotspot-nav' : '#hotspot-info';
                
                // Create clickable image plane
                const img = document.createElement('a-image');
                img.setAttribute('src', imgSrc);
                img.setAttribute('width', '1');
                img.setAttribute('height', '1');
                img.setAttribute('class', 'clickable');
                img.setAttribute('color', hs.color || '#0ea5e9');
                
                // Hover animations
                img.setAttribute('animation__mouseenter', 'property: scale; to: 1.2 1.2 1.2; dur: 200; startEvents: mouseenter');
                img.setAttribute('animation__mouseleave', 'property: scale; to: 1 1 1; dur: 200; startEvents: mouseleave');
                
                // Label
                const label = document.createElement('a-text');
                label.setAttribute('value', hs.label);
                label.setAttribute('align', 'center');
                label.setAttribute('position', '0 -0.8 0');
                label.setAttribute('scale', '1.5 1.5 1.5');
                label.setAttribute('color', 'white');
                // Optional text background for readability could be added here
                
                // Interactions
                img.addEventListener('click', () => {
                    if (isNav && hs.targetScene) {
                        loadScene(hs.targetScene);
                    } else if (!isNav) {
                        showInfoModal(hs);
                    }
                });
                
                entity.appendChild(img);
                entity.appendChild(label);
                DOM.hotspotsContainer.appendChild(entity);
            });
        }

        // --- UI Interactions ---

        function showInfoModal(hs) {
            DOM.modalTitle.textContent = hs.modalTitle || hs.label;
            DOM.modalContent.innerHTML = hs.modalContent || '';
            
            if (hs.modalImage) {
                DOM.modalImage.src = hs.modalImage;
                DOM.modalImage.style.display = 'block';
            } else {
                DOM.modalImage.style.display = 'none';
            }
            
            DOM.modal.classList.add('active');
        }

        function closeInfoModal() {
            DOM.modal.classList.remove('active');
        }

        function setupEventListeners() {
            // Fullscreen
            document.getElementById('btn-fullscreen').addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => console.log(err));
                } else {
                    document.exitFullscreen();
                }
            });
            
            // Gyro
            DOM.btnGyro.addEventListener('click', () => {
                State.isGyroEnabled = !State.isGyroEnabled;
                DOM.camera.setAttribute('look-controls', `magicWindowTrackingEnabled: ${State.isGyroEnabled}`);
                if (State.isGyroEnabled) {
                    DOM.btnGyro.classList.add('text-cyan-400');
                    // Request permission on iOS
                    if (typeof DeviceMotionEvent !== 'undefined' && typeof DeviceMotionEvent.requestPermission === 'function') {
                        DeviceMotionEvent.requestPermission()
                            .then(permissionState => {
                                if (permissionState !== 'granted') {
                                    alert('Akses sensor orientasi ditolak.');
                                    State.isGyroEnabled = false;
                                    DOM.btnGyro.classList.remove('text-cyan-400');
                                }
                            })
                            .catch(console.error);
                    }
                } else {
                    DOM.btnGyro.classList.remove('text-cyan-400');
                }
            });
            
            // Modal close
            document.getElementById('btn-close-modal').addEventListener('click', closeInfoModal);
            DOM.modal.addEventListener('click', (e) => {
                if (e.target === DOM.modal) closeInfoModal();
            });
            
            // Add cursor if not touch device
            if (!('ontouchstart' in window)) {
                document.getElementById('cursor').setAttribute('visible', 'true');
            }
            
            // Scroll to zoom
            window.addEventListener('wheel', (e) => {
                const camera = document.getElementById('camera');
                if(!camera) return;
                
                let fov = parseFloat(camera.getAttribute('fov')) || 80;
                
                // Zoom speed
                const zoomSpeed = 3;
                if(e.deltaY > 0) {
                    fov += zoomSpeed; // Zoom out
                } else if(e.deltaY < 0) {
                    fov -= zoomSpeed; // Zoom in
                }
                
                // Clamp zoom level
                fov = Math.max(30, Math.min(110, fov));
                
                camera.setAttribute('fov', fov);
            }, { passive: true });
        }

        // Boot
        window.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>
