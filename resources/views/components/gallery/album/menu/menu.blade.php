@props(['album', 'userCount' => 0])
<div class="w-full sm:m-7 mb-4 flex justify-center flex-wrap flex-row-reverse"
    x-cloak x-show="albumFlags.isDetailsOpen" x-collapse.duration.300ms >
    <ul class="
        text-neutral-200 text-sm w-full xl:w-1/6 xl:px-9
        max-xl:w-full max-xl:flex max-xl:justify-center
        ">
        <x-gallery.album.menu.item tab='0' >{{ __('lychee.ABOUT_ALBUM') }}</x-gallery.album.menu.item>
        @if ($userCount > 1 && ($album instanceof \App\Models\Album))
        <x-gallery.album.menu.item tab='1' >{{ __('lychee.SHARE_ALBUM') }}</x-gallery.album.menu.item>
        @endif
        @if($album instanceof \App\Models\Album)
        <x-gallery.album.menu.item tab='2' >{{ __('lychee.MOVE_ALBUM') }}</x-gallery.album.menu.item>
        @endif
        <x-gallery.album.menu.danger tab='3' >{{ "DANGER ZONE" }}</x-gallery.album.menu.danger>
    </ul>
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap" x-cloak x-show="albumFlags.activeTab === 0">
        <livewire:forms.album.properties :album="$album" />
        <livewire:forms.album.visibility :album="$album" />
    </div>
    @if ($userCount > 1 && ($album instanceof \App\Models\Album))
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap" x-cloak x-show="albumFlags.activeTab === 1">
        <livewire:forms.album.share-with :album="$album" />
    </div>
    @endif
    @if($album instanceof \App\Models\Album)
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap" x-cloak x-show="albumFlags.activeTab === 2">
        <livewire:forms.album.move-panel :album="$album" />
    </div>
    @endif
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap" x-cloak x-show="albumFlags.activeTab === 3">
            {{-- We only display this menu if there are more than 1 user, it does not make sense otherwise --}}
            @if ($userCount > 1)
            <livewire:forms.album.transfer :album="$album"  lazy />
            @endif
            <livewire:forms.album.delete-panel :album="$album" />
    </div>
</div>
