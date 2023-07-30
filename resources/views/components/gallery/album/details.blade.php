<div class="w-full px-7 my-4 flex flex-row-reverse">
    <div class="order-1 flex flex-col w-full">
        @if($url === null )
            <h1 class="font-bold text-2xl text-white">{{ $title }}</h1>
        @endif
        @if($created_at !== null)
        <span class="block text-neutral-200 text-sm">
            {{ __('lychee.ALBUM_CREATED') }} {{ $created_at }}
        </span>
        @endif
        @if($num_children > 0)
            <span class="block text-neutral-200 text-sm">
                {{ $num_children }} {{ __('lychee.ALBUM_SUBALBUMS') }}
            </span>
        @endif
        @if($num_photos > 0)
            <span class="block text-neutral-200 text-sm">
                {{ $num_photos }} {{ __('lychee.ALBUM_IMAGES') }}
            </span>
        @endif
    </div>
    @if($can_download)
    <a class="flex-shrink-0 px-3 cursor-pointer"
        title="{{ __('lychee.DOWNLOAD_ALBUM') }}"
        href="{{ route('download', ['albumIDs' => $album_id]) }}" >
        <x-icons.iconic class="my-0 w-4 h-4 mr-0 ml-0" icon="cloud-download" />
    </a>
    @endif
    <a class="flex-shrink-0 px-3 cursor-pointer" title={{ __('lychee.SHARE_ALBUM') }} wire:click="openSharingModal">
        <x-icons.iconic class="my-0 w-4 h-4 mr-0 ml-0" icon="share-ion" />
    </a>
</div>
@if($description !== null)
<div class="w-full px-7 my-4 text-justify text-neutral-200 markdown">
    @markdown{{ $description }}@endmarkdown
</div>
@endif