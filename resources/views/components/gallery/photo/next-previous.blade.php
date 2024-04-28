@props(['is_next'])
<div class="absolute w-1/6 h-1/2 top-1/2 -translate-y-1/2 group {{ $is_next ? 'right-0' : 'left-0' }}" x-cloak >
    <a 
    {{-- href="{{ route('livewire-gallery-photo', ['albumId' => $albumId, 'photoId' => $photo->id]) }}"  --}}
        id="{{ $is_next ? 'nextButton' : 'previousButton'}}"
        {{ $attributes }}
        @class([
			'absolute top-1/2 border border-solid border-neutral-200 -mt-5 py-2 px-3 transition-all opacity-0 group-hover:opacity-100 bg-cover',
			'-right-px group-hover:translate-x-0 translate-x-full' => $is_next,
			'-left-px group-hover:translate-x-0 -translate-x-full' => !$is_next,
		])>
        <x-icons.iconic icon="caret-{{ $is_next ? 'right' : 'left' }}" class="my-0 h-6 w-5 mr-0 ml-0" />
    </a>
</div>
