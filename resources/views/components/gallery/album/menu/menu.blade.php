@props(['album', 'userCount' => 0])
<div class="w-full sm:m-7 mb-4 flex justify-center flex-wrap flex-row-reverse
    "
    x-cloak x-show="detailsOpen" x-data="{ activeTab:  0 }" x-collapse.duration.300ms
    @keydown.window="
        if (event.keyCode === 82 && !detailsOpen) { event.preventDefault(); detailsOpen = true; activeTab = 0; }
        if (event.keyCode === 68 && !detailsOpen) { event.preventDefault(); detailsOpen = true; activeTab = 0; }
        if (event.keyCode === 77 && !detailsOpen) { event.preventDefault(); detailsOpen = true; activeTab = 2; }
    "
    {{-- 82 = r --}}
    {{-- 68 = d --}}
    {{-- 68 = m --}}
    >
    <ul class="
        text-neutral-200 text-sm w-full xl:w-1/6 xl:px-9
        max-xl:w-full max-xl:flex max-xl:justify-center
        ">
        <x-gallery.album.menu.item tab='0' >{{ __('lychee.ABOUT_ALBUM') }}</x-gallery.album.menu.item>
        @if ($userCount > 1)
        <x-gallery.album.menu.item tab='1' >{{ __('lychee.SHARE_ALBUM') }}</x-gallery.album.menu.item>
        @endif
        <x-gallery.album.menu.item tab='2' >{{ __('lychee.MOVE_ALBUM') }}</x-gallery.album.menu.item>
        <x-gallery.album.menu.danger tab='3' >{{ "DANGER ZONE" }}</x-gallery.album.menu.danger>
    </ul>
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap" x-cloak x-show="activeTab === 0">
        <livewire:forms.album.properties :album="$album" />
        <livewire:forms.album.visibility :album="$album" />
    </div>
    @if ($userCount > 1)
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap" x-cloak x-show="activeTab === 1">
        <livewire:forms.album.share-with :album="$album" />
    </div>
    @endif
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap" x-cloak x-show="activeTab === 2">
        <livewire:forms.album.move :album="$album"  lazy />
    </div>
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap" x-cloak x-show="activeTab === 3">
            {{-- We only display this menu if there are more than 1 user, it does not make sense otherwise --}}
            @if ($userCount > 1)
            <livewire:forms.album.transfer :album="$album"  lazy />
            @endif
            <livewire:forms.album.delete :album="$album" />
    </div>
</div>
