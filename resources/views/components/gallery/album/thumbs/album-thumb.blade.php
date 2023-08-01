<span
    class="thumbimg absolute w-full
	bg-neutral-800
	shadow-md shadow-black/25
	border-solid border border-neutral-400
	ease-out transition-transform
	{{ $class }}
	">
    <img alt='Album thumbnail'
	@class(['w-full h-full m-0 p-0 border-0', 'lazyload' => !$isVideo])

	{!! $src !!}
	{!! $dataSrc !!}
	{!! $dataSrcSet !!}
    data-overlay='false'
    draggable='false' />
</span>
