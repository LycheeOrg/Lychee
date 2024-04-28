<span
    class="thumbimg absolute w-full h-full
	bg-neutral-800
	shadow-md shadow-black/25
	border-solid border border-neutral-400
	ease-out transition-transform
	{{ $class }}
	">
    <img alt='{{ __('lychee.PHOTO_THUMBNAIL') }}'
		class='w-full h-full m-0 p-0 border-0 lazyload object-cover'
	{!! $src !!}
	{!! $dataSrc !!}
	{!! $dataSrcSet !!}
    data-overlay='false'
    draggable='false' />
</span>
