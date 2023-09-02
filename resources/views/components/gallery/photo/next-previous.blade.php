@props(['albumId', 'photo', 'is_next'])
<div @class([
    'absolute w-1/5 h-full top-0 group',
    'right-0' => $is_next,
    'left-0' => !$is_next,
])>
    <a href="{{ route('livewire-gallery-photo', ['albumId' => $albumId, 'photoId' => $photo->id]) }}" id="{{ $is_next ? 'nextButton' : 'previousButton'}}"
        {{ $attributes }}
        @class([
			'absolute top-1/2 border border-solid border-neutral-200 -mt-5 py-2 px-3 transition-all ',
			'-right-px group-hover:translate-x-0 translate-x-full' => $is_next,
			'-left-px group-hover:translate-x-0 -translate-x-full' => !$is_next,
		])
        style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('{{ URL::asset($photo->size_variants->getThumb()->url) }}');">
        <x-icons.iconic icon="caret-{{ $is_next ? 'right' : 'left' }}" class="my-0 h-6 w-5 mr-0 ml-0" />
    </a>
</div>
