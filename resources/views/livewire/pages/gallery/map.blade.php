<div class="w-full" x-data="mapView()"
    x-init="init()"
    @keydown.window="handleKeydown(event)"
    >
    <!-- toolbar -->
    <x-header.bar>
        <x-header.back @keydown.escape.window="$wire.back();" />
        <x-header.title>{{ $title }}</x-header.title>
    </x-header.bar>
    <!-- maps -->
    <div id="lychee_map_container"
        class="overlay-container fadeIn active leaflet-container leaflet-touch leaflet-retina leaflet-fade-anim leaflet-grab leaflet-touch-drag leaflet-touch-zoom h-[calc(100vh-56px)] w-full"
        tabindex="0" style="">
    </div>
</div>
