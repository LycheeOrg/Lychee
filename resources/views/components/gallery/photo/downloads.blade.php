<div class="flex flex-wrap gap-0.5 justify-center align-top text-white/80 p-9">
    @foreach ($size_variants as $size_variant)
        <a class="border border-solid
        border-black/20 cursor-pointer block w-full rounded-md my-1.5 p-3
            font-bold text-center
            duration-500
            bg-dark-400/0
            text-sm
            transition-all
            ease-in-out select-none
            text-sky-500 hover:bg-sky-500 hover:text-white
            fill-sky-500 hover:fill-white"
            href="{{ route('photo_download',['photoIDs' => $photoId, 'kind' => $size_variant['type']->name]) }}"
            >
            <x-icons.iconic icon="cloud-download" fill='' class="my-0 w-3 h-3 mr-1" />
            {{ $size_variant['type']->localized() }} ({{ $size_variant['width'] }}x{{ $size_variant['height'] }},
            {{ $size_variant['filesize'] }})
        </a>
    @endforeach
</div>