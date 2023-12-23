<div class="w-full" x-data="
    mapView(
    @js($map_provider->getLayer()),
    @js($map_provider->getAtributionHtml()),
    '{{ __('lychee.CAMERA_DATE') }}',
    @js($this->data),
    )"
    x-init="mapInit()"
    >
    <!-- toolbar -->
    <x-header.bar>
        <x-header.back @keydown.escape.window="$wire.back();" wire:click="back" />
        <x-header.title>{{ $title }}</x-header.title>
    </x-header.bar>
    <!-- maps -->
    <div id="lychee_map_container"
        class="
        leaflet-container leaflet-touch leaflet-retina leaflet-fade-anim leaflet-grab leaflet-touch-drag leaflet-touch-zoom
        h-[calc(100vh-56px)] w-full"
        tabindex="0" style="">
    </div>
</div>
