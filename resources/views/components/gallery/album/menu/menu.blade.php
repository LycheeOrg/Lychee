<div class="w-full flex justify-center flex-wrap flex-row-reverse" x-cloak x-show="albumFlags.isDetailsOpen" x-collapse.duration.300ms >
    @if($this->flags->is_base_album)
    <ul class="
        sm:mt-7 sm:px-7 mb-4
        text-text-main-200 text-sm w-full xl:w-1/6 xl:px-9
        max-xl:w-full max-xl:flex max-xl:justify-center
        ">
        <x-gallery.album.menu.item tab='0' >{{ __('lychee.ABOUT_ALBUM') }}</x-gallery.album.menu.item>
        @if ($this->rights->can_share_with_users === true && $this->num_users > 1)
        <x-gallery.album.menu.item tab='1' >{{ __('lychee.SHARE_ALBUM') }}</x-gallery.album.menu.item>
        @endif
        @if($this->rights->can_delete === true)
        <x-gallery.album.menu.item tab='2' >{{ __('lychee.MOVE_ALBUM') }}</x-gallery.album.menu.item>
        @endif
        @if($this->rights->can_delete === true)
        <x-gallery.album.menu.danger tab='3' >{{ "DANGER ZONE" }}</x-gallery.album.menu.danger>
        @endif
    </ul>
    @endif
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" x-cloak x-show="albumFlags.activeTab === 0">
        @if($this->flags->is_base_album)
        <livewire:forms.album.properties :album="$this->album" />
        @endif
        <livewire:forms.album.visibility :album="$this->album" />
    </div>
    @if($this->flags->is_base_album) 
    @if ($this->rights->can_share_with_users === true && $this->num_users > 1)
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" x-cloak x-show="albumFlags.activeTab === 1">
        <livewire:forms.album.share-with :album="$this->album" />
    </div>
    @endif
    @if($this->rights->can_delete === true)
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" x-cloak x-show="albumFlags.activeTab === 2">
        <livewire:forms.album.move-panel :album="$this->album" />
    </div>
    @endif
    @if($this->rights->can_delete === true)
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap mb-4 sm:mt-7 pl-7" x-cloak x-show="albumFlags.activeTab === 3">
            {{-- We only display this menu if there are more than 1 user, it does not make sense otherwise --}}
            @if ($this->num_users > 1)
            <livewire:forms.album.transfer :album="$this->album"  lazy />
            @endif
            <livewire:forms.album.delete-panel :album="$this->album" />
    </div>
    @endif
    @endif
</div>
