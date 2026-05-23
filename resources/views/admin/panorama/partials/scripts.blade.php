<!-- AlpineJS Logic -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('panoramaEditor', (situsId) => ({
            situsId: situsId,
            scenes: [],
            assets: [],
            isLoading: false,
            isSaving: false,
            uploadingImage: false,
            showSceneModal: false,
            showAssetModal: false,
            uploadingAsset: false,
            
            state: {
                activeSceneId: null,
                activeHotspotId: null
            },
            
            assetForm: { nama: '', file: null },
            sceneForm: { id: null, name: '', image: '', situs_id: situsId },
            hotspotForm: { id: null, label: '', type: 'navigation', position_x: 0, position_y: 0, position_z: 0, target_scene_id: '', modal_title: '', modal_content: '', modal_image: '', animation_config: { icon: 'icon-info', animation: 'none', custom_url: '' } },

            get activeScene() {
                return this.scenes.find(s => s.id === this.state.activeSceneId);
            },

            init() {
                this.loadScenes();
                this.loadAssets();
                
                // Setup A-Frame click listener for placing hotspots
                const sceneEl = document.getElementById('editor-scene');
                sceneEl.addEventListener('click', (e) => {
                    if(!this.state.activeSceneId) return;
                    
                    // We only want clicks on sky or floor
                    if (!e.target.id || (e.target.id !== 'editor-sky' && e.target.id !== 'editor-floor')) return;
                    
                    const intersection = e.detail.intersection;
                    if(intersection) {
                        const p = intersection.point;
                        let x, y, z;
                        
                        if (e.target.id === 'editor-floor') {
                            // If clicked exactly on the floor, place it exactly there
                            x = parseFloat(p.x.toFixed(2));
                            y = parseFloat(p.y.toFixed(2)); // should be -1.6
                            z = parseFloat(p.z.toFixed(2));
                        } else {
                            // If clicked on the sky, calculate direction in XZ plane
                            // and place it at a fixed distance on the floor
                            const len = Math.sqrt(p.x*p.x + p.z*p.z);
                            const dist = 4.0;
                            x = parseFloat(((p.x / len) * dist).toFixed(2));
                            y = -1.6; // Floor level
                            z = parseFloat(((p.z / len) * dist).toFixed(2));
                        }
                        
                        this.createNewHotspotAt(x, y, z);
                    }
                });
            },

            async loadScenes() {
                this.isLoading = true;
                try {
                    const res = await fetch(`/admin/panorama/scenes/${this.situsId}?t=${new Date().getTime()}`);
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

            async loadAssets() {
                try {
                    const res = await fetch(`/admin/panorama/assets?t=${new Date().getTime()}`);
                    const data = await res.json();
                    this.assets = data.data || [];
                } catch (err) {
                    console.error('Gagal memuat pustaka aset');
                }
            },

            async uploadAsset() {
                const fileInput = document.getElementById('asset-file');
                if (!fileInput.files[0]) return;

                this.uploadingAsset = true;
                const formData = new FormData();
                formData.append('nama', this.assetForm.nama);
                formData.append('file', fileInput.files[0]);

                try {
                    const res = await fetch(`/admin/panorama/assets`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    });

                    const data = await res.json();
                    if (res.ok) {
                        this.assets.unshift(data.data);
                        this.assetForm.nama = '';
                        fileInput.value = '';
                    } else {
                        alert(data.message || 'Gagal mengunggah aset');
                    }
                } catch (err) {
                    alert('Error koneksi saat mengunggah');
                } finally {
                    this.uploadingAsset = false;
                }
            },

            async quickUploadCustomMedia(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.uploadingAsset = true;
                const formData = new FormData();
                formData.append('nama', 'Upload Cepat: ' + file.name);
                formData.append('file', file);

                try {
                    const res = await fetch(`/admin/panorama/assets`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    });

                    const data = await res.json();
                    if (res.ok) {
                        this.assets.unshift(data.data);
                        this.hotspotForm.animation_config.custom_url = data.data.url;
                        this.updateHotspotVisual();
                    } else {
                        alert(data.message || 'Gagal mengunggah aset');
                    }
                } catch (err) {
                    alert('Error koneksi saat mengunggah');
                } finally {
                    this.uploadingAsset = false;
                    event.target.value = ''; // Reset input file
                }
            },

            async deleteAsset(id) {
                if (!confirm('Yakin ingin menghapus aset ini?')) return;

                try {
                    const res = await fetch(`/admin/panorama/assets/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (res.ok) {
                        this.assets = this.assets.filter(a => a.aset_id !== id);
                    } else {
                        alert('Gagal menghapus aset');
                    }
                } catch (err) {
                    alert('Error koneksi');
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
                if(!scene) return;
                
                if(scene.hotspots) {
                    scene.hotspots.forEach(hs => {
                        // skip rendering the saved one if it is currently active, 
                        // because we will render it from the form!
                        if (this.state.activeHotspotId === hs.id) return;
                        
                        const el = this.buildHotspotEntity(hs, hs.id);
                        container.appendChild(el);
                    });
                }
                
                // Now render the active hotspot from the form, so it always reflects the latest state!
                if (this.state.activeHotspotId && this.hotspotForm) {
                    const el = this.buildHotspotEntity(this.hotspotForm, this.state.activeHotspotId);
                    container.appendChild(el);
                }
            },

            buildHotspotEntity(hs, entityId) {
                const el = document.createElement('a-entity');
                el.setAttribute('position', hs.position || `${hs.position_x} ${hs.position_y} ${hs.position_z}`);
                el.setAttribute('look-at', '#editor-camera');
                
                let srcId = '#icon-info';
                let isCustomVideo = false;
                
                if(hs.animation_config && hs.animation_config.icon) {
                    if (hs.animation_config.icon === 'custom') {
                        if (hs.animation_config.custom_url) {
                            const url = hs.animation_config.custom_url;
                            isCustomVideo = !!url.match(/\.(mp4|webm)$/i);
                            
                            if (isCustomVideo) {
                                const assetId = 'video-' + entityId;
                                let videoEl = document.getElementById(assetId);
                                if (!videoEl) {
                                    videoEl = document.createElement('video');
                                    videoEl.id = assetId;
                                    videoEl.setAttribute('src', url);
                                    videoEl.setAttribute('autoplay', 'true');
                                    videoEl.setAttribute('loop', 'true');
                                    videoEl.setAttribute('muted', 'true');
                                    videoEl.setAttribute('playsinline', 'true');
                                    videoEl.setAttribute('crossorigin', 'anonymous');
                                    document.querySelector('a-assets').appendChild(videoEl);
                                    // Attempt autoplay
                                    videoEl.play().catch(e => console.log('Video autoplay prevented'));
                                }
                                srcId = '#' + assetId;
                            } else {
                                srcId = url;
                            }
                        } else {
                            srcId = hs.type === 'navigation' ? '#icon-arrow-up' : '#icon-info';
                        }
                    } else {
                        srcId = '#' + hs.animation_config.icon;
                    }
                } else {
                    srcId = hs.type === 'navigation' ? '#icon-arrow-up' : '#icon-info';
                }
                
                let img;
                if (isCustomVideo) {
                    img = document.createElement('a-video');
                    img.setAttribute('src', srcId);
                    img.setAttribute('width', '1');
                    img.setAttribute('height', '1');
                    img.setAttribute('class', 'clickable');
                } else {
                    // Render standard image
                    img = document.createElement('a-image');
                    img.setAttribute('src', srcId);
                    img.setAttribute('class', 'clickable');
                    
                    // Apply predefined animation if config exists (only for flat images)
                    if (hs.animation_config && hs.animation_config.animation) {
                        if (hs.animation_config.animation === 'pulse') {
                            img.setAttribute('animation__scale', 'property: scale; dir: alternate; dur: 800; easing: easeInOutSine; loop: true; to: 1.2 1.2 1.2');
                        } else if (hs.animation_config.animation === 'bob') {
                            img.setAttribute('animation__pos', 'property: position; dir: alternate; dur: 1000; easing: easeInOutSine; loop: true; to: 0 0.2 0');
                        } else if (hs.animation_config.animation === 'spin') {
                            img.setAttribute('animation__rot', 'property: rotation; dur: 2000; easing: linear; loop: true; to: 0 0 360');
                        }
                    }
                }
                
                // Add selection glow if active
                if(this.state.activeHotspotId === entityId) {
                    img.setAttribute('scale', '1.3 1.3 1.3');
                }

                el.appendChild(img);
                
                if (hs.label && hs.label.trim() !== '') {
                    const text = document.createElement('a-text');
                    text.setAttribute('value', hs.label);
                    text.setAttribute('align', 'center');
                    text.setAttribute('position', '0 -0.8 0');
                    text.setAttribute('color', 'white');
                    text.setAttribute('scale', '1.5 1.5 1.5');
                    el.appendChild(text);
                }
                
                // Add click event for selection
                img.addEventListener('click', (e) => {
                    e.stopPropagation(); // Prevent sky click
                    this.selectHotspot(entityId);
                });
                
                // Set ID for updating visual later
                el.id = `hs-entity-${entityId}`;
                return el;
            },

            selectHotspot(hsId) {
                if (hsId === 'new') return; // Do not reload from state if it's the temporary new hotspot
                
                this.state.activeHotspotId = hsId;
                const hs = this.activeScene.hotspots.find(h => h.id === hsId);
                if (!hs) return;
                
                // Fill form
                this.hotspotForm = {
                    id: hs.id,
                    label: hs.label,
                    type: hs.type || 'navigation',
                    position_x: hs.position_x,
                    position_y: hs.position_y,
                    position_z: hs.position_z,
                    target_scene_id: hs.target_scene_id || '',
                    modal_title: hs.modal_title || '',
                    modal_content: hs.modal_content || '',
                    modal_image: hs.modal_image || '',
                    animation_config: hs.animation_config || { icon: hs.type === 'navigation' ? 'icon-arrow-up' : 'icon-info', animation: 'none', custom_url: '' },
                };
                
                this.renderAframeHotspots(); // Re-render to show active state
            },

            updateHotspotVisual() {
                // By re-rendering the whole scene hotspots, we apply any modifications from the form instantly!
                this.renderAframeHotspots();
            },

            createNewHotspotAt(x, y, z) {
                this.hotspotForm = {
                    id: null, // null so that the backend treats it as new on save
                    label: 'Hotspot Baru',
                    type: 'navigation',
                    position_x: x,
                    position_y: y,
                    position_z: z,
                    target_scene_id: '',
                    modal_title: '',
                    modal_content: '',
                    modal_image: '',
                    animation_config: { icon: 'icon-arrow-up', animation: 'none', custom_url: '' },
                };
                this.state.activeHotspotId = 'new';
                this.renderAframeHotspots(); // This will instantly preview the new hotspot!
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
