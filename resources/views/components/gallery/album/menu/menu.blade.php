@props(['album', 'rights', 'userCount' => 0])
<div class="w-full flex justify-center flex-wrap flex-row-reverse" x-cloak x-show="albumFlags.isDetailsOpen" x-collapse.duration.300ms >
    <ul class="
        sm:mt-7 sm:px-7 mb-4
        text-text-main-200 text-sm w-full xl:w-1/6 xl:px-9
        max-xl:w-full max-xl:flex max-xl:justify-center
        ">
        <x-gallery.album.menu.item tab='0' >{{ __('lychee.ABOUT_ALBUM') }}</x-gallery.album.menu.item>
        @if ($rights->can_share_with_users === true && $userCount > 1 && ($album instanceof \App\Models\Album))
        <x-gallery.album.menu.item tab='1' >{{ __('lychee.SHARE_ALBUM') }}</x-gallery.album.menu.item>
        @endif
        @if($album instanceof \App\Models\Album && $rights->can_delete === true)
        <x-gallery.album.menu.item tab='2' >{{ __('lychee.MOVE_ALBUM') }}</x-gallery.album.menu.item>
        @endif
        @if($rights->can_delete === true)
        <x-gallery.album.menu.danger tab='3' >{{ "DANGER ZONE" }}</x-gallery.album.menu.danger>
        @endif
    </ul>
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" x-cloak x-show="albumFlags.activeTab === 0">
        <livewire:forms.album.properties :album="$album" />
        <livewire:forms.album.visibility :album="$album" />
    </div>
    @if ($rights->can_share_with_users === true && $userCount > 1 && ($album instanceof \App\Models\Album))
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" x-cloak x-show="albumFlags.activeTab === 1">
        <livewire:forms.album.share-with :album="$album" />
    </div>
    @endif
    @if($album instanceof \App\Models\Album && $rights->can_delete === true)
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" x-cloak x-show="albumFlags.activeTab === 2">
        <livewire:forms.album.move-panel :album="$album" />
    </div>
    @endif
    @if($rights->can_delete === true)
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" x-cloak x-show="albumFlags.activeTab === 3">
            {{-- We only display this menu if there are more than 1 user, it does not make sense otherwise --}}
            @if ($userCount > 1)
            <livewire:forms.album.transfer :album="$album"  lazy />
            @endif
            <livewire:forms.album.delete-panel :album="$album" />
    </div>
    @endif
</div>
