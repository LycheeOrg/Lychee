<div class="w-full sm:m-7 mb-4 flex justify-center flex-wrap flex-row-reverse">
    <ul class="
        text-neutral-200 text-sm w-full xl:w-1/6 xl:p-9
        max-xl:w-full max-xl:flex max-xl:justify-center
        ">
        <x-gallery.album.menu.item mode='about' current="{{ $mode }}" >{{ __('lychee.ABOUT_ALBUM') }}</x-gallery.album.menu.item>
        <x-gallery.album.menu.item mode='share' current="{{ $mode }}" >{{ __('lychee.SHARE_ALBUM') }}</x-gallery.album.menu.item>
        <x-gallery.album.menu.item mode='move' current="{{ $mode }}" >{{ __('lychee.MOVE_ALBUM') }}</x-gallery.album.menu.item>
        <x-gallery.album.menu.danger mode='danger' current="{{ $mode }}" >{{ "DANGER ZONE" }}</x-gallery.album.menu.danger>
    </ul>
    <div class="w-full xl:w-5/6 flex justify-center flex-wrap">
        @if($mode === 'share')
            <livewire:forms.album.share-with :album="$this->album" />
        @elseif($mode === 'move')
            <livewire:forms.album.move :album="$this->album" />
        @elseif($mode === 'danger')
            @if ($userCount > 1) {{-- We only display this menu if there are more than 1 user, it does not make sense otherwise --}}
            <livewire:forms.album.transfer :album="$this->album" />
            @endif
            <livewire:forms.album.delete :album="$this->album" />
        @else
            <livewire:forms.album.properties :album="$this->album" />
            <livewire:forms.album.visibility :album="$this->album" />
        @endif
    </div>
</div>
