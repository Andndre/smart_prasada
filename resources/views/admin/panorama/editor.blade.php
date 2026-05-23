<x-app-layout>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <!-- A-Frame and its dependencies must be loaded before the scene is initialized -->
    <script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>
    <script src="https://unpkg.com/aframe-look-at-component@0.8.0/dist/aframe-look-at-component.min.js"></script>

    <div class="h-[calc(100vh-4rem)] flex flex-col bg-gray-100 overflow-hidden" x-data="panoramaEditor({{ $situs->situs_id }})" x-init="init()">
        @include('admin.panorama.partials.header')

        <!-- Main 3-Panel Layout -->
        <div class="flex flex-1 overflow-hidden relative">
            @include('admin.panorama.partials.left-panel')
            @include('admin.panorama.partials.center-panel')
            @include('admin.panorama.partials.right-panel')
        </div>

        @include('admin.panorama.partials.modals')
    </div> <!-- End of x-data panoramaEditor -->

    @include('admin.panorama.partials.scripts')
</x-app-layout>
